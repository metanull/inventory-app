/**
 * Artistic Introduction Root Collection Importer
 *
 * Creates a single "Artistic Introduction" marker collection as a child of
 * the Islamic Art (ISL) project collection. This gives Artistic Introduction
 * data (artintro root, themes, pages) an unambiguous parent to hang off of,
 * the same pattern already used to identify other exhibition-like
 * categories (THG galleries/exhibitions roots, Explore roots, Travels
 * root): a dedicated collection with a stable backward_compatibility key,
 * rather than internal_name string matching.
 *
 * Mwnf3ExhibitionImporter nests the existing artintro root
 * ("mwnf3:artintros:{id}") under this collection instead of directly under
 * the ISL project collection.
 *
 * Legacy schema:
 * - No direct equivalent (we create a root container)
 *
 * New schema:
 * - collections (id, context_id, language_id, parent_id, type, internal_name, backward_compatibility, ...)
 * - collection_translations (collection_id, language_id, context_id, title, description, ...)
 *
 * Mapping:
 * - internal_name = 'artintro_root'
 * - type = 'collection'
 * - parent_id = ISL project collection
 * - backward_compatibility = 'mwnf3:artintro:root'
 *
 * Dependencies:
 * - ProjectImporter (ISL project + context must exist)
 * - LanguageImporter (for default language)
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';

const MWNF3_SCHEMA = 'mwnf3';
const ISL_PROJECT_KEY = 'ISL';

export class ArtintroRootCollectionImporter extends BaseImporter {
  getName(): string {
    return 'ArtintroRootCollectionImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      const backwardCompat = `${MWNF3_SCHEMA}:artintro:root`;

      if (await this.entityExistsAsync(backwardCompat, 'collection')) {
        this.logInfo('Artistic Introduction root collection already exists');
        // Ensure-semantics (#1505): a marker created before the purpose column
        // existed must still end up purposed, without a full re-import.
        const existingId = await this.getEntityUuidAsync(backwardCompat, 'collection');
        if (existingId && !this.isDryRun && !this.isSampleOnlyMode) {
          const currentPurpose = await this.context.strategy.getCollectionPurpose(existingId);
          if (currentPurpose === null) {
            await this.context.strategy.updateCollectionPurpose(
              existingId,
              'artistic-introduction-root'
            );
            this.logInfo(`Set purpose 'artistic-introduction-root' on ${backwardCompat}`);
            result.imported++;
            this.showProgress();
            return result;
          }
        }
        result.skipped++;
        this.showSkipped();
        return result;
      }

      this.logInfo('Looking up ISL project collection...');
      const projectBackwardCompat = `${MWNF3_SCHEMA}:projects:${ISL_PROJECT_KEY}`;
      const parentCollectionId = await this.getEntityUuidAsync(projectBackwardCompat, 'collection');
      if (!parentCollectionId) {
        throw new Error(
          `ISL project collection not found (${projectBackwardCompat}). Run ProjectImporter first.`
        );
      }

      const contextId = await this.getEntityUuidAsync(projectBackwardCompat, 'context');
      if (!contextId) {
        throw new Error(`ISL project context not found (${projectBackwardCompat}).`);
      }

      const defaultLanguageId = await this.getDefaultLanguageIdAsync();
      const internalName = 'artintro_root';

      this.collectSample(
        'artintro_root_collection',
        { internal_name: internalName, backward_compatibility: backwardCompat },
        'foundation',
        'Artistic Introduction root collection'
      );

      if (this.isDryRun || this.isSampleOnlyMode) {
        this.logInfo(
          `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create root collection: ${internalName}`
        );
        this.registerEntity('', backwardCompat, 'collection');
        result.imported++;
        this.showProgress();
        return result;
      }

      const collectionId = await this.context.strategy.writeCollection({
        internal_name: internalName,
        backward_compatibility: backwardCompat,
        context_id: contextId,
        language_id: defaultLanguageId,
        parent_id: parentCollectionId,
        type: 'collection',
        purpose: 'artistic-introduction-root',
        latitude: null,
        longitude: null,
        map_zoom: null,
        country_id: null,
      });

      this.registerEntity(collectionId, backwardCompat, 'collection');

      const translationBackwardCompat = `${backwardCompat}:translation:${defaultLanguageId}`;

      await this.context.strategy.writeCollectionTranslation({
        collection_id: collectionId,
        language_id: defaultLanguageId,
        context_id: contextId,
        backward_compatibility: translationBackwardCompat,
        title: 'Artistic Introduction',
        description:
          'Curated introductions to the major artistic traditions of Islamic art, illustrated with monuments and objects from the collection.',
      });

      this.logInfo(`Created Artistic Introduction root collection: ${collectionId}`);
      result.imported++;
      this.showProgress();
    } catch (error) {
      result.success = false;
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Error creating Artistic Introduction root collection: ${message}`);
      this.logError('ArtintroRootCollectionImporter', message);
      this.showError();
    }

    return result;
  }
}
