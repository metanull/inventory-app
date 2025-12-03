/**
 * Monument Detail Picture Importer
 *
 * Imports pictures from mwnf3.monument_detail_pictures.
 *
 * Strategy:
 * - First image (type='' AND lowest image_number) → Attach to parent Item as ItemImage
 * - ALL images (including first) → Create child Item (type="detail") with attached ItemImage
 *
 * Each picture Item gets:
 * - ItemTranslations for each language row (caption → name, copyright → extra)
 * - Artist relationships (photographer → Artist)
 * - Legacy type stored in Item.extra if not empty
 */

import { BaseImporter } from '../../core/base-importer.js';
import type {
  ImportResult,
  ItemData,
  ItemTranslationData,
  ItemImageData,
} from '../../core/types.js';
import type { LegacyMonumentDetailPicture } from '../../domain/types/index.js';
import { formatBackwardCompatibility } from '../../utils/backward-compatibility.js';
import { mapLanguageCode } from '../../utils/code-mappings.js';
import { convertHtmlToMarkdown } from '../../utils/html-to-markdown.js';
import { ArtistHelper } from '../../helpers/artist-helper.js';
import path from 'path';

interface PictureGroup {
  project_id: string;
  country_id: string;
  institution_id: string;
  monument_id: number;
  detail_id: number;
  picture_id: number;
  path: string;
  translations: LegacyMonumentDetailPicture[];
}

export class MonumentDetailPictureImporter extends BaseImporter {
  private artistHelper!: ArtistHelper;

