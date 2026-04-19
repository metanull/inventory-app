import { describe, expect, it } from 'vitest';
import { readFileSync } from 'node:fs';
import path from 'node:path';

const phase07Files = [
  'travels-itinerary-importer.ts',
  'travels-itinerary-translation-importer.ts',
  'travels-location-importer.ts',
  'travels-location-translation-importer.ts',
  'travels-monument-importer.ts',
  'travels-monument-translation-importer.ts',
  'travels-itinerary-picture-importer.ts',
  'travels-location-picture-importer.ts',
  'travels-monument-picture-importer.ts',
  'travels-trail-picture-importer.ts',
];

describe('Phase 07 Travels schema usage', () => {
  it('queries legacy travel tables from mwnf3_travels and not mwnf3', () => {
    for (const fileName of phase07Files) {
      const filePath = path.resolve(
        import.meta.dirname,
        '../../src/importers/phase-07',
        fileName
      );
      const source = readFileSync(filePath, 'utf8');

      expect(source).not.toContain('FROM mwnf3.tr_');
      expect(source).toContain('FROM mwnf3_travels.');
    }
  });
});