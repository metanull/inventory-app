import { BaseImporter, ImportResult } from '../BaseImporter.js';
import { BackwardCompatibilityFormatter } from '../../utils/BackwardCompatibilityFormatter.js';
import { mapLanguageCode, mapCountryCode } from '../../utils/CodeMappings.js';

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
        `SELECT * FROM mwnf3.institutions`
      );

      // Query institution translations
      const institutionNames = await this.context.legacyDb.query<LegacyInstitutionName>(
        'SELECT * FROM mwnf3.institutionnames'
      );

      if (institutions.length === 0) {
        this.logInfo('No institutions found');
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
          // Log detailed error info
          if (error && typeof error === 'object' && 'response' in error) {
            const axiosError = error as { response?: { status?: number; data?: unknown } };
            this.logError(
              `InstitutionImporter:${institution.institution_id}`,
              error instanceof Error ? error : new Error(message),
              { responseData: axiosError.response?.data }
            );
          }
          result.errors.push(`${institution.institution_id}: ${message}`);
          this.showError();
        }
      }
      this.showSummary(result.imported, result.skipped, result.errors.length);
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
    // Format backward_compatibility with ALL PK fields (institution_id + country)
    const backwardCompat = BackwardCompatibilityFormatter.format({
      schema: 'mwnf3',
      table: 'institutions',
      pkValues: [institution.institution_id, institution.country],
    });

    // Check if already imported
    if (this.context.tracker.exists(backwardCompat)) {
      return false;
    }

    if (this.context.dryRun) {
      this.logInfo(`[DRY-RUN] Would import institution: ${institution.institution_id}`);
      return true;
    }

    // Map 2-character country code to 3-character code
    const countryId = institution.country ? mapCountryCode(institution.country) : undefined;

    // Try to create Partner (may already exist from previous import run)
    let partnerId: string | undefined = undefined;
    try {
      const partnerResponse = await this.context.apiClient.partner.partnerStore({
        internal_name: institution.name || institution.institution_id, // Fallback to ID if name is empty
        type: 'institution',
        country_id: countryId,
        visible: true,
        backward_compatibility: backwardCompat,
      });
      partnerId = partnerResponse.data.data.id;
    } catch (error) {
      // If 422 conflict, check if it's a backward_compatibility duplicate or other validation error
      if (error && typeof error === 'object' && 'response' in error) {
        const axiosError = error as { response?: { status?: number; data?: any } };
        if (axiosError.response?.status === 422) {
          const responseData = axiosError.response?.data;
          const errorMessage = responseData?.message || '';
          
          // Only try to find existing partner if error is about backward_compatibility duplicate
          if (errorMessage.includes('backward_compatibility') || errorMessage.includes('already been taken')) {
            // Paginate through all results to find the existing partner
            let found = false;
            let page = 1;
            const perPage = 100;
            
            while (!found) {
              const partnersPage = await this.context.apiClient.partner.partnerIndex(
                page,
                perPage,
                undefined
              );
              
              const existing = partnersPage.data.data.find(
                (p) => p.backward_compatibility === backwardCompat
              );
              
              if (existing) {
                partnerId = existing.id;
                found = true;
              } else if (partnersPage.data.data.length < perPage) {
                // Reached last page without finding partner
                throw new Error(
                  `Partner conflict but not found in API: ${institution.institution_id}:${institution.country}`
                );
              } else {
                page++;
              }
            }
          } else {
            // Other validation error - re-throw with details
            throw error;
          }
        } else {
          throw error;
        }
      } else {
        throw error;
      }
    }

    // Ensure partnerId was successfully obtained
    if (!partnerId) {
      throw new Error(
        `Failed to get Partner ID for institution ${institution.institution_id}:${institution.country}`
      );
    }

    // Register in tracker
    this.context.tracker.register({
      uuid: partnerId,
      backwardCompatibility: backwardCompat,
      entityType: 'partner',
      createdAt: new Date(),
    });

    // Create translations
    for (const translation of translations) {
      await this.importTranslation(partnerId, institution, translation);
    }

    return true;
  }

  private async importTranslation(
    partnerId: string,
    institution: LegacyInstitution,
    translation: LegacyInstitutionName
  ) {
    // Map legacy ISO 639-1 to ISO 639-3
    const languageId = mapLanguageCode(translation.lang);

    // Institutions don't have project_id - always use default context
    const contextId = this.context.tracker.getUuid('__default_context__');

    if (!contextId) {
      throw new Error('Default context not found in tracker');
    }

    // Build contact notes with contact persons info
    const contactParts: string[] = [];
    if (institution.cp1_name) {
      contactParts.push(
        `Contact 1: ${institution.cp1_name}${institution.cp1_title ? ` (${institution.cp1_title})` : ''}${
          institution.cp1_phone ? ` - Phone: ${institution.cp1_phone}` : ''
        }${institution.cp1_fax ? ` - Fax: ${institution.cp1_fax}` : ''}${
          institution.cp1_email ? ` - Email: ${institution.cp1_email}` : ''
        }`
      );
    }
    if (institution.cp2_name) {
      contactParts.push(
        `Contact 2: ${institution.cp2_name}${institution.cp2_title ? ` (${institution.cp2_title})` : ''}${
          institution.cp2_phone ? ` - Phone: ${institution.cp2_phone}` : ''
        }${institution.cp2_fax ? ` - Fax: ${institution.cp2_fax}` : ''}${
          institution.cp2_email ? ` - Email: ${institution.cp2_email}` : ''
        }`
      );
    }
    if (institution.fax) {
      contactParts.push(`Fax: ${institution.fax}`);
    }
    if (institution.url2) {
      contactParts.push(`Additional URL: ${institution.url2}`);
    }

    const contactNotes = contactParts.length > 0 ? contactParts.join('\n') : null;

    try {
      await this.context.apiClient.partnerTranslation.partnerTranslationStore({
        partner_id: partnerId,
        language_id: languageId,
        context_id: contextId,
        name: translation.name,
        description: translation.description || null,
        // Address fields
        city_display: institution.city || null,
        address_line_1: institution.address || null,
        // Contact fields
        contact_phone: institution.phone || null,
        contact_email_general: institution.email || null,
        contact_website: institution.url || null,
        contact_notes: contactNotes,
      });
    } catch (error) {
      // If 422 conflict, translation already exists - skip silently
      if (error && typeof error === 'object' && 'response' in error) {
        const axiosError = error as { response?: { status?: number } };
        if (axiosError.response?.status === 422) {
          return; // Translation already exists, skip
        }
      }
      throw error;
    }
  }
}

interface LegacyInstitution {
  institution_id: string;
  country: string;
  name: string;
  city: string | null;
  address: string | null;
  description: string | null;
  phone: string | null;
  fax: string | null;
  email: string | null;
  url: string | null;
  url2: string | null;
  cp1_name: string | null;
  cp1_title: string | null;
  cp1_phone: string | null;
  cp1_fax: string | null;
  cp1_email: string | null;
  cp2_name: string | null;
  cp2_title: string | null;
  cp2_phone: string | null;
  cp2_fax: string | null;
  cp2_email: string | null;
  logo: string | null;
}

interface LegacyInstitutionName {
  institution_id: string;
  country: string;
  lang: string;
  name: string;
  description: string | null;
}
