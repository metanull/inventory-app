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
      const limitClause = this.context.limit > 0 ? ` LIMIT ${this.context.limit}` : '';
      const museums = await this.context.legacyDb.query<LegacyMuseum>(
        `SELECT * FROM mwnf3.museums${limitClause}`
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
            this.showSkipped();
          }
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          // Log detailed error info
          if (error && typeof error === 'object' && 'response' in error) {
            const axiosError = error as { response?: { status?: number; data?: unknown } };
            this.log(`Error importing ${museum.museum_id}: ${message}`);
            this.log(`Response: ${JSON.stringify(axiosError.response?.data)}`);
          }
          result.errors.push(`${museum.museum_id}: ${message}`);
          this.showError();
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
    // Format backward_compatibility with ALL PK fields (museum_id + country)
    const backwardCompat = BackwardCompatibilityFormatter.format({
      schema: 'mwnf3',
      table: 'museums',
      pkValues: [museum.museum_id, museum.country],
    });

    // Check if already imported
    if (this.context.tracker.exists(backwardCompat)) {
      return false;
    }

    if (this.context.dryRun) {
      this.log(`[DRY-RUN] Would import museum: ${museum.museum_id}`);
      return true;
    }

    // Resolve project_id to context_id via tracker
    const contextBackwardCompat = BackwardCompatibilityFormatter.format({
      schema: 'mwnf3',
      table: 'projects',
      pkValues: [museum.project_id],
    });
    const projectContextId = this.context.tracker.getUuid(contextBackwardCompat);

    // Parse GPS coordinates (format: \"lat,lng\")
    let latitude: number | undefined = undefined;
    let longitude: number | undefined = undefined;
    if (museum.geoCoordinates) {
      const coords = museum.geoCoordinates.split(',').map(s => parseFloat(s.trim()));
      if (coords.length >= 2 && coords[0] !== undefined && coords[1] !== undefined && !isNaN(coords[0]) && !isNaN(coords[1])) {
        latitude = coords[0];
        longitude = coords[1];
      }
    }

    // Map 2-character country code to 3-character code
    const countryId = museum.country ? this.mapCountryCode(museum.country) : undefined;

    // Create Partner
    const partnerResponse = await this.context.apiClient.partner.partnerStore({
      internal_name: museum.name,
      type: 'museum',
      country_id: countryId,
      project_id: projectContextId || undefined,
      latitude: latitude,
      longitude: longitude,
      map_zoom: museum.zoom ? parseInt(museum.zoom) : undefined,
      visible: true,
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
    const languageId = this.mapLanguageCode(translation.lang);

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

  /**
   * Map legacy 2-character ISO 3166-1 alpha-2 codes to 3-character ISO 3166-1 alpha-3 codes
   */
  private mapCountryCode(legacyCode: string): string {
    const mapping: Record<string, string> = {
      ae: 'are', // United Arab Emirates
      al: 'alb', // Albania
      ar: 'arg', // Argentina
      at: 'aut', // Austria
      au: 'aus', // Australia
      ba: 'bih', // Bosnia and Herzegovina
      be: 'bel', // Belgium
      bg: 'bgr', // Bulgaria
      br: 'bra', // Brazil
      ca: 'can', // Canada
      ch: 'che', // Switzerland
      cn: 'chn', // China
      cy: 'cyp', // Cyprus
      cz: 'cze', // Czech Republic
      de: 'deu', // Germany
      dk: 'dnk', // Denmark
      dz: 'dza', // Algeria
      eg: 'egy', // Egypt
      es: 'esp', // Spain
      fi: 'fin', // Finland
      fr: 'fra', // France
      gb: 'gbr', // United Kingdom
      gr: 'grc', // Greece
      hr: 'hrv', // Croatia
      hu: 'hun', // Hungary
      ie: 'irl', // Ireland
      il: 'isr', // Israel
      in: 'ind', // India
      iq: 'irq', // Iraq
      ir: 'irn', // Iran
      it: 'ita', // Italy
      jo: 'jor', // Jordan
      jp: 'jpn', // Japan
      kw: 'kwt', // Kuwait
      lb: 'lbn', // Lebanon
      ly: 'lby', // Libya
      ma: 'mar', // Morocco
      mk: 'mkd', // North Macedonia
      mx: 'mex', // Mexico
      nl: 'nld', // Netherlands
      no: 'nor', // Norway
      om: 'omn', // Oman
      pl: 'pol', // Poland
      ps: 'pse', // Palestine
      pt: 'prt', // Portugal
      qa: 'qat', // Qatar
      ro: 'rou', // Romania
      rs: 'srb', // Serbia
      ru: 'rus', // Russia
      sa: 'sau', // Saudi Arabia
      sd: 'sdn', // Sudan
      se: 'swe', // Sweden
      si: 'svn', // Slovenia
      sk: 'svk', // Slovakia
      sy: 'syr', // Syria
      tn: 'tun', // Tunisia
      tr: 'tur', // Turkey
      ua: 'ukr', // Ukraine
      us: 'usa', // United States
      ye: 'yem', // Yemen
    };

    return mapping[legacyCode] || legacyCode;
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
