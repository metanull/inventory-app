/**
 * THG Contributor Importer
 *
 * Imports contributors from mwnf3_thematic_gallery:
 * - contributor (9 rows) + contributor_category (4) + contributor_i18n (8)
 * - exhibition_partner (4 rows) + exhibition_partner_i18n (4)
 *
 * Creates Contributor + ContributorTranslation + ContributorImage (size:1 placeholder)
 * per entry. Resolves collection_id from gallery/theme backward_compatibility.
 *
 * Phase placement: Phase 04 (THG phase), after gallery/theme Collections exist.
 */

import { BaseImporter } from '../../core/base-importer.js';
import type {
  ImportResult,
  ContributorData,
  ContributorTranslationData,
  ContributorImageData,
} from '../../core/types.js';
import type {
  ThgLegacyContributor,
  ThgLegacyContributorCategory,
  ThgLegacyContributorI18n,
  ThgLegacyExhibitionPartner,
  ThgLegacyExhibitionPartnerI18n,
} from '../../domain/types/index.js';
import { formatBackwardCompatibility } from '../../utils/backward-compatibility.js';
import { convertHtmlToMarkdown } from '../../utils/html-to-markdown.js';
import path from 'path';

/**
 * Map exhibition_partner category_id to contributor category slug
 */
function mapExhibitionPartnerCategory(categoryId: number): string {
  switch (categoryId) {
    case 1:
      return 'full_partner';
    case 2:
      return 'co_organiser';
    case 3:
      return 'other_contributor';
    default:
      return 'other_contributor';
  }
}

export class ThgContributorImporter extends BaseImporter {
  private defaultContextId!: string;
  private categoryMap = new Map<string, string>();

