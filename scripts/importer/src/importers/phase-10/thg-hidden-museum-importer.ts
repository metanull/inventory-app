/**
 * THG Hidden Museum Importer
 *
 * Imports the museums an exhibition's curators explicitly suppressed from its
 * partner pages.
 *
 * Partner lists are not stored: the exporter derives them from the exhibition's
 * membership (the partners of the items it shows). Legacy does the same and then
 * subtracts this table, so without it a rebuilt site would display museums the
 * curators deliberately hid.
 *
 * Legacy schema:
 * - mwnf3_thematic_gallery.exhibition_hidden_mwnf3_museums (gallery_id, museum_id, country_id)
 *   - the sibling exhibition_hidden_sh_museums table is empty and is not imported
 *
 * New schema:
 * - collections.extra.thg_gallery.hidden_partners on the exhibition collection:
 *     [ { "backward_compatibility": "mwnf3:museums:Mus52:uk", "partner_id": "<uuid>" }, … ]
 *
 * Both the key and the resolved id are stored: the key keeps the entry readable
 * and diffable against legacy, the id spares the exporter a lookup. A museum
 * that never resolved to a partner is kept with a null partner_id rather than
 * dropped — an unresolvable exclusion still has to be honoured.
 *
 * Dependencies:
 * - ThgGalleryImporter (exhibition collections)
 * - PartnerImporter (museums as partners)
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';
import { formatBackwardCompatibility } from '../../utils/backward-compatibility.js';

interface LegacyHiddenMuseum {
  gallery_id: number;
  museum_id: string;
  country_id: string;
}

interface HiddenPartner {
  backward_compatibility: string;
  partner_id: string | null;
}

export class ThgHiddenMuseumImporter extends BaseImporter {
  getName(): string {
    return 'ThgHiddenMuseumImporter';
  }

  async import(): Promise<ImportResult> {
    const result = this.createResult();

    try {
      this.logInfo('Importing hidden-museum exclusions for exhibitions...');

      let hidden: LegacyHiddenMuseum[];
      try {
        hidden = await this.context.legacyDb.query<LegacyHiddenMuseum>(
          `SELECT gallery_id, museum_id, country_id
           FROM mwnf3_thematic_gallery.exhibition_hidden_mwnf3_museums
           ORDER BY gallery_id, country_id, museum_id`
        );
      } catch (queryError) {
        const message = queryError instanceof Error ? queryError.message : String(queryError);
        if (message.includes("doesn't exist") || message.includes('Unknown column')) {
          this.logInfo(
            `⚠️ Skipping: Legacy exhibition_hidden_mwnf3_museums table not available (${message})`
          );
          result.warnings = result.warnings || [];
          result.warnings.push(
            `Legacy exhibition_hidden_mwnf3_museums table not available: ${message}`
          );
          return result;
        }
        throw queryError;
      }

      this.logInfo(`Found ${hidden.length} hidden-museum rows to process`);

      // One write per gallery, so the exclusion list is replaced as a whole
      const byGallery = new Map<number, LegacyHiddenMuseum[]>();
      for (const row of hidden) {
        const existing = byGallery.get(row.gallery_id);
        if (existing) {
          existing.push(row);
        } else {
          byGallery.set(row.gallery_id, [row]);
        }
      }

      for (const [galleryId, rows] of byGallery) {
        try {
          const galleryBackwardCompat = `mwnf3_thematic_gallery:thg_gallery:${galleryId}`;
          const collectionId = await this.getEntityUuidAsync(galleryBackwardCompat, 'collection');
          if (!collectionId) {
            result.warnings = result.warnings || [];
            result.warnings.push(
              `Hidden museums for gallery ${galleryId}: collection not found (${galleryBackwardCompat}), skipping`
            );
            result.skipped += rows.length;
            this.showSkipped();
            continue;
          }

          const hiddenPartners: HiddenPartner[] = [];
          for (const row of rows) {
            const partnerBackwardCompat = formatBackwardCompatibility({
              schema: 'mwnf3',
              table: 'museums',
              pkValues: [row.museum_id, row.country_id],
            });

            const partnerId = await this.getEntityUuidAsync(partnerBackwardCompat, 'partner');
            if (!partnerId) {
              result.warnings = result.warnings || [];
              result.warnings.push(
                `Hidden museum ${partnerBackwardCompat} (gallery ${galleryId}): partner not found, kept as an unresolved exclusion`
              );
            }

            hiddenPartners.push({
              backward_compatibility: partnerBackwardCompat,
              partner_id: partnerId ?? null,
            });

            this.collectSample(
              'thg_exhibition_hidden_museum',
              { ...row, resolved_partner_backward_compat: partnerBackwardCompat },
              'success'
            );

            result.imported++;
            this.showProgress();
          }

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would store ${hiddenPartners.length} hidden partners on ${galleryBackwardCompat}`
            );
            continue;
          }

          const existingExtra =
            (await this.context.strategy.getCollectionExtra(collectionId)) ?? {};
          const galleryExtra = {
            ...((existingExtra.thg_gallery as Record<string, unknown> | undefined) ?? {}),
            hidden_partners: hiddenPartners,
          };

          await this.context.strategy.setCollectionExtra(
            collectionId,
            JSON.stringify({ ...existingExtra, thg_gallery: galleryExtra })
          );
        } catch (error) {
          const message = error instanceof Error ? error.message : String(error);
          result.errors.push(`Hidden museums for gallery ${galleryId}: ${message}`);
          this.logError(`Hidden museums for gallery ${galleryId}`, message);
          this.showError();
        }
      }

      this.showSummary(result.imported, result.skipped, result.errors.length);
    } catch (error) {
      const message = error instanceof Error ? error.message : String(error);
      result.success = false;
      result.errors.push(message);
      this.logError('ThgHiddenMuseumImporter', message);
    }

    return result;
  }
}
