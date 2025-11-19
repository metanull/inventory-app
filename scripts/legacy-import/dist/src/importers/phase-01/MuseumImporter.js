"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.MuseumImporter = void 0;
const BaseImporter_js_1 = require("../BaseImporter.js");
const BackwardCompatibilityFormatter_js_1 = require("../../utils/BackwardCompatibilityFormatter.js");
/**
 * Imports museums from mwnf3.museums and mwnf3.museumnames
 * Maps to Partner model with type='museum'
 */
class MuseumImporter extends BaseImporter_js_1.BaseImporter {
    getName() {
        return 'MuseumImporter';
    }
    async import() {
        const result = {
            success: true,
            imported: 0,
            skipped: 0,
            errors: [],
        };
        try {
            // Query museums
            const limitClause = this.context.limit > 0 ? ` LIMIT ${this.context.limit}` : '';
            const museums = await this.context.legacyDb.query(`SELECT * FROM mwnf3.museums${limitClause}`);
            // Query museum translations
            const museumNames = await this.context.legacyDb.query('SELECT * FROM mwnf3.museumnames');
            if (museums.length === 0) {
                this.log('No museums found');
                return result;
            }
            // Group translations by museum_id
            const translationsByMuseum = new Map();
            for (const name of museumNames) {
                const key = name.museum_id;
                if (!translationsByMuseum.has(key)) {
                    translationsByMuseum.set(key, []);
                }
                translationsByMuseum.get(key).push(name);
            }
            // Import each museum
            for (const museum of museums) {
                try {
                    const translations = translationsByMuseum.get(museum.museum_id) || [];
                    const imported = await this.importMuseum(museum, translations);
                    if (imported) {
                        result.imported++;
                        this.showProgress();
                    }
                    else {
                        result.skipped++;
                        this.showSkipped();
                    }
                }
                catch (error) {
                    const message = error instanceof Error ? error.message : String(error);
                    result.errors.push(`${museum.museum_id}: ${message}`);
                    this.showError();
                }
            }
            console.log(''); // New line after progress dots
        }
        catch (error) {
            const message = error instanceof Error ? error.message : String(error);
            result.errors.push(`Failed to query museums: ${message}`);
            result.success = false;
        }
        result.success = result.errors.length === 0;
        return result;
    }
    async importMuseum(museum, translations) {
        // Format backward_compatibility with ALL PK fields (museum_id + country)
        const backwardCompat = BackwardCompatibilityFormatter_js_1.BackwardCompatibilityFormatter.format({
            schema: 'mwnf3',
            table: 'museums',
            pkValues: [museum.museum_id, museum.country],
        });
        // Check if already imported
        if (this.context.tracker.exists(backwardCompat)) {
            return false;
        }
        if (this.context.dryRun) {
            this.log(`[DRY-RUN] Would import museum: ${museum.museum_id}`);
            return true;
        }
        // Create Partner
        const partnerResponse = await this.context.apiClient.partner.partnerStore({
            internal_name: museum.museum_id,
            type: 'museum',
            backward_compatibility: backwardCompat,
        });
        const partnerId = partnerResponse.data.data.id;
        // Register in tracker
        this.context.tracker.register({
            uuid: partnerId,
            backwardCompatibility: backwardCompat,
            entityType: 'partner',
            createdAt: new Date(),
        });
        // Create translations
        for (const translation of translations) {
            await this.importTranslation(partnerId, museum.project_id || '', translation);
        }
        this.log(`Imported museum: ${museum.name} (${museum.museum_id}:${museum.country}) → ${partnerId}`);
        return true;
    }
    async importTranslation(partnerId, projectId, translation) {
        // Map legacy ISO 639-1 to ISO 639-3
        const languageId = this.mapLanguageCode(translation.language);
        // Resolve project_id to context_id via tracker
        const contextBackwardCompat = BackwardCompatibilityFormatter_js_1.BackwardCompatibilityFormatter.format({
            schema: 'mwnf3',
            table: 'projects',
            pkValues: [projectId],
        });
        const contextId = this.context.tracker.getUuid(contextBackwardCompat);
        if (!contextId) {
            throw new Error(`Context not found for project ${projectId}`);
        }
        await this.context.apiClient.partnerTranslation.partnerTranslationStore({
            partner_id: partnerId,
            language_id: languageId,
            context_id: contextId,
            name: translation.name,
            description: translation.description || null,
        });
    }
    /**
     * Map legacy 2-character ISO 639-1 codes to 3-character ISO 639-3 codes
     */
    mapLanguageCode(legacyCode) {
        const mapping = {
            en: 'eng',
            fr: 'fra',
            es: 'spa',
            de: 'deu',
            it: 'ita',
            pt: 'por',
            ar: 'ara',
            ru: 'rus',
            zh: 'zho',
            ja: 'jpn',
        };
        return mapping[legacyCode] || legacyCode;
    }
}
exports.MuseumImporter = MuseumImporter;
//# sourceMappingURL=MuseumImporter.js.map