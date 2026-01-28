/**
 * Write Strategy Interface - Strategy Pattern for Data Persistence
 *
 * This interface defines the contract for writing data to the target system.
 * Implementations can use different mechanisms (API, SQL, etc.) while
 * maintaining the same interface for the import logic.
 *
 * Following the Dependency Inversion Principle, importers depend on this
 * abstraction rather than concrete implementations.
 */

import type {
  LanguageData,
  LanguageTranslationData,
  CountryData,
  CountryTranslationData,
  ContextData,
  ContextTranslationData,
  CollectionData,
  CollectionTranslationData,
  ProjectData,
  ProjectTranslationData,
  PartnerData,
  PartnerTranslationData,
  ItemData,
  ItemTranslationData,
  TagData,
  AuthorData,
  ArtistData,
  ItemImageData,
  PartnerImageData,
  GlossaryData,
  GlossaryTranslationData,
  GlossarySpellingData,
  ThemeData,
  ThemeTranslationData,
  ItemItemLinkData,
  ItemItemLinkTranslationData,
} from './types.js';

/**
 * Strategy interface for writing entities to the target system
 *
 * Each method returns the UUID of the created/found entity.
 * The strategy handles the specifics of the write mechanism.
 */
export interface IWriteStrategy {
  // =========================================================================
  // Reference Data
  // =========================================================================

  /**
   * Write a language record
   * @returns The language ID (ISO 639-3 code)
   */
  writeLanguage(data: LanguageData): Promise<string>;

  /**
   * Write a language translation record
   */
  writeLanguageTranslation(data: LanguageTranslationData): Promise<void>;

  /**
   * Write a country record
   * @returns The country ID (ISO 3166-1 alpha-3 code)
   */
  writeCountry(data: CountryData): Promise<string>;

  /**
   * Write a country translation record
   */
  writeCountryTranslation(data: CountryTranslationData): Promise<void>;

  // =========================================================================
  // Core Entities
  // =========================================================================

  /**
   * Write a context record
   * @returns The context UUID
   */
  writeContext(data: ContextData): Promise<string>;

  /**
   * Write a context translation record
   */
  writeContextTranslation(data: ContextTranslationData): Promise<void>;

  /**
   * Write a collection record
   * @returns The collection UUID
   */
  writeCollection(data: CollectionData): Promise<string>;

  /**
   * Write a collection translation record
   */
  writeCollectionTranslation(data: CollectionTranslationData): Promise<void>;

  /**
   * Write a project record
   * @returns The project UUID
   */
  writeProject(data: ProjectData): Promise<string>;

  /**
   * Write a project translation record
   */
  writeProjectTranslation(data: ProjectTranslationData): Promise<void>;

  // =========================================================================
  // Partners
  // =========================================================================

  /**
   * Write a partner record
   * @returns The partner UUID
   */
  writePartner(data: PartnerData): Promise<string>;

  /**
   * Write a partner translation record
   */
  writePartnerTranslation(data: PartnerTranslationData): Promise<void>;

  // =========================================================================
  // Items
  // =========================================================================

  /**
   * Write an item record
   * @returns The item UUID
   */
  writeItem(data: ItemData): Promise<string>;

  /**
   * Write an item translation record
   */
  writeItemTranslation(data: ItemTranslationData): Promise<void>;

  /**
   * Attach tags to an item
   * @param itemId The item UUID
   * @param tagIds Array of tag UUIDs
   */
  attachTagsToItem(itemId: string, tagIds: string[]): Promise<void>;

  /**
   * Attach artists to an item
   * @param itemId The item UUID
   * @param artistIds Array of artist UUIDs
   */
  attachArtistsToItem(itemId: string, artistIds: string[]): Promise<void>;

  /**
   * Attach items to a collection via many-to-many relationship
   * @param collectionId The collection UUID
   * @param itemIds Array of item UUIDs
   */
  attachItemsToCollection(collectionId: string, itemIds: string[]): Promise<void>;

  /**
   * Attach partners to a collection via many-to-many relationship
   * @param collectionId The collection UUID
   * @param partnerIds Array of partner UUIDs
   * @param collectionType The collection type (default: 'project')
   */
  attachPartnersToCollection(
    collectionId: string,
    partnerIds: string[],
    collectionType?: string
  ): Promise<void>;

  // =========================================================================
  // Supporting Entities
  // =========================================================================

  /**
   * Write a tag record
   * @returns The tag UUID
   */
  writeTag(data: TagData): Promise<string>;

  /**
   * Write an author record
   * @returns The author UUID
   */
  writeAuthor(data: AuthorData): Promise<string>;

  /**
   * Write an artist record
   * @returns The artist UUID
   */
  writeArtist(data: ArtistData): Promise<string>;

  /**
   * Write an item image record
   * @returns The item image UUID
   */
  writeItemImage(data: ItemImageData): Promise<string>;

  /**
   * Write a partner image record
   * @returns The partner image UUID
   */
  writePartnerImage(data: PartnerImageData): Promise<string>;

  // =========================================================================
  // Glossary
  // =========================================================================

  /**
   * Write a glossary (word) record
   * @returns The glossary UUID
   */
  writeGlossary(data: GlossaryData): Promise<string>;

  /**
   * Write a glossary translation (definition) record
   */
  writeGlossaryTranslation(data: GlossaryTranslationData): Promise<void>;

  /**
   * Write a glossary spelling record
   * @returns The glossary spelling UUID
   */
  writeGlossarySpelling(data: GlossarySpellingData): Promise<string>;

  // =========================================================================
  // Themes (Thematic Gallery)
  // =========================================================================

  /**
   * Write a theme record
   * @returns The theme UUID
   */
  writeTheme(data: ThemeData): Promise<string>;

  /**
   * Write a theme translation record
   */
  writeThemeTranslation(data: ThemeTranslationData): Promise<void>;

  // =========================================================================
  // Item Links
  // =========================================================================

  /**
   * Write an item-item link record
   * @returns The item-item link UUID
   */
  writeItemItemLink(data: ItemItemLinkData): Promise<string>;

  /**
   * Write an item-item link translation record
   */
  writeItemItemLinkTranslation(data: ItemItemLinkTranslationData): Promise<void>;

  // =========================================================================
  // Lookup Methods
  // =========================================================================

  /**
   * Check if an entity exists by backward_compatibility
   * @param table The table name
   * @param backwardCompatibility The backward_compatibility value
   * @returns True if exists
   */
  exists(table: string, backwardCompatibility: string): Promise<boolean>;

  /**
   * Find entity ID by backward_compatibility
   * @param table The table name
   * @param backwardCompatibility The backward_compatibility value
   * @returns The entity ID or null
   */
  findByBackwardCompatibility(table: string, backwardCompatibility: string): Promise<string | null>;
}
