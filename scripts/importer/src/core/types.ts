/**
 * Core Types and Interfaces for the Unified Import Architecture
 *
 * This module defines the fundamental types used throughout the import system.
 * Following Single Responsibility Principle, types are grouped by their concern.
 */

// ============================================================================
// Import Result Types
// ============================================================================

/**
 * Result of an import operation
 */
export interface ImportResult {
  success: boolean;
  imported: number;
  skipped: number;
  errors: string[];
  warnings?: string[];
}

/**
 * Create a default import result
 */
export function createImportResult(): ImportResult {
  return {
    success: true,
    imported: 0,
    skipped: 0,
    errors: [],
    warnings: [],
  };
}

// ============================================================================
// Entity Types
// ============================================================================

/**
 * Supported entity types in the import system
 */
export type EntityType =
  | 'language'
  | 'language_translation'
  | 'country'
  | 'country_translation'
  | 'context'
  | 'collection'
  | 'collection_translation'
  | 'project'
  | 'partner'
  | 'partner_translation'
  | 'item'
  | 'item_translation'
  | 'image'
  | 'tag'
  | 'author'
  | 'artist'
  | 'glossary'
  | 'glossary_translation'
  | 'glossary_spelling'
  | 'theme'
  | 'theme_translation'
  | 'item_item_link'
  | 'item_item_link_translation';

/**
 * Imported entity record for tracking
 */
export interface ImportedEntity {
  uuid: string;
  backwardCompatibility: string;
  entityType: EntityType;
  createdAt: Date;
}

// ============================================================================
// Base Data Types (Common across strategies)
// ============================================================================

/**
 * Base fields for all entities
 */
export interface BaseEntityData {
  internal_name: string;
  backward_compatibility: string;
}

/**
 * Language data for write operations
 */
export interface LanguageData extends BaseEntityData {
  id: string; // ISO 639-3 code
  is_default?: boolean;
}

/**
 * Language translation data
 */
export interface LanguageTranslationData {
  language_id: string; // The language being translated
  display_language_id: string; // The language of the translation
  name: string;
  backward_compatibility: string;
}

/**
 * Country data for write operations
 */
export interface CountryData extends BaseEntityData {
  id: string; // ISO 3166-1 alpha-3 code
}

/**
 * Country translation data
 */
export interface CountryTranslationData {
  country_id: string;
  language_id: string;
  name: string;
  backward_compatibility: string;
}

/**
 * Context data for write operations
 */
export interface ContextData extends BaseEntityData {
  is_default?: boolean;
}

/**
 * Context translation data
 */
export interface ContextTranslationData {
  context_id: string;
  language_id: string;
  name: string;
  description?: string | null;
}

/**
 * Collection data for write operations
 */
export interface CollectionData extends BaseEntityData {
  context_id: string;
  language_id: string; // Required: ISO 639-3 code
  parent_id?: string | null;
  type?: string | null; // collection, exhibition, gallery, theme, exhibition trail, itinerary, location
  // GPS Location (optional)
  latitude?: number | null;
  longitude?: number | null;
  map_zoom?: number | null;
  // Country reference (optional)
  country_id?: string | null;
}

/**
 * Collection translation data
 */
export interface CollectionTranslationData {
  collection_id: string;
  language_id: string;
  context_id: string;
  backward_compatibility: string;
  title: string;
  description?: string | null;
  quote?: string | null;
}

/**
 * Project data for write operations
 */
export interface ProjectData extends BaseEntityData {
  context_id: string;
  language_id: string; // Required: ISO 639-3 code
  launch_date?: string | null;
  is_launched?: boolean;
  is_enabled?: boolean;
}

/**
 * Project translation data
 */
export interface ProjectTranslationData {
  project_id: string;
  language_id: string;
  context_id: string;
  name: string;
  description?: string | null;
}

/**
 * Partner data for write operations
 */
export interface PartnerData extends BaseEntityData {
  type: 'museum' | 'institution';
  latitude?: number | null;
  longitude?: number | null;
  map_zoom?: number | null;
  country_id?: string | null;
  project_id?: string | null;
  monument_item_id?: string | null;
  visible?: boolean | null;
}

/**
 * Partner translation data
 */
export interface PartnerTranslationData {
  partner_id: string;
  language_id: string;
  context_id: string;
  backward_compatibility: string;
  name: string;
  description?: string | null;
  city_display?: string | null;
  address?: string | null;
  contact_website?: string | null;
  contact_phone?: string | null;
  contact_email_general?: string | null;
  extra?: string | null;
}

