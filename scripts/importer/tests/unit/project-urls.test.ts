/**
 * Tests for the project URL map utility
 */

import { describe, it, expect } from 'vitest';
import { lookupProjectUrls, PROJECT_URL_MAP } from '../../src/utils/project-urls.js';

describe('lookupProjectUrls', () => {
  it('resolves the Islamic Art site for ISL', () => {
    expect(lookupProjectUrls('ISL')).toEqual({
      site_url: 'https://islamicart.museumwnf.org',
      related_database_url: 'https://islamicart.museumwnf.org/database.php',
      artistic_introduction_url: 'https://islamicart.museumwnf.org/gai/ISL/',
    });
  });

  it('resolves EPM to the same Islamic Art site and the ISL-path artistic introduction', () => {
    const epm = lookupProjectUrls('EPM');
    const isl = lookupProjectUrls('ISL');
    expect(epm.site_url).toBe(isl.site_url);
    expect(epm.related_database_url).toBe(isl.related_database_url);
    // Legacy always points EPM's artistic introduction at the ISL path.
    expect(epm.artistic_introduction_url).toBe('https://islamicart.museumwnf.org/gai/ISL/');
  });

  it('resolves the Baroque Art site for BAR, with no artistic introduction', () => {
    expect(lookupProjectUrls('BAR')).toEqual({
      site_url: 'https://baroqueart.museumwnf.org',
      related_database_url: 'https://baroqueart.museumwnf.org/database.php',
      artistic_introduction_url: null,
    });
  });

  it('resolves the Sharing History site for AWE, with no artistic introduction', () => {
    expect(lookupProjectUrls('AWE')).toEqual({
      site_url: 'https://sharinghistory.museumwnf.org',
      related_database_url: 'https://sharinghistory.museumwnf.org/database.php',
      artistic_introduction_url: null,
    });
  });

  it('resolves DCA to all-null (legacy has no public database search to point at)', () => {
    expect(lookupProjectUrls('DCA')).toEqual({
      site_url: null,
      related_database_url: null,
      artistic_introduction_url: null,
    });
  });

  it('resolves placeholder keys (DGA, EXTHE, GALLERIES) to all-null', () => {
    for (const key of ['DGA', 'EXTHE', 'GALLERIES']) {
      expect(lookupProjectUrls(key)).toEqual({
        site_url: null,
        related_database_url: null,
        artistic_introduction_url: null,
      });
    }
  });

  it('resolves an entirely unknown legacy key to all-null rather than throwing', () => {
    expect(lookupProjectUrls('SOME_ONE_OFF_EXHIBITION')).toEqual({
      site_url: null,
      related_database_url: null,
      artistic_introduction_url: null,
    });
  });

  it('is case-sensitive: lowercase does not match the uppercase legacy key', () => {
    expect(lookupProjectUrls('isl')).toEqual({
      site_url: null,
      related_database_url: null,
      artistic_introduction_url: null,
    });
    expect(lookupProjectUrls('awe')).toEqual({
      site_url: null,
      related_database_url: null,
      artistic_introduction_url: null,
    });
  });

  it('does not contain the viewer-core-only DBA alias (not a real legacy key)', () => {
    expect(PROJECT_URL_MAP.DBA).toBeUndefined();
  });
});
