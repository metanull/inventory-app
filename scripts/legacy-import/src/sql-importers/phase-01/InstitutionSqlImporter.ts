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

    // Resolve country
    const countryBackwardCompat = this.formatBackwardCompat('production', 'countries', [
      group.institution.country,
    ]);
    const countryId = await this.findByBackwardCompat('countries', countryBackwardCompat);

    // Create Partner
    const partnerId = uuidv4();
    await this.db.execute(
      `INSERT INTO partners (id, country_id, type, internal_name, backward_compatibility, created_at, updated_at)
       VALUES (?, ?, 'institution', ?, ?, ?, ?)`,
      [partnerId, countryId, group.institution.institution_id, backwardCompat, this.now, this.now]
    );

    this.tracker.set(backwardCompat, partnerId);

    // Create translations
    for (const translation of group.translations) {
      await this.importTranslation(partnerId, group.institution, translation);
    }

    return true;
  }

  private async importTranslation(
    partnerId: string,
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
    if (institution.phone) extra.phone = institution.phone;
    if (institution.fax) extra.fax = institution.fax;
    if (institution.email) extra.email = institution.email;
    if (institution.url) extra.url = institution.url;
    if (institution.city) extra.city = institution.city;
    const extraJson = Object.keys(extra).length > 0 ? JSON.stringify(extra) : null;

    const translationId = uuidv4();
    await this.db.execute(
      `INSERT INTO partner_translations (id, partner_id, language_id, name, alternate_name, description, address, extra, created_at, updated_at)
       VALUES (?, ?, ?, ?, NULL, ?, ?, ?, ?, ?)`,
      [
        translationId,
        partnerId,
        languageId,
        nameMarkdown,
        descriptionMarkdown,
        institution.address,
        extraJson,
        this.now,
        this.now,
      ]
    );
  }
}
