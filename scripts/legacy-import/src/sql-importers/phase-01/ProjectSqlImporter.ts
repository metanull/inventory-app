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

    const name = group.project.name || group.translations[0]?.name || group.projectId;

    // Create Context
    const contextId = uuidv4();
    await this.db.execute(
      `INSERT INTO contexts (id, name, internal_name, backward_compatibility, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?)`,
      [contextId, name, group.projectId, backwardCompat, this.now, this.now]
    );

    this.tracker.set(backwardCompat, contextId);

    // Create root Collection for this context
    const collectionId = uuidv4();
    const collectionBackwardCompat = `${backwardCompat}:collection`;
    await this.db.execute(
      `INSERT INTO collections (id, context_id, name, internal_name, backward_compatibility, created_at, updated_at)
       VALUES (?, ?, ?, ?, ?, ?, ?)`,
      [
        collectionId,
        contextId,
        name,
        `${group.projectId}_root`,
        collectionBackwardCompat,
        this.now,
        this.now,
      ]
    );

    this.tracker.set(collectionBackwardCompat, collectionId);

    return true;
  }
}