  getName(): string {
    return 'MonumentDetailPictureImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    // Initialize helper
    this.artistHelper = new ArtistHelper(this.context.strategy, this.context.tracker);

    try {
      this.logInfo('Importing monument detail pictures...');

      // Query all pictures ordered by picture_id
      const pictures = await this.context.legacyDb.query<LegacyMonumentDetailPicture>(
        `SELECT * FROM mwnf3.monument_detail_pictures 
         ORDER BY project_id, country_id, institution_id, monument_id, detail_id, picture_id`
      );

      if (pictures.length === 0) {
        this.logInfo('No monument detail pictures found');
        return result;
      }

      // Group by PK excluding lang_id and type
      const groups = this.groupPictures(pictures);
      this.logInfo(`Found ${groups.length} unique pictures (${pictures.length} language rows)`);

      // Import each picture group
      for (const group of groups) {
        try {
          const imported = await this.importPicture(group, result);
          if (imported) {
            result.imported++;
            this.showProgress();
          } else {
            result.skipped++;
            this.showSkipped();
          }
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          const backwardCompat = this.getPictureBackwardCompatibility(group);
          result.errors.push(`${backwardCompat}: ${message}`);
          this.logError(`Picture ${backwardCompat}`, error);
          this.showError();
        }
      }

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to query monument detail pictures: ${message}`);
      result.success = false;
    }

    result.success = result.errors.length === 0;
    return result;
  }

  private groupPictures(pictures: LegacyMonumentDetailPicture[]): PictureGroup[] {
    const groups = new Map<string, PictureGroup>();

    for (const pic of pictures) {
      const key = `${pic.project_id}:${pic.country_id}:${pic.institution_id}:${pic.monument_id}:${pic.detail_id}:${pic.picture_id}`;

      if (!groups.has(key)) {
        groups.set(key, {
          project_id: pic.project_id,
          country_id: pic.country_id,
          institution_id: pic.institution_id,
          monument_id: pic.monument_id,
          detail_id: pic.detail_id,
          picture_id: pic.picture_id,
          path: pic.path,
          translations: [],
        });
      }

      groups.get(key)!.translations.push(pic);
    }

    return Array.from(groups.values());
  }

  private getPictureBackwardCompatibility(group: PictureGroup): string {
    return formatBackwardCompatibility({
      schema: 'mwnf3',
      table: 'monument_detail_pictures',
      pkValues: [
        group.project_id,
        group.country_id,
        group.institution_id,
        group.monument_id,
        group.detail_id,
        group.picture_id,
      ],
    });
  }

  private async importPicture(group: PictureGroup, result: ImportResult): Promise<boolean> {
    const backwardCompat = this.getPictureBackwardCompatibility(group);

    // Check if already imported using lowercase path as unique identifier
    const imageKey = group.path.toLowerCase();
    if (this.entityExists(imageKey, 'image')) {
      return false;
    }

    if (this.isDryRun || this.isSampleOnlyMode) {
      this.logInfo(
        `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would import picture: ${backwardCompat}`
      );
      this.registerEntity(`sample-image-${backwardCompat}`, imageKey, 'image');
      return true;
    }

    // Find parent Item
    const parentBackwardCompat = formatBackwardCompatibility({
      schema: 'mwnf3',
      table: 'monument_details',
      pkValues: [
        group.project_id,
        group.country_id,
        group.institution_id,
        group.monument_id,
        group.detail_id,
      ],
    });
    const parentItemId = this.getEntityUuid(parentBackwardCompat, 'item');
    if (!parentItemId) {
      throw new Error(`Parent item not found: ${parentBackwardCompat}`);
    }

    // Determine if this is the first image (picture_id = 1)
    const isFirstImage = group.picture_id === 1;

    // Extract metadata
    const mimeType = this.getMimeType(group.path);
    const originalName = path.basename(group.path);

    // Create child Item (type="detail")
    const pictureItemId = await this.createPictureItem(group, parentItemId, result);

    // Create ItemImage for child Item
    const displayOrder = 1; // Each picture Item has only one image
    const itemImageData: ItemImageData = {
      item_id: pictureItemId,
      path: group.path,
      original_name: originalName,
      mime_type: mimeType,
      size: 1, // Fake size as required
      alt_text: group.path,
      display_order: displayOrder,
    };
    await this.context.strategy.writeItemImage(itemImageData);

    // If this is the first image, also attach to parent
    if (isFirstImage) {
      const parentImageData: ItemImageData = {
        item_id: parentItemId,
        path: group.path,
        original_name: originalName,
        mime_type: mimeType,
        size: 1,
        alt_text: group.path,
        display_order: 1,
      };
      await this.context.strategy.writeItemImage(parentImageData);
    }

    // Register in tracker using lowercase path (images tracked by path, Items by backward_compatibility)
    this.registerEntity(pictureItemId, backwardCompat, 'item');

    return true;
  }

  private async createPictureItem(
    group: PictureGroup,
    parentItemId: string,
    result: ImportResult
  ): Promise<string> {
    const contextBackwardCompat = formatBackwardCompatibility({
      schema: 'mwnf3',
      table: 'projects',
      pkValues: [group.project_id],
    });
    const contextId = this.getEntityUuid(contextBackwardCompat, 'context');
    if (!contextId) {
      throw new Error(`Context not found: ${contextBackwardCompat}`);
    }

    const collectionId = this.getEntityUuid(contextBackwardCompat, 'collection');
    if (!collectionId) {
      throw new Error(`Collection not found: ${contextBackwardCompat}`);
    }

    const partnerBackwardCompat = formatBackwardCompatibility({
      schema: 'mwnf3',
      table: 'institutions',
      pkValues: [group.institution_id, group.country_id],
    });
    const partnerId = this.getEntityUuid(partnerBackwardCompat, 'partner');
    if (!partnerId) {
      throw new Error(`Partner not found: ${partnerBackwardCompat}`);
    }

    // No extra field needed (monument detail pictures don't have type field)
    const extra: Record<string, unknown> = {};

    // Create Item
    const itemData: ItemData = {
      type: 'picture',
      internal_name: `Picture ${group.picture_id} for ${group.project_id}:${group.institution_id}:${group.monument_id}:${group.detail_id}`,
      collection_id: collectionId,
      partner_id: partnerId,
      parent_id: parentItemId,
      country_id: null,
      project_id: null,
      owner_reference: null,
      mwnf_reference: null,
      backward_compatibility: this.getPictureBackwardCompatibility(group),
    };

    const pictureItemId = await this.context.strategy.writeItem(itemData);

    // Create translations for each language
    for (const translation of group.translations) {
      try {
        await this.createPictureTranslation(translation, pictureItemId, contextId, extra, result);
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        const translationBC = formatBackwardCompatibility({
          schema: 'mwnf3',
          table: 'monuments_details_pictures',
          pkValues: [
            translation.project_id,
            translation.country_id,
            translation.institution_id,
            translation.monument_id,
            String(translation.detail_id),
            String(translation.picture_id),
            translation.lang_id,
          ],
        });
        this.logWarning(`Failed to create translation ${translationBC}: ${message}`);
      }
    }

    return pictureItemId;
  }

  private async createPictureTranslation(
    translation: LegacyMonumentDetailPicture,
    pictureItemId: string,
    contextId: string,
    itemExtra: Record<string, unknown>,
    _result: ImportResult
  ): Promise<void> {
    const languageId = mapLanguageCode(translation.lang_id);

    // Determine name: use caption or fallback
    let name: string;
    if (translation.caption && translation.caption.trim()) {
      name = convertHtmlToMarkdown(translation.caption);
    } else {
      name = `Image ${translation.picture_id}`;
    }

    // Build extra with copyright if present
    const translationExtra: Record<string, unknown> = { ...itemExtra };
    if (translation.copyright && translation.copyright.trim()) {
      translationExtra.copyright = translation.copyright;
    }

    const translationData: ItemTranslationData = {
      item_id: pictureItemId,
      language_id: languageId,
      context_id: contextId,
      name,
      description: '', // Empty description as pictures don't have description
      alternate_name: null,
      type: null,
      holder: null,
      owner: null,
      initial_owner: null,
      dates: null,
      location: null,
      dimensions: null,
      place_of_production: null,
      method_for_datation: null,
      method_for_provenance: null,
      obtention: null,
      bibliography: null,
      author_id: null,
      text_copy_editor_id: null,
      translator_id: null,
      translation_copy_editor_id: null,
      extra: Object.keys(translationExtra).length > 0 ? JSON.stringify(translationExtra) : null,
      backward_compatibility: this.getPictureBackwardCompatibility({
        project_id: translation.project_id,
        country_id: translation.country_id,
        institution_id: translation.institution_id,
        monument_id: translation.monument_id,
        detail_id: translation.detail_id,
        picture_id: translation.picture_id,
        path: translation.path,
        translations: [],
      }),
    };

    await this.context.strategy.writeItemTranslation(translationData);

    // Handle photographer as Artist
    if (translation.photographer && translation.photographer.trim()) {
      const artistId = await this.artistHelper.findOrCreate(translation.photographer.trim());
      if (artistId) {
        await this.artistHelper.attachToItem(pictureItemId, [artistId]);
      }
    }
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
    };
    return mimeTypes[ext] || 'image/jpeg';
  }
}
