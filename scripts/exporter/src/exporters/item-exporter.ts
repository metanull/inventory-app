import type { ExportResult } from '../core/types.js';
import { BaseExporter } from './base-exporter.js';

interface ItemRow {
  id: string;
  internal_name: string;
  backward_compatibility: string | null;
  type: string;
  partner_id: string | null;
  country_id: string | null;
  collection_id: string | null;
  owner_reference: string | null;
  mwnf_reference: string | null;
  latitude: string | null;
  longitude: string | null;
}

interface ItemTranslationRow {
  item_id: string;
  language_id: string;
  name: string;
  alternate_name: string | null;
  description: string | null;
  type: string | null;
  holder: string | null;
  owner: string | null;
  dates: string | null;
  location: string | null;
  dimensions: string | null;
  place_of_production: string | null;
  bibliography: string | null;
  provenance: string | null;
}

interface ItemImageRow {
  item_id: string;
  id: string;
  path: string;
  alt_text: string | null;
  display_order: number;
}

interface ItemDynastyRow {
  item_id: string;
  dynasty_id: string;
}

interface ItemTagRow {
  item_id: string;
  tag_description: string;
}

export class ItemExporter extends BaseExporter {
  getName(): string {
    return 'Items';
  }

  async export(): Promise<ExportResult> {
    this.logger.info('Exporting items.json...');

    const ph = this.placeholders(this.projectIds.length);

    const items = await this.db.query<ItemRow>(
      `SELECT id, internal_name, backward_compatibility, type, partner_id, country_id,
              collection_id, owner_reference, mwnf_reference, latitude, longitude
       FROM items
       WHERE project_id IN (${ph})
       ORDER BY type, internal_name`,
      this.projectIds
    );

    if (items.length === 0) {
      await this.writeJson('items.json', []);
      this.logger.warning('items.json (0 items)');
      return { file: 'items.json', count: 0 };
    }

    const itemIds = items.map((i) => i.id);
    const itemPh = this.placeholders(itemIds.length);

    const [translations, images, dynastyLinks, tagLinks] = await Promise.all([
      this.db.query<ItemTranslationRow>(
        `SELECT it.item_id, it.language_id, it.name, it.alternate_name, it.description,
                it.type, it.holder, it.owner, it.dates, it.location, it.dimensions,
                it.place_of_production, it.bibliography, it.provenance
         FROM item_translations it
         WHERE it.item_id IN (${itemPh})`,
        itemIds
      ),
      this.db.query<ItemImageRow>(
        `SELECT ii.item_id, ii.id, ii.path, ii.alt_text, ii.display_order
         FROM item_images ii
         WHERE ii.item_id IN (${itemPh})
         ORDER BY ii.item_id, ii.display_order`,
        itemIds
      ),
      this.db.query<ItemDynastyRow>(
        `SELECT item_id, dynasty_id FROM item_dynasty WHERE item_id IN (${itemPh})`,
        itemIds
      ),
      this.db.query<ItemTagRow>(
        `SELECT it.item_id, t.description AS tag_description
         FROM item_tag it
         JOIN tags t ON t.id = it.tag_id
         WHERE it.item_id IN (${itemPh})`,
        itemIds
      ),
    ]);

    const langCodeMap = await this.buildLangCodeMap();

    // Build translation map: item_id -> lang_code -> fields
    const translationMap = new Map<string, Record<string, Record<string, string | null>>>();
    for (const t of translations) {
      if (!translationMap.has(t.item_id)) {
        translationMap.set(t.item_id, {});
      }
      const code = langCodeMap.get(t.language_id);
      if (code) {
        translationMap.get(t.item_id)![code] = {
          name: t.name,
          alternate_name: t.alternate_name,
          description: t.description,
          type: t.type,
          holder: t.holder,
          owner: t.owner,
          dates: t.dates,
          location: t.location,
          dimensions: t.dimensions,
          place_of_production: t.place_of_production,
          bibliography: t.bibliography,
          provenance: t.provenance,
        };
      }
    }

    // Build image map: item_id -> images[]
    const imageMap = new Map<string, { url: string; alt_text: string | null; display_order: number }[]>();
    for (const img of images) {
      if (!imageMap.has(img.item_id)) {
        imageMap.set(img.item_id, []);
      }
      imageMap.get(img.item_id)!.push({
        url: this.imageUrl(img.path),
        alt_text: img.alt_text,
        display_order: img.display_order,
      });
    }

    // Build dynasty map: item_id -> dynasty_ids[]
    const dynastyMap = new Map<string, string[]>();
    for (const link of dynastyLinks) {
      if (!dynastyMap.has(link.item_id)) {
        dynastyMap.set(link.item_id, []);
      }
      dynastyMap.get(link.item_id)!.push(link.dynasty_id);
    }

    // Build tag map: item_id -> tag descriptions[]
    const tagMap = new Map<string, string[]>();
    for (const link of tagLinks) {
      if (!tagMap.has(link.item_id)) {
        tagMap.set(link.item_id, []);
      }
      tagMap.get(link.item_id)!.push(link.tag_description);
    }

    const output = items.map((item) => ({
      id: item.id,
      type: item.type,
      partner_id: item.partner_id,
      country_id: item.country_id,
      collection_id: item.collection_id,
      owner_reference: item.owner_reference,
      mwnf_reference: item.mwnf_reference,
      latitude: item.latitude ? parseFloat(item.latitude) : null,
      longitude: item.longitude ? parseFloat(item.longitude) : null,
      translations: translationMap.get(item.id) ?? {},
      images: imageMap.get(item.id) ?? [],
      dynasty_ids: dynastyMap.get(item.id) ?? [],
      tags: tagMap.get(item.id) ?? [],
    }));

    await this.writeJson('items.json', output);
    this.logger.success(`items.json (${output.length} items)`);

    return { file: 'items.json', count: output.length };
  }

  private async buildLangCodeMap(): Promise<Map<string, string>> {
    const rows = await this.db.query<{ id: string; backward_compatibility: string }>(
      `SELECT id, backward_compatibility FROM languages`
    );
    return new Map(rows.map((r) => [r.id, r.backward_compatibility]));
  }
}
