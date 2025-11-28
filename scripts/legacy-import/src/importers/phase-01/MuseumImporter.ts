import { BaseImporter, ImportResult } from '../BaseImporter.js';
import { BackwardCompatibilityFormatter } from '../../utils/BackwardCompatibilityFormatter.js';
import { mapLanguageCode, mapCountryCode } from '../../utils/CodeMappings.js';

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
        `SELECT * FROM mwnf3.museums`
      );

      // Query museum translations
      const museumNames = await this.context.legacyDb.query<LegacyMuseumName>(
        'SELECT * FROM mwnf3.museumnames'
      );

      if (museums.length === 0) {
        this.logInfo('No museums found');
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
            this.showSkipped();
          }
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          // FULL compound PK for debugging
          const fullId = `${museum.museum_id}:${museum.country}`;
          this.logError(`Failed to import museum`, error, {
            museum_id: museum.museum_id,
            country: museum.country,
            full_pk: fullId,
            project_id: museum.project_id,
          });
          result.errors.push(`${fullId}: ${message}`);
          this.showError();
        }
      }
      this.showSummary(result.imported, result.skipped, result.errors.length);
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
    // Format backward_compatibility with ALL PK fields (museum_id + country)
    const backwardCompat = BackwardCompatibilityFormatter.format({
      schema: 'mwnf3',
      table: 'museums',
      pkValues: [museum.museum_id, museum.country],
    });

    // Check if already imported (in current session)
    if (this.context.tracker.exists(backwardCompat)) {
      return false;
    }

    // Collect sample for testing (BEFORE dependency resolution)
    this.collectSample('partner_museum', museum as unknown as Record<string, unknown>, 'success');

    if (this.context.dryRun || this.isSampleOnlyMode) {
      this.logInfo(
        `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would import museum: ${museum.museum_id}`
      );

      if (this.isSampleOnlyMode) {
        // Register fake partner ID
        const fakePartnerId = `sample-partner-${museum.museum_id}-${museum.country}`;
        this.context.tracker.register({
          uuid: fakePartnerId,
          backwardCompatibility: backwardCompat,
          entityType: 'partner',
          createdAt: new Date(),
        });
      }
      return true;
    }

    // Resolve project_id to Project UUID via tracker
    // Note: Legacy projects map to BOTH Context and Project in new system
    const projectBackwardCompat =
      BackwardCompatibilityFormatter.format({
        schema: 'mwnf3',
        table: 'projects',
        pkValues: [museum.project_id],
      }) + ':project'; // Use :project suffix to get the Project UUID, not Context UUID

    const projectId = this.context.tracker.getUuid(projectBackwardCompat);

    // If project not found, skip this museum
    if (!projectId) {
      this.logWarning(
        `Skipping museum ${museum.museum_id}:${museum.country} - project '${museum.project_id}' not found in tracker`,
        { museum_id: museum.museum_id, country: museum.country, project_id: museum.project_id }
      );
      return false; // Skipped
    }

    // Parse GPS coordinates (format: \"lat,lng\")
    let latitude: number | undefined = undefined;
    let longitude: number | undefined = undefined;
    if (museum.geoCoordinates) {
      const coords = museum.geoCoordinates.split(',').map((s) => parseFloat(s.trim()));
      if (
        coords.length >= 2 &&
        coords[0] !== undefined &&
        coords[1] !== undefined &&
        !isNaN(coords[0]) &&
        !isNaN(coords[1])
      ) {
        latitude = coords[0];
        longitude = coords[1];
      }
    }

    // Map 2-character country code to 3-character code
    const countryId = museum.country ? mapCountryCode(museum.country) : undefined;

    // Try to create Partner (may already exist from previous import run)
    let partnerId: string | undefined = undefined;
    try {
      const partnerResponse = await this.context.apiClient.partner.partnerStore({
        internal_name: museum.name,
        type: 'museum',
        country_id: countryId,
        project_id: projectId, // Use Project UUID, not Context UUID
        latitude: latitude,
        longitude: longitude,
        map_zoom: museum.zoom ? parseInt(museum.zoom) : undefined,
        visible: true,
        backward_compatibility: backwardCompat,
      });
      partnerId = partnerResponse.data.data.id;
    } catch (error) {
      // If 422 conflict, check if it's a backward_compatibility duplicate or other validation error
      if (error && typeof error === 'object' && 'response' in error) {
        const axiosError = error as { response?: { status?: number; data?: unknown } };
        if (axiosError.response?.status === 422) {
          const responseData = axiosError.response?.data as { message?: string } | undefined;
          const errorMessage = responseData?.message || '';

          // Only try to find existing partner if error is about backward_compatibility duplicate
          if (
            errorMessage.includes('backward_compatibility') ||
            errorMessage.includes('already been taken')
          ) {
            // Query API to find existing Partner by backward_compatibility
            // Paginate through all results since there may be more than 100 partners
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
                  `Partner conflict but not found in API: ${museum.museum_id}:${museum.country}`
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
          throw error; // Re-throw non-422 errors
        }
      } else {
        throw error;
      }
    }

    // Ensure partnerId was successfully obtained
    if (!partnerId) {
      throw new Error(`Failed to get Partner ID for museum ${museum.museum_id}:${museum.country}`);
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
      await this.importTranslation(partnerId, museum, translation);
    }

    return true;
  }

  private async importTranslation(
    partnerId: string,
    museum: LegacyMuseum,
    translation: LegacyMuseumName
  ) {
    // Map legacy ISO 639-1 to ISO 639-3
    const languageId = mapLanguageCode(translation.lang);

    // Resolve project_id to context_id via tracker
    const contextBackwardCompat = BackwardCompatibilityFormatter.format({
      schema: 'mwnf3',
      table: 'projects',
      pkValues: [museum.project_id],
    });
    const contextId = this.context.tracker.getUuid(contextBackwardCompat);

    if (!contextId) {
      throw new Error(`Context not found for project ${museum.project_id}`);
    }

    try {
      await this.context.apiClient.partnerTranslation.partnerTranslationStore({
        partner_id: partnerId,
        language_id: languageId,
        context_id: contextId,
        name: translation.name,
        description: translation.description || null,
        // Address fields
        city_display: translation.city || null,
        address_line_1: museum.address || null,
        postal_code: museum.postal_address || null,
        address_notes: translation.how_to_reach || null,
        // Contact fields
        contact_phone: museum.phone || null,
        contact_email_general: museum.email || null,
        contact_website: museum.url || null,
        contact_notes: translation.opening_hours || null,
      });
    } catch (error) {
      // If 422 conflict, translation already exists - skip silently
      if (error && typeof error === 'object' && 'response' in error) {
        const axiosError = error as { response?: { status?: number } };
        if (axiosError.response?.status === 422) {
          // Translation already exists, skip
          return;
        }
      }
      throw error; // Re-throw non-422 errors
    }
  }
}

interface LegacyMuseum {
  museum_id: string;
  country: string;
  name: string;
  city: string | null;
  address: string | null;
  postal_address: string | null;
  phone: string | null;
  fax: string | null;
  email: string | null;
  email2: string | null;
  url: string | null;
  url2: string | null;
  url3: string | null;
  url4: string | null;
  url5: string | null;
  title1: string | null;
  title2: string | null;
  title3: string | null;
  title4: string | null;
  title5: string | null;
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
  project_id: string;
  geoCoordinates: string | null;
  zoom: string | null;
}

interface LegacyMuseumName {
  museum_id: string;
  country: string;
  lang: string;
  name: string;
  ex_name: string | null;
  city: string | null;
  description: string | null;
  ex_description: string | null;
  how_to_reach: string | null;
  opening_hours: string | null;
}
