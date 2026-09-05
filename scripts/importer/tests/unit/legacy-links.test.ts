/**
 * A curator's link into their own exhibition is rewritten into the hash route
 * of the website that replaces it, in the importer and nowhere else: the
 * published Markdown works as it is, and no website rewrites a text.
 */

import { describe, expect, it } from 'vitest';
import { localiseConverted, localiseLegacyLinks } from '../../src/utils/legacy-links.js';
import { convertHtmlToMarkdown } from '../../src/utils/html-to-markdown.js';

const water = { host: 'https://exhibitions.museumwnf.org', slug: 'water_in_islam', shared: true };
const carpets = { host: 'https://carpets.museumwnf.org', slug: 'carpets' };

describe('localiseLegacyLinks', () => {
  it('turns a same-site address into a hash route and drops the language segment', () => {
    expect(
      localiseLegacyLinks('[Themes](https://exhibitions.museumwnf.org/water_in_islam/en/themes)', water)
    ).toBe('[Themes](#/themes)');
    expect(localiseLegacyLinks('https://exhibitions.museumwnf.org/water_in_islam/fr/theme/3/overview', water)).toBe(
      '#/theme/3/overview'
    );
  });

  it('handles a gallery on its own host, with or without the slug', () => {
    expect(localiseLegacyLinks('https://carpets.museumwnf.org/en/collection', carpets)).toBe('#/collection');
    expect(localiseLegacyLinks('https://carpets.museumwnf.org/carpets/en/about', carpets)).toBe('#/about');
    expect(localiseLegacyLinks('http://carpets.museumwnf.org/', carpets)).toBe('#/');
  });

  it('consumes the whitespace a curator left inside the address', () => {
    expect(localiseLegacyLinks('[Themes](< https://exhibitions.museumwnf.org/water_in_islam/en/themes>)', water)).toBe(
      '[Themes](<#/themes>)'
    );
    expect(localiseLegacyLinks('<a href=" https://exhibitions.museumwnf.org/water_in_islam/en/themes">', water)).toBe(
      '<a href="#/themes">'
    );
  });

  it('leaves every other link exactly as written', () => {
    const text =
      'See [MWNF](https://www.museumwnf.org/about) and [another](https://exhibitions.museumwnf.org/colours/en/themes).';
    expect(localiseLegacyLinks(text, water)).toBe(text);
  });

  it('does nothing without a host', () => {
    const text = 'https://exhibitions.museumwnf.org/water_in_islam/en/themes';
    expect(localiseLegacyLinks(text, { host: null, slug: 'water_in_islam' })).toBe(text);
    expect(localiseLegacyLinks(null, water)).toBe('');
  });

  it('never claims the shared exhibitions host itself for one exhibition', () => {
    const text = 'https://exhibitions.museumwnf.org/en/list';
    expect(localiseLegacyLinks(text, water)).toBe(text);
  });
});

describe('localiseConverted', () => {
  it('converts the HTML first, then rewrites the destinations the conversion produced', () => {
    const html =
      '<p>Visit the <a href="https://exhibitions.museumwnf.org/water_in_islam/en/themes">themes</a>.</p>';
    expect(localiseConverted(convertHtmlToMarkdown, html, water)).toBe('Visit the [themes](#/themes).');
  });

  it('only converts when no address is known', () => {
    const html = '<p><a href="https://exhibitions.museumwnf.org/water_in_islam/en/themes">themes</a></p>';
    expect(localiseConverted(convertHtmlToMarkdown, html, undefined)).toBe(
      '[themes](https://exhibitions.museumwnf.org/water_in_islam/en/themes)'
    );
  });
});
