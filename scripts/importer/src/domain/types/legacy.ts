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
  postal_address?: string;
  // Monument location reference (museum is located in this monument)
  mon_project_id?: string | null;
  mon_country_id?: string | null;
  mon_institution_id?: string | null;
  mon_monument_id?: number | null;
  mon_lang_id?: string | null;
  // Connected museum reference
  con_museum_id?: string | null;
  con_country_id?: string | null;
  // Contact information
  phone?: string;
  fax?: string;
  email?: string;
  email2?: string;
  url?: string;
  url2?: string;
  url3?: string;
  url4?: string;
  url5?: string;
  // URL titles
  title1?: string;
  title2?: string;
  title3?: string;
  title4?: string;
  title5?: string;
  // Logos
  logo?: string;
  logo1?: string;
  logo2?: string;
  logo3?: string;
  // Contact person 1
  cp1_name?: string;
  cp1_title?: string;
  cp1_phone?: string;
  cp1_fax?: string;
  cp1_email?: string;
  // Contact person 2
  cp2_name?: string;
  cp2_title?: string;
  cp2_phone?: string;
  cp2_fax?: string;
  cp2_email?: string;
  // Other
  project_id: string;
  region_id?: string;
  geoCoordinates?: string;
  zoom?: string;
  portal_display?: string;
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
  description?: string;
  phone?: string;
  fax?: string;
  email?: string;
  url?: string;
  url2?: string;
  // Contact person 1
  cp1_name?: string;
  cp1_title?: string;
  cp1_phone?: string;
  cp1_fax?: string;
  cp1_email?: string;
  // Contact person 2
  cp2_name?: string;
  cp2_title?: string;
  cp2_phone?: string;
  cp2_fax?: string;
  cp2_email?: string;
  // Other
  region_id?: string;
  // Logos
  logo?: string;
  logo1?: string;
  logo2?: string;
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
  linkcatalogs?: string | null;
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
  linkcatalogs?: string | null;
  external_sources?: string | null;
  // Contact fields
  address?: string | null;
  phone?: string | null;
  fax?: string | null;
  email?: string | null;
  institution?: string | null;
  // Extra descriptive fields
  patrons?: string | null;
  architects?: string | null;
  history?: string | null;
  dynasty?: string | null;
}

export interface MonumentGroup {
  project_id: string;
  country: string;
  institution_id: string;
  number: string;
  translations: LegacyMonument[];
}

// ============================================================================
// Monument Detail Types
// ============================================================================

export interface LegacyMonumentDetail {
  project_id: string;
  country_id: string;
  institution_id: string;
  monument_id: string;
  lang_id: string;
  detail_id: string;
  name?: string;
  description?: string;
  location?: string;
  date?: string;
  artist?: string;
}

export interface MonumentDetailGroup {
  project_id: string;
  country_id: string;
  institution_id: string;
  monument_id: string;
  detail_id: string;
  translations: LegacyMonumentDetail[];
}

// ============================================================================
// Picture Types
// ============================================================================

export interface LegacyObjectPicture {
  project_id: string;
  country: string;
  museum_id: string;
  number: number;
  lang: string;
  type: string;
  image_number: number;
  path: string;
  caption?: string;
  photographer?: string;
  copyright?: string;
}

export interface LegacyMonumentPicture {
  project_id: string;
  country: string;
  institution_id: string;
  number: number;
  lang: string;
  type: string;
  image_number: number;
  path: string;
  caption?: string;
  photographer?: string;
  copyright?: string;
}

export interface LegacyMonumentDetailPicture {
  project_id: string;
  country_id: string;
  institution_id: string;
  monument_id: number;
  lang_id: string;
  detail_id: number;
  picture_id: number;
  path: string;
  caption?: string;
  photographer?: string;
  copyright?: string;
}

export interface LegacyMuseumPicture {
  museum_id: string;
  country: string;
  image_number: number;
  path: string;
  caption?: string;
  photographer?: string;
  copyright?: string;
}

export interface LegacyInstitutionPicture {
  institution_id: string;
  country: string;
  image_number: number;
  path: string;
  caption?: string;
  photographer?: string;
  copyright?: string;
}
