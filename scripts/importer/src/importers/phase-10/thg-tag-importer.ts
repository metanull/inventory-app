/**
 * THG Tag Importer (Story 15.1)
 *
 * Imports curated gallery tags from mwnf3_thematic_gallery and links them to items.
 *
 * Creates:
 * - 2,629 Tags from thg_tags (joined with thg_tag_types)
 *   - Dedup: if tag with same (internal_name, category, language_id='eng') already exists,
 *     reuse existing UUID and append THG BC as semicolon-delimited
 *   - category mapped from type_id: material→material, artist→artist, dynasty→dynasty,
 *     subject→subject, type→type
 * - 27,543 item_tag links:
 *   - 20,406 from thg_objects_mwnf3_tags (mwnf3 objects)
 *   - 7,137 from thg_objects_sh_tags (SH objects)
 *
 * Source tables (mwnf3_thematic_gallery):
 * - thg_tags (2,629 rows) — tag definitions
 * - thg_tag_types — category labels
 * - thg_objects_mwnf3_tags (20,406 rows) — links to mwnf3 objects
 * - thg_objects_sh_tags (7,137 rows) — links to SH objects
 *
 * Skip: 6 empty junction/sequence tables
 *
 * Phase placement: Phase 10, after items from all schemas exist.
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';
import type {
  ThgLegacyTag,
  ThgLegacyObjectMwnf3Tag,
  ThgLegacyObjectShTag,
} from '../../domain/types/index.js';
import { formatBackwardCompatibility } from '../../utils/backward-compatibility.js';

/**
 * Map THG type_id to inventory-app tag category.
 * Returns null for unknown types.
 */
function mapThgTypeToCategory(typeId: string): string | null {
  switch (typeId.toLowerCase()) {
    case 'material':
      return 'material';
    case 'artist':
      return 'artist';
    case 'dynasty':
      return 'dynasty';
    case 'subject':
      return 'subject';
    case 'type':
      return 'type';
    default:
      return null;
  }
}

export class ThgTagImporter extends BaseImporter {
  /** Maps THG tag_id → resolved Tag UUID (for linking step) */
  private tagMap = new Map<string, string>();

