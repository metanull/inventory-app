import type { ExportResult } from '../core/types.js';
import { BaseExporter } from './base-exporter.js';

interface CollectionRow {
  id: string;
  internal_name: string;
  type: string;
  backward_compatibility: string | null;
  parent_id: string | null;
}

interface CollectionTranslationRow {
  collection_id: string;
  language_id: string;
  title: string;
  description: string | null;
  url: string | null;
}

interface CollectionImageRow {
  collection_id: string;
  id: string;
  path: string;
  alt_text: string | null;
  display_order: number;
}

interface CollectionItemRow {
  collection_id: string;
  item_id: string;
  display_order: number | null;
}

export class CollectionExporter extends BaseExporter {
  getName(): string {
    return 'Collections';
  }

  async export(): Promise<ExportResult> {
    this.logger.info('Exporting collections.json...');

    const ph = this.placeholders(this.projectIds.length);

    // Collections that contain items from the given projects
    const collections = await this.db.query<CollectionRow>(
      `SELECT DISTINCT c.id, c.internal_name, c.type, c.backward_compatibility, c.parent_id
       FROM collections c
       JOIN collection_item ci ON c.id = ci.collection_id
       JOIN items i ON ci.item_id = i.id
       WHERE i.project_id IN (${ph})
       ORDER BY c.type, c.internal_name`,
      this.projectIds
    );

    if (collections.length === 0) {
      await this.writeJson('collections.json', []);
      this.logger.warning('collections.json (0 collections)');
      return { file: 'collections.json', count: 0 };
    }

    const collectionIds = collections.map((c) => c.id);
    const colPh = this.placeholders(collectionIds.length);

    const [translations, images, itemLinks] = await Promise.all([
      this.db.query<CollectionTranslationRow>(
        `SELECT ct.collection_id, ct.language_id, ct.title, ct.description, ct.url
         FROM collection_translations ct
         WHERE ct.collection_id IN (${colPh})`,
        collectionIds
      ),
      this.db.query<CollectionImageRow>(
        `SELECT ci.collection_id, ci.id, ci.path, ci.alt_text, ci.display_order
         FROM collection_images ci
         WHERE ci.collection_id IN (${colPh})
         ORDER BY ci.collection_id, ci.display_order`,
        collectionIds
      ),
      this.db.query<CollectionItemRow>(
        `SELECT ci.collection_id, ci.item_id, ci.display_order
         FROM collection_item ci
         JOIN items i ON ci.item_id = i.id
         WHERE ci.collection_id IN (${colPh})
           AND i.project_id IN (${ph})
         ORDER BY ci.collection_id, ci.display_order`,
        [...collectionIds, ...this.projectIds]
      ),
    ]);

    const langCodeMap = await this.buildLangCodeMap();

    // Build translation map: collection_id -> lang_code -> fields
    const translationMap = new Map<string, Record<string, Record<string, string | null>>>();
    for (const t of translations) {
      if (!translationMap.has(t.collection_id)) {
        translationMap.set(t.collection_id, {});
      }
      const code = langCodeMap.get(t.language_id);
      if (code) {
        translationMap.get(t.collection_id)![code] = {
          title: t.title,
          description: t.description,
          url: t.url,
        };
      }
    }

    // Build image map: collection_id -> images[]
    const imageMap = new Map<string, { url: string; alt_text: string | null; display_order: number }[]>();
    for (const img of images) {
      if (!imageMap.has(img.collection_id)) {
        imageMap.set(img.collection_id, []);
      }
      imageMap.get(img.collection_id)!.push({
        url: this.imageUrl(img.path),
        alt_text: img.alt_text,
        display_order: img.display_order,
      });
    }

    // Build item map: collection_id -> item_ids[]
    const itemMap = new Map<string, string[]>();
    for (const link of itemLinks) {
      if (!itemMap.has(link.collection_id)) {
        itemMap.set(link.collection_id, []);
      }
      itemMap.get(link.collection_id)!.push(link.item_id);
    }

    const output = collections.map((c) => ({
      id: c.id,
      type: c.type,
      parent_id: c.parent_id,
      translations: translationMap.get(c.id) ?? {},
      images: imageMap.get(c.id) ?? [],
      item_ids: itemMap.get(c.id) ?? [],
    }));

    await this.writeJson('collections.json', output);
    this.logger.success(`collections.json (${output.length} collections)`);

    return { file: 'collections.json', count: output.length };
  }

  private async buildLangCodeMap(): Promise<Map<string, string>> {
    const rows = await this.db.query<{ id: string; backward_compatibility: string }>(
      `SELECT id, backward_compatibility FROM languages`
    );
    return new Map(rows.map((r) => [r.id, r.backward_compatibility]));
  }
}
