/**
 * SH Project Importer
 *
 * Imports projects from mwnf3_sharing_history database.
 * Creates Context, Collection, and Project entities for each SH project.
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';
import {
  transformShProject,
  transformShProjectTranslation,
} from '../../domain/transformers/index.js';
import type { ShLegacyProject, ShLegacyProjectName } from '../../domain/types/index.js';
import { mapLanguageCode } from '../../utils/code-mappings.js';

export class ShProjectImporter extends BaseImporter {
  getName(): string {
    return 'ShProjectImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing Sharing History projects...');

      // Query SH projects
      const projects = await this.context.legacyDb.query<ShLegacyProject>(
        'SELECT * FROM mwnf3_sharing_history.sh_projects ORDER BY project_id'
      );

      // Query SH project names for translations
      const projectNames = await this.context.legacyDb.query<ShLegacyProjectName>(
        'SELECT * FROM mwnf3_sharing_history.sh_project_names ORDER BY project_id, lang'
      );

      // Group translations by project_id
      const translationMap = new Map<string, ShLegacyProjectName[]>();
      for (const name of projectNames) {
        if (!translationMap.has(name.project_id)) {
          translationMap.set(name.project_id, []);
        }
        translationMap.get(name.project_id)!.push(name);
      }

      this.logInfo(
        `Found ${projects.length} SH projects with ${projectNames.length} translations`
      );

      // Get default language ID from tracker
      const defaultLanguageId = this.getDefaultLanguageId();

      for (const legacy of projects) {
        try {
          // Get translations for this project
          const translations = translationMap.get(legacy.project_id) || [];

          // Find the default language translation to get project title
          const defaultLangTranslation = translations.find(
            (t) => mapLanguageCode(t.lang) === defaultLanguageId
          );

          // Use title from translation, or fallback to name from project
          let projectTitle: string;
          if (defaultLangTranslation?.title) {
            projectTitle = defaultLangTranslation.title;
          } else if (legacy.name) {
            projectTitle = legacy.name;
          } else {
            throw new Error(
              `SH Project ${legacy.project_id} missing title for internal_name`
            );
          }

          const transformed = transformShProject(legacy, defaultLanguageId, projectTitle);

          // Check if already exists
          if (this.entityExists(transformed.context.backwardCompatibility, 'context')) {
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Collect sample
          this.collectSample('sh_project', legacy as unknown as Record<string, unknown>, 'success');

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would import SH project: ${legacy.project_id}`
            );
            // Register for tracking even in dry-run
            this.registerEntity(
              'sample-context-sh-' + legacy.project_id,
              transformed.context.backwardCompatibility,
              'context'
            );
            this.registerEntity(
              'sample-collection-sh-' + legacy.project_id,
              transformed.collection.backwardCompatibility,
              'collection'
            );
            this.registerEntity(
              'sample-project-sh-' + legacy.project_id,
              transformed.project.backwardCompatibility,
              'project'
            );
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
          this.registerEntity(
            collectionId,
            transformed.collection.backwardCompatibility,
            'collection'
          );

          // 3. Create Project (linked to context)
          const projectData = {
            ...transformed.project.data,
            context_id: contextId,
          };
          const projectId = await this.context.strategy.writeProject(projectData);
          this.registerEntity(projectId, transformed.project.backwardCompatibility, 'project');

          // 4. Create translations
          for (const legacyTranslation of translations) {
            try {
              const translationBundle = transformShProjectTranslation(legacyTranslation);

              // Context translation (no-op in current schema, but keep for future)
              await this.context.strategy.writeContextTranslation({
                ...translationBundle.contextTranslation,
                context_id: contextId,
              });

              // Collection translation
              await this.context.strategy.writeCollectionTranslation({
                ...translationBundle.collectionTranslation,
                collection_id: collectionId,
                context_id: contextId,
                backward_compatibility: transformed.collection.backwardCompatibility,
              });

              // Project translation
              await this.context.strategy.writeProjectTranslation({
                ...translationBundle.projectTranslation,
                project_id: projectId,
                context_id: contextId,
              });
            } catch (error) {
              const message = error instanceof Error ? error.message : String(error);
              this.logWarning(
                `Failed to create translation for SH project ${legacy.project_id}:${legacyTranslation.lang}: ${message}`
              );
            }
          }

          result.imported++;
          this.showProgress();
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(`SH Project ${legacy.project_id}: ${message}`);
          this.logError(`SH Project ${legacy.project_id}`, error);
          this.showError();
        }
      }

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to query SH projects: ${message}`);
      result.success = false;
    }

    result.success = result.errors.length === 0;
    return result;
  }
}
