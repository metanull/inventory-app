"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.CountryImporter = void 0;
const BaseImporter_js_1 = require("../BaseImporter.js");
/**
 * Phase 0: Ensures Country reference data exists
 * Uses same data as CountrySeeder
 * Skips if already exists (idempotent)
 */
class CountryImporter extends BaseImporter_js_1.BaseImporter {
    getName() {
        return 'CountryImporter';
    }
    async import() {
        const result = {
            success: true,
            imported: 0,
            skipped: 0,
            errors: [],
        };
        const countries = [
            { id: 'usa', internal_name: 'United States of America', backward_compatibility: 'us' },
            { id: 'can', internal_name: 'Canada', backward_compatibility: 'ca' },
            { id: 'gbr', internal_name: 'United Kingdom of Great Britain and Northern Ireland', backward_compatibility: 'gb' },
            { id: 'fra', internal_name: 'France', backward_compatibility: 'fr' },
            { id: 'deu', internal_name: 'Germany', backward_compatibility: 'de' },
            { id: 'ita', internal_name: 'Italy', backward_compatibility: 'it' },
            { id: 'esp', internal_name: 'Spain', backward_compatibility: 'es' },
            { id: 'jpn', internal_name: 'Japan', backward_compatibility: 'jp' },
            { id: 'chn', internal_name: 'China', backward_compatibility: 'cn' },
            { id: 'ind', internal_name: 'India', backward_compatibility: 'in' },
            { id: 'bra', internal_name: 'Brazil', backward_compatibility: 'br' },
            { id: 'aus', internal_name: 'Australia', backward_compatibility: 'au' },
        ];
        for (const country of countries) {
            try {
                if (this.context.dryRun) {
                    this.log(`[DRY-RUN] Would create country: ${country.id}`);
                    result.imported++;
                    continue;
                }
                // Try to create - if 422, it already exists
                await this.context.apiClient.country.countryStore(country);
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
                result.errors.push(`${country.id}: ${message}`);
                this.showError();
            }
        }
        console.log(''); // New line after progress dots
        result.success = result.errors.length === 0;
        return result;
    }
}
exports.CountryImporter = CountryImporter;
//# sourceMappingURL=CountryImporter.js.map