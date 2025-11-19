"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.LanguageImporter = void 0;
const BaseImporter_js_1 = require("../BaseImporter.js");
/**
 * Phase 0: Ensures Language reference data exists
 * Uses same data as LanguageSeeder
 * Skips if already exists (idempotent)
 */
class LanguageImporter extends BaseImporter_js_1.BaseImporter {
    getName() {
        return 'LanguageImporter';
    }
    async import() {
        const result = {
            success: true,
            imported: 0,
            skipped: 0,
            errors: [],
        };
        const languages = [
            { id: 'eng', internal_name: 'English', backward_compatibility: 'en', is_default: true },
            { id: 'fra', internal_name: 'Français', backward_compatibility: 'fr', is_default: false },
            { id: 'deu', internal_name: 'Deutsch', backward_compatibility: 'de', is_default: false },
            { id: 'spa', internal_name: 'Español', backward_compatibility: 'es', is_default: false },
            { id: 'ita', internal_name: 'Italiano', backward_compatibility: 'it', is_default: false },
            { id: 'jpn', internal_name: 'Japanese', backward_compatibility: 'ja', is_default: false },
            { id: 'kor', internal_name: 'Korean', backward_compatibility: 'ko', is_default: false },
            { id: 'ara', internal_name: 'Arabic', backward_compatibility: 'ar', is_default: false },
            { id: 'hin', internal_name: 'Hindi', backward_compatibility: 'hi', is_default: false },
            { id: 'nld', internal_name: 'Dutch', backward_compatibility: 'nl', is_default: false },
            { id: 'por', internal_name: 'Português', backward_compatibility: 'pt', is_default: false },
            { id: 'rus', internal_name: 'Russian', backward_compatibility: 'ru', is_default: false },
        ];
        for (const language of languages) {
            try {
                if (this.context.dryRun) {
                    this.log(`[DRY-RUN] Would create language: ${language.id}`);
                    result.imported++;
                    continue;
                }
                // Try to create - if 422, it already exists
                await this.context.apiClient.language.languageStore(language);
                // If this is English (default), set it as default
                if (language.is_default && language.id === 'eng') {
                    await this.context.apiClient.language.languageSetDefault('eng', { is_default: true });
                    this.log('Set English (eng) as default language');
                }
                result.imported++;
                this.showProgress();
            }
            catch (error) {
                if (error && typeof error === 'object' && 'response' in error) {
                    const axiosError = error;
                    if (axiosError.response?.status === 422) {
                        // Already exists - skip
                        result.skipped++;
                        this.showSkipped();
                        continue;
                    }
                }
                const message = error instanceof Error ? error.message : String(error);
                result.errors.push(`${language.id}: ${message}`);
                this.showError();
            }
        }
        console.log(''); // New line after progress dots
        result.success = result.errors.length === 0;
        return result;
    }
}
exports.LanguageImporter = LanguageImporter;
//# sourceMappingURL=LanguageImporter.js.map