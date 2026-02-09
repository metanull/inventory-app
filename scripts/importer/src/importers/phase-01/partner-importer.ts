/**
 * Partner Importer
 *
 * Imports museums and institutions from legacy database.
 * Creates Partner entities with translations.
 *
 * Note: monument_item_id resolution is deferred since monuments may not be imported yet
 * at the time partners are imported. A separate pass or the PartnerMonumentLinker
 * should handle this after monuments are imported.
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';
import {
  transformMuseum,
  transformMuseumTranslation,
  groupMuseumsByKey,
  transformInstitution,
  transformInstitutionTranslation,
  groupInstitutionsByKey,
} from '../../domain/transformers/index.js';
import type { MuseumMonumentReference } from '../../domain/transformers/museum-transformer.js';
import type {
  LegacyMuseum,
  LegacyMuseumName,
  LegacyInstitution,
  LegacyInstitutionName,
} from '../../domain/types/index.js';

/**
 * Deferred monument link for later resolution
 */
export interface DeferredMonumentLink {
  partnerId: string;
  partnerBackwardCompat: string;
  monumentReference: MuseumMonumentReference;
}

export class PartnerImporter extends BaseImporter {
  private defaultContextId: string | null = null;
  /** Deferred monument links to be resolved after monuments are imported */
  private deferredMonumentLinks: DeferredMonumentLink[] = [];

  getName(): string {
    return 'PartnerImporter';
  }

  /**
   * Get deferred monument links for later resolution
   */
  getDeferredMonumentLinks(): DeferredMonumentLink[] {
    return this.deferredMonumentLinks;
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      // Get default context ID for partner translations
      this.defaultContextId = this.getDefaultContextId();

      // Import museums
      this.logInfo('Importing museums...');
      const museumResult = await this.importMuseums();
      result.imported += museumResult.imported;
      result.skipped += museumResult.skipped;
      result.errors.push(...museumResult.errors);
      if (museumResult.warnings) {
        result.warnings?.push(...museumResult.warnings);
      }

      // Import institutions
      this.logInfo('Importing institutions...');
      const institutionResult = await this.importInstitutions();
      result.imported += institutionResult.imported;
      result.skipped += institutionResult.skipped;
      result.errors.push(...institutionResult.errors);
      if (institutionResult.warnings) {
        result.warnings?.push(...institutionResult.warnings);
      }

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.errors.push(`Failed to import partners: ${message}`);
      result.success = false;
    }

