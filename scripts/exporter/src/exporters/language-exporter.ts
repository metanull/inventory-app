import type { ExportResult } from '../core/types.js';
import { BaseExporter } from './base-exporter.js';

interface LangRow {
  id: string;
  internal_name: string;
  backward_compatibility: string;
}

interface LangTranslationRow {
  language_id: string;
  display_language_id: string;
  name: string;
}

export class LanguageExporter extends BaseExporter {
  getName(): string {
    return 'Languages';
  }

  async export(): Promise<ExportResult> {
    this.logger.info('Exporting languages.json...');

    const langs = await this.db.query<LangRow>(`SELECT id, internal_name, backward_compatibility FROM languages ORDER BY id`);
    const translations = await this.db.query<LangTranslationRow>(`SELECT language_id, display_language_id, name FROM language_translations`);

    // Build lookup: language_id -> display_language_id -> name
    const translationMap = new Map<string, Map<string, string>>();
    for (const t of translations) {
      if (!translationMap.has(t.language_id)) {
        translationMap.set(t.language_id, new Map());
      }
      translationMap.get(t.language_id)!.set(t.display_language_id, t.name);
    }

    // Also build id -> backward_compatibility for resolving display_language_id keys
    const idToCode = new Map<string, string>(langs.map((l) => [l.id, l.backward_compatibility]));

    const output = langs.map((lang) => {
      const rawTranslations = translationMap.get(lang.id) ?? new Map<string, string>();
      const translations: Record<string, string> = {};
      for (const [displayLangId, name] of rawTranslations) {
        const code = idToCode.get(displayLangId);
        if (code) {
          translations[code] = name;
        }
      }
      return {
        id: lang.id,
        code: lang.backward_compatibility,
        internal_name: lang.internal_name,
        translations,
      };
    });

    await this.writeJson('languages.json', output);
    this.logger.success(`languages.json (${output.length} languages)`);

    return { file: 'languages.json', count: output.length };
  }
}
