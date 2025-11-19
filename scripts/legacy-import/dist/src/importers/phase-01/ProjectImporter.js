"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.ProjectImporter = void 0;
const BaseImporter_js_1 = require("../BaseImporter.js");
const BackwardCompatibilityFormatter_js_1 = require("../../utils/BackwardCompatibilityFormatter.js");
class ProjectImporter extends BaseImporter_js_1.BaseImporter {
    getName() {
        return 'ProjectImporter';
    }
    async import() {
        const result = {
            success: true,
            imported: 0,
            skipped: 0,
            errors: [],
        };
        try {
            // Query projects
            const limitClause = this.context.limit > 0 ? ` LIMIT ${this.context.limit}` : '';
            const projects = await this.context.legacyDb.query(`SELECT project_id, name, launchdate FROM mwnf3.projects ORDER BY project_id${limitClause}`, []);
            // Query all translations
            const projectNames = await this.context.legacyDb.query('SELECT project_id, lang, name FROM mwnf3.projectnames ORDER BY project_id, lang', []);
            // Group translations by project
            const translationsByProject = new Map();
            for (const translation of projectNames) {
                if (!translationsByProject.has(translation.project_id)) {
                    translationsByProject.set(translation.project_id, []);
                }
                translationsByProject.get(translation.project_id).push(translation);
            }
            // Import each project
            for (const project of projects) {
                try {
                    await this.importProject(project, translationsByProject.get(project.project_id) || []);
                    result.imported++;
                    this.showProgress();
                }
                catch (error) {
                    const message = error instanceof Error ? error.message : String(error);
                    // Check if it's a 422 duplicate error
                    if (error && typeof error === 'object' && 'response' in error) {
                        const axiosError = error;
                        if (axiosError.response?.status === 422) {
                            // Likely duplicate internal_name - treat as skipped
                            this.log(`Skipping ${project.project_id}: ${project.name} (already exists)`);
                            result.skipped++;
                            this.showSkipped();
                            continue;
                        }
                        this.log(`Error details for ${project.project_id}: ${JSON.stringify(axiosError.response?.data)}`);
                    }
                    result.errors.push(`${project.project_id}: ${message}`);
                    this.showError();
                }
            }
            console.log(''); // New line after progress dots
        }
        catch (error) {
            const message = error instanceof Error ? error.message : String(error);
            result.errors.push(`Failed to query projects: ${message}`);
        }
        return result;
    }
    async importProject(project, translations) {
        const contextBackwardCompat = BackwardCompatibilityFormatter_js_1.BackwardCompatibilityFormatter.format({
            schema: 'mwnf3',
            table: 'projects',
            pkValues: [project.project_id],
        });
        const collectionBackwardCompat = `${contextBackwardCompat}:collection`;
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
        // Register in tracker
        this.context.tracker.register({
            uuid: contextId,
            backwardCompatibility: contextBackwardCompat,
            entityType: 'context',
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
    mapLanguageCode(lang2char) {
        // Map 2-character legacy codes to 3-character ISO 639-2/T codes
        const mapping = {
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
exports.ProjectImporter = ProjectImporter;
//# sourceMappingURL=ProjectImporter.js.map