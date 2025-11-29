/**
 * Project Importer
 *
 * Imports projects from legacy database.
 * Creates Context, Collection, and Project entities.
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';
import { transformProject, transformProjectTranslation } from '../../domain/transformers/index.js';
import type { LegacyProject, LegacyProjectName } from '../../domain/types/index.js';

export class ProjectImporter extends BaseImporter {
  getName(): string {
    return 'ProjectImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing projects...');

      // Query legacy projects
      const projects = await this.context.legacyDb.query<LegacyProject>(
        'SELECT * FROM mwnf3.projects ORDER BY project_id'
      );

      // Query project names for translations
      const projectNames = await this.context.legacyDb.query<LegacyProjectName>(
        'SELECT * FROM mwnf3.projectnames ORDER BY project_id, lang'
      );

      // Group translations by project_id
      const translationMap = new Map<string, LegacyProjectName[]>();
      for (const name of projectNames) {
        if (!translationMap.has(name.project_id)) {
          translationMap.set(name.project_id, []);
        }
        translationMap.get(name.project_id)!.push(name);
      }

      this.logInfo(`Found ${projects.length} projects with ${projectNames.length} translations`);

      for (const legacy of projects) {
        try {
          const transformed = transformProject(legacy);

          // Check if already exists
          if (this.entityExists(transformed.context.backwardCompatibility)) {
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Collect sample
          this.collectSample(
            'project',
            legacy as unknown as Record<string, unknown>,
            'success'
          );

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(`[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would import project: ${legacy.project_id}`);
            // Register for tracking even in dry-run
            this.registerEntity('sample-context-' + legacy.project_id, transformed.context.backwardCompatibility, 'context');
            this.registerEntity('sample-collection-' + legacy.project_id, transformed.collection.backwardCompatibility, 'collection');
            this.registerEntity('sample-project-' + legacy.project_id, transformed.project.backwardCompatibility, 'project');
            result.imported++;
            this.showProgress();
            continue;
          }

          // 1. Create Context
          const contextId = await this.context.strategy.writeContext(transformed.context.data);
          this.registerEntity(contextId, transformed.context.backwardCompatibility, 'context');

          // 2. Create Collection (linked to context)
          const collectionData = {
            ...transformed.collection.data,
            context_id: contextId,
          };
          const collectionId = await this.context.strategy.writeCollection(collectionData);
          this.registerEntity(collectionId, transformed.collection.backwardCompatibility, 'collection');

          // 3. Create Project (linked to context)
          const projectData = {
            ...transformed.project.data,
            context_id: contextId,
          };
          const projectId = await this.context.strategy.writeProject(projectData);
          this.registerEntity(projectId, transformed.project.backwardCompatibility, 'project');

          // 4. Create translations
          const translations = translationMap.get(legacy.project_id) || [];
          for (const legacyTranslation of translations) {
            try {
              const translationBundle = transformProjectTranslation(legacyTranslation);

              // Context translation
              await this.context.strategy.writeContextTranslation({
                ...translationBundle.contextTranslation,
                context_id: contextId,
              });

              // Collection translation
              await this.context.strategy.writeCollectionTranslation({
                ...translationBundle.collectionTranslation,
                collection_id: collectionId,
              });

              // Project translation
              await this.context.strategy.writeProjectTranslation({
                ...translationBundle.projectTranslation,
                project_id: projectId,
                context_id: contextId,
              });
            } catch (error) {
              const message = error instanceof Error ? error.message : String(error);
              this.logWarning(`Failed to create translation for ${legacy.project_id}:${legacyTranslation.lang}: ${message}`);
            }
          }

          result.imported++;
          this.showProgress();
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(`${legacy.project_id}: ${message}`);
          this.logError(`Project ${legacy.project_id}`, error);
          this.showError();
        }
      }

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to query projects: ${message}`);
      result.success = false;
    }

    result.success = result.errors.length === 0;
    return result;
  }
}
