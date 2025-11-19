"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.InstitutionImporter = void 0;
const BaseImporter_js_1 = require("../BaseImporter.js");
const BackwardCompatibilityFormatter_js_1 = require("../../utils/BackwardCompatibilityFormatter.js");
/**
 * Imports institutions from mwnf3.institutions and mwnf3.institutionnames
 * Maps to Partner model with type='institution'
 */
class InstitutionImporter extends BaseImporter_js_1.BaseImporter {
    getName() {
        return 'InstitutionImporter';
    }
    async import() {
        const result = {
            success: true,
            imported: 0,
            skipped: 0,
            errors: [],
        };
        try {
            // Query institutions
            const limitClause = this.context.limit > 0 ? ` LIMIT ${this.context.limit}` : '';
            const institutions = await this.context.legacyDb.query(`SELECT * FROM mwnf3.institutions${limitClause}`);
            // Query institution translations
            const institutionNames = await this.context.legacyDb.query('SELECT * FROM mwnf3.institutionnames');
            if (institutions.length === 0) {
                this.log('No institutions found');
                return result;
            }
            // Group translations by institution_id
            const translationsByInstitution = new Map();
            for (const name of institutionNames) {
                const key = name.institution_id;
                if (!translationsByInstitution.has(key)) {
                    translationsByInstitution.set(key, []);
                }
                translationsByInstitution.get(key).push(name);
            }
            // Import each institution
            for (const institution of institutions) {
                try {
                    const translations = translationsByInstitution.get(institution.institution_id) || [];
                    const imported = await this.importInstitution(institution, translations);
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
                    result.errors.push(`${institution.institution_id}: ${message}`);
                    this.showError();
                }
            }
            console.log(''); // New line after progress dots
        }
        catch (error) {
            const message = error instanceof Error ? error.message : String(error);
            result.errors.push(`Failed to query institutions: ${message}`);
            result.success = false;
        }
        result.success = result.errors.length === 0;
        return result;
    }
    async importInstitution(institution, translations) {
        // Format backward_compatibility with ALL PK fields (institution_id + country)
        const backwardCompat = BackwardCompatibilityFormatter_js_1.BackwardCompatibilityFormatter.format({
            schema: 'mwnf3',
            table: 'institutions',
            pkValues: [institution.institution_id, institution.country],
        });
        // Check if already imported
        if (this.context.tracker.exists(backwardCompat)) {
            return false;
        }
        if (this.context.dryRun) {
            this.log(`[DRY-RUN] Would import institution: ${institution.institution_id}`);
            return true;
        }
        // Create Partner
        const partnerResponse = await this.context.apiClient.partner.partnerStore({
            internal_name: institution.institution_id,
            type: 'institution',
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
        // Create translations (institutions don't have project_id, skip for now)
        for (const translation of translations) {
            await this.importTranslation(partnerId, '', translation);
        }
        this.log(`Imported institution: ${institution.name} (${institution.institution_id}:${institution.country}) → ${partnerId}`);
        return true;
    }
    async importTranslation(partnerId, projectId, translation) {
        // Map legacy ISO 639-1 to ISO 639-3
        const languageId = this.mapLanguageCode(translation.language);
        // Institutions don't have project_id - use default context
        let contextId;
        if (projectId) {
            // If institution has project_id, use that context
            const contextBackwardCompat = BackwardCompatibilityFormatter_js_1.BackwardCompatibilityFormatter.format({
                schema: 'mwnf3',
                table: 'projects',
                pkValues: [projectId],
            });
            contextId = this.context.tracker.getUuid(contextBackwardCompat);
        }
        if (!contextId) {
            // Use default context
            contextId = this.context.tracker.getUuid('__default_context__');
            if (!contextId) {
                throw new Error('Default context not found in tracker');
            }
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
exports.InstitutionImporter = InstitutionImporter;
//# sourceMappingURL=InstitutionImporter.js.map