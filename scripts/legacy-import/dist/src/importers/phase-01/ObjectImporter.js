"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.ObjectImporter = void 0;
const BaseImporter_js_1 = require("../BaseImporter.js");
const BackwardCompatibilityFormatter_js_1 = require("../../utils/BackwardCompatibilityFormatter.js");
/**
 * Imports objects from mwnf3.objects
 *
 * CRITICAL: objects table is denormalized with language in PK
 * - PK: project_id, country, museum_id, number, LANG (5 columns)
 * - Multiple rows per object (one per language)
 * - Must group by non-lang columns and create ItemTranslations
 * - backward_compatibility: mwnf3:objects:{proj}:{country}:{museum}:{num} (NO LANG)
 */
class ObjectImporter extends BaseImporter_js_1.BaseImporter {
    getName() {
        return 'ObjectImporter';
    }
    async import() {
        const result = {
            success: true,
            imported: 0,
            skipped: 0,
            errors: [],
        };
        try {
            this.log('Importing objects...');
            // Query all objects (denormalized - multiple rows per object)
            const limitClause = this.context.limit > 0 ? ` LIMIT ${this.context.limit * 10}` : '';
            const objects = await this.context.legacyDb.query(`SELECT * FROM mwnf3.objects ORDER BY project_id, country, museum_id, number${limitClause}`);
            if (objects.length === 0) {
                this.log('No objects found');
                return result;
            }
            // Group objects by non-lang PK columns
            const objectGroups = this.groupObjectsByPK(objects);
            this.log(`Found ${objectGroups.length} unique objects (${objects.length} language rows)`);
            // Limit number of unique objects if limit specified
            const limitedGroups = this.context.limit > 0 ? objectGroups.slice(0, this.context.limit) : objectGroups;
            // Import each object group
            for (const group of limitedGroups) {
                try {
                    const imported = await this.importObject(group);
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
                    result.errors.push(`${group.project_id}:${group.museum_id}:${group.number}: ${message}`);
                    this.showError();
                }
            }
            console.log(''); // New line after progress dots
        }
        catch (error) {
            const message = error instanceof Error ? error.message : String(error);
            result.errors.push(`Failed to query objects: ${message}`);
            result.success = false;
        }
        result.success = result.errors.length === 0;
        return result;
    }
    /**
     * Group denormalized object rows by non-lang PK columns
     */
    groupObjectsByPK(objects) {
        const groups = new Map();
        for (const obj of objects) {
            const key = `${obj.project_id}:${obj.country}:${obj.museum_id}:${obj.number}`;
            if (!groups.has(key)) {
                groups.set(key, {
                    project_id: obj.project_id,
                    country: obj.country,
                    museum_id: obj.museum_id,
                    number: obj.number,
                    translations: [],
                });
            }
            groups.get(key).translations.push(obj);
        }
        return Array.from(groups.values());
    }
    async importObject(group) {
        // Format backward_compatibility (NO LANG)
        const backwardCompat = BackwardCompatibilityFormatter_js_1.BackwardCompatibilityFormatter.format({
            schema: 'mwnf3',
            table: 'objects',
            pkValues: [group.project_id, group.country, group.museum_id, group.number],
        });
        // Check if already imported
        if (this.context.tracker.exists(backwardCompat)) {
            return false;
        }
        if (this.context.dryRun) {
            this.log(`[DRY-RUN] Would import object: ${group.project_id}:${group.museum_id}:${group.number}`);
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
        // Resolve museum_id → partner_id
        const partnerBackwardCompat = BackwardCompatibilityFormatter_js_1.BackwardCompatibilityFormatter.format({
            schema: 'mwnf3',
            table: 'museums',
            pkValues: [group.museum_id, group.country],
        });
        const partnerId = this.context.tracker.getUuid(partnerBackwardCompat);
        if (!partnerId) {
            throw new Error(`Partner not found for museum ${group.museum_id}:${group.country}`);
        }
        // Use first translation for base data
        const firstTranslation = group.translations[0];
        if (!firstTranslation) {
            throw new Error('No translations found for object');
        }
        // Create Item
        const itemResponse = await this.context.apiClient.item.itemStore({
            internal_name: firstTranslation.inventory_id || firstTranslation.working_number || group.number,
            type: 'object',
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
        this.log(`Imported object: ${firstTranslation.name || group.number} (${group.project_id}:${group.museum_id}:${group.number}) → ${itemId}`);
        return true;
    }
    async importTranslation(itemId, contextId, obj) {
        // Map legacy ISO 639-1 to ISO 639-3
        const languageId = this.mapLanguageCode(obj.lang);
        await this.context.apiClient.itemTranslation.itemTranslationStore({
            item_id: itemId,
            language_id: languageId,
            context_id: contextId,
            name: obj.name || '',
            description: obj.description || '',
            alternate_name: obj.name2 || null,
            location: obj.location || null,
            dimensions: obj.dimensions || null,
            dates: obj.date_description || null,
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
exports.ObjectImporter = ObjectImporter;
//# sourceMappingURL=ObjectImporter.js.map