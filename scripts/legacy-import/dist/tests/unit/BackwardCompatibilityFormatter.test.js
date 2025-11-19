"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const vitest_1 = require("vitest");
const BackwardCompatibilityFormatter_js_1 = require("../../src/utils/BackwardCompatibilityFormatter.js");
(0, vitest_1.describe)('BackwardCompatibilityFormatter', () => {
    (0, vitest_1.describe)('format', () => {
        (0, vitest_1.it)('should format simple reference with single PK', () => {
            const ref = {
                schema: 'mwnf3',
                table: 'projects',
                pkValues: ['vm'],
            };
            const result = BackwardCompatibilityFormatter_js_1.BackwardCompatibilityFormatter.format(ref);
            (0, vitest_1.expect)(result).toBe('mwnf3:projects:vm');
        });
        (0, vitest_1.it)('should format reference with multi-column PK', () => {
            const ref = {
                schema: 'mwnf3',
                table: 'objects',
                pkValues: ['vm', 'ma', 'louvre', '001'],
            };
            const result = BackwardCompatibilityFormatter_js_1.BackwardCompatibilityFormatter.format(ref);
            (0, vitest_1.expect)(result).toBe('mwnf3:objects:vm:ma:louvre:001');
        });
        (0, vitest_1.it)('should convert numeric PK values to strings', () => {
            const ref = {
                schema: 'explore',
                table: 'exploremonument',
                pkValues: [1234],
            };
            const result = BackwardCompatibilityFormatter_js_1.BackwardCompatibilityFormatter.format(ref);
            (0, vitest_1.expect)(result).toBe('explore:exploremonument:1234');
        });
    });
    (0, vitest_1.describe)('parse', () => {
        (0, vitest_1.it)('should parse formatted reference', () => {
            const formatted = 'mwnf3:objects:vm:ma:louvre:001';
            const result = BackwardCompatibilityFormatter_js_1.BackwardCompatibilityFormatter.parse(formatted);
            (0, vitest_1.expect)(result).toEqual({
                schema: 'mwnf3',
                table: 'objects',
                pkValues: ['vm', 'ma', 'louvre', '001'],
            });
        });
        (0, vitest_1.it)('should throw error for invalid format', () => {
            (0, vitest_1.expect)(() => BackwardCompatibilityFormatter_js_1.BackwardCompatibilityFormatter.parse('invalid')).toThrow();
        });
    });
    (0, vitest_1.describe)('formatDenormalized', () => {
        (0, vitest_1.it)('should exclude language column from PK', () => {
            const pkColumns = {
                project_id: 'vm',
                country: 'ma',
                museum_id: 'louvre',
                number: '001',
                lang: 'en',
            };
            const result = BackwardCompatibilityFormatter_js_1.BackwardCompatibilityFormatter.formatDenormalized('mwnf3', 'objects', pkColumns);
            (0, vitest_1.expect)(result).toBe('mwnf3:objects:vm:ma:louvre:001');
            (0, vitest_1.expect)(result).not.toContain('en');
        });
        (0, vitest_1.it)('should exclude custom columns', () => {
            const pkColumns = {
                id: '123',
                type: 'foo',
                status: 'active',
            };
            const result = BackwardCompatibilityFormatter_js_1.BackwardCompatibilityFormatter.formatDenormalized('test', 'table', pkColumns, [
                'status',
            ]);
            (0, vitest_1.expect)(result).toBe('test:table:123:foo');
            (0, vitest_1.expect)(result).not.toContain('active');
        });
    });
    (0, vitest_1.describe)('formatImage', () => {
        (0, vitest_1.it)('should append image index to item PK', () => {
            const itemPkValues = ['vm', 'ma', 'louvre', '001'];
            const imageIndex = 1;
            const result = BackwardCompatibilityFormatter_js_1.BackwardCompatibilityFormatter.formatImage('mwnf3', 'objects_pictures', itemPkValues, imageIndex);
            (0, vitest_1.expect)(result).toBe('mwnf3:objects_pictures:vm:ma:louvre:001:1');
        });
        (0, vitest_1.it)('should handle multiple image indices', () => {
            const itemPkValues = ['vm', 'ma', 'louvre', '001'];
            const result1 = BackwardCompatibilityFormatter_js_1.BackwardCompatibilityFormatter.formatImage('mwnf3', 'objects_pictures', itemPkValues, 1);
            const result2 = BackwardCompatibilityFormatter_js_1.BackwardCompatibilityFormatter.formatImage('mwnf3', 'objects_pictures', itemPkValues, 2);
            (0, vitest_1.expect)(result1).toBe('mwnf3:objects_pictures:vm:ma:louvre:001:1');
            (0, vitest_1.expect)(result2).toBe('mwnf3:objects_pictures:vm:ma:louvre:001:2');
        });
    });
});
//# sourceMappingURL=BackwardCompatibilityFormatter.test.js.map