import { describe, it, expect, beforeAll, afterAll } from 'vitest';
import { SampleReader, SampleRecord } from '../../../src/utils/SampleReader.js';
import * as path from 'path';

describe('SampleReader', () => {
  let reader: SampleReader;
  const sampleDbPath = path.resolve(__dirname, '../../../test-fixtures/samples.sqlite');

  beforeAll(() => {
    reader = new SampleReader(sampleDbPath);
  });

  afterAll(() => {
    reader.close();
  });

  describe('query', () => {
    it('should query samples by entity type', () => {
      const samples = reader.getByEntityType('language', 10);
      expect(samples.length).toBeGreaterThan(0);
      expect(samples.length).toBeLessThanOrEqual(10);
      expect(samples[0].entity_type).toBe('language');
    });

    it('should query success samples', () => {
      const samples = reader.getSuccessSamples('language', 5);
      expect(samples.length).toBeGreaterThan(0);
      expect(samples.length).toBeLessThanOrEqual(5);
      samples.forEach((sample: SampleRecord) => {
        expect(sample.sample_reason).toBe('success');
      });
    });

    it('should query warning samples', () => {
      const samples = reader.getWarningSamples('object', 'missing_name');
      // May or may not have warning samples depending on data quality
      samples.forEach((sample: SampleRecord) => {
        expect(sample.sample_reason).toBe('warning:missing_name');
      });
    });

    it('should query edge case samples', () => {
      const samples = reader.getEdgeCaseSamples('object');
      // May or may not have edge cases
      samples.forEach((sample: SampleRecord) => {
        expect(sample.sample_reason).toContain('edge');
      });
    });

    it('should query samples by language', () => {
      const samples = reader.getByLanguage('language_translation', 'eng', 5);
      expect(samples.length).toBeGreaterThan(0);
      samples.forEach((sample: SampleRecord) => {
        expect(sample.language).toBe('eng');
      });
    });
  });

  describe('parseRawData', () => {
    it('should parse JSON raw_data into object', () => {
      const samples = reader.getSuccessSamples('language', 1);
      expect(samples.length).toBeGreaterThan(0);

      const parsed = reader.parseRawData(samples[0]);
      expect(parsed).toHaveProperty('id');
      expect(parsed).toHaveProperty('internal_name');
    });
  });

  describe('getStats', () => {
    it('should return statistics about collected samples', () => {
      const stats = reader.getStats();
      expect(Object.keys(stats).length).toBeGreaterThan(0);
      expect(stats).toHaveProperty('language:success');
      expect(stats['language:success']).toBeGreaterThan(0);
    });
  });

  describe('getCount', () => {
    it('should return total count', () => {
      const count = reader.getTotalCount();
      expect(count).toBeGreaterThan(0);
    });

    it('should return count for specific entity type', () => {
      const count = reader.getCount('language');
      expect(count).toBeGreaterThan(0);
    });
  });
});
