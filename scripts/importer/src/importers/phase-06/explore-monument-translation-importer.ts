/**
 * Explore Monument Translation Importer (Story 13.1)
 *
 * Imports 2,061 exploremonumentext rows → ItemTranslation records.
 * Also imports further_reading (74 rows) → ItemTranslation.extra.further_readings[].
 *
 * Mapping:
 * - name → name
 * - description → description (HTML→Markdown via strategy sanitizer)
 * - related_bibliography → bibliography
 * - date → dates
 * - styles → type
 * - prepared_by → author_id via AuthorHelper
 * - Extras: how_to_reach, info, contact, history, note, abstract, further_reading,
 *   url_prog_pdf, pdf_text, url_prog_doc, monument_contact object
 *
 * BC: mwnf3_explore:monument:{monumentId}:translation:{languageId}
 *
 * Dependencies:
 * - ExploreMonumentImporter (monument items must exist)
 * - ExploreContextImporter
 */

import { BaseImporter } from '../../core/base-importer.js';
import type { ImportResult } from '../../core/types.js';
import { AuthorHelper } from '../../helpers/author-helper.js';

interface LegacyMonumentText {
  monumentId: number;
  langId: string;
  name: string | null;
  description: string | null;
  related_bibliography: string | null;
  date: string | null;
  styles: string | null;
  prepared_by: string | null;
  how_to_reach: string | null;
  info: string | null;
  contact: string | null;
  history: string | null;
  note: string | null;
  abstract: string | null;
  further_reading: string | null;
  url_prog_pdf: string | null;
  pdf_text: string | null;
  url_prog_doc: string | null;
  institution: string | null;
  address: string | null;
  phone: string | null;
  fax: string | null;
  email: string | null;
  website: string | null;
}

interface LegacyFurtherReading {
  monumentId: number;
  langId: string;
  title: string | null;
  author: string | null;
  url: string | null;
}

export class ExploreMonumentTranslationImporter extends BaseImporter {
  private exploreContextId!: string;
  private authorHelper!: AuthorHelper;

