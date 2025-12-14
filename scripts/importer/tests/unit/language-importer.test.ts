/**
 * Tests for Language Importer
 *
 * Verifies that the LanguageImporter correctly loads languages from the JSON file
 * instead of querying the legacy database.
 */

import { describe, it, expect, beforeEach, vi } from 'vitest';
import {
  LanguageImporter,
  LanguageTranslationImporter,
} from '../../src/importers/phase-00/language-importer.js';
import { UnifiedTracker } from '../../src/core/tracker.js';
import type { ImportContext, ILegacyDatabase } from '../../src/core/base-importer.js';
import type { IWriteStrategy } from '../../src/core/strategy.js';

// Mock the file system module
vi.mock('fs', () => ({
  readFileSync: vi.fn(() =>
    JSON.stringify([
      { id: 'eng', internal_name: 'English', backward_compatibility: 'en', is_default: true },
      { id: 'fra', internal_name: 'Français', backward_compatibility: 'fr', is_default: false },
      { id: 'ara', internal_name: 'Arabic', backward_compatibility: 'ar', is_default: false },
    ])
  ),
}));

describe('LanguageImporter', () => {
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
      execute: vi.fn(),
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
      writeItemImage: vi.fn().mockResolvedValue('item-image-uuid'),
      writePartnerImage: vi.fn().mockResolvedValue('partner-image-uuid'),
      attachItemsToCollection: vi.fn().mockResolvedValue(undefined),
      attachPartnersToCollection: vi.fn().mockResolvedValue(undefined),
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
    const importer = new LanguageImporter(context);
    expect(importer.getName()).toBe('LanguageImporter');
  });

  it('should import languages from JSON file without querying legacy database', async () => {
    const importer = new LanguageImporter(context);
    const result = await importer.import();

    // Should not query the legacy database
    expect(mockLegacyDb.query).not.toHaveBeenCalled();

    // Should write languages through the strategy
    expect(mockStrategy.writeLanguage).toHaveBeenCalledTimes(3);

    // Verify that it imported successfully
    expect(result.success).toBe(true);
    expect(result.imported).toBe(3);
    expect(result.skipped).toBe(0);
    expect(result.errors).toHaveLength(0);
  });

  it('should pass correct data to writeLanguage', async () => {
    const importer = new LanguageImporter(context);
    await importer.import();

    // Check that writeLanguage was called with correct data
    // Note: is_enabled is not passed because the languages table doesn't have that column
    expect(mockStrategy.writeLanguage).toHaveBeenCalledWith({
      id: 'eng',
      internal_name: 'English',
      backward_compatibility: 'en',
      is_default: true,
    });

    expect(mockStrategy.writeLanguage).toHaveBeenCalledWith({
      id: 'fra',
      internal_name: 'Français',
      backward_compatibility: 'fr',
      is_default: false,
    });
  });

  it('should register languages in tracker', async () => {
    const importer = new LanguageImporter(context);
    await importer.import();

    // Check that tracker has the languages registered
    expect(tracker.exists('en', 'language')).toBe(true);
    expect(tracker.exists('fr', 'language')).toBe(true);
    expect(tracker.exists('ar', 'language')).toBe(true);

    // Check that the tracker returns correct UUIDs
    expect(tracker.getUuid('en', 'language')).toBe('eng');
    expect(tracker.getUuid('fr', 'language')).toBe('fra');
    expect(tracker.getUuid('ar', 'language')).toBe('ara');
  });

  it('should skip languages that already exist in tracker', async () => {
    // Pre-register a language
    tracker.register({
      uuid: 'eng',
      backwardCompatibility: 'en',
      entityType: 'language',
      createdAt: new Date(),
    });

    const importer = new LanguageImporter(context);
    const result = await importer.import();

    // Should skip the pre-registered language
    expect(result.imported).toBe(2);
    expect(result.skipped).toBe(1);
    expect(mockStrategy.writeLanguage).toHaveBeenCalledTimes(2);
  });

  it('should work in dry-run mode without writing', async () => {
    context.dryRun = true;
    const importer = new LanguageImporter(context);
    const result = await importer.import();

    // Should not write to strategy in dry-run
    expect(mockStrategy.writeLanguage).not.toHaveBeenCalled();

    // But should still register in tracker
    expect(tracker.exists('en', 'language')).toBe(true);
    expect(tracker.exists('fr', 'language')).toBe(true);

    // And report as imported
    expect(result.imported).toBe(3);
    expect(result.success).toBe(true);
  });
});

describe('LanguageTranslationImporter', () => {
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
      execute: vi.fn(),
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
      writeItemImage: vi.fn().mockResolvedValue('item-image-uuid'),
      writePartnerImage: vi.fn().mockResolvedValue('partner-image-uuid'),
      attachItemsToCollection: vi.fn().mockResolvedValue(undefined),
      attachPartnersToCollection: vi.fn().mockResolvedValue(undefined),
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
    const importer = new LanguageTranslationImporter(context);
    expect(importer.getName()).toBe('LanguageTranslationImporter');
  });

  it('should query legacy database even if no translations exist', async () => {
    translationQueryMock.mockResolvedValue([]);
    const importer = new LanguageTranslationImporter(context);
    const result = await importer.import();

    expect(translationQueryMock).toHaveBeenCalledWith(
      'SELECT lang_id, lang, name FROM mwnf3.langnames ORDER BY lang_id, lang'
    );

    expect(mockStrategy.writeLanguageTranslation).not.toHaveBeenCalled();
    expect(result.success).toBe(true);
    expect(result.imported).toBe(0);
    expect(result.skipped).toBe(0);
    expect(result.errors).toHaveLength(0);
  });
});
