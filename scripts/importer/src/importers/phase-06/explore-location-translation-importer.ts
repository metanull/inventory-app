/**
 * Explore Location Translation Importer (Story 11.4)
 *
 * Enhances existing location collections with:
 * 1. Multilingual translations from locationtranslated (888 rows)
 *    spelling→title, description→description, extras for how_to_reach/info/contact/description_1/prepared_by
 * 2. showOnMonument flag from explorelocation (235 rows) → extra.showOnMonument
 * 3. locations_contact (1 row) → extra.contacts
 *
 * Dependencies:
 * - ExploreLocationImporter (location collections must exist)
 * - ExploreContextImporter
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';

interface LegacyLocationTranslation {
  locationId: number;
  langId: string;
  spelling: string | null;
  description: string | null;
  how_to_reach: string | null;
  info: string | null;
  contact: string | null;
  description_1: string | null;
  prepared_by: string | null;
}

interface LegacyExploreLocation {
  locationId: number;
  showOnMonument: number | null;
}

interface LegacyLocationContact {
  locationId: number;
  name: string | null;
  address: string | null;
  phone: string | null;
  fax: string | null;
  email: string | null;
  website: string | null;
}

export class ExploreLocationTranslationImporter extends BaseImporter {
  private exploreContextId!: string;

  getName(): string {
    return 'ExploreLocationTranslationImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      // Resolve Explore context
      const exploreContextBC = 'mwnf3_explore:context';
      const exploreContextId = await this.getEntityUuidAsync(exploreContextBC, 'context');
      if (!exploreContextId) {
        throw new Error(`Explore context not found (${exploreContextBC}).`);
      }
      this.exploreContextId = exploreContextId;

      this.logInfo('Importing location translations...');

      // 1. Multilingual translations
      const translations = await this.context.legacyDb.query<LegacyLocationTranslation>(
        `SELECT locationId, langId, spelling, description, how_to_reach, info, contact, description_1, prepared_by
         FROM mwnf3_explore.locationtranslated`
      );
      this.logInfo(`Found ${translations.length} location translations`);

      for (const trans of translations) {
        try {
          const locationBC = `mwnf3_explore:location:${trans.locationId}`;
          const collectionId = await this.getEntityUuidAsync(locationBC, 'collection');
          if (!collectionId) {
            this.logWarning(`Location collection not found: ${locationBC}, skipping translation`);
            result.skipped++;
            this.showSkipped();
            continue;
          }

          const languageId = await this.getLanguageIdByLegacyCodeAsync(trans.langId);
          if (!languageId) {
            this.logWarning(
              `Unknown language code '${trans.langId}' for location ${trans.locationId}, skipping`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          if (!trans.spelling || !trans.spelling.trim()) {
            result.skipped++;
            this.showSkipped();
            continue;
          }

          const translationBC = `${locationBC}:multilingual:${languageId}`;

          // Skip if already exists
          if (await this.entityExistsAsync(translationBC, 'collection_translation')) {
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Build extra JSON for additional fields
          const extra: Record<string, unknown> = {};
          if (trans.how_to_reach) extra.how_to_reach = trans.how_to_reach;
          if (trans.info) extra.info = trans.info;
          if (trans.contact) extra.contact = trans.contact;
          if (trans.description_1) extra.description_1 = trans.description_1;
          if (trans.prepared_by) extra.prepared_by = trans.prepared_by;
          const extraJson = Object.keys(extra).length > 0 ? JSON.stringify(extra) : null;

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create location translation: location ${trans.locationId} / ${languageId}`
            );
            result.imported++;
            this.showProgress();
            continue;
          }

          // For English: the original ExploreLocationImporter already created an English translation.
          // If this is English, update the existing translation's extra instead of creating a new one.
          if (languageId === 'eng') {
            if (extraJson) {
              const existingExtra = await this.context.strategy.getCollectionTranslationExtra(
                collectionId,
                'eng'
              );
              const merged = existingExtra ?? {};
              Object.assign(merged, extra);
              await this.context.strategy.setCollectionTranslationExtra(
                collectionId,
                'eng',
                JSON.stringify(merged)
              );
            }
            result.imported++;
            this.showProgress();
            continue;
          }

          await this.context.strategy.writeCollectionTranslation({
            collection_id: collectionId,
            language_id: languageId,
            context_id: this.exploreContextId,
            backward_compatibility: translationBC,
            title: trans.spelling,
            description: trans.description ?? '',
            extra: extraJson,
          });

          result.imported++;
          this.showProgress();
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          this.logWarning(
            `Failed location translation ${trans.locationId}/${trans.langId}: ${message}`
          );
        }
      }

      // 2. showOnMonument flag from explorelocation → extra.showOnMonument
      const exploreLocations = await this.context.legacyDb.query<LegacyExploreLocation>(
        `SELECT locationId, showOnMonument FROM mwnf3_explore.explorelocation`
      );
      this.logInfo(`Found ${exploreLocations.length} explorelocation visibility flags`);

      for (const loc of exploreLocations) {
        try {
          const locationBC = `mwnf3_explore:location:${loc.locationId}`;
          const collectionId = await this.getEntityUuidAsync(locationBC, 'collection');
          if (!collectionId) continue;

          if (this.isDryRun || this.isSampleOnlyMode) continue;

          const existingExtra = await this.context.strategy.getCollectionTranslationExtra(
            collectionId,
            'eng'
          );
          const extra = existingExtra ?? {};
          extra.showOnMonument = loc.showOnMonument === 1;

          await this.context.strategy.setCollectionTranslationExtra(
            collectionId,
            'eng',
            JSON.stringify(extra)
          );
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          this.logWarning(`Failed showOnMonument for location ${loc.locationId}: ${message}`);
        }
      }

      // 3. locations_contact → extra.contacts
      const contacts = await this.context.legacyDb.query<LegacyLocationContact>(
        `SELECT locationId, name, address, phone, fax, email, website FROM mwnf3_explore.locations_contact`
      );
      this.logInfo(`Found ${contacts.length} location contacts`);

      for (const c of contacts) {
        try {
          const locationBC = `mwnf3_explore:location:${c.locationId}`;
          const collectionId = await this.getEntityUuidAsync(locationBC, 'collection');
          if (!collectionId) continue;

          if (this.isDryRun || this.isSampleOnlyMode) continue;

          const existingExtra = await this.context.strategy.getCollectionTranslationExtra(
            collectionId,
            'eng'
          );
          const extra = existingExtra ?? {};
          const contactObj: Record<string, string> = {};
          if (c.name) contactObj.name = c.name;
          if (c.address) contactObj.address = c.address;
          if (c.phone) contactObj.phone = c.phone;
          if (c.fax) contactObj.fax = c.fax;
          if (c.email) contactObj.email = c.email;
          if (c.website) contactObj.website = c.website;

          if (Object.keys(contactObj).length > 0) {
            const existing = extra.contacts as Array<Record<string, string>> | undefined;
            extra.contacts = existing ? [...existing, contactObj] : [contactObj];
            await this.context.strategy.setCollectionTranslationExtra(
              collectionId,
              'eng',
              JSON.stringify(extra)
            );
          }
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          this.logWarning(`Failed contact for location ${c.locationId}: ${message}`);
        }
      }
    } catch (error) {
      result.success = false;
      const errorMessage = error instanceof Error ? error.message : String(error);
      result.errors.push(`Error in location translation import: ${errorMessage}`);
      this.logError('ExploreLocationTranslationImporter', errorMessage);
      this.showError();
    }

    return result;
  }
}
