/**
 * Tests for Country Importer
 *
 * Verifies that the CountryImporter correctly loads countries from the JSON file
 * instead of querying the legacy database.
 */

import { describe, it, expect, beforeEach, vi } from 'vitest';
import {
  CountryImporter,
  CountryTranslationImporter,
} from '../../src/importers/phase-00/country-importer.js';
import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';

// Mock the file system module
vi.mock('fs', () => ({
  readFileSync: vi.fn(() =>
    JSON.stringify([
      { id: 'usa', internal_name: 'United States of America', backward_compatibility: 'us' },
      { id: 'fra', internal_name: 'France', backward_compatibility: 'fr' },
      { id: 'egy', internal_name: 'Egypt', backward_compatibility: 'eg' },
    ])
  ),
}));

describe('CountryImporter', () => {
  let mockLegacyDb: ILegacyDatabase;
  let mockStrategy: IWriteStrategy;
  let tracker: UnifiedTracker;
  let context: ImportContext;

  beforeEach(() => {
    // Reset mocks
    vi.clearAllMocks();

    // Create mock legacy database (should NOT be called)
    mockLegacyDb = {
      query: vi.fn(),
      connect: vi.fn(),
      disconnect: vi.fn(),
    };

    // Create mock write strategy
    mockStrategy = {
      writeLanguage: vi.fn().mockResolvedValue(undefined),
      writeCountry: vi.fn().mockResolvedValue(undefined),
      writeContext: vi.fn().mockResolvedValue('context-uuid'),
      writeCollection: vi.fn().mockResolvedValue('collection-uuid'),
      writeProject: vi.fn().mockResolvedValue('project-uuid'),
      writePartner: vi.fn().mockResolvedValue('partner-uuid'),
      writeItem: vi.fn().mockResolvedValue('item-uuid'),
      writeLanguageTranslation: vi.fn().mockResolvedValue(undefined),
      writeCountryTranslation: vi.fn().mockResolvedValue(undefined),
      writeContextTranslation: vi.fn().mockResolvedValue(undefined),
      writeCollectionTranslation: vi.fn().mockResolvedValue(undefined),
      writeProjectTranslation: vi.fn().mockResolvedValue(undefined),
      writePartnerTranslation: vi.fn().mockResolvedValue(undefined),
      writeItemTranslation: vi.fn().mockResolvedValue(undefined),
      attachTagsToItem: vi.fn().mockResolvedValue(undefined),
      attachArtistsToItem: vi.fn().mockResolvedValue(undefined),
      writeTag: vi.fn().mockResolvedValue('tag-uuid'),
      writeAuthor: vi.fn().mockResolvedValue('author-uuid'),
      writeArtist: vi.fn().mockResolvedValue('artist-uuid'),
      exists: vi.fn().mockResolvedValue(false),
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
    };

    // Create tracker
    tracker = new UnifiedTracker();

    // Create import context
    context = {
      legacyDb: mockLegacyDb,
      strategy: mockStrategy,
      tracker,
      dryRun: false,
    };
  });

  it('should have the correct name', () => {
    const importer = new CountryImporter(context);
    expect(importer.getName()).toBe('CountryImporter');
  });

  it('should import countries from JSON file without querying legacy database', async () => {
    const importer = new CountryImporter(context);
    const result = await importer.import();

    // Should not query the legacy database
    expect(mockLegacyDb.query).not.toHaveBeenCalled();

    // Should write countries through the strategy
    expect(mockStrategy.writeCountry).toHaveBeenCalledTimes(3);

    // Verify that it imported successfully
    expect(result.success).toBe(true);
    expect(result.imported).toBe(3);
    expect(result.skipped).toBe(0);
    expect(result.errors).toHaveLength(0);
  });

  it('should pass correct data to writeCountry', async () => {
    const importer = new CountryImporter(context);
    await importer.import();

    // Check that writeCountry was called with correct data
    // Note: is_default and is_enabled are not passed because the countries table doesn't have those columns
    expect(mockStrategy.writeCountry).toHaveBeenCalledWith({
      id: 'usa',
      internal_name: 'United States of America',
      backward_compatibility: 'us',
    });

    expect(mockStrategy.writeCountry).toHaveBeenCalledWith({
      id: 'fra',
      internal_name: 'France',
      backward_compatibility: 'fr',
    });
  });

  it('should register countries in tracker', async () => {
    const importer = new CountryImporter(context);
    await importer.import();

    // Check that tracker has the countries registered
    expect(tracker.exists('us', 'country')).toBe(true);
    expect(tracker.exists('fr', 'country')).toBe(true);
    expect(tracker.exists('eg', 'country')).toBe(true);

    // Check that the tracker returns correct UUIDs
    expect(tracker.getUuid('us', 'country')).toBe('usa');
    expect(tracker.getUuid('fr', 'country')).toBe('fra');
    expect(tracker.getUuid('eg', 'country')).toBe('egy');
  });

  it('should skip countries that already exist in tracker', async () => {
    // Pre-register a country
    tracker.register({
      uuid: 'usa',
      backwardCompatibility: 'us',
      entityType: 'country',
      createdAt: new Date(),
    });

    const importer = new CountryImporter(context);
    const result = await importer.import();

    // Should skip the pre-registered country
    expect(result.imported).toBe(2);
    expect(result.skipped).toBe(1);
    expect(mockStrategy.writeCountry).toHaveBeenCalledTimes(2);
  });

  it('should work in dry-run mode without writing', async () => {
    context.dryRun = true;
    const importer = new CountryImporter(context);
    const result = await importer.import();

    // Should not write to strategy in dry-run
    expect(mockStrategy.writeCountry).not.toHaveBeenCalled();

    // But should still register in tracker
    expect(tracker.exists('us', 'country')).toBe(true);
    expect(tracker.exists('fr', 'country')).toBe(true);

    // And report as imported
    expect(result.imported).toBe(3);
    expect(result.success).toBe(true);
  });
});

