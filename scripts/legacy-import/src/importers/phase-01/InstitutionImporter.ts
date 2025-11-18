import { BaseImporter, ImportResult } from '../BaseImporter.js';
import { BackwardCompatibilityFormatter } from '../../utils/BackwardCompatibilityFormatter.js';

/**
 * Imports institutions from mwnf3.institutions and mwnf3.institutionnames
 * Maps to Partner model with type='institution'
 */
export class InstitutionImporter extends BaseImporter {
  getName(): string {
    return 'InstitutionImporter';
  }

  async import(): Promise<ImportResult> {
    const result: ImportResult = {
      success: true,
      imported: 0,
      skipped: 0,
      errors: [],
    };

    try {
      // Query institutions
      const institutions = await this.context.legacyDb.query<LegacyInstitution>(
        'SELECT * FROM mwnf3.institutions'
      );

      // Query institution translations
      const institutionNames = await this.context.legacyDb.query<LegacyInstitutionName>(
        'SELECT * FROM mwnf3.institutionnames'
      );

      if (institutions.length === 0) {
        this.log('No institutions found');
        return result;
      }

      // Group translations by institution_id
      const translationsByInstitution = new Map<string, LegacyInstitutionName[]>();
      for (const name of institutionNames) {
        const key = name.institution_id;
        if (!translationsByInstitution.has(key)) {
          translationsByInstitution.set(key, []);
        }
        translationsByInstitution.get(key)!.push(name);
      }

      // Import each institution
      for (const institution of institutions) {
        try {
          const translations = translationsByInstitution.get(institution.institution_id) || [];
          const imported = await this.importInstitution(institution, translations);
          if (imported) {
            result.imported++;
            this.showProgress();
          } else {
            result.skipped++;
            this.showSkipped();
          }
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(`${institution.institution_id}: ${message}`);
          this.showError();
        }
      }
      console.log(''); // New line after progress dots
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to query institutions: ${message}`);
      result.success = false;
    }

    result.success = result.errors.length === 0;
    return result;
  }

  private async importInstitution(
    institution: LegacyInstitution,
    translations: LegacyInstitutionName[]
  ): Promise<boolean> {
    // Format backward_compatibility
    const backwardCompat = BackwardCompatibilityFormatter.format({
      schema: 'mwnf3',
      table: 'institutions',
      pkValues: [institution.institution_id],
    });

    // Check if already imported
    if (this.context.tracker.exists(backwardCompat)) {
      return false;
    }

    if (this.context.dryRun) {
      this.log(`[DRY-RUN] Would import institution: ${institution.institution_id}`);
      return true;
    }

    // Create Partner
    const partnerResponse = await this.context.apiClient.partner.partnerStore({
      internal_name: institution.institution_id,
      type: 'institution',
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

    this.log(
      `Imported institution: ${institution.name} (${institution.institution_id}:${institution.country}) → ${partnerId}`
    );
    return true;
  }

  private async importTranslation(partnerId: string, translation: LegacyInstitutionName) {
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

interface LegacyInstitution {
  institution_id: string;
  country: string;
  name: string;
  city?: string;
  address?: string;
}

interface LegacyInstitutionName {
  institution_id: string;
  language: string;
  name: string;
  description?: string;
}
