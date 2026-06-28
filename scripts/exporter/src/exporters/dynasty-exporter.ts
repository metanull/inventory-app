import type { ExportResult } from '../core/types.js';
import { BaseExporter } from './base-exporter.js';

interface DynastyRow {
  id: string;
  backward_compatibility: string | null;
  from_ah: number | null;
  to_ah: number | null;
  from_ad: number | null;
  to_ad: number | null;
}

interface DynastyTranslationRow {
  dynasty_id: string;
  language_id: string;
  name: string | null;
  also_known_as: string | null;
  area: string | null;
  history: string | null;
  date_description_ah: string | null;
  date_description_ad: string | null;
}

export class DynastyExporter extends BaseExporter {
  getName(): string {
    return 'Dynasties';
  }

  async export(): Promise<ExportResult> {
    this.logger.info('Exporting dynasties.json...');

    // Export only dynasties linked to items in the given projects
    const ph = this.placeholders(this.projectIds.length);
    const dynasties = await this.db.query<DynastyRow>(
      `SELECT DISTINCT d.id, d.backward_compatibility, d.from_ah, d.to_ah, d.from_ad, d.to_ad
       FROM dynasties d
       JOIN item_dynasty id2 ON d.id = id2.dynasty_id
       JOIN items i ON id2.item_id = i.id
       WHERE i.project_id IN (${ph})
       ORDER BY d.from_ad`,
      this.projectIds
    );

    if (dynasties.length === 0) {
      await this.writeJson('dynasties.json', []);
      this.logger.warning('dynasties.json (0 dynasties — none linked to items in these projects)');
      return { file: 'dynasties.json', count: 0 };
    }

    const dynastyIds = dynasties.map((d) => d.id);
    const translations = await this.db.query<DynastyTranslationRow>(
      `SELECT dynasty_id, language_id, name, also_known_as, area, history, date_description_ah, date_description_ad
       FROM dynasty_translations
       WHERE dynasty_id IN (${this.placeholders(dynastyIds.length)})`,
      dynastyIds
    );

    const langCodeMap = await this.buildLangCodeMap();

    // Build: dynasty_id -> lang_code -> translation fields
    const translationMap = new Map<string, Record<string, Record<string, string | null>>>();
    for (const t of translations) {
      if (!translationMap.has(t.dynasty_id)) {
        translationMap.set(t.dynasty_id, {});
      }
      const code = langCodeMap.get(t.language_id);
      if (code) {
        translationMap.get(t.dynasty_id)![code] = {
          name: t.name,
          also_known_as: t.also_known_as,
          area: t.area,
          history: t.history,
          date_description_ah: t.date_description_ah,
          date_description_ad: t.date_description_ad,
        };
      }
    }

    const output = dynasties.map((d) => ({
      id: d.id,
      backward_compatibility: d.backward_compatibility,
      from_ah: d.from_ah,
      to_ah: d.to_ah,
      from_ad: d.from_ad,
      to_ad: d.to_ad,
      translations: translationMap.get(d.id) ?? {},
    }));

    await this.writeJson('dynasties.json', output);
    this.logger.success(`dynasties.json (${output.length} dynasties)`);

    return { file: 'dynasties.json', count: output.length };
  }

  private async buildLangCodeMap(): Promise<Map<string, string>> {
    const rows = await this.db.query<{ id: string; backward_compatibility: string }>(
      `SELECT id, backward_compatibility FROM languages`
    );
    return new Map(rows.map((r) => [r.id, r.backward_compatibility]));
  }
}