describe('CountryTranslationImporter', () => {
  let mockLegacyDb: ILegacyDatabase;
  let mockStrategy: IWriteStrategy;
  let tracker: UnifiedTracker;
  let context: ImportContext;
  let translationQueryMock: ReturnType<typeof vi.fn>;

  beforeEach(() => {
    vi.clearAllMocks();

    translationQueryMock = vi.fn();

    mockLegacyDb = {
      query: translationQueryMock as ILegacyDatabase['query'],
      connect: vi.fn(),
      disconnect: vi.fn(),
    };

    mockStrategy = {
      writeLanguage: vi.fn().mockResolvedValue(undefined),
      writeCountry: vi.fn().mockResolvedValue(undefined),
      writeContext: vi.fn().mockResolvedValue('context-uuid'),
      writeCollection: vi.fn().mockResolvedValue('collection-uuid'),
      writeProject: vi.fn().mockResolvedValue('project-uuid'),
      writePartner: vi.fn().mockResolvedValue('partner-uuid'),
      writeItem: vi.fn().mockResolvedValue('item-uuid'),
      writeLanguageTranslation: vi.fn().mockResolvedValue(undefined),
      writeCountryTranslation: vi.fn().mockResolvedValue(undefined),
      writeContextTranslation: vi.fn().mockResolvedValue(undefined),
      writeCollectionTranslation: vi.fn().mockResolvedValue(undefined),
      writeProjectTranslation: vi.fn().mockResolvedValue(undefined),
      writePartnerTranslation: vi.fn().mockResolvedValue(undefined),
      writeItemTranslation: vi.fn().mockResolvedValue(undefined),
      attachTagsToItem: vi.fn().mockResolvedValue(undefined),
      attachArtistsToItem: vi.fn().mockResolvedValue(undefined),
      writeTag: vi.fn().mockResolvedValue('tag-uuid'),
      writeAuthor: vi.fn().mockResolvedValue('author-uuid'),
      writeArtist: vi.fn().mockResolvedValue('artist-uuid'),
      exists: vi.fn().mockResolvedValue(false),
      findByBackwardCompatibility: vi.fn().mockResolvedValue(null),
    };

    tracker = new UnifiedTracker();

    context = {
      legacyDb: mockLegacyDb,
      strategy: mockStrategy,
      tracker,
      dryRun: false,
    };
  });

  it('should have the correct name', () => {
    const importer = new CountryTranslationImporter(context);
    expect(importer.getName()).toBe('CountryTranslationImporter');
  });

  it('should query legacy database even if no translations exist', async () => {
    translationQueryMock.mockResolvedValue([]);
    const importer = new CountryTranslationImporter(context);
    const result = await importer.import();

    expect(translationQueryMock).toHaveBeenCalledWith(
      'SELECT country, lang, name FROM mwnf3.countrynames ORDER BY country, lang'
    );

    expect(mockStrategy.writeCountryTranslation).not.toHaveBeenCalled();
    expect(result.success).toBe(true);
    expect(result.imported).toBe(0);
    expect(result.skipped).toBe(0);
    expect(result.errors).toHaveLength(0);
  });
});
