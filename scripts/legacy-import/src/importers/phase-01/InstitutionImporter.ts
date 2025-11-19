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
    const countryId = institution.country ? this.mapCountryCode(institution.country) : undefined;

    // Create Partner (institutions don't have project_id or GPS coordinates)
    const partnerResponse = await this.context.apiClient.partner.partnerStore({
      internal_name: institution.name,
      type: 'institution',
      country_id: countryId,
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
    const languageId = this.mapLanguageCode(translation.lang);

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
      tr: 'tur',
    };

    const mapped = mapping[legacyCode];
    if (!mapped) {
      throw new Error(
        `Unknown language code '${legacyCode}'. Add mapping to InstitutionImporter.mapLanguageCode()`
      );
    }
    return mapped;
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
