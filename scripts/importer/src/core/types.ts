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
  | 'project'
  | 'partner'
  | 'partner_translation'
  | 'item'
  | 'item_translation'
  | 'image'
  | 'tag'
  | 'author'
  | 'artist';

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
  type: 'object' | 'monument';
  collection_id: string;
  partner_id: string;
  country_id?: string | null;
  project_id?: string | null;
  owner_reference?: string | null;
  mwnf_reference?: string | null;
}

/**
 * Item translation data
 */
export interface ItemTranslationData {
  item_id: string;
  language_id: string;
  context_id: string;
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