    result.success = result.errors.length === 0;
    return result;
  }

  private async importMuseums(): Promise<ImportResult> {
    const result = this.createResult();

    // Query legacy museums
    const museums = await this.context.legacyDb.query<LegacyMuseum>(
      'SELECT * FROM mwnf3.museums ORDER BY museum_id, country'
    );

    const museumNames = await this.context.legacyDb.query<LegacyMuseumName>(
      'SELECT * FROM mwnf3.museumnames ORDER BY museum_id, country, lang'
    );

    const grouped = groupMuseumsByKey(museums, museumNames);
    this.logInfo(`Found ${grouped.length} unique museums`);

    let monumentLinksCount = 0;
    let logosCount = 0;

    for (const group of grouped) {
      try {
        const transformed = transformMuseum(group.museum);

        // Check if already exists
        if (this.entityExists(transformed.backwardCompatibility, 'partner')) {
          result.skipped++;
          this.showSkipped();
          continue;
        }

        // Count logos for reporting
        if (transformed.logos.length > 0) {
          logosCount += transformed.logos.length;
        }

        // Collect sample
        this.collectSample('museum', group.museum as unknown as Record<string, unknown>, 'success');

        if (this.isDryRun || this.isSampleOnlyMode) {
          this.logInfo(
            `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would import museum: ${group.key}` +
              (transformed.monumentReference ? ' (has monument link)' : '') +
              (transformed.logos.length > 0 ? ` (${transformed.logos.length} logos)` : '')
          );
          this.registerEntity(
            'sample-museum-' + group.key,
            transformed.backwardCompatibility,
            'partner'
          );

          // Track deferred monument link even in dry-run for reporting
          if (transformed.monumentReference) {
            this.deferredMonumentLinks.push({
              partnerId: 'sample-museum-' + group.key,
              partnerBackwardCompat: transformed.backwardCompatibility,
              monumentReference: transformed.monumentReference,
            });
            monumentLinksCount++;
          }

          result.imported++;
          this.showProgress();
          continue;
        }

        // Create partner
        const partnerId = await this.context.strategy.writePartner(transformed.data);
        this.registerEntity(partnerId, transformed.backwardCompatibility, 'partner');

        // Track deferred monument link for later resolution
        if (transformed.monumentReference) {
          this.deferredMonumentLinks.push({
            partnerId,
            partnerBackwardCompat: transformed.backwardCompatibility,
            monumentReference: transformed.monumentReference,
          });
          monumentLinksCount++;
        }

        // Create translations
        for (const translation of group.translations) {
          try {
            const translationData = transformMuseumTranslation(group.museum, translation);
            await this.context.strategy.writePartnerTranslation({
              ...translationData.data,
              partner_id: partnerId,
              context_id: this.defaultContextId!,
            });
          } catch (error) {
            const message = error instanceof Error ? error.message : String(error);
            this.logWarning(
              `Failed to create translation for museum ${group.key}:${translation.lang}: ${message}`
            );
          }
        }

        result.imported++;
        this.showProgress();
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        result.errors.push(`Museum ${group.key}: ${message}`);
        this.logError(`Museum ${group.key}`, message);
        this.showError();
      }
    }

    this.logInfo(`  Museums with monument location links: ${monumentLinksCount}`);
    this.logInfo(`  Total logos to import: ${logosCount}`);

    return result;
  }

  private async importInstitutions(): Promise<ImportResult> {
    const result = this.createResult();

    // Query legacy institutions
    const institutions = await this.context.legacyDb.query<LegacyInstitution>(
      'SELECT * FROM mwnf3.institutions ORDER BY institution_id, country'
    );

    const institutionNames = await this.context.legacyDb.query<LegacyInstitutionName>(
      'SELECT * FROM mwnf3.institutionnames ORDER BY institution_id, country, lang'
    );

    const grouped = groupInstitutionsByKey(institutions, institutionNames);
    this.logInfo(`Found ${grouped.length} unique institutions`);

    let logosCount = 0;

    for (const group of grouped) {
      try {
        const transformed = transformInstitution(group.institution);

        // Check if already exists
        if (this.entityExists(transformed.backwardCompatibility, 'partner')) {
          result.skipped++;
          this.showSkipped();
          continue;
        }

        // Count logos for reporting
        if (transformed.logos.length > 0) {
          logosCount += transformed.logos.length;
        }

        // Collect sample
        this.collectSample(
          'institution',
          group.institution as unknown as Record<string, unknown>,
          'success'
        );

        if (this.isDryRun || this.isSampleOnlyMode) {
          this.logInfo(
            `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would import institution: ${group.key}` +
              (transformed.logos.length > 0 ? ` (${transformed.logos.length} logos)` : '')
          );
          this.registerEntity(
            'sample-institution-' + group.key,
            transformed.backwardCompatibility,
            'partner'
          );
          result.imported++;
          this.showProgress();
          continue;
        }

        // Create partner
        const partnerId = await this.context.strategy.writePartner(transformed.data);
        this.registerEntity(partnerId, transformed.backwardCompatibility, 'partner');

        // Create translations
        for (const translation of group.translations) {
          try {
            const translationData = transformInstitutionTranslation(group.institution, translation);
            await this.context.strategy.writePartnerTranslation({
              ...translationData.data,
              partner_id: partnerId,
              context_id: this.defaultContextId!,
            });
          } catch (error) {
            const message = error instanceof Error ? error.message : String(error);
            this.logWarning(
              `Failed to create translation for institution ${group.key}:${translation.lang}: ${message}`
            );
          }
        }

        result.imported++;
        this.showProgress();
      } catch (error) {
        const message = error instanceof Error ? error.message : String(error);
        result.errors.push(`Institution ${group.key}: ${message}`);
        this.logError(`Institution ${group.key}`, message);
        this.showError();
      }
    }

    this.logInfo(`  Total logos to import: ${logosCount}`);

    return result;
  }
}
