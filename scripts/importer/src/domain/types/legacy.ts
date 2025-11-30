/**
 * Legacy Data Types
 *
 * These types represent the structure of data in the legacy database.
 * They are used as input to transformers.
 */

// ============================================================================
// Language Types
// ============================================================================

export interface LegacyLanguage {
  code: string;
  name: string;
  active?: number | boolean;
}

export interface LegacyLanguageName {
  code: string;
  lang: string;
  name: string;
}

// ============================================================================
// Country Types
// ============================================================================

export interface LegacyCountry {
  code: string;
  name: string;
}

export interface LegacyCountryName {
  code: string;
  lang: string;
  name: string;
}

// ============================================================================
// Project Types
// ============================================================================

export interface LegacyProject {
  project_id: string;
  name?: string;
  launchdate?: string | null;
  active?: number | boolean;
}

export interface LegacyProjectName {
  project_id: string;
  lang: string;
  name: string;
  description?: string;
}

// ============================================================================
// Museum Types
// ============================================================================

export interface LegacyMuseum {
  museum_id: string;
  country: string;
  name: string;
  city?: string;
  address?: string;
  phone?: string;
  fax?: string;
  email?: string;
  url?: string;
  project_id: string;
  geoCoordinates?: string;
}

export interface LegacyMuseumName {
  museum_id: string;
  country: string;
  lang: string;
  name: string;
  ex_name?: string;
  city?: string;
  description?: string;
  ex_description?: string;
  how_to_reach?: string;
  opening_hours?: string;
}

// ============================================================================
// Institution Types
// ============================================================================

export interface LegacyInstitution {
  institution_id: string;
  country: string;
  name: string;
  city?: string;
  address?: string;
  phone?: string;
  fax?: string;
  email?: string;
  url?: string;
}

export interface LegacyInstitutionName {
  institution_id: string;
  country: string;
  lang: string;
  name: string;
  description?: string;
}

// ============================================================================
// Object Types
// ============================================================================

export interface LegacyObject {
  project_id: string;
  country: string;
  museum_id: string;
  number: string;
  lang: string;
  working_number?: string;
  inventory_id?: string;
  name?: string;
  name2?: string;
  typeof?: string;
  holding_museum?: string;
  location?: string;
  province?: string;
  date_description?: string;
  start_date?: string | null;
  end_date?: string | null;
  dynasty?: string;
  current_owner?: string;
  original_owner?: string;
  provenance?: string;
  dimensions?: string;
  materials?: string;
  artist?: string;
  birthdate?: string;
  birthplace?: string;
  deathdate?: string;
  deathplace?: string;
  period_activity?: string;
  production_place?: string;
  workshop?: string;
  description?: string;
  description2?: string;
  datationmethod?: string;
  provenancemethod?: string;
  obtentionmethod?: string;
  bibliography?: string;
  keywords?: string;
  preparedby?: string;
  copyeditedby?: string;
  translationby?: string;
  translationcopyeditedby?: string;
  copyright?: string;
  binding_desc?: string;
}

export interface ObjectGroup {
  project_id: string;
  country: string;
  museum_id: string;
  number: string;
  translations: LegacyObject[];
}

// ============================================================================
// Monument Types
// ============================================================================

export interface LegacyMonument {
  project_id: string;
  country: string;
  institution_id: string;
  number: string;
  lang: string;
  working_number?: string;
  inventory_id?: string;
  name?: string;
  name2?: string;
  typeof?: string;
  location?: string;
  province?: string;
  date_description?: string;
  current_owner?: string;
  original_owner?: string;
  description?: string;
  description2?: string;
  datationmethod?: string;
  bibliography?: string;
  keywords?: string;
  preparedby?: string;
  copyeditedby?: string;
  translationby?: string;
  translationcopyeditedby?: string;
}

export interface MonumentGroup {
  project_id: string;
  country: string;
  institution_id: string;
  number: string;
  translations: LegacyMonument[];
}
