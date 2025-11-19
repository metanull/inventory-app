"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.MonumentImporter = void 0;
const BaseImporter_js_1 = require("../BaseImporter.js");
const BackwardCompatibilityFormatter_js_1 = require("../../utils/BackwardCompatibilityFormatter.js");
/**
 * Imports monuments from mwnf3.monuments
 *
 * CRITICAL: monuments table is denormalized with language in PK
 * - PK: project_id, country, institution_id, number, LANG (5 columns)
 * - Multiple rows per monument (one per language)
 * - Must group by non-lang columns and create ItemTranslations
 * - backward_compatibility: mwnf3:monuments:{proj}:{country}:{inst}:{num} (NO LANG)
 */
class MonumentImporter extends BaseImporter_js_1.BaseImporter {
    getName() {
        return 'MonumentImporter';
    }
    async import() {
        const result = {
            success: true,
            imported: 0,
            skipped: 0,
            errors: [],
        };
        try {
            this.log('Importing monuments...');
            // Query all monuments (denormalized - multiple rows per monument)
            const limitClause = this.context.limit > 0 ? ` LIMIT ${this.context.limit * 10}` : '';
            const monuments = await this.context.legacyDb.query(`SELECT * FROM mwnf3.monuments ORDER BY project_id, country, institution_id, number${limitClause}`);
            if (monuments.length === 0) {
                this.log('No monuments found');
                return result;
            }
            // Group monuments by non-lang PK columns
            const monumentGroups = this.groupMonumentsByPK(monuments);
            this.log(`Found ${monumentGroups.length} unique monuments (${monuments.length} language rows)`);
            // Limit number of unique monuments if limit specified
            const limitedGroups = this.context.limit > 0 ? monumentGroups.slice(0, this.context.limit) : monumentGroups;
            // Import each monument group
            for (const group of limitedGroups) {
                try {
                    const imported = await this.importMonument(group);
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
                    result.errors.push(`${group.project_id}:${group.institution_id}:${group.number}: ${message}`);
                    this.showError();
                }
            }
            console.log(''); // New line after progress dots
        }
        catch (error) {
            const message = error instanceof Error ? error.message : String(error);
            result.errors.push(`Failed to query monuments: ${message}`);
            result.success = false;
        }
        result.success = result.errors.length === 0;
        return result;
    }
    /**
     * Group denormalized monument rows by non-lang PK columns
     */
    groupMonumentsByPK(monuments) {
        const groups = new Map();
        for (const monument of monuments) {
            const key = `${monument.project_id}:${monument.country}:${monument.institution_id}:${monument.number}`;
            if (!groups.has(key)) {
                groups.set(key, {
                    project_id: monument.project_id,
                    country: monument.country,
                    institution_id: monument.institution_id,
                    number: monument.number,
                    translations: [],
                });
            }
            groups.get(key).translations.push(monument);
        }
        return Array.from(groups.values());
    }
    async importMonument(group) {
        // Format backward_compatibility (NO LANG)
        const backwardCompat = BackwardCompatibilityFormatter_js_1.BackwardCompatibilityFormatter.format({
            schema: 'mwnf3',
            table: 'monuments',
            pkValues: [group.project_id, group.country, group.institution_id, group.number],
        });
        // Check if already imported
        if (this.context.tracker.exists(backwardCompat)) {
            return false;
        }
        if (this.context.dryRun) {
            this.log(`[DRY-RUN] Would import monument: ${group.project_id}:${group.institution_id}:${group.number}`);
            return true;
        }
        // Resolve project_id → context_id
        const contextBackwardCompat = BackwardCompatibilityFormatter_js_1.BackwardCompatibilityFormatter.format({
            schema: 'mwnf3',
            table: 'projects',
            pkValues: [group.project_id],
        });
        const contextId = this.context.tracker.getUuid(contextBackwardCompat);
        if (!contextId) {
            throw new Error(`Context not found for project ${group.project_id}`);
        }
        // Resolve context → collection (root collection for this project)
        const collectionBackwardCompat = `${contextBackwardCompat}:collection`;
        const collectionId = this.context.tracker.getUuid(collectionBackwardCompat);
        if (!collectionId) {
            throw new Error(`Collection not found for project ${group.project_id}`);
        }
        // Resolve institution_id → partner_id
        const partnerBackwardCompat = BackwardCompatibilityFormatter_js_1.BackwardCompatibilityFormatter.format({
            schema: 'mwnf3',
            table: 'institutions',
            pkValues: [group.institution_id, group.country],
        });
        const partnerId = this.context.tracker.getUuid(partnerBackwardCompat);
        if (!partnerId) {
            throw new Error(`Partner not found for institution ${group.institution_id}:${group.country}`);
        }
        // Use first translation for base data
        const firstTranslation = group.translations[0];
        if (!firstTranslation) {
            throw new Error('No translations found for monument');
        }
        // Create Item
        const itemResponse = await this.context.apiClient.item.itemStore({
            internal_name: firstTranslation.working_number || firstTranslation.name || group.number,
            type: 'monument',
            collection_id: collectionId,
            partner_id: partnerId,
            backward_compatibility: backwardCompat,
        });
        const itemId = itemResponse.data.data.id;
        // Register in tracker
        this.context.tracker.register({
            uuid: itemId,
            backwardCompatibility: backwardCompat,
            entityType: 'item',
            createdAt: new Date(),
        });
        // Create translations for each language
        for (const translation of group.translations) {
            await this.importTranslation(itemId, contextId, translation);
        }
        this.log(`Imported monument: ${firstTranslation.name || group.number} (${group.project_id}:${group.institution_id}:${group.number}) → ${itemId}`);
        return true;
    }
    async importTranslation(itemId, contextId, monument) {
        // Map legacy ISO 639-1 to ISO 639-3
        const languageId = this.mapLanguageCode(monument.lang);
        await this.context.apiClient.itemTranslation.itemTranslationStore({
            item_id: itemId,
            language_id: languageId,
            context_id: contextId,
            name: monument.name || '',
            description: monument.description || '',
            alternate_name: monument.name2 || null,
            location: monument.location || null,
            dates: monument.date_description || null,
        });
    }
    mapLanguageCode(legacyCode) {
        const mapping = {
            en: 'eng',
            fr: 'fra',
            es: 'spa',
            de: 'deu',
            it: 'ita',
            pt: 'por',
            ar: 'ara',
            tr: 'tur',
        };
        return mapping[legacyCode] || legacyCode;
    }
}
exports.MonumentImporter = MonumentImporter;
//# sourceMappingURL=MonumentImporter.js.map