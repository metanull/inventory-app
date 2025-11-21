import { BaseImporter, type ImportResult } from '../BaseImporter.js';
import { BackwardCompatibilityFormatter } from '../../utils/BackwardCompatibilityFormatter.js';

interface LegacyProject {
  project_id: string;
  name: string;
  launchdate: Date | null;
}

interface LegacyProjectName {
  project_id: string;
  lang: string;
  name: string;
}

export class ProjectImporter extends BaseImporter {
  getName(): string {
    return 'ProjectImporter';
  }

  async import(): Promise<ImportResult> {
    const result: ImportResult = {
      success: true,
      imported: 0,
      skipped: 0,
      errors: [],
    };

    try {
      // Query projects
      const projects = await this.context.legacyDb.query<LegacyProject>(
        `SELECT project_id, name, launchdate FROM mwnf3.projects ORDER BY project_id`,
        []
      );

      // Query all translations
      const projectNames = await this.context.legacyDb.query<LegacyProjectName>(
        'SELECT project_id, lang, name FROM mwnf3.projectnames ORDER BY project_id, lang',
        []
      );

      // Group translations by project
      const translationsByProject = new Map<string, LegacyProjectName[]>();
      for (const translation of projectNames) {
        if (!translationsByProject.has(translation.project_id)) {
          translationsByProject.set(translation.project_id, []);
        }
        translationsByProject.get(translation.project_id)!.push(translation);
      }

      // Import each project
      for (const project of projects) {
        try {
          await this.importProject(project, translationsByProject.get(project.project_id) || []);
          result.imported++;
          this.showProgress();
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          // Check if it's a 422 duplicate error
          if (error && typeof error === 'object' && 'response' in error) {
            const axiosError = error as { response?: { status?: number; data?: unknown } };
            if (axiosError.response?.status === 422) {
              // Likely duplicate internal_name - treat as skipped
              result.skipped++;
              this.showSkipped();
              continue;
            }
          }
          result.errors.push(`${project.project_id}: ${message}`);
          this.showError();
        }
      }
      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to query projects: ${message}`);
    }

    return result;
  }

  private async importProject(
    project: LegacyProject,
    translations: LegacyProjectName[]
  ): Promise<void> {
    const contextBackwardCompat = BackwardCompatibilityFormatter.format({
      schema: 'mwnf3',
      table: 'projects',
      pkValues: [project.project_id],
    });

    const collectionBackwardCompat = `${contextBackwardCompat}:collection`;
    const projectBackwardCompat = `${contextBackwardCompat}:project`;

    // Check if already imported
    if (this.context.tracker.exists(contextBackwardCompat)) {
      return; // Skip, already exists
    }

    // Skip if dry-run
    if (this.context.dryRun) {
      return;
    }

    // Create Context (following SPA pattern: apiClient.contextStore(data))
    const contextResponse = await this.context.apiClient.context.contextStore({
      internal_name: project.name,
      backward_compatibility: contextBackwardCompat,
    });
    const contextId = contextResponse.data.data.id;

    // Register Context in tracker
    this.context.tracker.register({
      uuid: contextId,
      backwardCompatibility: contextBackwardCompat,
      entityType: 'context',
      createdAt: new Date(),
    });

    // Create Project (website project linked to this context)
    // Validate launch_date: MySQL can return invalid dates like '0000-00-00 00:00:00'
    let launchDate: string | undefined = undefined;
    let isLaunched = false;
    if (project.launchdate) {
      const date = new Date(project.launchdate);
      // Check if date is valid (not NaN and not invalid date like 0000-00-00)
      if (!isNaN(date.getTime()) && date.getFullYear() > 1970) {
        launchDate = date.toISOString().split('T')[0];
        isLaunched = true;
      }
    }

    const projectResponse = await this.context.apiClient.project.projectStore({
      internal_name: project.name,
      backward_compatibility: projectBackwardCompat,
      context_id: contextId,
      language_id: 'eng', // Default language
      launch_date: launchDate,
      is_launched: isLaunched,
      is_enabled: true,
    });
    const projectId = projectResponse.data.data.id;

    // Register Project in tracker (Partners will reference this)
    this.context.tracker.register({
      uuid: projectId,
      backwardCompatibility: projectBackwardCompat,
      entityType: 'project',
      createdAt: new Date(),
    });

    // Create Collection (following SPA pattern)
    const collectionResponse = await this.context.apiClient.collection.collectionStore({
      internal_name: `${project.name} Collection`,
      type: 'collection',
      language_id: 'eng', // Default language for collection creation
      context_id: contextId,
      parent_id: null, // Root collection - no parent
      backward_compatibility: collectionBackwardCompat,
    });
    const collectionId = collectionResponse.data.data.id;

    // Register collection in tracker
    this.context.tracker.register({
      uuid: collectionId,
      backwardCompatibility: collectionBackwardCompat,
      entityType: 'collection',
      createdAt: new Date(),
    });

    // Create translations for Collection (Contexts don't have translations)
    for (const translation of translations) {
      const languageId = this.mapLanguageCode(translation.lang);

      // Collection translation (following SPA pattern)
      await this.context.apiClient.collectionTranslation.collectionTranslationStore({
        collection_id: collectionId,
        language_id: languageId,
        context_id: contextId,
        title: translation.name,
        description: translation.name, // Use same value for description
      });
    }
  }

  private mapLanguageCode(lang2char: string): string {
    // Map 2-character legacy codes to 3-character ISO 639-2/T codes
    const mapping: Record<string, string> = {
      ar: 'ara',
      de: 'deu',
      en: 'eng',
      es: 'spa',
      fr: 'fra',
      it: 'ita',
      pt: 'por',
      tr: 'tur',
    };

    return mapping[lang2char] || lang2char;
  }
}
