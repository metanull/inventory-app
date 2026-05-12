/**
 * THG Gallery Content Importer
 *
 * Imports exhibition-specific content for thematic galleries:
 * - exhibition_logo -> collection_images
 * - exhibition_partner -> partners + collection_partner + partner_logos
 * - exhibition_related_content links/documents -> collection_media
 */

import path from 'path';

import { BaseImporter } from '../../core/base-importer.js';
import type {
  CollectionMediaData,
  ImportResult,
  PartnerData,
  PartnerLogoData,
  PartnerTranslationData,
} from '../../core/types.js';
import { formatBackwardCompatibility } from '../../utils/backward-compatibility.js';
import { mapCountryCode } from '../../utils/code-mappings.js';
import { convertHtmlToMarkdown } from '../../utils/html-to-markdown.js';

interface LegacyExhibitionLogo {
  logo_id: number;
  gallery_id: number;
  category_id: number | null;
  display_order: number | null;
  logo: string | null;
  label: string | null;
  alt: string | null;
  link: string | null;
  visible: string | null;
  further_reading: string | null;
}

interface LegacyExhibitionPartner {
  partner_id: number;
  gallery_id: number;
  category_id: number;
  display_order: number | null;
  entity_name: string | null;
  entity_location: string | null;
  entity_country: string | null;
  title: string | null;
  link: string | null;
  description: string | null;
  logo: string | null;
  visible: string | null;
  contact_title: string | null;
  contact_name: string | null;
  contact_email: string | null;
  contact_phone: string | null;
  contact_fax: string | null;
  further_reading: string | null;
}

interface LegacyExhibitionPartnerI18n {
  partner_id: number;
  language_id: string | null;
  entity_name: string | null;
  entity_location: string | null;
  entity_country: string | null;
  title: string | null;
  link: string | null;
  description: string | null;
  logo: string | null;
  contact_title: string | null;
  contact_name: string | null;
  contact_email: string | null;
  contact_phone: string | null;
  contact_fax: string | null;
  further_reading: string | null;
}

interface LegacyExhibitionRelatedContent {
  related_content_id: number;
  gallery_id: number;
  category_id: number | null;
  display_order: number | null;
  further_reading: string | null;
  entity_name: string | null;
  entity_location: string | null;
  entity_country: string | null;
  title: string | null;
  link: string | null;
  authors: string | null;
  type_resource: string | null;
  description: string | null;
  uploaded_document: string | null;
}

interface LegacyExhibitionRelatedContentI18n {
  related_content_id: number;
  language_id: string | null;
  further_reading: string | null;
  entity_name: string | null;
  entity_location: string | null;
  entity_country: string | null;
  title: string | null;
  link: string | null;
  authors: string | null;
  type_resource: string | null;
  description: string | null;
  uploaded_document: string | null;
}

interface GalleryTypeRow {
  gallery_id: number;
  is_gallery: number | boolean | string | null;
  is_exhibition: number | boolean | string | null;
}

const IMAGE_MIME_TYPES: Record<string, string> = {
  '.jpg': 'image/jpeg',
  '.jpeg': 'image/jpeg',
  '.png': 'image/png',
  '.gif': 'image/gif',
  '.webp': 'image/webp',
  '.svg': 'image/svg+xml',
  '.bmp': 'image/bmp',
  '.tif': 'image/tiff',
  '.tiff': 'image/tiff',
};

function trimToNull(value: string | null | undefined): string | null {
  const trimmed = value?.trim();
  return trimmed ? trimmed : null;
}

function guessMimeType(filePath: string): string {
  const ext = path.extname(filePath).toLowerCase();
  return IMAGE_MIME_TYPES[ext] ?? 'application/octet-stream';
}

function mapContentType(legacyType: string | null): 'audio' | 'video' | 'document' | null {
  const normalized = trimToNull(legacyType)?.toLowerCase();
  if (!normalized) {
    return null;
  }
  if (['audio', 'mp3', 'ogg'].includes(normalized)) {
    return 'audio';
  }
  if (['video', 'mp4', 'youtube', 'vimeo'].includes(normalized)) {
    return 'video';
  }
  if (['document', 'pdf'].includes(normalized)) {
    return 'document';
  }
  return null;
}

