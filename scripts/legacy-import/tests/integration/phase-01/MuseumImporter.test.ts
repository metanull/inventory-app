import { describe, it, expect, beforeEach, vi } from 'vitest';
import { MuseumImporter } from '../../../src/importers/phase-01/MuseumImporter.js';
import { BackwardCompatibilityTracker } from '../../../src/utils/BackwardCompatibilityTracker.js';
import type { ImportContext } from '../../../src/importers/BaseImporter.js';

describe('MuseumImporter', () => {
  let importer: MuseumImporter;
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

    importer = new MuseumImporter(mockContext);
  });

  describe('import', () => {
    it('should import museums with translations', async () => {
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

      vi.mocked(mockContext.legacyDb.query)
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
      vi.mocked(mockContext.apiClient.partner.partnerStore).mockResolvedValue({
        data: { data: { id: 'uuid-louvre-123' } },
      } as never);

      vi.mocked(mockContext.apiClient.partnerTranslation.partnerTranslationStore).mockResolvedValue(
        {
          data: { data: { id: 'uuid-trans-123' } },
        } as never
      );

      // Execute import
      const result = await importer.import();

      // Verify results
      expect(result.imported).toBe(1);
      expect(result.skipped).toBe(0);
      expect(result.errors).toHaveLength(0);

      // Verify Partner API call
      expect(mockContext.apiClient.partner.partnerStore).toHaveBeenCalledWith({
        internal_name: 'The Louvre Museum',
        type: 'museum',
        country_id: 'fr',
        project_id: 'testproject',
        backward_compatibility: 'mwnf3:museums:louvre:fr',
      });

      // Verify Translation API calls (2 languages)
      expect(
        mockContext.apiClient.partnerTranslation.partnerTranslationStore
      ).toHaveBeenCalledTimes(2);

      // Verify English translation
      expect(mockContext.apiClient.partnerTranslation.partnerTranslationStore).toHaveBeenCalledWith(
        {
          partner_id: 'uuid-louvre-123',
          language_id: 'eng', // ISO 639-3 code
          context_id: 'uuid-context-testproject',
          name: 'The Louvre Museum',
          description: 'World famous art museum',
        }
      );

      // Verify French translation
      expect(mockContext.apiClient.partnerTranslation.partnerTranslationStore).toHaveBeenCalledWith(
        {
          partner_id: 'uuid-louvre-123',
          language_id: 'fra', // ISO 639-3 code
          context_id: 'uuid-context-testproject',
          name: 'Le Musée du Louvre',
          description: "Musée d'art mondialement connu",
        }
      );

      // Verify tracker registration
      expect(tracker.exists('mwnf3:museums:louvre:fr')).toBe(true);
      expect(tracker.getUuid('mwnf3:museums:louvre:fr')).toBe('uuid-louvre-123');
    });

    it('should skip museums already in tracker', async () => {
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

      vi.mocked(mockContext.legacyDb.query)
        .mockResolvedValueOnce(mockMuseums) // museums
        .mockResolvedValueOnce([]); // museumnames

      const result = await importer.import();

      expect(result.imported).toBe(0);
      expect(result.skipped).toBe(1);
      expect(mockContext.apiClient.partner.partnerStore).not.toHaveBeenCalled();
    });

    it('should handle empty museum table', async () => {
      vi.mocked(mockContext.legacyDb.query)
        .mockResolvedValueOnce([]) // museums
        .mockResolvedValueOnce([]); // museumnames

      const result = await importer.import();

      expect(result.imported).toBe(0);
      expect(result.skipped).toBe(0);
      expect(result.errors).toHaveLength(0);
    });

    it('should map legacy ISO 639-1 codes to ISO 639-3', async () => {
      const mockMuseums = [{ museum_id: 'test', country: 'es' }];
      const mockMuseumNames = [
        { museum_id: 'test', language: 'es', name: 'Museo' },
        { museum_id: 'test', language: 'de', name: 'Museum' },
        { museum_id: 'test', language: 'it', name: 'Museo' },
      ];

      vi.mocked(mockContext.legacyDb.query)
        .mockResolvedValueOnce(mockMuseums)
        .mockResolvedValueOnce(mockMuseumNames);

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

      // Verify language code mapping: es→spa, de→deu, it→ita
      const calls = vi.mocked(mockContext.apiClient.partnerTranslation.partnerTranslationStore).mock
        .calls;
      expect(calls[0]?.[0]?.language_id).toBe('spa');
      expect(calls[1]?.[0]?.language_id).toBe('deu');
      expect(calls[2]?.[0]?.language_id).toBe('ita');
    });

    it('should respect dry-run mode', async () => {
      mockContext.dryRun = true;

      const mockMuseums = [{ museum_id: 'louvre', country: 'fr' }];
      const mockMuseumNames = [{ museum_id: 'louvre', language: 'en', name: 'Louvre' }];

      vi.mocked(mockContext.legacyDb.query)
        .mockResolvedValueOnce(mockMuseums)
        .mockResolvedValueOnce(mockMuseumNames);

      const result = await importer.import();

      expect(result.imported).toBe(1);
      expect(mockContext.apiClient.partner.partnerStore).not.toHaveBeenCalled();
      expect(
        mockContext.apiClient.partnerTranslation.partnerTranslationStore
      ).not.toHaveBeenCalled();
    });

    it('should handle API errors gracefully', async () => {
      const mockMuseums = [{ museum_id: 'louvre', country: 'fr' }];

      vi.mocked(mockContext.legacyDb.query)
        .mockResolvedValueOnce(mockMuseums) // museums
        .mockResolvedValueOnce([]); // museumnames

      vi.mocked(mockContext.apiClient.partner.partnerStore).mockRejectedValue(
        new Error('API connection failed')
      );

      const result = await importer.import();

      expect(result.imported).toBe(0);
      expect(result.errors).toHaveLength(1);
      expect(result.errors[0]).toContain('louvre');
      expect(result.errors[0]).toContain('API connection failed');
    });
  });
});