/**
 * Item data for write operations
 */
export interface ItemData extends BaseEntityData {
  type: 'object' | 'monument' | 'detail' | 'picture';
  collection_id?: string | null; // Optional: some items (like explore monuments) may not have a default collection
  partner_id?: string | null;
  country_id?: string | null;
  project_id?: string | null;
  parent_id?: string | null;
  owner_reference?: string | null;
  mwnf_reference?: string | null;
  // GPS Location (optional, primarily for monuments)
  latitude?: number | null;
  longitude?: number | null;
  map_zoom?: number | null;
}

/**
 * Collection-Item link data for pivot table (many-to-many)
 */
export interface CollectionItemData {
  collection_id: string;
  item_id: string;
  backward_compatibility?: string | null;
  display_order?: number | null;
}

/**
 * Item translation data
 */
export interface ItemTranslationData {
  item_id: string;
  language_id: string;
  context_id: string;
  backward_compatibility: string;
  name: string;
  description: string;
  alternate_name?: string | null;
  type?: string | null;
  holder?: string | null;
  owner?: string | null;
  initial_owner?: string | null;
  dates?: string | null;
  location?: string | null;
  dimensions?: string | null;
  place_of_production?: string | null;
  method_for_datation?: string | null;
  method_for_provenance?: string | null;
  obtention?: string | null;
  bibliography?: string | null;
  author_id?: string | null;
  text_copy_editor_id?: string | null;
  translator_id?: string | null;
  translation_copy_editor_id?: string | null;
  extra?: string | null;
}

/**
 * Tag data for write operations
 */
export interface TagData extends BaseEntityData {
  category: string;
  language_id: string;
  description?: string | null;
}

/**
 * Author data for write operations
 */
export interface AuthorData extends BaseEntityData {
  name: string;
}

/**
 * Artist data for write operations
 */
export interface ArtistData extends BaseEntityData {
  name: string;
  place_of_birth?: string | null;
  place_of_death?: string | null;
  date_of_birth?: string | null;
  date_of_death?: string | null;
  period_of_activity?: string | null;
}

/**
 * Item image data for write operations
 */
export interface ItemImageData {
  id?: string; // Optional: for preserving IDs from AvailableImage
  item_id: string;
  path: string;
  original_name: string;
  mime_type: string;
  size: number;
  alt_text?: string | null;
  display_order: number;
}

/**
 * Partner image data for write operations
 */
export interface PartnerImageData {
  id?: string; // Optional: for preserving IDs from AvailableImage
  partner_id: string;
  path: string;
  original_name: string;
  mime_type: string;
  size: number;
  alt_text?: string | null;
  display_order: number;
}

/**
 * Collection image data for write operations
 */
export interface CollectionImageData {
  id?: string; // Optional: for preserving IDs
  collection_id: string;
  path: string;
  original_name: string;
  mime_type: string;
  size: number;
  alt_text?: string | null;
  display_order: number;
}

/**
 * Glossary (Word) data for write operations
 */
export type GlossaryData = BaseEntityData;

/**
 * Glossary translation (definition) data for write operations
 */
export interface GlossaryTranslationData {
  glossary_id: string;
  language_id: string;
  definition: string;
}

/**
 * Glossary spelling data for write operations
 */
export interface GlossarySpellingData {
  glossary_id: string;
  language_id: string;
  spelling: string;
}

// ============================================================================
// Phase 05 Types - Thematic Gallery
// ============================================================================

/**
 * Theme data for write operations
 */
export interface ThemeData {
  collection_id: string;
  parent_id?: string | null;
  display_order: number;
  internal_name: string;
  backward_compatibility: string;
}

/**
 * Theme translation data for write operations
 */
export interface ThemeTranslationData {
  theme_id: string;
  language_id: string;
  context_id: string;
  title: string;
  description?: string | null;
  introduction?: string | null;
  backward_compatibility: string;
}

/**
 * Item-Item Link data for write operations
 */
export interface ItemItemLinkData {
  source_id: string;
  target_id: string;
  context_id: string;
  backward_compatibility?: string | null;
}

/**
 * Item-Item Link Translation data for write operations
 */
export interface ItemItemLinkTranslationData {
  item_item_link_id: string;
  language_id: string;
  description?: string | null;
  reciprocal_description?: string | null;
  backward_compatibility?: string | null;
}