  getName(): string {
    return 'ThgContributorImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      const defaultContextId = await this.getDefaultContextIdAsync();
      if (!defaultContextId) {
        throw new Error('Default context not found. Run DefaultContextImporter first.');
      }
      this.defaultContextId = defaultContextId;

      // Load category lookup
      await this.loadCategories();

      // Import THG contributors
      this.logInfo('Importing THG contributors...');
      const contributorResult = await this.importContributors();
      result.imported += contributorResult.imported;
      result.skipped += contributorResult.skipped;
      result.errors.push(...contributorResult.errors);

      // Import THG exhibition partners as contributors
      this.logInfo('Importing THG exhibition partners as contributors...');
      const partnerResult = await this.importExhibitionPartners();
      result.imported += partnerResult.imported;
      result.skipped += partnerResult.skipped;
      result.errors.push(...partnerResult.errors);

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to import THG contributors: ${message}`);
      result.success = false;
    }

    result.success = result.errors.length === 0;
    return result;
  }

  private async loadCategories(): Promise<void> {
    const categories = await this.context.legacyDb.query<ThgLegacyContributorCategory>(
      'SELECT * FROM mwnf3_thematic_gallery.contributor_category'
    );
    for (const cat of categories) {
      this.categoryMap.set(cat.category_id, cat.label.toLowerCase().replace(/\s+/g, '_'));
    }
    this.logInfo(`Loaded ${this.categoryMap.size} contributor categories`);
  }

  private async importContributors(): Promise<ImportResult> {
    const result = this.createResult();

    const contributors = await this.context.legacyDb.query<ThgLegacyContributor>(
      'SELECT * FROM mwnf3_thematic_gallery.contributor ORDER BY gallery_id, theme_id, display_order'
    );

    const i18nRows = await this.context.legacyDb.query<ThgLegacyContributorI18n>(
      'SELECT * FROM mwnf3_thematic_gallery.contributor_i18n'
    );

    // Index i18n by contributor_id
    const i18nMap = new Map<number, ThgLegacyContributorI18n[]>();
    for (const row of i18nRows) {
      if (!i18nMap.has(row.contributor_id)) {
        i18nMap.set(row.contributor_id, []);
      }
      i18nMap.get(row.contributor_id)!.push(row);
    }

    this.logInfo(`Found ${contributors.length} THG contributors`);

    for (const legacy of contributors) {
      try {
        const backwardCompat = formatBackwardCompatibility({
          schema: 'mwnf3_thematic_gallery',
          table: 'contributor',
          pkValues: [String(legacy.contributor_id)],
        });

        // Check if already imported
        if (await this.entityExistsAsync(backwardCompat, 'contributor')) {
          result.skipped++;
          this.showSkipped();
          continue;
        }

        // Resolve collection_id from gallery/theme
        const collectionId = await this.resolveCollectionId(legacy.gallery_id, legacy.theme_id);
        if (!collectionId) {
          this.logWarning(
            `Contributor ${legacy.contributor_id}: collection not found for gallery=${legacy.gallery_id} theme=${legacy.theme_id}, skipping`
          );
          result.skipped++;
          this.showSkipped();
          continue;
        }

        // Resolve category
        const category = this.categoryMap.get(legacy.category_id) || 'partner';

        const internalName = legacy.context
          ? convertHtmlToMarkdown(legacy.context)
          : `contributor-${legacy.contributor_id}`;

        if (this.isDryRun || this.isSampleOnlyMode) {
          this.logInfo(
            `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would import contributor: ${backwardCompat}`
          );
          this.registerEntity(
            `sample-contributor-${legacy.contributor_id}`,
            backwardCompat,
            'contributor'
          );
          result.imported++;
          this.showProgress();
          continue;
        }

        // Create Contributor
        const contributorData: ContributorData = {
          collection_id: collectionId,
          category,
          display_order: legacy.display_order,
          visible: true,
          backward_compatibility: backwardCompat,
          internal_name: internalName,
        };
        const contributorId = await this.context.strategy.writeContributor(contributorData);
        this.registerEntity(contributorId, backwardCompat, 'contributor');

        // Create ContributorImage (size: 1 placeholder) if src is provided
        if (legacy.src?.trim()) {
          try {
            const imagePath = legacy.src.trim();
            const imageData: ContributorImageData = {
              contributor_id: contributorId,
              path: imagePath,
              original_name: path.basename(imagePath),
              mime_type: this.getMimeType(imagePath),
              size: 1,
              alt_text: legacy.alt?.trim() || null,
              display_order: 1,
            };
            await this.context.strategy.writeContributorImage(imageData);
          } catch (imgError) {
            const imgMsg = imgError instanceof Error ? imgError.message : String(imgError);
            this.logWarning(
              `Contributor ${legacy.contributor_id}: failed to create image: ${imgMsg}`
            );
          }
        }

        // Create translations
        const translations = i18nMap.get(legacy.contributor_id) || [];
        for (const i18n of translations) {
          try {
            const languageId = await this.getLanguageIdByLegacyCodeAsync(i18n.lang);
            if (!languageId) {
              this.logWarning(
                `Contributor ${legacy.contributor_id}: unknown language '${i18n.lang}', skipping translation`
              );
              continue;
            }

            const translationData: ContributorTranslationData = {
              contributor_id: contributorId,
              language_id: languageId,
              context_id: this.defaultContextId,
              name: i18n.context ? convertHtmlToMarkdown(i18n.context) : null,
              description: null,
              link: legacy.href?.trim() || null,
              alt_text: legacy.alt?.trim() || null,
              backward_compatibility: formatBackwardCompatibility({
                schema: 'mwnf3_thematic_gallery',
                table: 'contributor_i18n',
                pkValues: [String(legacy.contributor_id), i18n.lang],
              }),
            };
            await this.context.strategy.writeContributorTranslation(translationData);
          } catch (translationError) {
            const msg =
              translationError instanceof Error
                ? translationError.message
                : String(translationError);
            this.logWarning(
              `Contributor ${legacy.contributor_id}: translation (${i18n.lang}) failed: ${msg}`
            );
          }
        }

        result.imported++;
        this.showProgress();
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        const backwardCompat = formatBackwardCompatibility({
          schema: 'mwnf3_thematic_gallery',
          table: 'contributor',
          pkValues: [String(legacy.contributor_id)],
        });
        result.errors.push(`${backwardCompat}: ${message}`);
        this.logError(`Contributor ${legacy.contributor_id}`, message);
        this.showError();
      }
    }

    return result;
  }

  private async importExhibitionPartners(): Promise<ImportResult> {
    const result = this.createResult();

    const partners = await this.context.legacyDb.query<ThgLegacyExhibitionPartner>(
      'SELECT * FROM mwnf3_thematic_gallery.exhibition_partner ORDER BY gallery_id, display_order'
    );

    const i18nRows = await this.context.legacyDb.query<ThgLegacyExhibitionPartnerI18n>(
      'SELECT * FROM mwnf3_thematic_gallery.exhibition_partner_i18n'
    );

    // Index i18n by partner_id
    const i18nMap = new Map<number, ThgLegacyExhibitionPartnerI18n[]>();
    for (const row of i18nRows) {
      if (!i18nMap.has(row.partner_id)) {
        i18nMap.set(row.partner_id, []);
      }
      i18nMap.get(row.partner_id)!.push(row);
    }

    this.logInfo(`Found ${partners.length} THG exhibition partners`);

    for (const legacy of partners) {
      try {
        const backwardCompat = formatBackwardCompatibility({
          schema: 'mwnf3_thematic_gallery',
          table: 'exhibition_partner',
          pkValues: [String(legacy.partner_id)],
        });

        // Check if already imported
        if (await this.entityExistsAsync(backwardCompat, 'contributor')) {
          result.skipped++;
          this.showSkipped();
          continue;
        }

        // Resolve collection_id from gallery (exhibition_partner is per-gallery, no theme)
        const collectionId = await this.resolveCollectionId(legacy.gallery_id, 0);
        if (!collectionId) {
          this.logWarning(
            `Exhibition partner ${legacy.partner_id}: collection not found for gallery=${legacy.gallery_id}, skipping`
          );
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const category = mapExhibitionPartnerCategory(legacy.category_id);
        const internalName = legacy.entity_name
          ? convertHtmlToMarkdown(legacy.entity_name)
          : `exhibition-partner-${legacy.partner_id}`;

        if (this.isDryRun || this.isSampleOnlyMode) {
          this.logInfo(
            `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would import exhibition partner: ${backwardCompat}`
          );
          this.registerEntity(`sample-ep-${legacy.partner_id}`, backwardCompat, 'contributor');
          result.imported++;
          this.showProgress();
          continue;
        }

        // Create Contributor
        const contributorData: ContributorData = {
          collection_id: collectionId,
          category,
          display_order: legacy.display_order,
          visible: legacy.visible === 'Y',
          backward_compatibility: backwardCompat,
          internal_name: internalName,
        };
        const contributorId = await this.context.strategy.writeContributor(contributorData);
        this.registerEntity(contributorId, backwardCompat, 'contributor');

        // Create ContributorImage (size: 1 placeholder) if logo exists
        if (legacy.logo?.trim()) {
          try {
            const imagePath = legacy.logo.trim();
            const imageData: ContributorImageData = {
              contributor_id: contributorId,
              path: imagePath,
              original_name: path.basename(imagePath),
              mime_type: this.getMimeType(imagePath),
              size: 1,
              alt_text: null,
              display_order: 1,
            };
            await this.context.strategy.writeContributorImage(imageData);
          } catch (imgError) {
            const imgMsg = imgError instanceof Error ? imgError.message : String(imgError);
            this.logWarning(
              `Exhibition partner ${legacy.partner_id}: failed to create image: ${imgMsg}`
            );
          }
        }

        // Create translations
        const translations = i18nMap.get(legacy.partner_id) || [];
        for (const i18n of translations) {
          try {
            const languageId = await this.getLanguageIdByLegacyCodeAsync(i18n.lang);
            if (!languageId) {
              this.logWarning(
                `Exhibition partner ${legacy.partner_id}: unknown language '${i18n.lang}', skipping translation`
              );
              continue;
            }

            // Build extra with contact details and further_reading
            const extra: Record<string, string> = {};
            if (legacy.contact_title) extra.contact_title = legacy.contact_title;
            if (legacy.contact_name) extra.contact_name = legacy.contact_name;
            if (legacy.contact_email) extra.contact_email = legacy.contact_email;
            if (legacy.contact_phone) extra.contact_phone = legacy.contact_phone;
            if (legacy.contact_fax) extra.contact_fax = legacy.contact_fax;
            if (i18n.further_reading) extra.further_reading = i18n.further_reading;
            if (legacy.entity_location) extra.location = legacy.entity_location;
            if (legacy.entity_country) extra.country = legacy.entity_country;

            const translationData: ContributorTranslationData = {
              contributor_id: contributorId,
              language_id: languageId,
              context_id: this.defaultContextId,
              name: legacy.entity_name ? convertHtmlToMarkdown(legacy.entity_name) : null,
              description: i18n.description ? convertHtmlToMarkdown(i18n.description) : null,
              link: null,
              alt_text: null,
              extra: Object.keys(extra).length > 0 ? JSON.stringify(extra) : null,
              backward_compatibility: formatBackwardCompatibility({
                schema: 'mwnf3_thematic_gallery',
                table: 'exhibition_partner_i18n',
                pkValues: [String(legacy.partner_id), i18n.lang],
              }),
            };
            await this.context.strategy.writeContributorTranslation(translationData);
          } catch (translationError) {
            const msg =
              translationError instanceof Error
                ? translationError.message
                : String(translationError);
            this.logWarning(
              `Exhibition partner ${legacy.partner_id}: translation (${i18n.lang}) failed: ${msg}`
            );
          }
        }

        result.imported++;
        this.showProgress();
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        const backwardCompat = formatBackwardCompatibility({
          schema: 'mwnf3_thematic_gallery',
          table: 'exhibition_partner',
          pkValues: [String(legacy.partner_id)],
        });
        result.errors.push(`${backwardCompat}: ${message}`);
        this.logError(`Exhibition partner ${legacy.partner_id}`, message);
        this.showError();
      }
    }

    return result;
  }

  /**
   * Resolve collection_id from gallery_id + theme_id backward_compatibility.
   * THG galleries use BC: mwnf3_thematic_gallery:thg_gallery:{gallery_id}
   * THG themes use BC: mwnf3_thematic_gallery:thg_theme:{gallery_id}:{theme_id}
   */
  private async resolveCollectionId(galleryId: number, themeId: number): Promise<string | null> {
    // If theme_id > 0, try to find the theme collection first
    if (themeId > 0) {
      const themeBC = formatBackwardCompatibility({
        schema: 'mwnf3_thematic_gallery',
        table: 'thg_theme',
        pkValues: [String(galleryId), String(themeId)],
      });
      const themeCollectionId = await this.getEntityUuidAsync(themeBC, 'collection');
      if (themeCollectionId) {
        return themeCollectionId;
      }
    }

    // Fall back to gallery collection
    const galleryBC = formatBackwardCompatibility({
      schema: 'mwnf3_thematic_gallery',
      table: 'thg_gallery',
      pkValues: [String(galleryId)],
    });
    return this.getEntityUuidAsync(galleryBC, 'collection');
  }

  private getMimeType(filePath: string): string {
    const ext = path.extname(filePath).toLowerCase();
    const mimeTypes: Record<string, string> = {
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
    return mimeTypes[ext] || 'application/octet-stream';
  }
}
