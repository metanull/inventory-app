/**
 * Partner Importer
 *
 * Imports museums and institutions from legacy database.
 * Creates Partner entities with translations.
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
import type {
  LegacyMuseum,
  LegacyMuseumName,
  LegacyInstitution,
  LegacyInstitutionName,
} from '../../domain/types/index.js';

export class PartnerImporter extends BaseImporter {
  private defaultContextId: string | null = null;

  getName(): string {
    return 'PartnerImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      // Ensure default context exists for partner translations
      await this.ensureDefaultContext();

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

  private async ensureDefaultContext(): Promise<void> {
    const backwardCompat = 'system:default_partner_context';

    // Check if already exists
    const existing = this.getEntityUuid(backwardCompat);
    if (existing) {
      this.defaultContextId = existing;
      return;
    }

    // Check database
    const found = await this.context.strategy.findByBackwardCompatibility(
      'contexts',
      backwardCompat
    );
    if (found) {
      this.defaultContextId = found;
      this.registerEntity(found, backwardCompat, 'context');
      return;
    }

    if (this.isDryRun || this.isSampleOnlyMode) {
      this.defaultContextId = 'sample-default-context';
      this.registerEntity(this.defaultContextId, backwardCompat, 'context');
      return;
    }

    // Create default context
    const contextId = await this.context.strategy.writeContext({
      internal_name: 'default_partners',
      backward_compatibility: backwardCompat,
      is_default: false,
    });

    this.defaultContextId = contextId;
    this.registerEntity(contextId, backwardCompat, 'context');
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

    for (const group of grouped) {
      try {
        const transformed = transformMuseum(group.museum);

        // Check if already exists
        if (this.entityExists(transformed.backwardCompatibility)) {
          result.skipped++;
          this.showSkipped();
          continue;
        }

        // Collect sample
        this.collectSample('museum', group.museum as unknown as Record<string, unknown>, 'success');

        if (this.isDryRun || this.isSampleOnlyMode) {
          this.logInfo(
            `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would import museum: ${group.key}`
          );
          this.registerEntity(
            'sample-museum-' + group.key,
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
        this.logError(`Museum ${group.key}`, error);
        this.showError();
      }
    }

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

    for (const group of grouped) {
      try {
        const transformed = transformInstitution(group.institution);

        // Check if already exists
        if (this.entityExists(transformed.backwardCompatibility)) {
          result.skipped++;
          this.showSkipped();
          continue;
        }

        // Collect sample
        this.collectSample(
          'institution',
          group.institution as unknown as Record<string, unknown>,
          'success'
        );

        if (this.isDryRun || this.isSampleOnlyMode) {
          this.logInfo(
            `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would import institution: ${group.key}`
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
        this.logError(`Institution ${group.key}`, error);
        this.showError();
      }
    }

    return result;
  }
}
