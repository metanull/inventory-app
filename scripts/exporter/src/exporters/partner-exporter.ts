import type { ExportResult } from '../core/types.js';
import { BaseExporter } from './base-exporter.js';

interface PartnerRow {
  id: string;
  internal_name: string;
  backward_compatibility: string | null;
  type: string;
  country_id: string | null;
}

interface PartnerTranslationRow {
  partner_id: string;
  language_id: string;
  name: string;
  description: string | null;
  city_display: string | null;
  contact_website: string | null;
}

interface PartnerImageRow {
  partner_id: string;
  id: string;
  path: string;
  alt_text: string | null;
  display_order: number;
}

export class PartnerExporter extends BaseExporter {
  getName(): string {
    return 'Partners';
  }

  async export(): Promise<ExportResult> {
    this.logger.info('Exporting partners.json...');

    const ph = this.placeholders(this.projectIds.length);

    // Partners that have at least one item in the given projects
    const partners = await this.db.query<PartnerRow>(
      `SELECT DISTINCT p.id, p.internal_name, p.backward_compatibility, p.type, p.country_id
       FROM partners p
       JOIN items i ON i.partner_id = p.id
       WHERE i.project_id IN (${ph})
       ORDER BY p.internal_name`,
      this.projectIds
    );

    if (partners.length === 0) {
      await this.writeJson('partners.json', []);
      this.logger.warning('partners.json (0 partners)');
      return { file: 'partners.json', count: 0 };
    }

    const partnerIds = partners.map((p) => p.id);
    const partnerPh = this.placeholders(partnerIds.length);

    const [translations, images] = await Promise.all([
      this.db.query<PartnerTranslationRow>(
        `SELECT pt.partner_id, pt.language_id, pt.name, pt.description, pt.city_display, pt.contact_website
         FROM partner_translations pt
         WHERE pt.partner_id IN (${partnerPh})`,
        partnerIds
      ),
      this.db.query<PartnerImageRow>(
        `SELECT pi.partner_id, pi.id, pi.path, pi.alt_text, pi.display_order
         FROM partner_images pi
         WHERE pi.partner_id IN (${partnerPh})
         ORDER BY pi.partner_id, pi.display_order`,
        partnerIds
      ),
    ]);

    const langCodeMap = await this.buildLangCodeMap();

    // Build translation map: partner_id -> lang_code -> fields
    const translationMap = new Map<string, Record<string, Record<string, string | null>>>();
    for (const t of translations) {
      if (!translationMap.has(t.partner_id)) {
        translationMap.set(t.partner_id, {});
      }
      const code = langCodeMap.get(t.language_id);
      if (code) {
        translationMap.get(t.partner_id)![code] = {
          name: t.name,
          description: t.description,
          city: t.city_display,
          website: t.contact_website,
        };
      }
    }

    // Build image map: partner_id -> images[]
    const imageMap = new Map<string, { url: string; alt_text: string | null; display_order: number }[]>();
    for (const img of images) {
      if (!imageMap.has(img.partner_id)) {
        imageMap.set(img.partner_id, []);
      }
      imageMap.get(img.partner_id)!.push({
        url: this.imageUrl(img.path),
        alt_text: img.alt_text,
        display_order: img.display_order,
      });
    }

    const output = partners.map((p) => ({
      id: p.id,
      type: p.type,
      country_id: p.country_id,
      translations: translationMap.get(p.id) ?? {},
      images: imageMap.get(p.id) ?? [],
    }));

    await this.writeJson('partners.json', output);
    this.logger.success(`partners.json (${output.length} partners)`);

    return { file: 'partners.json', count: output.length };
  }

  private async buildLangCodeMap(): Promise<Map<string, string>> {
    const rows = await this.db.query<{ id: string; backward_compatibility: string }>(
      `SELECT id, backward_compatibility FROM languages`
    );
    return new Map(rows.map((r) => [r.id, r.backward_compatibility]));
  }
}
