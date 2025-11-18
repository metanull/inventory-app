import { describe, it, expect, beforeEach, vi } from 'vitest';
import { PartnerImporter } from '../../../src/importers/phase-01/PartnerImporter.js';
import { BackwardCompatibilityTracker } from '../../../src/utils/BackwardCompatibilityTracker.js';
import type { ImportContext } from '../../../src/importers/BaseImporter.js';

describe('PartnerImporter', () => {
  let importer: PartnerImporter;
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
      limit: 0,
    };

    importer = new PartnerImporter(mockContext);
  });

  describe('import', () => {
    it('should import both museums and institutions', async () => {
      // Mock museums
      const mockMuseums = [{ museum_id: 'louvre', country: 'fr' }];
      const mockMuseumNames = [{ museum_id: 'louvre', language: 'en', name: 'Louvre' }];

      // Mock institutions
      const mockInstitutions = [{ institution_id: 'unesco', country: 'fr' }];
      const mockInstitutionNames = [{ institution_id: 'unesco', language: 'en', name: 'UNESCO' }];

      // Setup query mocks in order: museums, museumnames, institutions, institutionnames
      vi.mocked(mockContext.legacyDb.query)
        .mockResolvedValueOnce(mockMuseums)
        .mockResolvedValueOnce(mockMuseumNames)
        .mockResolvedValueOnce(mockInstitutions)
        .mockResolvedValueOnce(mockInstitutionNames);

      vi.mocked(mockContext.apiClient.context.contextGetDefault).mockResolvedValue({
        data: { data: { id: 'uuid-context-default' } },
      } as never);

      vi.mocked(mockContext.apiClient.partner.partnerStore)
        .mockResolvedValueOnce({
          data: { data: { id: 'uuid-louvre' } },
        } as never)
        .mockResolvedValueOnce({
          data: { data: { id: 'uuid-unesco' } },
        } as never);

      vi.mocked(mockContext.apiClient.partnerTranslation.partnerTranslationStore).mockResolvedValue(
        {
          data: { data: { id: 'uuid-trans' } },
        } as never
      );

      const result = await importer.import();

      expect(result.imported).toBe(2); // 1 museum + 1 institution
      expect(result.skipped).toBe(0);
      expect(result.errors).toHaveLength(0);
      expect(result.success).toBe(true);

      // Verify both types were created
      expect(mockContext.apiClient.partner.partnerStore).toHaveBeenCalledWith({
        internal_name: 'louvre',
        type: 'museum',
        backward_compatibility: 'mwnf3:museums:louvre',
      });

      expect(mockContext.apiClient.partner.partnerStore).toHaveBeenCalledWith({
        internal_name: 'unesco',
        type: 'institution',
        backward_compatibility: 'mwnf3:institutions:unesco',
      });

      // Verify tracker has both
      expect(tracker.exists('mwnf3:museums:louvre')).toBe(true);
      expect(tracker.exists('mwnf3:institutions:unesco')).toBe(true);
    });

    it('should handle errors from sub-importers', async () => {
      // Museums query succeeds but is empty
      vi.mocked(mockContext.legacyDb.query)
        .mockResolvedValueOnce([]) // museums
        .mockResolvedValueOnce([]) // museumnames
        .mockRejectedValueOnce(new Error('Database connection failed')); // institutions fail

      const result = await importer.import();

      expect(result.imported).toBe(0);
      expect(result.errors.length).toBeGreaterThan(0);
      expect(result.errors[0]).toContain('Database connection failed');
      expect(result.success).toBe(false);
    });

    it('should aggregate counts from both importers', async () => {
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

      vi.mocked(mockContext.legacyDb.query)
        .mockResolvedValueOnce(mockMuseums)
        .mockResolvedValueOnce([]) // no translations
        .mockResolvedValueOnce(mockInstitutions)
        .mockResolvedValueOnce([]); // no translations

      vi.mocked(mockContext.apiClient.partner.partnerStore).mockResolvedValue({
        data: { data: { id: 'uuid-partner' } },
      } as never);

      const result = await importer.import();

      expect(result.imported).toBe(5); // 2 + 3
      expect(result.success).toBe(true);
    });
  });
});
