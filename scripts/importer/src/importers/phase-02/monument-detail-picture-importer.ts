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
import { mapLanguageCode, mapCountryCode } from '../../utils/code-mappings.js';
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
          this.logError(`Picture ${backwardCompat}`, message);
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
    const parentItemId = await this.getEntityUuidAsync(parentBackwardCompat, 'item');
    if (!parentItemId) {
      throw new Error(`Parent item not found: ${parentBackwardCompat}`);
    }

    // Determine if this is the first image (picture_id = 1)
    const isFirstImage = group.picture_id === 1;

    // Calculate display_order for this parent item (increment sequence per parent)
    const parentDisplayOrders = this.context.tracker.getMetadata(`display_order:${parentItemId}`);
    const currentDisplayOrder = parentDisplayOrders ? parseInt(parentDisplayOrders, 10) + 1 : 1;
    this.context.tracker.setMetadata(`display_order:${parentItemId}`, String(currentDisplayOrder));

    // Extract metadata
    const mimeType = this.getMimeType(group.path);
    const originalName = path.basename(group.path);

    // Create child Item (type="picture")
    const pictureItemId = await this.createPictureItem(group, parentItemId, result);

    // Create ItemImage for child Item (use currentDisplayOrder for proper sequencing)
    const itemImageData: ItemImageData = {
      item_id: pictureItemId,
      path: group.path,
      original_name: originalName,
      mime_type: mimeType,
      size: 1, // Fake size as required
      alt_text: group.path,
      display_order: currentDisplayOrder,
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
        display_order: currentDisplayOrder,
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
    const contextId = await this.getEntityUuidAsync(contextBackwardCompat, 'context');
    if (!contextId) {
      throw new Error(`Context not found: ${contextBackwardCompat}`);
    }

    const collectionId = await this.getEntityUuidAsync(contextBackwardCompat, 'collection');
    if (!collectionId) {
      throw new Error(`Collection not found: ${contextBackwardCompat}`);
    }

    const partnerBackwardCompat = formatBackwardCompatibility({
      schema: 'mwnf3',
      table: 'institutions',
      pkValues: [group.institution_id, group.country_id],
    });
    const partnerId = await this.getEntityUuidAsync(partnerBackwardCompat, 'partner');
    if (!partnerId) {
      throw new Error(`Partner not found: ${partnerBackwardCompat}`);
    }

    // Get project_id using same backward_compatibility as context
    const projectId = await this.getEntityUuidAsync(contextBackwardCompat, 'project');

    // Map country code from legacy 2-char to ISO 3-char
    const countryId = mapCountryCode(group.country_id);

    // No extra field needed (monument detail pictures don't have type field)
    const extra: Record<string, unknown> = {};

    // Create Item
    const itemData: ItemData = {
      type: 'picture',
      internal_name: `Picture ${group.picture_id} for ${group.project_id}:${group.institution_id}:${group.monument_id}:${group.detail_id}`,
      collection_id: collectionId,
      partner_id: partnerId,
      parent_id: parentItemId,
      country_id: countryId,
      project_id: projectId || null,
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

    // Get parent item's name in this language
    const parentBackwardCompat = formatBackwardCompatibility({
      schema: 'mwnf3',
      table: 'monuments_details',
      pkValues: [
        translation.project_id,
        translation.country_id,
        translation.institution_id,
        translation.monument_id,
        String(translation.detail_id),
        translation.lang_id,
      ],
    });
    const parentName = await this.getParentItemName(parentBackwardCompat, languageId);
    const name = parentName ? convertHtmlToMarkdown(parentName) : `Image ${translation.picture_id}`;

    // Use caption as description if present (empty string if no caption, as field doesn't accept null)
    const description =
      translation.caption && translation.caption.trim()
        ? convertHtmlToMarkdown(translation.caption)
        : '';

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
      description,
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

  private async getParentItemName(
    parentBackwardCompat: string,
    _languageId: string
  ): Promise<string | null> {
    try {
      const params = parentBackwardCompat.split(':').slice(2);
      const result = await this.context.legacyDb.query<{ name: string }>(
        'SELECT name FROM mwnf3.monuments_details WHERE project_id = ? AND country_id = ? AND institution_id = ? AND monument_id = ? AND detail_id = ? AND lang_id = ?',
        params
      );
      return result.length > 0 ? result[0]!.name : null;
    } catch {
      return null;
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
