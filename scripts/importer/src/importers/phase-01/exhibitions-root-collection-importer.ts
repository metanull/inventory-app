/**
 * Exhibitions Root Collection Importer
 *
 * Creates a single "Exhibitions" marker collection as a child of the Islamic
 * Art (ISL) project collection. This mirrors ArtintroRootCollectionImporter:
 * `type='exhibition'` (and even the `mwnf3:exhibitions:{id}` backward_compatibility
 * key) is not project-scoped — the mwnf3 legacy schema shares its exhibitions
 * table across multiple projects (ISL, BAR, SH, ...), so neither field alone
 * can identify "the ISL exhibitions". This collection gives them an
 * unambiguous, stable anchor.
 *
 * Mwnf3ExhibitionImporter nests ISL exhibitions ("mwnf3:exhibitions:{id}")
 * under this collection instead of directly under the ISL project
 * collection. Exhibitions belonging to other projects are unaffected —
 * they keep nesting directly under their own project collection.
 *
 * Legacy schema:
 * - No direct equivalent (we create a root container)
 *
 * New schema:
 * - collections (id, context_id, language_id, parent_id, type, internal_name, backward_compatibility, ...)
 * - collection_translations (collection_id, language_id, context_id, title, description, ...)
 *
 * Mapping:
 * - internal_name = 'exhibitions_root'
 * - type = 'collection'
 * - parent_id = ISL project collection
 * - backward_compatibility = 'mwnf3:exhibitions:root'
 *
 * Dependencies:
 * - ProjectImporter (ISL project + context must exist)
 * - LanguageImporter (for default language)
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';

const MWNF3_SCHEMA = 'mwnf3';
const ISL_PROJECT_KEY = 'ISL';

export class ExhibitionsRootCollectionImporter extends BaseImporter {
  getName(): string {
    return 'ExhibitionsRootCollectionImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      const backwardCompat = `${MWNF3_SCHEMA}:exhibitions:root`;

      if (await this.entityExistsAsync(backwardCompat, 'collection')) {
        this.logInfo('Exhibitions root collection already exists, skipping');
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
      const internalName = 'exhibitions_root';

      this.collectSample(
        'exhibitions_root_collection',
        { internal_name: internalName, backward_compatibility: backwardCompat },
        'foundation',
        'Exhibitions root collection'
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
        title: 'Exhibitions',
        description:
          'Curated virtual exhibitions exploring themes, monuments, and objects from the Islamic art collection.',
      });

      this.logInfo(`Created Exhibitions root collection: ${collectionId}`);
      result.imported++;
      this.showProgress();
    } catch (error) {
      result.success = false;
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Error creating Exhibitions root collection: ${message}`);
      this.logError('ExhibitionsRootCollectionImporter', message);
      this.showError();
    }

    return result;
  }
}
