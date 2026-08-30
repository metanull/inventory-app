import { describe, expect, it } from 'vitest';

import { relatedContentCarriesMedia } from '../../src/importers/phase-10/thg-gallery-content-importer.js';

/**
 * `collection_media` needs a URL, so an `exhibition_related_content` entry only
 * belongs there if some row of it — base or translation — actually carries a
 * link or an uploaded document. Everything else is a bibliography and goes to
 * the exhibition collection's `extra.further_readings` instead.
 *
 * The two live exhibitions sit on opposite sides of this test, which is why it
 * reads both levels: gallery 47 (The Use of Colours in Art) has ten bare base
 * rows whose `_i18n` rows each carry a document, and gallery 56 (Water in
 * Islam) has five entries with nothing at either level.
 */
describe('relatedContentCarriesMedia', () => {
  const bare = { link: null, uploaded_document: null };

  it('is true when the base row has a link', () => {
    expect(relatedContentCarriesMedia({ link: 'https://example.org', uploaded_document: null }, [])).toBe(
      true
    );
  });

  it('is true when the base row has an uploaded document', () => {
    expect(relatedContentCarriesMedia({ link: null, uploaded_document: 'docs/x.pdf' }, [])).toBe(
      true
    );
  });

  it('is true when only a translation carries the document — gallery 47', () => {
    expect(
      relatedContentCarriesMedia(bare, [{ link: null, uploaded_document: 'thg/47/liturgy.pdf' }])
    ).toBe(true);
  });

  it('is false when neither level carries anything — gallery 56', () => {
    expect(relatedContentCarriesMedia(bare, [bare, bare])).toBe(false);
  });

  it('treats whitespace-only values as absent', () => {
    expect(relatedContentCarriesMedia({ link: '  ', uploaded_document: '' }, [bare])).toBe(false);
  });
});
