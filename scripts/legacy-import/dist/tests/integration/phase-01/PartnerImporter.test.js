"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const vitest_1 = require("vitest");
const PartnerImporter_js_1 = require("../../../src/importers/phase-01/PartnerImporter.js");
const BackwardCompatibilityTracker_js_1 = require("../../../src/utils/BackwardCompatibilityTracker.js");
(0, vitest_1.describe)('PartnerImporter', () => {
    let importer;
    let mockContext;
    let tracker;
    (0, vitest_1.beforeEach)(() => {
        tracker = new BackwardCompatibilityTracker_js_1.BackwardCompatibilityTracker();
        mockContext = {
            legacyDb: {
                query: vitest_1.vi.fn(),
            },
            apiClient: {
                context: {
                    contextGetDefault: vitest_1.vi.fn(),
                },
                partner: {
                    partnerStore: vitest_1.vi.fn(),
                },
                partnerTranslation: {
                    partnerTranslationStore: vitest_1.vi.fn(),
                },
            },
            tracker,
            dryRun: false,
            limit: 0,
        };
        importer = new PartnerImporter_js_1.PartnerImporter(mockContext);
    });
    (0, vitest_1.describe)('import', () => {
        (0, vitest_1.it)('should import both museums and institutions', async () => {
            // Mock museums
            const mockMuseums = [{ museum_id: 'louvre', country: 'fr' }];
            const mockMuseumNames = [{ museum_id: 'louvre', language: 'en', name: 'Louvre' }];
            // Mock institutions
            const mockInstitutions = [{ institution_id: 'unesco', country: 'fr' }];
            const mockInstitutionNames = [{ institution_id: 'unesco', language: 'en', name: 'UNESCO' }];
            // Setup query mocks in order: museums, museumnames, institutions, institutionnames
            vitest_1.vi.mocked(mockContext.legacyDb.query)
                .mockResolvedValueOnce(mockMuseums)
                .mockResolvedValueOnce(mockMuseumNames)
                .mockResolvedValueOnce(mockInstitutions)
                .mockResolvedValueOnce(mockInstitutionNames);
            vitest_1.vi.mocked(mockContext.apiClient.context.contextGetDefault).mockResolvedValue({
                data: { data: { id: 'uuid-context-default' } },
            });
            vitest_1.vi.mocked(mockContext.apiClient.partner.partnerStore)
                .mockResolvedValueOnce({
                data: { data: { id: 'uuid-louvre' } },
            })
                .mockResolvedValueOnce({
                data: { data: { id: 'uuid-unesco' } },
            });
            vitest_1.vi.mocked(mockContext.apiClient.partnerTranslation.partnerTranslationStore).mockResolvedValue({
                data: { data: { id: 'uuid-trans' } },
            });
            const result = await importer.import();
            (0, vitest_1.expect)(result.imported).toBe(2); // 1 museum + 1 institution
            (0, vitest_1.expect)(result.skipped).toBe(0);
            (0, vitest_1.expect)(result.errors).toHaveLength(0);
            (0, vitest_1.expect)(result.success).toBe(true);
            // Verify both types were created
            (0, vitest_1.expect)(mockContext.apiClient.partner.partnerStore).toHaveBeenCalledWith({
                internal_name: 'louvre',
                type: 'museum',
                backward_compatibility: 'mwnf3:museums:louvre',
            });
            (0, vitest_1.expect)(mockContext.apiClient.partner.partnerStore).toHaveBeenCalledWith({
                internal_name: 'unesco',
                type: 'institution',
                backward_compatibility: 'mwnf3:institutions:unesco',
            });
            // Verify tracker has both
            (0, vitest_1.expect)(tracker.exists('mwnf3:museums:louvre')).toBe(true);
            (0, vitest_1.expect)(tracker.exists('mwnf3:institutions:unesco')).toBe(true);
        });
        (0, vitest_1.it)('should handle errors from sub-importers', async () => {
            // Museums query succeeds but is empty
            vitest_1.vi.mocked(mockContext.legacyDb.query)
                .mockResolvedValueOnce([]) // museums
                .mockResolvedValueOnce([]) // museumnames
                .mockRejectedValueOnce(new Error('Database connection failed')); // institutions fail
            const result = await importer.import();
            (0, vitest_1.expect)(result.imported).toBe(0);
            (0, vitest_1.expect)(result.errors.length).toBeGreaterThan(0);
            (0, vitest_1.expect)(result.errors[0]).toContain('Database connection failed');
            (0, vitest_1.expect)(result.success).toBe(false);
        });
        (0, vitest_1.it)('should aggregate counts from both importers', async () => {
            // Mock 2 museums, 3 institutions
            const mockMuseums = [
                { museum_id: 'louvre', country: 'fr' },
                { museum_id: 'british', country: 'gb' },
            ];
            const mockInstitutions = [
                { institution_id: 'unesco', country: 'fr' },
                { institution_id: 'icom', country: 'fr' },
                { institution_id: 'icomos', country: 'fr' },
            ];
            vitest_1.vi.mocked(mockContext.legacyDb.query)
                .mockResolvedValueOnce(mockMuseums)
                .mockResolvedValueOnce([]) // no translations
                .mockResolvedValueOnce(mockInstitutions)
                .mockResolvedValueOnce([]); // no translations
            vitest_1.vi.mocked(mockContext.apiClient.partner.partnerStore).mockResolvedValue({
                data: { data: { id: 'uuid-partner' } },
            });
            const result = await importer.import();
            (0, vitest_1.expect)(result.imported).toBe(5); // 2 + 3
            (0, vitest_1.expect)(result.success).toBe(true);
        });
    });
});
//# sourceMappingURL=PartnerImporter.test.js.map