import { describe, it, expect } from 'vitest';
import { BackwardCompatibilityFormatter } from '../../src/utils/BackwardCompatibilityFormatter.js';
describe('BackwardCompatibilityFormatter', () => {
  describe('format', () => {
    it('should format simple reference with single PK', () => {
      const ref = {
        schema: 'mwnf3',
        table: 'projects',
        pkValues: ['vm'],
      };
      const result = BackwardCompatibilityFormatter.format(ref);
      expect(result).toBe('mwnf3:projects:vm');
    });
    it('should format reference with multi-column PK', () => {
      const ref = {
        schema: 'mwnf3',
        table: 'objects',
        pkValues: ['vm', 'ma', 'louvre', '001'],
      };
      const result = BackwardCompatibilityFormatter.format(ref);
      expect(result).toBe('mwnf3:objects:vm:ma:louvre:001');
    });
    it('should convert numeric PK values to strings', () => {
      const ref = {
        schema: 'explore',
        table: 'exploremonument',
        pkValues: [1234],
      };
      const result = BackwardCompatibilityFormatter.format(ref);
      expect(result).toBe('explore:exploremonument:1234');
    });
  });
  describe('parse', () => {
    it('should parse formatted reference', () => {
      const formatted = 'mwnf3:objects:vm:ma:louvre:001';
      const result = BackwardCompatibilityFormatter.parse(formatted);
      expect(result).toEqual({
        schema: 'mwnf3',
        table: 'objects',
        pkValues: ['vm', 'ma', 'louvre', '001'],
      });
    });
    it('should throw error for invalid format', () => {
      expect(() => BackwardCompatibilityFormatter.parse('invalid')).toThrow();
    });
  });
  describe('formatDenormalized', () => {
    it('should exclude language column from PK', () => {
      const pkColumns = {
        project_id: 'vm',
        country: 'ma',
        museum_id: 'louvre',
        number: '001',
        lang: 'en',
      };
      const result = BackwardCompatibilityFormatter.formatDenormalized(
        'mwnf3',
        'objects',
        pkColumns
      );
      expect(result).toBe('mwnf3:objects:vm:ma:louvre:001');
      expect(result).not.toContain('en');
    });
    it('should exclude custom columns', () => {
      const pkColumns = {
        id: '123',
        type: 'foo',
        status: 'active',
      };
      const result = BackwardCompatibilityFormatter.formatDenormalized('test', 'table', pkColumns, [
        'status',
      ]);
      expect(result).toBe('test:table:123:foo');
      expect(result).not.toContain('active');
    });
  });
  describe('formatImage', () => {
    it('should append image index to item PK', () => {
      const itemPkValues = ['vm', 'ma', 'louvre', '001'];
      const imageIndex = 1;
      const result = BackwardCompatibilityFormatter.formatImage(
        'mwnf3',
        'objects_pictures',
        itemPkValues,
        imageIndex
      );
      expect(result).toBe('mwnf3:objects_pictures:vm:ma:louvre:001:1');
    });
    it('should handle multiple image indices', () => {
      const itemPkValues = ['vm', 'ma', 'louvre', '001'];
      const result1 = BackwardCompatibilityFormatter.formatImage(
        'mwnf3',
        'objects_pictures',
        itemPkValues,
        1
      );
      const result2 = BackwardCompatibilityFormatter.formatImage(
        'mwnf3',
        'objects_pictures',
        itemPkValues,
        2
      );
      expect(result1).toBe('mwnf3:objects_pictures:vm:ma:louvre:001:1');
      expect(result2).toBe('mwnf3:objects_pictures:vm:ma:louvre:001:2');
    });
  });
});
//# sourceMappingURL=BackwardCompatibilityFormatter.test.js.map
