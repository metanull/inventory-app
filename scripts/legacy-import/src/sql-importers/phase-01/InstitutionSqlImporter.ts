import { BaseSqlImporter, type ImportResult } from '../base/BaseSqlImporter.js';
import { v4 as uuidv4 } from 'uuid';
import type { Connection } from 'mysql2/promise';
import type { LegacyDatabase } from '../../database/LegacyDatabase.js';
import { mapLanguageCode } from '../../utils/CodeMappings.js';
import { convertHtmlToMarkdown } from '../../utils/HtmlToMarkdownConverter.js';

interface LegacyInstitution {
  institution_id: string;
  country: string;
  name: string;
  city?: string;
  address?: string;
  phone?: string;
  fax?: string;
  email?: string;
  url?: string;
}

interface LegacyInstitutionName {
  institution_id: string;
  country: string;
  lang: string;
  name: string;
  description?: string;
}

export class InstitutionSqlImporter extends BaseSqlImporter {
  private legacyDb: LegacyDatabase;

  constructor(db: Connection, tracker: Map<string, string>, legacyDb: LegacyDatabase) {
    super(db, tracker);
    this.legacyDb = legacyDb;
  }

  getName(): string {
    return 'InstitutionSqlImporter';
  }

  async import(): Promise<ImportResult> {
    const result: ImportResult = {
      success: true,
      imported: 0,
      skipped: 0,
      errors: [],
    };

    try {
      this.log('Importing institutions...');

      const institutions = await this.legacyDb.query<LegacyInstitution>(
        'SELECT * FROM mwnf3.institutions ORDER BY institution_id, country'
      );

      const institutionNames = await this.legacyDb.query<LegacyInstitutionName>(
        'SELECT * FROM mwnf3.institutionnames ORDER BY institution_id, country, lang'
      );

      const grouped = this.groupByInstitution(institutions, institutionNames);
      this.log(`Found ${grouped.length} unique institutions`);

      let processed = 0;
      for (const group of grouped) {
        try {
          const success = await this.importInstitution(group);
          if (success) {
            result.imported++;
          } else {
            result.skipped++;
          }
          processed++;
          this.showProgress(processed, grouped.length);
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(`${group.key}: ${message}`);
          this.logError(`Failed to import institution ${group.key}`, error);
        }
      }

      console.log('');
      this.logSuccess(`Imported ${result.imported}, skipped ${result.skipped}`);
    } catch (error) {
      result.success = false;
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(message);
      this.logError('Failed to import institutions', error);
    }

    return result;
  }

  private groupByInstitution(
    institutions: LegacyInstitution[],
    institutionNames: LegacyInstitutionName[]
  ): Array<{
    key: string;
    institution: LegacyInstitution;
    translations: LegacyInstitutionName[];
  }> {
    const translationMap = new Map<string, LegacyInstitutionName[]>();
    for (const translation of institutionNames) {
      const key = `${translation.institution_id}:${translation.country}`;
      if (!translationMap.has(key)) {
        translationMap.set(key, []);
      }
      translationMap.get(key)!.push(translation);
    }

    return institutions.map((institution) => {
      const key = `${institution.institution_id}:${institution.country}`;
      return {
        key,
        institution,
        translations: translationMap.get(key) || [],
      };
    });
  }

  private async importInstitution(group: {
    key: string;
    institution: LegacyInstitution;
    translations: LegacyInstitutionName[];
  }): Promise<boolean> {
    const backwardCompat = this.formatBackwardCompat('mwnf3', 'institutions', [
      group.institution.institution_id,
      group.institution.country,
    ]);

    if (await this.exists('partners', backwardCompat)) {
      return false;
    }

    // Get or create default context for partners
    const defaultContextId = await this.getOrCreateDefaultContext();

    // Create Partner (no country_id in schema)
    const partnerId = uuidv4();
    const internalName = group.institution.name
      ? convertHtmlToMarkdown(group.institution.name)
      : group.institution.institution_id;
    await this.db.execute(
      `INSERT INTO partners (id, type, internal_name, backward_compatibility, created_at, updated_at)
       VALUES (?, 'institution', ?, ?, ?, ?)`,
      [partnerId, internalName, backwardCompat, this.now, this.now]
    );

    this.tracker.set(backwardCompat, partnerId);

    // Create translations
    for (const translation of group.translations) {
      await this.importTranslation(partnerId, defaultContextId, group.institution, translation);
    }

    return true;
  }

  private async importTranslation(
    partnerId: string,
    contextId: string,
    institution: LegacyInstitution,
    translation: LegacyInstitutionName
  ): Promise<void> {
    const languageId = mapLanguageCode(translation.lang);
    const name =
      translation.name?.trim() || institution.name || `Institution ${institution.institution_id}`;

    const nameMarkdown = convertHtmlToMarkdown(name);
    const descriptionMarkdown = translation.description
      ? convertHtmlToMarkdown(translation.description)
      : null;

    // Build extra field
    const extra: Record<string, string> = {};
    if (institution.address) extra.address_legacy = institution.address;
    if (institution.city) extra.city_legacy = institution.city;
    if (institution.country) extra.country_code = institution.country;
    const extraJson = Object.keys(extra).length > 0 ? JSON.stringify(extra) : null;

    const translationId = uuidv4();
    await this.db.execute(
      `INSERT INTO partner_translations (id, partner_id, language_id, context_id, name, description, city_display, contact_website, contact_phone, contact_email_general, extra, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        translationId,
        partnerId,
        languageId,
        contextId,
        nameMarkdown,
        descriptionMarkdown,
        institution.city,
        institution.url,
        institution.phone,
        institution.email,
        extraJson,
        this.now,
        this.now,
      ]
    );
  }

  private async getOrCreateDefaultContext(): Promise<string> {
    const backwardCompat = 'system:default_partner_context';
    const existing = await this.findByBackwardCompat('contexts', backwardCompat);
    if (existing) return existing;

    const contextId = uuidv4();
    await this.db.execute(
      `INSERT INTO contexts (id, internal_name, backward_compatibility, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?)`,
      [contextId, 'default_partners', backwardCompat, this.now, this.now]
    );
    this.tracker.set(backwardCompat, contextId);
    return contextId;
  }
}
