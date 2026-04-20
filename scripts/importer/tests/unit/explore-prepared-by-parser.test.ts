import { describe, expect, it } from 'vitest';

import { parseExplorePreparedBy } from '../../src/helpers/explore-prepared-by-parser.js';

describe('parseExplorePreparedBy', () => {
  it('extracts author and editor roles from the recurring Francesco Petrucci block', () => {
    const parsed = parseExplorePreparedBy(
      'arch. Francesco Petrucci<br/>\nCopy-editor (Italian): Saverio Capozzi<br/>\nTranslator: Lavinia Amenduni<br/>\nCopy-editor (English): Danny de la Vega<br/>\n© Museum With No Frontiers (MWNF)'
    );

    expect(parsed).toEqual({
      authorName: 'arch. Francesco Petrucci',
      textCopyEditorName: 'Saverio Capozzi',
      translatorName: 'Lavinia Amenduni',
      translationCopyEditorName: 'Danny de la Vega',
      preserveRawInExtra: true,
    });
  });

  it('extracts the primary author from attribution-based prepared_by blocks', () => {
    const parsed = parseExplorePreparedBy(
      'Museo Senza Frontiere, Saverio Capozzi, sulla base di materiale fornito da:<br/>\narch. Francesco Petrucci; © Museum With No Frontiers (MWNF).'
    );

    expect(parsed).toEqual({
      authorName: 'arch. Francesco Petrucci',
      textCopyEditorName: null,
      translatorName: null,
      translationCopyEditorName: null,
      preserveRawInExtra: true,
    });
  });

  it('extracts labeled author and copyeditor pairs', () => {
    const parsed = parseExplorePreparedBy(
      '<br/>\nAuthor: Fiza Ishaq<br/>\nCopyeditor: Amber "AJ" Stephens'
    );

    expect(parsed).toEqual({
      authorName: 'Fiza Ishaq',
      textCopyEditorName: 'Amber "AJ" Stephens',
      translatorName: null,
      translationCopyEditorName: null,
      preserveRawInExtra: true,
    });
  });

  it('returns null for plain single-name values', () => {
    expect(parseExplorePreparedBy('John Doe')).toBeNull();
  });
});
