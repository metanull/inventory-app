import { BaseImporter, ImportContext, ImportResult } from '../BaseImporter.js';
import { MuseumImporter } from './MuseumImporter.js';
import { InstitutionImporter } from './InstitutionImporter.js';

/**
 * Phase 1 Task 2: Import Partners (Museums + Institutions)
 * Orchestrates museum and institution imports
 */
export class PartnerImporter extends BaseImporter {
  private museumImporter: MuseumImporter;
  private institutionImporter: InstitutionImporter;

  constructor(context: ImportContext) {
    super(context);
    this.museumImporter = new MuseumImporter(context);
    this.institutionImporter = new InstitutionImporter(context);
  }

  getName(): string {
    return 'PartnerImporter';
  }

  async import(): Promise<ImportResult> {
    this.logInfo('Starting Partner import (Museums + Institutions)');

    const result: ImportResult = {
      success: true,
      imported: 0,
      skipped: 0,
      errors: [],
    };

    // Import museums
    this.logInfo('Importing museums...');
    const museumResult = await this.museumImporter.import();
    result.imported += museumResult.imported;
    result.skipped += museumResult.skipped;
    result.errors.push(...museumResult.errors);

    // Import institutions
    this.logInfo('Importing institutions...');
    const institutionResult = await this.institutionImporter.import();
    result.imported += institutionResult.imported;
    result.skipped += institutionResult.skipped;
    result.errors.push(...institutionResult.errors);

    result.success = result.errors.length === 0;

    this.logInfo(
      `Partner import complete: ${result.imported} imported, ${result.skipped} skipped, ${result.errors.length} errors`
    );

    return result;
  }
}