  getName(): string {
    return 'ExploreMonumentTranslationImporter';
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

      this.authorHelper = new AuthorHelper(
        this.context.strategy,
        this.context.tracker,
        this.logger
      );

      this.logInfo('Importing Explore monument translations...');

      // Query all monument texts
      const texts = await this.context.legacyDb.query<LegacyMonumentText>(
        `SELECT monumentId, langId, name, description, related_bibliography, date, styles,
                prepared_by, how_to_reach, info, contact, history, note, abstract,
                further_reading, url_prog_pdf, pdf_text, url_prog_doc,
                institution, address, phone, fax, email, website
         FROM mwnf3_explore.exploremonumentext
         ORDER BY monumentId, langId`
      );
      this.logInfo(`Found ${texts.length} monument text rows`);

      // Pre-fetch further_reading
      const furtherReadings = await this.context.legacyDb.query<LegacyFurtherReading>(
        `SELECT monumentId, langId, title, author, url
         FROM mwnf3_explore.exploremonument_further_reading
         ORDER BY monumentId, langId`
      );
      this.logInfo(`Found ${furtherReadings.length} further_reading entries`);

      // Group further readings by monument+lang
      const frByKey = new Map<string, LegacyFurtherReading[]>();
      for (const fr of furtherReadings) {
        const key = `${fr.monumentId}:${fr.langId}`;
        const list = frByKey.get(key) ?? [];
        list.push(fr);
        frByKey.set(key, list);
      }

      for (const text of texts) {
        try {
          // Must have at least a name
          if (!text.name || !text.name.trim()) {
            result.skipped++;
            this.showSkipped();
            continue;
          }

          const monumentBC = `mwnf3_explore:monument:${text.monumentId}`;
          const itemId = await this.getEntityUuidAsync(monumentBC, 'item');
          if (!itemId) {
            this.logWarning(`Monument item not found: ${monumentBC}, skipping translation`);
            result.skipped++;
            this.showSkipped();
            continue;
          }

          const languageId = await this.getLanguageIdByLegacyCodeAsync(text.langId);
          if (!languageId) {
            this.logWarning(
              `Unknown language code '${text.langId}' for monument ${text.monumentId}, skipping`
            );
            result.skipped++;
            this.showSkipped();
            continue;
          }

          const translationBC = `mwnf3_explore:monument:${text.monumentId}:translation:${languageId}`;

          if (await this.entityExistsAsync(translationBC, 'item_translation')) {
            result.skipped++;
            this.showSkipped();
            continue;
          }

          // Resolve author
          let authorId: string | null = null;
          if (text.prepared_by && text.prepared_by.trim()) {
            authorId = await this.authorHelper.findOrCreate(text.prepared_by.trim());
          }

          // Build extra JSON
          const extra: Record<string, unknown> = {};
          if (text.how_to_reach) extra.how_to_reach = text.how_to_reach;
          if (text.info) extra.info = text.info;
          if (text.contact) extra.contact = text.contact;
          if (text.history) extra.history = text.history;
          if (text.note) extra.note = text.note;
          if (text.abstract) extra.abstract = text.abstract;
          if (text.further_reading) extra.further_reading = text.further_reading;
          if (text.url_prog_pdf) extra.url_prog_pdf = text.url_prog_pdf;
          if (text.pdf_text) extra.pdf_text = text.pdf_text;
          if (text.url_prog_doc) extra.url_prog_doc = text.url_prog_doc;

          // Monument contact object
          const contactFields: Record<string, string> = {};
          if (text.institution) contactFields.institution = text.institution;
          if (text.address) contactFields.address = text.address;
          if (text.phone) contactFields.phone = text.phone;
          if (text.fax) contactFields.fax = text.fax;
          if (text.email) contactFields.email = text.email;
          if (text.website) contactFields.website = text.website;
          if (Object.keys(contactFields).length > 0) {
            extra.monument_contact = contactFields;
          }

          // Further readings
          const frKey = `${text.monumentId}:${text.langId}`;
          const frEntries = frByKey.get(frKey);
          if (frEntries && frEntries.length > 0) {
            extra.further_readings = frEntries.map((fr) => {
              const entry: Record<string, string> = {};
              if (fr.title) entry.title = fr.title;
              if (fr.author) entry.author = fr.author;
              if (fr.url) entry.url = fr.url;
              return entry;
            });
          }

          const extraJson = Object.keys(extra).length > 0 ? JSON.stringify(extra) : null;

          if (this.isDryRun || this.isSampleOnlyMode) {
            this.logInfo(
              `[${this.isSampleOnlyMode ? 'SAMPLE' : 'DRY-RUN'}] Would create monument translation: monument ${text.monumentId} / ${languageId}`
            );
            result.imported++;
            this.showProgress();
            continue;
          }

          await this.context.strategy.writeItemTranslation({
            item_id: itemId,
            language_id: languageId,
            context_id: this.exploreContextId,
            backward_compatibility: translationBC,
            name: text.name.trim(),
            description: text.description ?? '',
            bibliography: text.related_bibliography ?? null,
            dates: text.date ?? null,
            type: text.styles ?? null,
            author_id: authorId,
            extra: extraJson,
          });

          result.imported++;
          this.showProgress();
        } catch (error) {
          result.success = false;
          const errorMessage = error instanceof Error ? error.message : String(error);
          result.errors.push(`Monument text ${text.monumentId}/${text.langId}: ${errorMessage}`);
          this.logError('ExploreMonumentTranslationImporter', errorMessage, {
            monumentId: text.monumentId,
            langId: text.langId,
          });
          this.showError();
        }
      }
    } catch (error) {
      result.success = false;
      const errorMessage = error instanceof Error ? error.message : String(error);
      result.errors.push(`Error in monument translation import: ${errorMessage}`);
      this.logError('ExploreMonumentTranslationImporter', errorMessage);
      this.showError();
    }

    return result;
  }
}
