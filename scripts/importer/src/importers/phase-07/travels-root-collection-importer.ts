/**
 * Travels Root Collection Importer
 *
 * Creates the root "Travels" collection that will contain all trail collections.
 *
 * Legacy schema:
 * - No direct equivalent (we create a root container)
 *
 * New schema:
 * - collections (id, context_id, language_id, parent_id, type, internal_name, ...)
 * - collection_translations (collection_id, language_id, context_id, title, description, ...)
 *
 * Mapping:
 * - internal_name = 'travels_root'
 * - type = 'collection'
 * - parent_id = null (root level)
 * - backward_compatibility = 'mwnf3_travels:root'
 *
 * Dependencies:
 * - TravelsContextImporter (must run first)
 * - LanguageImporter (for default language)
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';

export class TravelsRootCollectionImporter extends BaseImporter {
  private travelsContextId: string | null = null;
  private defaultLanguageId: string = 'eng';

  getName(): string {
    return 'TravelsRootCollectionImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Looking up Travels context...');

      // Get the Travels context ID
      const travelsContextBackwardCompat = 'mwnf3_travels:context';
      this.travelsContextId = await this.getEntityUuidAsync(
        travelsContextBackwardCompat,
        'context'
      );

      if (!this.travelsContextId) {
        throw new Error(
          `Travels context not found (${travelsContextBackwardCompat}). Run TravelsContextImporter first.`
        );
      }

      // Get default language ID
      this.defaultLanguageId = await this.getDefaultLanguageIdAsync();

      this.logInfo(`Found Travels context: ${this.travelsContextId}`);
      this.logInfo('Creating Travels root collection...');

      const backwardCompat = 'mwnf3_travels:root';
      const internalName = 'travels_root';

      // Check if already exists
      if (await this.entityExistsAsync(backwardCompat, 'collection')) {
        this.logInfo('Travels root collection already exists, skipping');
        result.skipped++;
        this.showSkipped();
        return result;
      }

      // Collect sample
      this.collectSample(
        'travels_root_collection',
        { internal_name: internalName, backward_compatibility: backwardCompat },
        'foundation',
        'Travels root collection'
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

      // Write root collection
      const collectionId = await this.context.strategy.writeCollection({
        internal_name: internalName,
        backward_compatibility: backwardCompat,
        context_id: this.travelsContextId,
        language_id: this.defaultLanguageId,
        parent_id: null,
        type: 'collection',
        latitude: null,
        longitude: null,
        map_zoom: null,
        country_id: null,
      });

      this.registerEntity(collectionId, backwardCompat, 'collection');

      // Create translation for the root collection
      const translationBackwardCompat = `${backwardCompat}:translation:${this.defaultLanguageId}`;

      await this.context.strategy.writeCollectionTranslation({
        collection_id: collectionId,
        language_id: this.defaultLanguageId,
        context_id: this.travelsContextId,
        backward_compatibility: translationBackwardCompat,
        title: 'Travels',
        description:
          'Virtual visits and exhibition trails from the Museum With No Frontiers Travels application.',
      });

      this.logInfo(`Created Travels root collection: ${collectionId}`);
      result.imported++;
      this.showProgress();
    } catch (error) {
      result.success = false;
      const errorMessage = error instanceof Error ? error.message : String(error);
      result.errors.push(`Error creating Travels root collection: ${errorMessage}`);
      this.logError('TravelsRootCollectionImporter', error);
      this.showError();
    }

    return result;
  }
}
