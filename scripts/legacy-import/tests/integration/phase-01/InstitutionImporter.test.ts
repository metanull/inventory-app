import { describe, it, expect, beforeEach, vi } from 'vitest';
import { InstitutionImporter } from '../../../src/importers/phase-01/InstitutionImporter.js';
import { BackwardCompatibilityTracker } from '../../../src/utils/BackwardCompatibilityTracker.js';
import type { ImportContext } from '../../../src/importers/BaseImporter.js';

describe('InstitutionImporter', () => {
  let importer: InstitutionImporter;
  let mockContext: ImportContext;
  let tracker: BackwardCompatibilityTracker;

  beforeEach(() => {
    tracker = new BackwardCompatibilityTracker();

    mockContext = {
      legacyDb: {
        query: vi.fn(),
      } as unknown as ImportContext['legacyDb'],
      apiClient: {
        context: {
          contextGetDefault: vi.fn(),
        },
        partner: {
          partnerStore: vi.fn(),
        },
        partnerTranslation: {
          partnerTranslationStore: vi.fn(),
        },
      } as unknown as ImportContext['apiClient'],
      tracker,
      dryRun: false,
    };

    importer = new InstitutionImporter(mockContext);
  });

  describe('import', () => {
    it('should import institutions with translations', async () => {
      // Register default context in tracker (institutions use default context)
      tracker.register({
        uuid: 'uuid-context-default',
        backwardCompatibility: '__default_context__',
        entityType: 'context',
        createdAt: new Date(),
      });

      // Mock legacy data
      const mockInstitutions = [
        { institution_id: 'unesco', country: 'fr', name: 'UNESCO', city: 'Paris' },
      ];

      const mockInstitutionNames = [
        {
          institution_id: 'unesco',
          lang: 'en',
          name: 'UNESCO',
          description: 'United Nations Educational, Scientific and Cultural Organization',
        },
        {
          institution_id: 'unesco',
          lang: 'fr',
          name: 'UNESCO',
          description: "Organisation des Nations unies pour l'éducation, la science et la culture",
        },
      ];

      vi.mocked(mockContext.legacyDb.query)
        .mockResolvedValueOnce(mockInstitutions)
        .mockResolvedValueOnce(mockInstitutionNames);

      vi.mocked(mockContext.apiClient.context.contextGetDefault).mockResolvedValue({
        data: { data: { id: 'uuid-context-default' } },
      } as never);

      vi.mocked(mockContext.apiClient.partner.partnerStore).mockResolvedValue({
        data: { data: { id: 'uuid-unesco-123' } },
      } as never);

      vi.mocked(mockContext.apiClient.partnerTranslation.partnerTranslationStore).mockResolvedValue(
        {
          data: { data: { id: 'uuid-trans-123' } },
        } as never
      );

      const result = await importer.import();

      expect(result.imported).toBe(1);
      expect(result.skipped).toBe(0);
      expect(result.errors).toHaveLength(0);
      expect(result.success).toBe(true);

      // Verify Partner API call
      expect(mockContext.apiClient.partner.partnerStore).toHaveBeenCalledWith({
        internal_name: 'UNESCO',
        type: 'institution',
        country_id: 'fra', // 3-letter ISO 3166-1 alpha-3
        visible: true,
        backward_compatibility: 'mwnf3:institutions:unesco:fr', // Uses legacy 2-letter country code
      });

      // Verify Translation API calls (2 languages)
      expect(
        mockContext.apiClient.partnerTranslation.partnerTranslationStore
      ).toHaveBeenCalledTimes(2);

      // Verify English translation
      expect(mockContext.apiClient.partnerTranslation.partnerTranslationStore).toHaveBeenCalledWith(
        expect.objectContaining({
          partner_id: 'uuid-unesco-123',
          language_id: 'eng',
          context_id: 'uuid-context-default',
          name: 'UNESCO',
          description: 'United Nations Educational, Scientific and Cultural Organization',
        })
      );

      // Verify French translation
      expect(mockContext.apiClient.partnerTranslation.partnerTranslationStore).toHaveBeenCalledWith(
        expect.objectContaining({
          partner_id: 'uuid-unesco-123',
          language_id: 'fra',
          context_id: 'uuid-context-default',
          name: 'UNESCO',
          description: "Organisation des Nations unies pour l'éducation, la science et la culture",
        })
      );

      // Verify tracker registration
      expect(tracker.exists('mwnf3:institutions:unesco:fr')).toBe(true);
      expect(tracker.getUuid('mwnf3:institutions:unesco:fr')).toBe('uuid-unesco-123');
    });

    it('should skip institutions already in tracker', async () => {
      tracker.register({
        uuid: 'existing-uuid-123',
        backwardCompatibility: 'mwnf3:institutions:unesco:fr',
        entityType: 'partner',
        createdAt: new Date(),
      });

      const mockInstitutions = [{ institution_id: 'unesco', country: 'fr', name: 'UNESCO' }];

      vi.mocked(mockContext.legacyDb.query)
        .mockResolvedValueOnce(mockInstitutions)
        .mockResolvedValueOnce([]);

      vi.mocked(mockContext.apiClient.context.contextGetDefault).mockResolvedValue({
        data: { data: { id: 'uuid-context-default' } },
      } as never);

      const result = await importer.import();

      expect(result.imported).toBe(0);
      expect(result.skipped).toBe(1);
      expect(mockContext.apiClient.partner.partnerStore).not.toHaveBeenCalled();
    });

    it('should handle empty institution table', async () => {
      vi.mocked(mockContext.legacyDb.query).mockResolvedValueOnce([]).mockResolvedValueOnce([]);

      const result = await importer.import();

      expect(result.imported).toBe(0);
      expect(result.skipped).toBe(0);
      expect(result.errors).toHaveLength(0);
    });

    it('should map legacy ISO 639-1 codes to ISO 639-3', async () => {
      // Register default context in tracker
      tracker.register({
        uuid: 'uuid-context-default',
        backwardCompatibility: '__default_context__',
        entityType: 'context',
        createdAt: new Date(),
      });

      const mockInstitutions = [
        { institution_id: 'test', country: 'es', name: 'Test Institution' },
      ];
      const mockInstitutionNames = [
        { institution_id: 'test', lang: 'es', name: 'Institución' },
        { institution_id: 'test', lang: 'de', name: 'Institution' },
        { institution_id: 'test', lang: 'it', name: 'Istituzione' },
      ];

      vi.mocked(mockContext.legacyDb.query)
        .mockResolvedValueOnce(mockInstitutions)
        .mockResolvedValueOnce(mockInstitutionNames);

      vi.mocked(mockContext.apiClient.context.contextGetDefault).mockResolvedValue({
        data: { data: { id: 'uuid-context-default' } },
      } as never);

      vi.mocked(mockContext.apiClient.context.contextGetDefault).mockResolvedValue({
        data: { data: { id: 'uuid-context-default' } },
      } as never);

      vi.mocked(mockContext.apiClient.partner.partnerStore).mockResolvedValue({
        data: { data: { id: 'uuid-test' } },
      } as never);

      vi.mocked(mockContext.apiClient.partnerTranslation.partnerTranslationStore).mockResolvedValue(
        {
          data: { data: { id: 'uuid-trans' } },
        } as never
      );

      await importer.import();

      const calls = vi.mocked(mockContext.apiClient.partnerTranslation.partnerTranslationStore).mock
        .calls;
      expect(calls[0]?.[0]?.language_id).toBe('spa');
      expect(calls[1]?.[0]?.language_id).toBe('deu');
      expect(calls[2]?.[0]?.language_id).toBe('ita');
    });

    it('should respect dry-run mode', async () => {
      mockContext.dryRun = true;

      const mockInstitutions = [{ institution_id: 'unesco', country: 'fr', name: 'UNESCO' }];
      const mockInstitutionNames = [{ institution_id: 'unesco', lang: 'en', name: 'UNESCO' }];

      vi.mocked(mockContext.legacyDb.query)
        .mockResolvedValueOnce(mockInstitutions)
        .mockResolvedValueOnce(mockInstitutionNames);

      const result = await importer.import();

      expect(result.imported).toBe(1);
      expect(mockContext.apiClient.partner.partnerStore).not.toHaveBeenCalled();
      expect(
        mockContext.apiClient.partnerTranslation.partnerTranslationStore
      ).not.toHaveBeenCalled();
    });

    it('should handle API errors gracefully', async () => {
      const mockInstitutions = [{ institution_id: 'unesco', country: 'fr', name: 'UNESCO' }];

      vi.mocked(mockContext.legacyDb.query)
        .mockResolvedValueOnce(mockInstitutions)
        .mockResolvedValueOnce([]);

      vi.mocked(mockContext.apiClient.partner.partnerStore).mockRejectedValue(
        new Error('API connection failed')
      );

      const result = await importer.import();

      expect(result.imported).toBe(0);
      expect(result.errors).toHaveLength(1);
      expect(result.errors[0]).toContain('unesco');
      expect(result.errors[0]).toContain('API connection failed');
      expect(result.success).toBe(false);
    });

    it('should throw error on missing required name field', async () => {
      const mockInstitutions = [{ institution_id: 'unesco', country: 'fr' }]; // Missing name

      vi.mocked(mockContext.legacyDb.query)
        .mockResolvedValueOnce(mockInstitutions)
        .mockResolvedValueOnce([]);

      const result = await importer.import();

      expect(result.imported).toBe(0);
      expect(result.errors).toHaveLength(1);
      expect(result.errors[0]).toContain('unesco');
      expect(result.errors[0]).toContain("missing required field 'name'");
      expect(result.success).toBe(false);
    });
  });
});