  getName(): string {
    return 'ThgTagImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      // Step 1: Import THG tags
      this.logInfo('Importing THG tags...');
      const tagResult = await this.importTags();
      result.imported += tagResult.imported;
      result.skipped += tagResult.skipped;
      result.errors.push(...tagResult.errors);

      // Step 2: Import mwnf3 object-tag links
      this.logInfo('Importing THG mwnf3 object-tag links...');
      const mwnf3Result = await this.importMwnf3ObjectTagLinks();
      result.imported += mwnf3Result.imported;
      result.skipped += mwnf3Result.skipped;
      result.errors.push(...mwnf3Result.errors);

      // Step 3: Import SH object-tag links
      this.logInfo('Importing THG SH object-tag links...');
      const shResult = await this.importShObjectTagLinks();
      result.imported += shResult.imported;
      result.skipped += shResult.skipped;
      result.errors.push(...shResult.errors);

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to import THG tags: ${message}`);
      result.success = false;
    }

    result.success = result.errors.length === 0;
    return result;
  }

  // ===========================================================================
  // Step 1: Import THG tags (with dedup against existing tags)
  // ===========================================================================

  private async importTags(): Promise<ImportResult> {
    const result = this.createResult();

    const tags = await this.context.legacyDb.query<ThgLegacyTag>(
      `SELECT t.tag_id, t.type_id, t.description
       FROM mwnf3_thematic_gallery.thg_tags t
       ORDER BY t.type_id, t.tag_id`
    );
    this.logInfo(`Found ${tags.length} THG tags to import`);

    for (const tag of tags) {
      try {
        const thgBC = `thg:tags:${tag.tag_id}`;

        // Check if this THG tag was already imported (re-run protection)
        const existingThgId = await this.getEntityUuidAsync(thgBC, 'tag');
        if (existingThgId) {
          this.tagMap.set(tag.tag_id, existingThgId);
          result.skipped++;
          this.showSkipped();
          continue;
        }

        // Map type to category
        const category = mapThgTypeToCategory(tag.type_id);
        if (!category) {
          this.logWarning(`THG tag '${tag.tag_id}': unknown type_id '${tag.type_id}', skipping`);
          result.skipped++;
          this.showSkipped();
          continue;
        }

        const internalName = tag.tag_id.toLowerCase().trim();

        // Check for existing tag with same (internal_name, category, language_id='eng')
        // The TagHelper creates tags with BC: mwnf3:tags:{category}:eng:{normalized_name}
        const existingBC = formatBackwardCompatibility({
          schema: 'mwnf3',
          table: `tags:${category}:eng`,
          pkValues: [internalName],
        });
        const existingTagId = await this.context.strategy.findByBackwardCompatibility(
          'tags',
          existingBC
        );

        if (existingTagId) {
          // Dedup: reuse existing tag, append THG BC as semicolon-delimited
          const currentBC = existingBC; // this is the BC we found it by
          const mergedBC = `${currentBC};${thgBC}`;

          if (!(this.isDryRun || this.isSampleOnlyMode)) {
            await this.context.strategy.updateBackwardCompatibility(
              'tags',
              existingTagId,
              mergedBC
            );
          }

          // Register THG BC alias pointing to same UUID
          this.registerEntity(existingTagId, thgBC, 'tag');
          this.tagMap.set(tag.tag_id, existingTagId);

          this.logInfo(`Dedup: THG tag '${tag.tag_id}' merged with existing tag ${existingBC}`);
          result.imported++;
          this.showProgress();
          continue;
        }

        // No existing match — create new tag
        this.collectSample('thg_tag', tag as unknown as Record<string, unknown>, 'success');

        if (this.isDryRun || this.isSampleOnlyMode) {
          this.logInfo(
            `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create tag: ${internalName} (${thgBC})`
          );
          this.registerEntity('', thgBC, 'tag');
          result.imported++;
          this.showProgress();
          continue;
        }

        const tagId = await this.context.strategy.writeTag({
          internal_name: internalName,
          category,
          language_id: 'eng',
          description: tag.description || tag.tag_id,
          backward_compatibility: thgBC,
        });

        this.registerEntity(tagId, thgBC, 'tag');
        this.tagMap.set(tag.tag_id, tagId);

        result.imported++;
        this.showProgress();
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        result.errors.push(`THG tag '${tag.tag_id}': ${message}`);
        this.logError(`ThgTag ${tag.tag_id}`, message);
        this.showError();
      }
    }

    return result;
  }

  // ===========================================================================
  // Step 2: Import mwnf3 object-tag links (thg_objects_mwnf3_tags)
  // ===========================================================================

  private async importMwnf3ObjectTagLinks(): Promise<ImportResult> {
    const result = this.createResult();

    const links = await this.context.legacyDb.query<ThgLegacyObjectMwnf3Tag>(
      `SELECT tag_id, objects_project_id, objects_country, objects_museum_id, objects_number
       FROM mwnf3_thematic_gallery.thg_objects_mwnf3_tags
       ORDER BY tag_id, objects_project_id, objects_country, objects_museum_id, objects_number`
    );
    this.logInfo(`Found ${links.length} mwnf3 object-tag links`);

    for (const link of links) {
      try {
        // Resolve tag
        const tagId = await this.resolveTagId(link.tag_id);
        if (!tagId) {
          this.logWarning(`THG mwnf3 link: tag not found for '${link.tag_id}', skipping`);
          result.skipped++;
          this.showSkipped();
          continue;
        }

        // Resolve mwnf3 object item
        const itemBC = formatBackwardCompatibility({
          schema: 'mwnf3',
          table: 'objects',
          pkValues: [
            link.objects_project_id,
            link.objects_country,
            link.objects_museum_id,
            String(link.objects_number),
          ],
        });
        const itemId = await this.getEntityUuidAsync(itemBC, 'item');
        if (!itemId) {
          this.logWarning(`THG mwnf3 link: item not found for BC=${itemBC}, skipping`);
          result.skipped++;
          this.showSkipped();
          continue;
        }

        if (this.isDryRun || this.isSampleOnlyMode) {
          result.imported++;
          this.showProgress();
          continue;
        }

        await this.context.strategy.attachTagsToItem(itemId, [tagId]);
        result.imported++;
        this.showProgress();
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        if (message.includes('Duplicate')) {
          this.logSkip(`Duplicate THG mwnf3 object-tag link tag=${link.tag_id}, skipping`);
          result.skipped++;
        } else {
          const ctx = `mwnf3_tag_link:${link.tag_id}:${link.objects_project_id}:${link.objects_country}:${link.objects_museum_id}:${link.objects_number}`;
          this.logWarning(`Failed THG mwnf3 object-tag link ${ctx}: ${message}`);
        }
      }
    }

    return result;
  }

  // ===========================================================================
  // Step 3: Import SH object-tag links (thg_objects_sh_tags)
  // ===========================================================================

  private async importShObjectTagLinks(): Promise<ImportResult> {
    const result = this.createResult();

    const links = await this.context.legacyDb.query<ThgLegacyObjectShTag>(
      `SELECT tag_id, sh_objects_project_id, sh_objects_country, sh_objects_number
       FROM mwnf3_thematic_gallery.thg_objects_sh_tags
       ORDER BY tag_id, sh_objects_project_id, sh_objects_country, sh_objects_number`
    );
    this.logInfo(`Found ${links.length} SH object-tag links`);

    for (const link of links) {
      try {
        // Resolve tag
        const tagId = await this.resolveTagId(link.tag_id);
        if (!tagId) {
          this.logWarning(`THG SH link: tag not found for '${link.tag_id}', skipping`);
          result.skipped++;
          this.showSkipped();
          continue;
        }

        // Resolve SH object item
        const itemBC = `mwnf3_sharing_history:sh_objects:${link.sh_objects_project_id}:${link.sh_objects_country}:${link.sh_objects_number}`;
        const itemId = await this.getEntityUuidAsync(itemBC, 'item');
        if (!itemId) {
          this.logWarning(`THG SH link: item not found for BC=${itemBC}, skipping`);
          result.skipped++;
          this.showSkipped();
          continue;
        }

        if (this.isDryRun || this.isSampleOnlyMode) {
          result.imported++;
          this.showProgress();
          continue;
        }

        await this.context.strategy.attachTagsToItem(itemId, [tagId]);
        result.imported++;
        this.showProgress();
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        if (message.includes('Duplicate')) {
          this.logSkip(`Duplicate THG SH object-tag link tag=${link.tag_id}, skipping`);
          result.skipped++;
        } else {
          const ctx = `sh_tag_link:${link.tag_id}:${link.sh_objects_project_id}:${link.sh_objects_country}:${link.sh_objects_number}`;
          this.logWarning(`Failed THG SH object-tag link ${ctx}: ${message}`);
        }
      }
    }

    return result;
  }

  // ===========================================================================
  // Helpers
  // ===========================================================================

  /**
   * Resolve a THG tag_id to its inventory-app Tag UUID.
   * Checks the local cache first, then falls back to tracker/DB lookup.
   */
  private async resolveTagId(thgTagId: string): Promise<string | null> {
    // Check local cache first
    const cached = this.tagMap.get(thgTagId);
    if (cached) return cached;

    // Try THG BC
    const thgBC = `thg:tags:${thgTagId}`;
    const tagId = await this.getEntityUuidAsync(thgBC, 'tag');
    if (tagId) {
      this.tagMap.set(thgTagId, tagId);
      return tagId;
    }

    return null;
  }
}
