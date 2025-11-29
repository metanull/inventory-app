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
    };

    importer = new PartnerImporter(mockContext);
  });

  describe('import', () => {
    it('should import both museums and institutions', async () => {
      // Register project and context in tracker for museums
      tracker.register({
        uuid: 'uuid-project-testproject',
        backwardCompatibility: 'mwnf3:projects:testproject:project',
        entityType: 'project',
        createdAt: new Date(),
      });
      tracker.register({
        uuid: 'uuid-context-testproject',
        backwardCompatibility: 'mwnf3:projects:testproject',
        entityType: 'context',
        createdAt: new Date(),
      });
      // Register default context for institutions
      tracker.register({
        uuid: 'uuid-context-default',
        backwardCompatibility: '__default_context__',
        entityType: 'context',
        createdAt: new Date(),
      });

      // Mock museums
      const mockMuseums = [
        { museum_id: 'louvre', country: 'fr', name: 'Louvre', project_id: 'testproject' },
      ];
      const mockMuseumNames = [{ museum_id: 'louvre', lang: 'en', name: 'Louvre' }];

      // Mock institutions
      const mockInstitutions = [{ institution_id: 'unesco', country: 'fr', name: 'UNESCO' }];
      const mockInstitutionNames = [{ institution_id: 'unesco', lang: 'en', name: 'UNESCO' }];

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
        internal_name: 'Louvre',
        type: 'museum',
        country_id: 'fra', // 3-letter ISO 3166-1 alpha-3
        project_id: 'uuid-project-testproject',
        latitude: undefined,
        longitude: undefined,
        map_zoom: undefined,
        visible: true,
        backward_compatibility: 'mwnf3:museums:louvre:fr',
      });

      expect(mockContext.apiClient.partner.partnerStore).toHaveBeenCalledWith({
        internal_name: 'UNESCO',
        type: 'institution',
        country_id: 'fra',
        visible: true,
        backward_compatibility: 'mwnf3:institutions:unesco:fr',
      });

      // Verify tracker has both
      expect(tracker.exists('mwnf3:museums:louvre:fr')).toBe(true);
      expect(tracker.exists('mwnf3:institutions:unesco:fr')).toBe(true);
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
      // Register project and context in tracker for museums
      tracker.register({
        uuid: 'uuid-project-testproject',
        backwardCompatibility: 'mwnf3:projects:testproject:project',
        entityType: 'project',
        createdAt: new Date(),
      });
      tracker.register({
        uuid: 'uuid-context-testproject',
        backwardCompatibility: 'mwnf3:projects:testproject',
        entityType: 'context',
        createdAt: new Date(),
      });

      // Mock 2 museums, 3 institutions
      const mockMuseums = [
        { museum_id: 'louvre', country: 'fr', name: 'Louvre', project_id: 'testproject' },
        { museum_id: 'prado', country: 'es', name: 'Museo del Prado', project_id: 'testproject' },
      ];
      const mockInstitutions = [
        { institution_id: 'unesco', country: 'fr', name: 'UNESCO' },
        { institution_id: 'icom', country: 'fr', name: 'ICOM' },
        { institution_id: 'icomos', country: 'fr', name: 'ICOMOS' },
      ];

      vi.mocked(mockContext.legacyDb.query)
        .mockResolvedValueOnce(mockMuseums)
        .mockResolvedValueOnce([]) // no translations
        .mockResolvedValueOnce(mockInstitutions)
        .mockResolvedValueOnce([]); // no translations

      vi.mocked(mockContext.apiClient.partner.partnerStore).mockResolvedValue({
        data: { data: { id: 'uuid-partner' } },
      } as never);

      vi.mocked(mockContext.apiClient.context.contextGetDefault).mockResolvedValue({
        data: { data: { id: 'uuid-context-default' } },
      } as never);

      const result = await importer.import();

      console.log('Import result:', result);
      console.log(
        'Partner store calls:',
        vi.mocked(mockContext.apiClient.partner.partnerStore).mock.calls.length
      );

      // Must import exactly 5: 2 museums + 3 institutions
      expect(result.imported).toBe(5);
      expect(result.skipped).toBe(0);
      expect(result.success).toBe(true);
    });
  });
});
