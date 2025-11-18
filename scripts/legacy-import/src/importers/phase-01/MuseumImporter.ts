import { BaseImporter, ImportResult } from '../BaseImporter.js';
import { BackwardCompatibilityFormatter } from '../../utils/BackwardCompatibilityFormatter.js';

/**
 * Imports museums from mwnf3.museums and mwnf3.museumnames
 * Maps to Partner model with type='museum'
 */
export class MuseumImporter extends BaseImporter {
  getName(): string {
    return 'MuseumImporter';
  }

  async import(): Promise<ImportResult> {
    const result: ImportResult = {
      success: true,
      imported: 0,
      skipped: 0,
      errors: [],
    };

    try {
      // Query museums
      const museums = await this.context.legacyDb.query<LegacyMuseum>(
        'SELECT * FROM mwnf3.museums'
      );

      // Query museum translations
      const museumNames = await this.context.legacyDb.query<LegacyMuseumName>(
        'SELECT * FROM mwnf3.museumnames'
      );

      if (museums.length === 0) {
        this.log('No museums found');
        return result;
      }

      // Group translations by museum_id
      const translationsByMuseum = new Map<string, LegacyMuseumName[]>();
      for (const name of museumNames) {
        const key = name.museum_id;
        if (!translationsByMuseum.has(key)) {
          translationsByMuseum.set(key, []);
        }
        translationsByMuseum.get(key)!.push(name);
      }

      // Import each museum
      for (const museum of museums) {
        try {
          const translations = translationsByMuseum.get(museum.museum_id) || [];
          const imported = await this.importMuseum(museum, translations);
          if (imported) {
            result.imported++;
            this.showProgress();
          } else {
            result.skipped++;
          }
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(`${museum.museum_id}: ${message}`);
        }
      }
      console.log(''); // New line after progress dots
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to query museums: ${message}`);
      result.success = false;
    }

    result.success = result.errors.length === 0;
    return result;
  }

  private async importMuseum(
    museum: LegacyMuseum,
    translations: LegacyMuseumName[]
  ): Promise<boolean> {
    // Format backward_compatibility
    const backwardCompat = BackwardCompatibilityFormatter.format({
      schema: 'mwnf3',
      table: 'museums',
      pkValues: [museum.museum_id],
    });

    // Check if already imported
    if (this.context.tracker.exists(backwardCompat)) {
      this.log(`Skipping museum ${museum.museum_id} - already imported`);
      return false;
    }

    if (this.context.dryRun) {
      this.log(`[DRY-RUN] Would import museum: ${museum.museum_id}`);
      return true;
    }

    // Create Partner
    const partnerResponse = await this.context.apiClient.partner.partnerStore({
      internal_name: museum.museum_id,
      type: 'museum',
      backward_compatibility: backwardCompat,
    });

    const partnerId = partnerResponse.data.data.id;

    // Register in tracker
    this.context.tracker.register({
      uuid: partnerId,
      backwardCompatibility: backwardCompat,
      entityType: 'partner',
      createdAt: new Date(),
    });

    // Create translations
    for (const translation of translations) {
      await this.importTranslation(partnerId, translation);
    }

    this.log(`Imported museum: ${museum.museum_id} → ${partnerId}`);
    return true;
  }

  private async importTranslation(partnerId: string, translation: LegacyMuseumName) {
    // Map legacy ISO 639-1 to ISO 639-3
    const languageId = this.mapLanguageCode(translation.language);

    // Get default context - partners need context_id for translations
    const contextResponse = await this.context.apiClient.context.contextGetDefault();
    const contextId = contextResponse.data.data.id;

    await this.context.apiClient.partnerTranslation.partnerTranslationStore({
      partner_id: partnerId,
      language_id: languageId,
      context_id: contextId,
      name: translation.name,
      description: translation.description || null,
    });
  }

  /**
   * Map legacy 2-character ISO 639-1 codes to 3-character ISO 639-3 codes
   */
  private mapLanguageCode(legacyCode: string): string {
    const mapping: Record<string, string> = {
      en: 'eng',
      fr: 'fra',
      es: 'spa',
      de: 'deu',
      it: 'ita',
      pt: 'por',
      ar: 'ara',
      ru: 'rus',
      zh: 'zho',
      ja: 'jpn',
    };

    return mapping[legacyCode] || legacyCode;
  }
}

interface LegacyMuseum {
  museum_id: string;
  country: string;
  city?: string;
  address?: string;
}

interface LegacyMuseumName {
  museum_id: string;
  language: string;
  name: string;
  description?: string;
}
