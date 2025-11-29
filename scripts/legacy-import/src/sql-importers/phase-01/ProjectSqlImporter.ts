import { BaseSqlImporter, type ImportResult } from '../base/BaseSqlImporter.js';
import type { Connection } from 'mysql2/promise';
import { v4 as uuidv4 } from 'uuid';
import type { LegacyDatabase } from '../../database/LegacyDatabase.js';

interface LegacyProject {
  project_id: string;
  name: string;
  launchdate?: string;
}

interface LegacyProjectName {
  project_id: string;
  lang: string;
  name: string;
}

export class ProjectSqlImporter extends BaseSqlImporter {
  private legacyDb: LegacyDatabase;

  constructor(db: Connection, tracker: Map<string, string>, legacyDb: LegacyDatabase) {
    super(db, tracker);
    this.legacyDb = legacyDb;
  }

  getName(): string {
    return 'ProjectSqlImporter';
  }

  async import(): Promise<ImportResult> {
    const result: ImportResult = {
      success: true,
      imported: 0,
      skipped: 0,
      errors: [],
    };

    try {
      this.log('Importing projects...');

      // Query legacy projects (main table)
      const projects = await this.legacyDb.query<LegacyProject>(
        'SELECT * FROM mwnf3.projects ORDER BY project_id'
      );

      // Query project translations
      const projectNames = await this.legacyDb.query<LegacyProjectName>(
        'SELECT * FROM mwnf3.projectnames ORDER BY project_id, lang'
      );

      // Group by project_id
      const grouped = this.groupByProjectId(projects, projectNames);
      this.log(`Found ${grouped.length} unique projects`);

      let imported = 0;
      for (const group of grouped) {
        try {
          const success = await this.importProject(group);
          if (success) {
            imported++;
            result.imported++;
          } else {
            result.skipped++;
          }
          this.showProgress(imported + result.skipped, grouped.length);
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(`${group.projectId}: ${message}`);
          this.logError(`Failed to import project ${group.projectId}`, error);
        }
      }

      console.log(''); // New line after progress
      this.logSuccess(`Imported ${result.imported}, skipped ${result.skipped}`);
    } catch (error) {
      result.success = false;
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(message);
      this.logError('Failed to import projects', error);
    }

    return result;
  }

  private groupByProjectId(
    projects: LegacyProject[],
    projectNames: LegacyProjectName[]
  ): Array<{
    projectId: string;
    project: LegacyProject;
    translations: LegacyProjectName[];
  }> {
    const translationMap = new Map<string, LegacyProjectName[]>();
    for (const translation of projectNames) {
      if (!translationMap.has(translation.project_id)) {
        translationMap.set(translation.project_id, []);
      }
      translationMap.get(translation.project_id)!.push(translation);
    }

    return projects.map((project) => ({
      projectId: project.project_id,
      project,
      translations: translationMap.get(project.project_id) || [],
    }));
  }

  private async importProject(group: {
    projectId: string;
    project: LegacyProject;
    translations: LegacyProjectName[];
  }): Promise<boolean> {
    const backwardCompat = this.formatBackwardCompat('mwnf3', 'projects', [group.projectId]);

    // Check if already imported
    if (await this.exists('contexts', backwardCompat)) {
      return false;
    }

    // Create Context (only internal_name, no name column)
    const contextId = uuidv4();
    const internalName = group.project.name || group.projectId;
    await this.db.execute(
      `INSERT INTO contexts (id, internal_name, backward_compatibility, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?)`,
      [contextId, internalName, backwardCompat, this.now, this.now]
    );

    this.tracker.set(backwardCompat, contextId);

    // Create Project (linked to context)
    const projectId = uuidv4();
    const projectBackwardCompat = `${backwardCompat}:project`;
    
    // Validate launch_date: MySQL can return invalid dates like '0000-00-00 00:00:00'
    let launchDate: string | null = null;
    let isLaunched = false;
    if (group.project.launchdate) {
      const date = new Date(group.project.launchdate);
      // Check if date is valid (not NaN and not invalid date like 0000-00-00)
      if (!isNaN(date.getTime()) && date.getFullYear() > 1970) {
        launchDate = date.toISOString().split('T')[0];
        isLaunched = true;
      }
    }

    await this.db.execute(
      `INSERT INTO projects (id, internal_name, context_id, language_id, launch_date, is_launched, is_enabled, backward_compatibility, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
        projectId,
        internalName,
        contextId,
        'eng', // Default language
        launchDate,
        isLaunched ? 1 : 0,
        1, // is_enabled = true
        projectBackwardCompat,
        this.now,
        this.now,
      ]
    );

    this.tracker.set(projectBackwardCompat, projectId);

    // Create root Collection for this context (with required language_id and context_id)
    const collectionId = uuidv4();
    const collectionBackwardCompat = `${backwardCompat}:collection`;
    const defaultLanguage = 'eng'; // Use English as default
    const collectionInternalName = group.project.name || group.projectId;
    await this.db.execute(
      `INSERT INTO collections (id, internal_name, language_id, context_id, backward_compatibility, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?)`,
      [
        collectionId,
        collectionInternalName,
        defaultLanguage,
        contextId,
        collectionBackwardCompat,
        this.now,
        this.now,
      ]
    );

    this.tracker.set(collectionBackwardCompat, collectionId);

    // Create collection_translation for each language
    for (const translation of group.translations) {
      const translationId = uuidv4();
      // Map legacy 2-char language code to ISO 639-3 (3-char)
      const { mapLanguageCode } = await import('../../utils/CodeMappings.js');
      const languageId = mapLanguageCode(translation.lang);
      const title = translation.name || group.project.name || group.projectId;
      const description = `Collection for ${title}`;

      await this.db.execute(
        `INSERT INTO collection_translations (id, collection_id, language_id, context_id, title, description, backward_compatibility, created_at, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)`,
        [
          translationId,
          collectionId,
          languageId,
          contextId,
          title,
          description,
          `${collectionBackwardCompat}:${languageId}`,
          this.now,
          this.now,
        ]
      );
    }

    return true;
  }
}
