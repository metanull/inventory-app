"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const vitest_1 = require("vitest");
const MuseumImporter_js_1 = require("../../../src/importers/phase-01/MuseumImporter.js");
const BackwardCompatibilityTracker_js_1 = require("../../../src/utils/BackwardCompatibilityTracker.js");
(0, vitest_1.describe)('MuseumImporter', () => {
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
        importer = new MuseumImporter_js_1.MuseumImporter(mockContext);
    });
    (0, vitest_1.describe)('import', () => {
        (0, vitest_1.it)('should import museums with translations', async () => {
            // Mock legacy data
            const mockMuseums = [
                { museum_id: 'louvre', country: 'fr', city: 'Paris', address: '1 Rue de Rivoli' },
            ];
            const mockMuseumNames = [
                {
                    museum_id: 'louvre',
                    language: 'en',
                    name: 'The Louvre Museum',
                    description: 'World famous art museum',
                },
                {
                    museum_id: 'louvre',
                    language: 'fr',
                    name: 'Le Musée du Louvre',
                    description: "Musée d'art mondialement connu",
                },
            ];
            vitest_1.vi.mocked(mockContext.legacyDb.query)
                .mockResolvedValueOnce(mockMuseums) // museums
                .mockResolvedValueOnce(mockMuseumNames); // museumnames
            // Register project in tracker so it can be resolved
            tracker.register({
                uuid: 'uuid-context-testproject',
                backwardCompatibility: 'mwnf3:projects:testproject',
                entityType: 'context',
                createdAt: new Date(),
            });
            // Mock API responses
            vitest_1.vi.mocked(mockContext.apiClient.partner.partnerStore).mockResolvedValue({
                data: { data: { id: 'uuid-louvre-123' } },
            });
            vitest_1.vi.mocked(mockContext.apiClient.partnerTranslation.partnerTranslationStore).mockResolvedValue({
                data: { data: { id: 'uuid-trans-123' } },
            });
            // Execute import
            const result = await importer.import();
            // Verify results
            (0, vitest_1.expect)(result.imported).toBe(1);
            (0, vitest_1.expect)(result.skipped).toBe(0);
            (0, vitest_1.expect)(result.errors).toHaveLength(0);
            // Verify Partner API call
            (0, vitest_1.expect)(mockContext.apiClient.partner.partnerStore).toHaveBeenCalledWith({
                internal_name: 'The Louvre Museum',
                type: 'museum',
                country_id: 'fr',
                project_id: 'testproject',
                backward_compatibility: 'mwnf3:museums:louvre:fr',
            });
            // Verify Translation API calls (2 languages)
            (0, vitest_1.expect)(mockContext.apiClient.partnerTranslation.partnerTranslationStore).toHaveBeenCalledTimes(2);
            // Verify English translation
            (0, vitest_1.expect)(mockContext.apiClient.partnerTranslation.partnerTranslationStore).toHaveBeenCalledWith({
                partner_id: 'uuid-louvre-123',
                language_id: 'eng', // ISO 639-3 code
                context_id: 'uuid-context-testproject',
                name: 'The Louvre Museum',
                description: 'World famous art museum',
            });
            // Verify French translation
            (0, vitest_1.expect)(mockContext.apiClient.partnerTranslation.partnerTranslationStore).toHaveBeenCalledWith({
                partner_id: 'uuid-louvre-123',
                language_id: 'fra', // ISO 639-3 code
                context_id: 'uuid-context-testproject',
                name: 'Le Musée du Louvre',
                description: "Musée d'art mondialement connu",
            });
            // Verify tracker registration
            (0, vitest_1.expect)(tracker.exists('mwnf3:museums:louvre:fr')).toBe(true);
            (0, vitest_1.expect)(tracker.getUuid('mwnf3:museums:louvre:fr')).toBe('uuid-louvre-123');
        });
        (0, vitest_1.it)('should skip museums already in tracker', async () => {
            // Pre-register museum in tracker
            tracker.register({
                uuid: 'existing-uuid-123',
                backwardCompatibility: 'mwnf3:museums:louvre:fr',
                entityType: 'partner',
                createdAt: new Date(),
            });
            const mockMuseums = [
                {
                    museum_id: 'louvre',
                    country: 'fr',
                    name: 'The Louvre',
                    project_id: 'testproject',
                    city: 'Paris',
                    address: '1 Rue de Rivoli',
                },
            ];
            vitest_1.vi.mocked(mockContext.legacyDb.query)
                .mockResolvedValueOnce(mockMuseums) // museums
                .mockResolvedValueOnce([]); // museumnames
            const result = await importer.import();
            (0, vitest_1.expect)(result.imported).toBe(0);
            (0, vitest_1.expect)(result.skipped).toBe(1);
            (0, vitest_1.expect)(mockContext.apiClient.partner.partnerStore).not.toHaveBeenCalled();
        });
        (0, vitest_1.it)('should handle empty museum table', async () => {
            vitest_1.vi.mocked(mockContext.legacyDb.query)
                .mockResolvedValueOnce([]) // museums
                .mockResolvedValueOnce([]); // museumnames
            const result = await importer.import();
            (0, vitest_1.expect)(result.imported).toBe(0);
            (0, vitest_1.expect)(result.skipped).toBe(0);
            (0, vitest_1.expect)(result.errors).toHaveLength(0);
        });
        (0, vitest_1.it)('should map legacy ISO 639-1 codes to ISO 639-3', async () => {
            const mockMuseums = [{ museum_id: 'test', country: 'es' }];
            const mockMuseumNames = [
                { museum_id: 'test', language: 'es', name: 'Museo' },
                { museum_id: 'test', language: 'de', name: 'Museum' },
                { museum_id: 'test', language: 'it', name: 'Museo' },
            ];
            vitest_1.vi.mocked(mockContext.legacyDb.query)
                .mockResolvedValueOnce(mockMuseums)
                .mockResolvedValueOnce(mockMuseumNames);
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
            // Verify language code mapping: es→spa, de→deu, it→ita
            const calls = vitest_1.vi.mocked(mockContext.apiClient.partnerTranslation.partnerTranslationStore).mock
                .calls;
            (0, vitest_1.expect)(calls[0]?.[0]?.language_id).toBe('spa');
            (0, vitest_1.expect)(calls[1]?.[0]?.language_id).toBe('deu');
            (0, vitest_1.expect)(calls[2]?.[0]?.language_id).toBe('ita');
        });
        (0, vitest_1.it)('should respect dry-run mode', async () => {
            mockContext.dryRun = true;
            const mockMuseums = [{ museum_id: 'louvre', country: 'fr' }];
            const mockMuseumNames = [{ museum_id: 'louvre', language: 'en', name: 'Louvre' }];
            vitest_1.vi.mocked(mockContext.legacyDb.query)
                .mockResolvedValueOnce(mockMuseums)
                .mockResolvedValueOnce(mockMuseumNames);
            const result = await importer.import();
            (0, vitest_1.expect)(result.imported).toBe(1);
            (0, vitest_1.expect)(mockContext.apiClient.partner.partnerStore).not.toHaveBeenCalled();
            (0, vitest_1.expect)(mockContext.apiClient.partnerTranslation.partnerTranslationStore).not.toHaveBeenCalled();
        });
        (0, vitest_1.it)('should handle API errors gracefully', async () => {
            const mockMuseums = [{ museum_id: 'louvre', country: 'fr' }];
            vitest_1.vi.mocked(mockContext.legacyDb.query)
                .mockResolvedValueOnce(mockMuseums) // museums
                .mockResolvedValueOnce([]); // museumnames
            vitest_1.vi.mocked(mockContext.apiClient.partner.partnerStore).mockRejectedValue(new Error('API connection failed'));
            const result = await importer.import();
            (0, vitest_1.expect)(result.imported).toBe(0);
            (0, vitest_1.expect)(result.errors).toHaveLength(1);
            (0, vitest_1.expect)(result.errors[0]).toContain('louvre');
            (0, vitest_1.expect)(result.errors[0]).toContain('API connection failed');
        });
    });
});
//# sourceMappingURL=MuseumImporter.test.js.map