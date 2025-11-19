"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.PartnerImporter = void 0;
const BaseImporter_js_1 = require("../BaseImporter.js");
const MuseumImporter_js_1 = require("./MuseumImporter.js");
const InstitutionImporter_js_1 = require("./InstitutionImporter.js");
/**
 * Phase 1 Task 2: Import Partners (Museums + Institutions)
 * Orchestrates museum and institution imports
 */
class PartnerImporter extends BaseImporter_js_1.BaseImporter {
    museumImporter;
    institutionImporter;
    constructor(context) {
        super(context);
        this.museumImporter = new MuseumImporter_js_1.MuseumImporter(context);
        this.institutionImporter = new InstitutionImporter_js_1.InstitutionImporter(context);
    }
    getName() {
        return 'PartnerImporter';
    }
    async import() {
        this.log('Starting Partner import (Museums + Institutions)');
        const result = {
            success: true,
            imported: 0,
            skipped: 0,
            errors: [],
        };
        // Import museums
        this.log('Importing museums...');
        const museumResult = await this.museumImporter.import();
        result.imported += museumResult.imported;
        result.skipped += museumResult.skipped;
        result.errors.push(...museumResult.errors);
        // Import institutions
        this.log('Importing institutions...');
        const institutionResult = await this.institutionImporter.import();
        result.imported += institutionResult.imported;
        result.skipped += institutionResult.skipped;
        result.errors.push(...institutionResult.errors);
        result.success = result.errors.length === 0;
        this.log(`Partner import complete: ${result.imported} imported, ${result.skipped} skipped, ${result.errors.length} errors`);
        return result;
    }
}
exports.PartnerImporter = PartnerImporter;
//# sourceMappingURL=PartnerImporter.js.map