function mapPartnerLevel(categoryId: number): string | null {
  switch (categoryId) {
    case 1:
      return 'full_partner';
    case 2:
      return 'co_organiser';
    case 3:
      return 'other_contributor';
    default:
      return null;
  }
}

function isTruthyFlag(value: number | boolean | string | null): boolean {
  return value === true || value === 1 || value === '1' || value === 'Y';
}

function buildExtra(fields: Record<string, string | number | null | undefined>): string | null {
  const extra: Record<string, string | number> = {};
  for (const [key, value] of Object.entries(fields)) {
    if (typeof value === 'number') {
      extra[key] = value;
      continue;
    }
    const trimmed = trimToNull(value);
    if (trimmed) {
      extra[key] = trimmed;
    }
  }

  return Object.keys(extra).length > 0 ? JSON.stringify(extra) : null;
}

export class ThgGalleryContentImporter extends BaseImporter {
  private galleryCollectionTypes = new Map<number, 'gallery' | 'exhibition'>();
  private defaultContextId: string | null = null;

  getName(): string {
    return 'ThgGalleryContentImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      await this.loadGalleryCollectionTypes();

      this.logInfo('Importing exhibition logos as collection images...');
      const logoResult = await this.importExhibitionLogos();
      result.imported += logoResult.imported;
      result.skipped += logoResult.skipped;
      result.errors.push(...logoResult.errors);
      result.warnings!.push(...(logoResult.warnings ?? []));

      this.logInfo('Importing exhibition partners as partners...');
      const partnerResult = await this.importExhibitionPartners();
      result.imported += partnerResult.imported;
      result.skipped += partnerResult.skipped;
      result.errors.push(...partnerResult.errors);
      result.warnings!.push(...(partnerResult.warnings ?? []));

      this.logInfo('Importing exhibition related content as collection media...');
      const contentResult = await this.importExhibitionRelatedContent();
      result.imported += contentResult.imported;
      result.skipped += contentResult.skipped;
      result.errors.push(...contentResult.errors);
      result.warnings!.push(...(contentResult.warnings ?? []));

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.success = false;
      result.errors.push(message);
      this.logError('ThgGalleryContentImporter', message);
    }

