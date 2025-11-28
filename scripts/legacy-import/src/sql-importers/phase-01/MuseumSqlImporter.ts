import { BaseSqlImporter, type ImportResult } from '../base/BaseSqlImporter.js';
import { v4 as uuidv4 } from 'uuid';
import type { Connection } from 'mysql2/promise';
import type { LegacyDatabase } from '../../database/LegacyDatabase.js';
import { mapLanguageCode } from '../../utils/CodeMappings.js';
import { convertHtmlToMarkdown } from '../../utils/HtmlToMarkdownConverter.js';

interface LegacyMuseum {
  museum_id: string;
  country: string;
  name: string;
  city?: string;
  address?: string;
  phone?: string;
  fax?: string;
  email?: string;
  url?: string;
  project_id: string;
  geoCoordinates?: string;
}

interface LegacyMuseumName {
  museum_id: string;
  country: string;
  lang: string;
  name: string;
  ex_name?: string;
  city?: string;
  description?: string;
  ex_description?: string;
  how_to_reach?: string;
  opening_hours?: string;
}

export class MuseumSqlImporter extends BaseSqlImporter {
  private legacyDb: LegacyDatabase;

  constructor(db: Connection, tracker: Map<string, string>, legacyDb: LegacyDatabase) {
    super(db, tracker);
    this.legacyDb = legacyDb;
  }

  getName(): string {
    return 'MuseumSqlImporter';
  }

  async import(): Promise<ImportResult> {
    const result: ImportResult = {
      success: true,
      imported: 0,
      skipped: 0,
      errors: [],
    };

    try {
      this.log('Importing museums...');

      const museums = await this.legacyDb.query<LegacyMuseum>(
        'SELECT * FROM mwnf3.museums ORDER BY museum_id, country'
      );

      const museumNames = await this.legacyDb.query<LegacyMuseumName>(
        'SELECT * FROM mwnf3.museumnames ORDER BY museum_id, country, lang'
      );

      const grouped = this.groupByMuseum(museums, museumNames);
      this.log(`Found ${grouped.length} unique museums`);

      let processed = 0;
      for (const group of grouped) {
        try {
          const success = await this.importMuseum(group);
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
          this.logError(`Failed to import museum ${group.key}`, error);
        }
      }

      console.log('');
      this.logSuccess(`Imported ${result.imported}, skipped ${result.skipped}`);
    } catch (error) {
      result.success = false;
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(message);
      this.logError('Failed to import museums', error);
    }

    return result;
  }

  private groupByMuseum(
    museums: LegacyMuseum[],
    museumNames: LegacyMuseumName[]
  ): Array<{
    key: string;
    museum: LegacyMuseum;
    translations: LegacyMuseumName[];
  }> {
    const translationMap = new Map<string, LegacyMuseumName[]>();
    for (const translation of museumNames) {
      const key = `${translation.museum_id}:${translation.country}`;
      if (!translationMap.has(key)) {
        translationMap.set(key, []);
      }
      translationMap.get(key)!.push(translation);
    }

    return museums.map((museum) => {
      const key = `${museum.museum_id}:${museum.country}`;
      return {
        key,
        museum,
        translations: translationMap.get(key) || [],
      };
    });
  }

  private async importMuseum(group: {
    key: string;
    museum: LegacyMuseum;
    translations: LegacyMuseumName[];
  }): Promise<boolean> {
    const backwardCompat = this.formatBackwardCompat('mwnf3', 'museums', [
      group.museum.museum_id,
      group.museum.country,
    ]);

    if (await this.exists('partners', backwardCompat)) {
      return false;
    }

    // Resolve country
    const countryBackwardCompat = this.formatBackwardCompat('production', 'countries', [
      group.museum.country,
    ]);
    const countryId = await this.findByBackwardCompat('countries', countryBackwardCompat);

    // Create Partner
    const partnerId = uuidv4();
    await this.db.execute(
      `INSERT INTO partners (id, country_id, type, internal_name, backward_compatibility, created_at, updated_at)
       VALUES (?, ?, 'museum', ?, ?, ?, ?)`,
      [partnerId, countryId, group.museum.museum_id, backwardCompat, this.now, this.now]
    );

    this.tracker.set(backwardCompat, partnerId);

    // Create translations
    for (const translation of group.translations) {
      await this.importTranslation(partnerId, group.museum, translation);
    }

    return true;
  }

  private async importTranslation(
    partnerId: string,
    museum: LegacyMuseum,
    translation: LegacyMuseumName
  ): Promise<void> {
    const languageId = mapLanguageCode(translation.lang);
    const name = translation.name?.trim();
    if (!name) return;

    const nameMarkdown = convertHtmlToMarkdown(name);
    const alternateNameMarkdown = translation.ex_name
      ? convertHtmlToMarkdown(translation.ex_name)
      : null;
    const descriptionMarkdown = translation.description
      ? convertHtmlToMarkdown(translation.description)
      : null;

    // Build extra field
    const extra: Record<string, string> = {};
    if (museum.phone) extra.phone = museum.phone;
    if (museum.fax) extra.fax = museum.fax;
    if (museum.email) extra.email = museum.email;
    if (museum.url) extra.url = museum.url;
    if (translation.opening_hours) extra.opening_hours = translation.opening_hours;
    if (translation.how_to_reach) extra.how_to_reach = translation.how_to_reach;
    if (museum.geoCoordinates) extra.geoCoordinates = museum.geoCoordinates;
    if (translation.city) extra.city = translation.city;
    if (translation.ex_description) extra.ex_description = translation.ex_description;
    const extraJson = Object.keys(extra).length > 0 ? JSON.stringify(extra) : null;

    const translationId = uuidv4();
    await this.db.execute(
      `INSERT INTO partner_translations (id, partner_id, language_id, name, alternate_name, description, address, extra, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        translationId,
        partnerId,
        languageId,
        nameMarkdown,
        alternateNameMarkdown,
        descriptionMarkdown,
        museum.address,
        extraJson,
        this.now,
        this.now,
      ]
    );
  }
}
