/**
 * Object Picture Importer
 *
 * Imports pictures from mwnf3.objects_pictures.
 *
 * Strategy:
 * - First image (type='' AND lowest image_number) → Attach to parent Item as ItemImage
 * - ALL images (including first) → Create child Item (type="picture") with attached ItemImage
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
import type { LegacyObjectPicture } from '../../domain/types/index.js';
import { formatBackwardCompatibility } from '../../utils/backward-compatibility.js';
import { mapLanguageCode, mapCountryCode } from '../../utils/code-mappings.js';
import { convertHtmlToMarkdown } from '../../utils/html-to-markdown.js';
import { ArtistHelper } from '../../helpers/artist-helper.js';
import path from 'path';

interface PictureGroup {
  project_id: string;
  country: string;
  museum_id: string;
  number: number;
  type: string;
  image_number: number;
  path: string;
  translations: LegacyObjectPicture[];
}

export class ObjectPictureImporter extends BaseImporter {
  private artistHelper!: ArtistHelper;

  getName(): string {
    return 'ObjectPictureImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    // Initialize helper
    this.artistHelper = new ArtistHelper(this.context.strategy, this.context.tracker);

    try {
      this.logInfo('Importing object pictures...');

      // Query all pictures ordered by type (empty first) then image_number
      const pictures = await this.context.legacyDb.query<LegacyObjectPicture>(
        `SELECT * FROM mwnf3.objects_pictures 
         ORDER BY project_id, country, museum_id, number, 
                  CASE WHEN type = '' THEN 0 ELSE 1 END, 
                  image_number`
      );

      if (pictures.length === 0) {
        this.logInfo('No object pictures found');
        return result;
      }

      // Group by PK excluding lang and type
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
      result.errors.push(`Failed to query object pictures: ${message}`);
      result.success = false;
    }

    result.success = result.errors.length === 0;
    return result;
  }

  private groupPictures(pictures: LegacyObjectPicture[]): PictureGroup[] {
    const groups = new Map<string, PictureGroup>();

    for (const pic of pictures) {
      const key = `${pic.project_id}:${pic.country}:${pic.museum_id}:${pic.number}:${pic.type}:${pic.image_number}`;

      if (!groups.has(key)) {
        groups.set(key, {
          project_id: pic.project_id,
          country: pic.country,
          museum_id: pic.museum_id,
          number: pic.number,
          type: pic.type,
          image_number: pic.image_number,
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
      table: 'objects_pictures',
      pkValues: [
        group.project_id,
        group.country,
        group.museum_id,
        group.number,
        group.image_number,
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
      table: 'objects',
      pkValues: [group.project_id, group.country, group.museum_id, group.number],
    });
    const parentItemId = this.getEntityUuid(parentBackwardCompat, 'item');
    if (!parentItemId) {
      throw new Error(`Parent item not found: ${parentBackwardCompat}`);
    }

    // Determine if this is the first image
    const isFirstImage = group.type === '' && group.image_number === 1;

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
    _result: ImportResult
  ): Promise<string> {
    // Get parent item's collection and context
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
      table: 'museums',
      pkValues: [group.museum_id, group.country],
    });
    const partnerId = this.getEntityUuid(partnerBackwardCompat, 'partner');
    if (!partnerId) {
      throw new Error(`Partner not found: ${partnerBackwardCompat}`);
    }

    // Get project_id using same backward_compatibility as context
    const projectId = this.getEntityUuid(contextBackwardCompat, 'project');

    // Map country code from legacy 2-char to ISO 3-char
    const countryId = mapCountryCode(group.country);

    // Build extra with legacy type if not empty
    const extra: Record<string, unknown> = {};
    if (group.type && group.type.trim() !== '') {
      extra.legacy_type = group.type;
    }

    // Create Item
    const itemData: ItemData = {
      type: 'picture',
      internal_name: `Picture ${group.image_number} for ${group.project_id}:${group.museum_id}:${group.number}`,
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
        await this.createPictureTranslation(translation, pictureItemId, contextId, extra);
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        const translationBC = formatBackwardCompatibility({
          schema: 'mwnf3',
          table: 'objects_pictures',
          pkValues: [
            translation.project_id,
            translation.country,
            translation.museum_id,
            String(translation.number),
            translation.type,
            String(translation.image_number),
            translation.lang,
          ],
        });
        this.logWarning(`Failed to create translation ${translationBC}: ${message}`);
      }
    }

    return pictureItemId;
  }

  private async createPictureTranslation(
    translation: LegacyObjectPicture,
    pictureItemId: string,
    contextId: string,
    itemExtra: Record<string, unknown>
  ): Promise<void> {
    const languageId = mapLanguageCode(translation.lang);

    // Get parent item's name in this language
    const parentBackwardCompat = formatBackwardCompatibility({
      schema: 'mwnf3',
      table: 'objects',
      pkValues: [
        translation.project_id,
        translation.country,
        translation.museum_id,
        String(translation.number),
        translation.lang,
      ],
    });
    const parentName = await this.getParentItemName(parentBackwardCompat, languageId);
    const name = parentName
      ? convertHtmlToMarkdown(parentName)
      : `Image ${translation.image_number}`;

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
        country: translation.country,
        museum_id: translation.museum_id,
        number: translation.number,
        type: translation.type,
        image_number: translation.image_number,
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
        'SELECT name FROM mwnf3.objects WHERE project_id = ? AND country = ? AND museum_id = ? AND number = ? AND lang = ?',
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
