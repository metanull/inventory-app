"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const vitest_1 = require("vitest");
const InstitutionImporter_js_1 = require("../../../src/importers/phase-01/InstitutionImporter.js");
const BackwardCompatibilityTracker_js_1 = require("../../../src/utils/BackwardCompatibilityTracker.js");
(0, vitest_1.describe)('InstitutionImporter', () => {
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
        importer = new InstitutionImporter_js_1.InstitutionImporter(mockContext);
    });
    (0, vitest_1.describe)('import', () => {
        (0, vitest_1.it)('should import institutions with translations', async () => {
            // Mock legacy data
            const mockInstitutions = [{ institution_id: 'unesco', country: 'fr', city: 'Paris' }];
            const mockInstitutionNames = [
                {
                    institution_id: 'unesco',
                    language: 'en',
                    name: 'UNESCO',
                    description: 'United Nations Educational, Scientific and Cultural Organization',
                },
                {
                    institution_id: 'unesco',
                    language: 'fr',
                    name: 'UNESCO',
                    description: "Organisation des Nations unies pour l'éducation, la science et la culture",
                },
            ];
            vitest_1.vi.mocked(mockContext.legacyDb.query)
                .mockResolvedValueOnce(mockInstitutions)
                .mockResolvedValueOnce(mockInstitutionNames);
            vitest_1.vi.mocked(mockContext.apiClient.context.contextGetDefault).mockResolvedValue({
                data: { data: { id: 'uuid-context-default' } },
            });
            vitest_1.vi.mocked(mockContext.apiClient.partner.partnerStore).mockResolvedValue({
                data: { data: { id: 'uuid-unesco-123' } },
            });
            vitest_1.vi.mocked(mockContext.apiClient.partnerTranslation.partnerTranslationStore).mockResolvedValue({
                data: { data: { id: 'uuid-trans-123' } },
            });
            const result = await importer.import();
            (0, vitest_1.expect)(result.imported).toBe(1);
            (0, vitest_1.expect)(result.skipped).toBe(0);
            (0, vitest_1.expect)(result.errors).toHaveLength(0);
            (0, vitest_1.expect)(result.success).toBe(true);
            // Verify Partner API call
            (0, vitest_1.expect)(mockContext.apiClient.partner.partnerStore).toHaveBeenCalledWith({
                internal_name: 'unesco',
                type: 'institution',
                backward_compatibility: 'mwnf3:institutions:unesco',
            });
            // Verify Translation API calls (2 languages)
            (0, vitest_1.expect)(mockContext.apiClient.partnerTranslation.partnerTranslationStore).toHaveBeenCalledTimes(2);
            // Verify English translation
            (0, vitest_1.expect)(mockContext.apiClient.partnerTranslation.partnerTranslationStore).toHaveBeenCalledWith({
                partner_id: 'uuid-unesco-123',
                language_id: 'eng',
                context_id: 'uuid-context-default',
                name: 'UNESCO',
                description: 'United Nations Educational, Scientific and Cultural Organization',
            });
            // Verify French translation
            (0, vitest_1.expect)(mockContext.apiClient.partnerTranslation.partnerTranslationStore).toHaveBeenCalledWith({
                partner_id: 'uuid-unesco-123',
                language_id: 'fra',
                context_id: 'uuid-context-default',
                name: 'UNESCO',
                description: "Organisation des Nations unies pour l'éducation, la science et la culture",
            });
            // Verify tracker registration
            (0, vitest_1.expect)(tracker.exists('mwnf3:institutions:unesco')).toBe(true);
            (0, vitest_1.expect)(tracker.getUuid('mwnf3:institutions:unesco')).toBe('uuid-unesco-123');
        });
        (0, vitest_1.it)('should skip institutions already in tracker', async () => {
            tracker.register({
                uuid: 'existing-uuid-123',
                backwardCompatibility: 'mwnf3:institutions:unesco',
                entityType: 'partner',
                createdAt: new Date(),
            });
            const mockInstitutions = [{ institution_id: 'unesco', country: 'fr' }];
            vitest_1.vi.mocked(mockContext.legacyDb.query)
                .mockResolvedValueOnce(mockInstitutions)
                .mockResolvedValueOnce([]);
            const result = await importer.import();
            (0, vitest_1.expect)(result.imported).toBe(0);
            (0, vitest_1.expect)(result.skipped).toBe(1);
            (0, vitest_1.expect)(mockContext.apiClient.partner.partnerStore).not.toHaveBeenCalled();
        });
        (0, vitest_1.it)('should handle empty institution table', async () => {
            vitest_1.vi.mocked(mockContext.legacyDb.query).mockResolvedValueOnce([]).mockResolvedValueOnce([]);
            const result = await importer.import();
            (0, vitest_1.expect)(result.imported).toBe(0);
            (0, vitest_1.expect)(result.skipped).toBe(0);
            (0, vitest_1.expect)(result.errors).toHaveLength(0);
        });
        (0, vitest_1.it)('should map legacy ISO 639-1 codes to ISO 639-3', async () => {
            const mockInstitutions = [{ institution_id: 'test', country: 'es' }];
            const mockInstitutionNames = [
                { institution_id: 'test', language: 'es', name: 'Institución' },
                { institution_id: 'test', language: 'de', name: 'Institution' },
                { institution_id: 'test', language: 'it', name: 'Istituzione' },
            ];
            vitest_1.vi.mocked(mockContext.legacyDb.query)
                .mockResolvedValueOnce(mockInstitutions)
                .mockResolvedValueOnce(mockInstitutionNames);
            vitest_1.vi.mocked(mockContext.apiClient.context.contextGetDefault).mockResolvedValue({
                data: { data: { id: 'uuid-context-default' } },
            });
            vitest_1.vi.mocked(mockContext.apiClient.partner.partnerStore).mockResolvedValue({
                data: { data: { id: 'uuid-test' } },
            });
            vitest_1.vi.mocked(mockContext.apiClient.partnerTranslation.partnerTranslationStore).mockResolvedValue({
                data: { data: { id: 'uuid-trans' } },
            });
            await importer.import();
            const calls = vitest_1.vi.mocked(mockContext.apiClient.partnerTranslation.partnerTranslationStore).mock
                .calls;
            (0, vitest_1.expect)(calls[0]?.[0]?.language_id).toBe('spa');
            (0, vitest_1.expect)(calls[1]?.[0]?.language_id).toBe('deu');
            (0, vitest_1.expect)(calls[2]?.[0]?.language_id).toBe('ita');
        });
        (0, vitest_1.it)('should respect dry-run mode', async () => {
            mockContext.dryRun = true;
            const mockInstitutions = [{ institution_id: 'unesco', country: 'fr' }];
            const mockInstitutionNames = [{ institution_id: 'unesco', language: 'en', name: 'UNESCO' }];
            vitest_1.vi.mocked(mockContext.legacyDb.query)
                .mockResolvedValueOnce(mockInstitutions)
                .mockResolvedValueOnce(mockInstitutionNames);
            const result = await importer.import();
            (0, vitest_1.expect)(result.imported).toBe(1);
            (0, vitest_1.expect)(mockContext.apiClient.partner.partnerStore).not.toHaveBeenCalled();
            (0, vitest_1.expect)(mockContext.apiClient.partnerTranslation.partnerTranslationStore).not.toHaveBeenCalled();
        });
        (0, vitest_1.it)('should handle API errors gracefully', async () => {
            const mockInstitutions = [{ institution_id: 'unesco', country: 'fr' }];
            vitest_1.vi.mocked(mockContext.legacyDb.query)
                .mockResolvedValueOnce(mockInstitutions)
                .mockResolvedValueOnce([]);
            vitest_1.vi.mocked(mockContext.apiClient.partner.partnerStore).mockRejectedValue(new Error('API connection failed'));
            const result = await importer.import();
            (0, vitest_1.expect)(result.imported).toBe(0);
            (0, vitest_1.expect)(result.errors).toHaveLength(1);
            (0, vitest_1.expect)(result.errors[0]).toContain('unesco');
            (0, vitest_1.expect)(result.errors[0]).toContain('API connection failed');
            (0, vitest_1.expect)(result.success).toBe(false);
        });
    });
});
//# sourceMappingURL=InstitutionImporter.test.js.map