    result.success = result.errors.length === 0;
    return result;
  }

  private async loadGalleryCollectionTypes(): Promise<void> {
    const rows = await this.context.legacyDb.query<GalleryTypeRow>(
      `SELECT g.gallery_id, pt.is_gallery, pt.is_exhibition
       FROM mwnf3_thematic_gallery.thg_gallery g
       INNER JOIN mwnf3_thematic_gallery.thg_projects p ON p.project_id = g.project_id
       INNER JOIN mwnf3_thematic_gallery.thg_project_type pt ON pt.type_id = p.type_id`
    );

    for (const row of rows) {
      const isGallery = isTruthyFlag(row.is_gallery);
      const isExhibition = isTruthyFlag(row.is_exhibition);
      if (isGallery && !isExhibition) {
        this.galleryCollectionTypes.set(row.gallery_id, 'gallery');
      }
      if (isExhibition && !isGallery) {
        this.galleryCollectionTypes.set(row.gallery_id, 'exhibition');
      }
    }
  }

  private async importExhibitionLogos(): Promise<ImportResult> {
    const result = this.createResult();

    let logoRows: LegacyExhibitionLogo[];
    try {
      logoRows = await this.context.legacyDb.query<LegacyExhibitionLogo>(
        `SELECT logo_id, gallery_id, category_id, display_order, logo, label, alt, link, visible, further_reading
         FROM mwnf3_thematic_gallery.exhibition_logo
         ORDER BY gallery_id, display_order IS NULL, display_order, logo_id`
      );
    } catch (queryError) {
      const message = queryError instanceof Error ? queryError.message : String(queryError);
      if (message.includes("doesn't exist") || message.includes('Table')) {
        this.logInfo(`exhibition_logo table not available: ${message}`);
        result.warnings!.push(`exhibition_logo table not available: ${message}`);
        result.success = true;
        return result;
      }
      throw queryError;
    }

    this.logInfo(`Found ${logoRows.length} exhibition logo rows`);

    for (const logo of logoRows) {
      try {
        const logoPath = trimToNull(logo.logo);
        if (!logoPath) {
          result.warnings!.push(
            `exhibition_logo.logo_id=${logo.logo_id}: logo path is empty, skipping`
          );
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const collectionId = await this.resolveGalleryCollectionId(logo.gallery_id);
        if (!collectionId) {
          result.warnings!.push(
            `exhibition_logo.logo_id=${logo.logo_id}: gallery collection not found for gallery_id=${logo.gallery_id}, skipping`
          );
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const trackerKey = logoPath.toLowerCase();
        if (await this.getEntityUuidAsync(trackerKey, 'image')) {
          result.skipped++;
          this.showSkipped();
          continue;
        }

        this.collectSample('exhibition_logo', logo as unknown as Record<string, unknown>, 'success');

        if (!this.isDryRun && !this.isSampleOnlyMode) {
          await this.context.strategy.writeCollectionImage({
            collection_id: collectionId,
            path: logoPath,
            original_name: path.basename(logoPath),
            mime_type: guessMimeType(logoPath),
            size: 1,
            alt_text: trimToNull(logo.alt),
            display_order: logo.display_order ?? 0,
          });
        } else {
          this.logInfo(
            `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create exhibition logo image: ${logoPath} -> collection ${collectionId}`
          );
        }

        result.imported++;
        this.showProgress();
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        result.errors.push(`exhibition_logo.logo_id=${logo.logo_id}: ${message}`);
        this.logError(`exhibition_logo.logo_id=${logo.logo_id}`, message);
        this.showError();
      }
    }

    return result;
  }

  private async importExhibitionPartners(): Promise<ImportResult> {
    const result = this.createResult();

    let partnerRows: LegacyExhibitionPartner[];
    try {
      partnerRows = await this.context.legacyDb.query<LegacyExhibitionPartner>(
        `SELECT partner_id, gallery_id, category_id, display_order, entity_name, entity_location,
                entity_country, title, link, description, logo, visible, contact_title,
                contact_name, contact_email, contact_phone, contact_fax, further_reading
         FROM mwnf3_thematic_gallery.exhibition_partner
         ORDER BY gallery_id, display_order IS NULL, display_order, partner_id`
      );
    } catch (queryError) {
      const message = queryError instanceof Error ? queryError.message : String(queryError);
      if (message.includes("doesn't exist") || message.includes('Table')) {
        this.logInfo(`exhibition_partner table not available: ${message}`);
        result.warnings!.push(`exhibition_partner table not available: ${message}`);
        result.success = true;
        return result;
      }
      throw queryError;
    }

    const i18nRows = await this.loadExhibitionPartnerTranslations(result);
    if (i18nRows.length > 0 && !this.defaultContextId) {
      this.defaultContextId = await this.getDefaultContextIdAsync();
    }

    const translationsByPartnerId = new Map<number, LegacyExhibitionPartnerI18n[]>();
    for (const i18n of i18nRows) {
      const existing = translationsByPartnerId.get(i18n.partner_id) ?? [];
      existing.push(i18n);
      translationsByPartnerId.set(i18n.partner_id, existing);
    }

    this.logInfo(`Found ${partnerRows.length} exhibition partner rows`);

    for (const partner of partnerRows) {
      try {
        const collectionId = await this.resolveGalleryCollectionId(partner.gallery_id);
        if (!collectionId) {
          result.warnings!.push(
            `exhibition_partner.partner_id=${partner.partner_id}: gallery collection not found for gallery_id=${partner.gallery_id}, skipping`
          );
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const collectionType = this.galleryCollectionTypes.get(partner.gallery_id);
        if (!collectionType) {
          result.warnings!.push(
            `exhibition_partner.partner_id=${partner.partner_id}: collection type not found for gallery_id=${partner.gallery_id}`
          );
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const level = mapPartnerLevel(partner.category_id);
        if (!level) {
          result.warnings!.push(
            `exhibition_partner.partner_id=${partner.partner_id}: unknown category_id=${partner.category_id}`
          );
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const partnerId = await this.resolveOrCreatePartner(partner, result);
        if (!partnerId) {
          continue;
        }

        if (!this.isDryRun && !this.isSampleOnlyMode) {
          await this.context.strategy.attachPartnerToCollectionWithLevel(
            collectionId,
            partnerId,
            collectionType,
            level
          );
        }

        await this.importPartnerLogo(
          partnerId,
          partner.logo,
          partner.partner_id,
          partner.display_order ?? 0
        );
        await this.importPartnerTranslations(
          partnerId,
          partner,
          translationsByPartnerId.get(partner.partner_id) ?? [],
          result
        );

        result.imported++;
        this.showProgress();
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        result.errors.push(`exhibition_partner.partner_id=${partner.partner_id}: ${message}`);
        this.logError(`exhibition_partner.partner_id=${partner.partner_id}`, message);
        this.showError();
      }
    }

    return result;
  }

  private async importExhibitionRelatedContent(): Promise<ImportResult> {
    const result = this.createResult();

    let contentRows: LegacyExhibitionRelatedContent[];
    try {
      contentRows = await this.context.legacyDb.query<LegacyExhibitionRelatedContent>(
        `SELECT related_content_id, gallery_id, category_id, display_order, further_reading,
                entity_name, entity_location, entity_country, title, link, authors,
                type_resource, description, uploaded_document
         FROM mwnf3_thematic_gallery.exhibition_related_content
         ORDER BY gallery_id, display_order IS NULL, display_order, related_content_id`
      );
    } catch (queryError) {
      const message = queryError instanceof Error ? queryError.message : String(queryError);
      if (message.includes("doesn't exist") || message.includes('Table')) {
        this.logInfo(`exhibition_related_content table not available: ${message}`);
        result.warnings!.push(`exhibition_related_content table not available: ${message}`);
        result.success = true;
        return result;
      }
      throw queryError;
    }

    const i18nRows = await this.loadRelatedContentTranslations(result);
    const translationsByContentId = new Map<number, LegacyExhibitionRelatedContentI18n[]>();
    for (const i18n of i18nRows) {
      const existing = translationsByContentId.get(i18n.related_content_id) ?? [];
      existing.push(i18n);
      translationsByContentId.set(i18n.related_content_id, existing);
    }

    this.logInfo(
      `Found ${contentRows.length} related content rows, ${i18nRows.length} translations`
    );

    for (const content of contentRows) {
      try {
        const collectionId = await this.resolveGalleryCollectionId(content.gallery_id);
        if (!collectionId) {
          result.warnings!.push(
            `exhibition_related_content.related_content_id=${content.related_content_id}: gallery collection not found for gallery_id=${content.gallery_id}, skipping`
          );
          result.skipped++;
          this.showSkipped();
          continue;
        }

        await this.importRelatedContentBaseRows(collectionId, content, result);

        const translations = translationsByContentId.get(content.related_content_id) ?? [];
        for (const i18n of translations) {
          await this.importRelatedContentTranslationRows(collectionId, content, i18n, result);
        }
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        result.errors.push(
          `exhibition_related_content.related_content_id=${content.related_content_id}: ${message}`
        );
        this.logError(
          `exhibition_related_content.related_content_id=${content.related_content_id}`,
          message
        );
        this.showError();
      }
    }

    return result;
  }

  private async loadExhibitionPartnerTranslations(
    result: ImportResult
  ): Promise<LegacyExhibitionPartnerI18n[]> {
    try {
      return await this.context.legacyDb.query<LegacyExhibitionPartnerI18n>(
        `SELECT partner_id, language_id, entity_name, entity_location, entity_country, title,
                link, description, logo, contact_title, contact_name, contact_email,
                contact_phone, contact_fax, further_reading
         FROM mwnf3_thematic_gallery.exhibition_partner_i18n
         ORDER BY partner_id, language_id`
      );
    } catch {
      result.warnings!.push('exhibition_partner_i18n table not available; partner translations skipped');
      return [];
    }
  }

  private async loadRelatedContentTranslations(
    result: ImportResult
  ): Promise<LegacyExhibitionRelatedContentI18n[]> {
    try {
      return await this.context.legacyDb.query<LegacyExhibitionRelatedContentI18n>(
        `SELECT related_content_id, language_id, further_reading, entity_name,
                entity_location, entity_country, title, link, authors, type_resource,
                description, uploaded_document
         FROM mwnf3_thematic_gallery.exhibition_related_content_i18n
         ORDER BY related_content_id, language_id`
      );
    } catch {
      result.warnings!.push(
        'exhibition_related_content_i18n table not available; language-specific related content skipped'
      );
      return [];
    }
  }

  private async resolveGalleryCollectionId(galleryId: number): Promise<string | null> {
    return this.getEntityUuidAsync(`mwnf3_thematic_gallery:thg_gallery:${galleryId}`, 'collection');
  }

  private async resolveOrCreatePartner(
    partner: LegacyExhibitionPartner,
    result: ImportResult
  ): Promise<string | null> {
    const backwardCompatibility = formatBackwardCompatibility({
      schema: 'mwnf3_thematic_gallery',
      table: 'exhibition_partner',
      pkValues: [String(partner.partner_id)],
    });

    const existingPartnerId = await this.getEntityUuidAsync(backwardCompatibility, 'partner');
    if (existingPartnerId) {
      return existingPartnerId;
    }

    const internalName = trimToNull(partner.entity_name) ?? trimToNull(partner.title);
    if (!internalName) {
      result.warnings!.push(
        `exhibition_partner.partner_id=${partner.partner_id}: entity_name and title are both empty`
      );
      result.skipped++;
      this.showSkipped();
      return null;
    }

    const countryId = await this.resolveCountryId(partner.entity_country, partner.partner_id, result);

    const partnerData: PartnerData = {
      type: 'institution',
      internal_name: internalName,
      backward_compatibility: backwardCompatibility,
      country_id: countryId,
      visible: partner.visible === 'Y',
    };

    this.collectSample(
      'exhibition_partner',
      partner as unknown as Record<string, unknown>,
      'success'
    );

    if (this.isDryRun || this.isSampleOnlyMode) {
      const sampleId = `sample-exhibition-partner-${partner.partner_id}`;
      this.registerEntity(sampleId, backwardCompatibility, 'partner');
      return sampleId;
    }

    const partnerId = await this.context.strategy.writePartner(partnerData);
    this.registerEntity(partnerId, backwardCompatibility, 'partner');
    return partnerId;
  }

  private async resolveCountryId(
    sourceCountry: string | null,
    partnerId: number,
    result: ImportResult
  ): Promise<string | null> {
    const legacyCode = trimToNull(sourceCountry);
    if (!legacyCode) {
      return null;
    }

    let mappedCountryId: string;
    try {
      mappedCountryId = mapCountryCode(legacyCode);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.warnings!.push(
        `exhibition_partner.partner_id=${partnerId}: entity_country='${legacyCode}' cannot be mapped: ${message}`
      );
      return null;
    }

    const countryId = await this.getEntityUuidAsync(legacyCode, 'country');
    return countryId ?? mappedCountryId;
  }

  private async importPartnerTranslations(
    partnerId: string,
    partner: LegacyExhibitionPartner,
    translations: LegacyExhibitionPartnerI18n[],
    result: ImportResult
  ): Promise<void> {
    for (const translation of translations) {
      const sourceLabel = `exhibition_partner_i18n.partner_id=${translation.partner_id}, language_id=${translation.language_id ?? 'null'}`;
      const sourceLanguage = trimToNull(translation.language_id);
      if (!sourceLanguage) {
        result.warnings!.push(`${sourceLabel}: language_id is empty, skipping translation`);
        continue;
      }

      const languageId = await this.getLanguageIdByLegacyCodeAsync(sourceLanguage);
      if (!languageId) {
        result.warnings!.push(`${sourceLabel}: unknown language, skipping translation`);
        continue;
      }

      const name = trimToNull(translation.entity_name) ?? trimToNull(translation.title);
      if (!name) {
        result.warnings!.push(`${sourceLabel}: entity_name and title are both empty, skipping translation`);
        continue;
      }

      const backwardCompatibility = formatBackwardCompatibility({
        schema: 'mwnf3_thematic_gallery',
        table: 'exhibition_partner_i18n',
        pkValues: [String(translation.partner_id), sourceLanguage],
      });

      if (await this.entityExistsAsync(backwardCompatibility, 'partner_translation')) {
        continue;
      }

      const description = trimToNull(translation.description);
      const translationData: PartnerTranslationData = {
        partner_id: partnerId,
        language_id: languageId,
        context_id: this.defaultContextId!,
        backward_compatibility: backwardCompatibility,
        name: convertHtmlToMarkdown(name),
        description: description ? convertHtmlToMarkdown(description) : null,
        city_display: trimToNull(translation.entity_location),
        contact_website: trimToNull(translation.link),
        contact_phone: trimToNull(translation.contact_phone),
        contact_email_general: trimToNull(translation.contact_email),
        extra: buildExtra({
          entity_country: translation.entity_country,
          contact_title: translation.contact_title,
          contact_name: translation.contact_name,
          contact_fax: translation.contact_fax,
          further_reading: translation.further_reading,
        }),
      };

      if (!this.isDryRun && !this.isSampleOnlyMode) {
        await this.context.strategy.writePartnerTranslation(translationData);
      }

      await this.importPartnerLogo(partnerId, translation.logo, partner.partner_id, partner.display_order ?? 0);
    }
  }

  private async importPartnerLogo(
    partnerId: string,
    logoPathValue: string | null,
    sourcePartnerId: number,
    displayOrder: number
  ): Promise<void> {
    const logoPath = trimToNull(logoPathValue);
    if (!logoPath) {
      return;
    }

    const trackerKey = `logo:${logoPath.toLowerCase()}`;
    if (await this.getEntityUuidAsync(trackerKey, 'image')) {
      return;
    }

    const logoData: PartnerLogoData = {
      partner_id: partnerId,
      path: logoPath,
      original_name: path.basename(logoPath),
      mime_type: guessMimeType(logoPath),
      size: 1,
      logo_type: 'primary',
      alt_text: null,
      display_order: displayOrder,
    };

    if (!this.isDryRun && !this.isSampleOnlyMode) {
      await this.context.strategy.writePartnerLogo(logoData);
    } else {
      this.logInfo(
        `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create partner logo for exhibition_partner.partner_id=${sourcePartnerId}: ${logoPath}`
      );
    }
  }

  private async importRelatedContentBaseRows(
    collectionId: string,
    content: LegacyExhibitionRelatedContent,
    result: ImportResult
  ): Promise<void> {
    const link = trimToNull(content.link);
    if (link) {
      await this.writeRelatedContentMedia({
        collectionId,
        relatedContentId: content.related_content_id,
        sourceTable: 'exhibition_related_content',
        languageId: null,
        source: content,
        url: link,
        type: mapContentType(content.type_resource),
        typeRequired: true,
        displayOrder: content.display_order ?? 0,
        categoryId: content.category_id,
        backwardCompatibility: `mwnf3_thematic_gallery:exhibition_related_content:${content.related_content_id}:link`,
        result,
      });
    }

    const uploadedDocument = trimToNull(content.uploaded_document);
    if (uploadedDocument) {
      await this.writeRelatedContentMedia({
        collectionId,
        relatedContentId: content.related_content_id,
        sourceTable: 'exhibition_related_content',
        languageId: null,
        source: content,
        url: uploadedDocument,
        type: 'document',
        typeRequired: false,
        displayOrder: content.display_order ?? 0,
        categoryId: content.category_id,
        backwardCompatibility: `mwnf3_thematic_gallery:exhibition_related_content:${content.related_content_id}:document`,
        result,
      });
    }
  }

  private async importRelatedContentTranslationRows(
    collectionId: string,
    baseContent: LegacyExhibitionRelatedContent,
    translation: LegacyExhibitionRelatedContentI18n,
    result: ImportResult
  ): Promise<void> {
    const sourceLanguage = trimToNull(translation.language_id);
    const sourceLabel = `exhibition_related_content_i18n.related_content_id=${translation.related_content_id}, language_id=${translation.language_id ?? 'null'}`;
    if (!sourceLanguage) {
      result.warnings!.push(`${sourceLabel}: language_id is empty, skipping related content translation`);
      return;
    }

    const languageId = await this.getLanguageIdByLegacyCodeAsync(sourceLanguage);
    if (!languageId) {
      result.warnings!.push(`${sourceLabel}: unknown language, skipping related content translation`);
      return;
    }

    const link = trimToNull(translation.link);
    if (link) {
      await this.writeRelatedContentMedia({
        collectionId,
        relatedContentId: translation.related_content_id,
        sourceTable: 'exhibition_related_content_i18n',
        languageId,
        source: translation,
        url: link,
        type: mapContentType(translation.type_resource),
        typeRequired: true,
        displayOrder: baseContent.display_order ?? 0,
        categoryId: baseContent.category_id,
        backwardCompatibility: `mwnf3_thematic_gallery:exhibition_related_content_i18n:${translation.related_content_id}:${sourceLanguage}:link`,
        result,
      });
    }

    const uploadedDocument = trimToNull(translation.uploaded_document);
    if (uploadedDocument) {
      await this.writeRelatedContentMedia({
        collectionId,
        relatedContentId: translation.related_content_id,
        sourceTable: 'exhibition_related_content_i18n',
        languageId,
        source: translation,
        url: uploadedDocument,
        type: 'document',
        typeRequired: false,
        displayOrder: baseContent.display_order ?? 0,
        categoryId: baseContent.category_id,
        backwardCompatibility: `mwnf3_thematic_gallery:exhibition_related_content_i18n:${translation.related_content_id}:${sourceLanguage}:document`,
        result,
      });
    }

    if (!link && !uploadedDocument && !trimToNull(baseContent.link) && !trimToNull(baseContent.uploaded_document)) {
      result.skipped++;
      this.showSkipped();
    }
  }

  private async writeRelatedContentMedia(parameters: {
    collectionId: string;
    relatedContentId: number;
    sourceTable: string;
    languageId: string | null;
    source: LegacyExhibitionRelatedContent | LegacyExhibitionRelatedContentI18n;
    url: string;
    type: 'audio' | 'video' | 'document' | null;
    typeRequired: boolean;
    displayOrder: number;
    categoryId: number | null;
    backwardCompatibility: string;
    result: ImportResult;
  }): Promise<void> {
    let resolvedType = parameters.type;
    if (parameters.typeRequired && !resolvedType) {
      const ext = path.extname(parameters.url).slice(1).toLowerCase();
      resolvedType = mapContentType(ext) ?? 'document';
    }

    const title =
      trimToNull(parameters.source.title) ??
      trimToNull(parameters.source.entity_name) ??
      path.basename(parameters.url);

    if (await this.entityExistsAsync(parameters.backwardCompatibility, 'collection_media')) {
      parameters.result.skipped++;
      this.showSkipped();
      return;
    }

    const description = trimToNull(parameters.source.description);
    const mediaData: CollectionMediaData = {
      collection_id: parameters.collectionId,
      language_id: parameters.languageId,
      type: resolvedType ?? 'document',
      title: convertHtmlToMarkdown(title),
      description: description ? convertHtmlToMarkdown(description) : null,
      url: parameters.url,
      display_order: parameters.displayOrder,
      extra: buildExtra({
        category_id: parameters.categoryId,
        further_reading: parameters.source.further_reading,
        entity_location: parameters.source.entity_location,
        entity_country: parameters.source.entity_country,
        authors: parameters.source.authors,
        type_resource: parameters.source.type_resource,
      }),
      backward_compatibility: parameters.backwardCompatibility,
    };

    this.collectSample(
      parameters.sourceTable,
      parameters.source as unknown as Record<string, unknown>,
      'success',
      undefined,
      parameters.languageId ?? undefined
    );

    if (!this.isDryRun && !this.isSampleOnlyMode) {
      await this.context.strategy.writeCollectionMedia(mediaData);
    } else {
      this.logInfo(
        `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create related content media: ${parameters.url} (${mediaData.type})`
      );
    }

    parameters.result.imported++;
    this.showProgress();
  }
}
