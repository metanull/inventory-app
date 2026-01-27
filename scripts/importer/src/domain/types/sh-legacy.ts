/**
 * Sharing History Legacy Data Types
 *
 * These types represent the structure of data in the mwnf3_sharing_history legacy database.
 * They are used as input to SH transformers.
 *
 * Key differences from mwnf3:
 * - Partners: Single table with `partners_id` PK (not composite with country)
 * - Items: Composite PK `(project_id, country, number)` without partner in PK
 * - Partner linked via FK `partners_id` in items
 * - Translations in `*_texts` tables (not `*names`)
 */

// ============================================================================
// SH Project Types
// ============================================================================

export interface ShLegacyProject {
  project_id: string;
  name: string;
  addeddate?: string | null;
  new_status?: 'Y' | 'N';
  show?: 'Y' | 'N';
  category?: 'SP' | 'PP'; // SP: SH Projects, PP: Portal Projects
  exhibition_landing_url?: string | null;
  portal_image?: string | null;
}

export interface ShLegacyProjectName {
  project_id: string;
  lang: string;
  title: string;
  sub_title?: string | null;
  short_introduction?: string | null;
  introduction?: string | null;
  about_text?: string | null;
  timeline_text?: string | null;
  sponsor_text?: string | null;
}

// ============================================================================
// SH Partner Types
// ============================================================================

export interface ShLegacyPartner {
  partners_id: string;
  country: string | null;
  partner_category: string;
  name: string;
  city?: string | null;
  address?: string | null;
  phone?: string | null;
  fax?: string | null;
  email?: string | null;
  email2?: string | null;
  url?: string | null;
  url2?: string | null;
  url3?: string | null;
  url4?: string | null;
  url5?: string | null;
  title1?: string | null;
  title2?: string | null;
  title3?: string | null;
  title4?: string | null;
  title5?: string | null;
  logo?: string | null;
  logo1?: string | null;
  logo2?: string | null;
  cp1_name?: string | null;
  cp1_title?: string | null;
  cp1_phone?: string | null;
  cp1_fax?: string | null;
  cp1_email?: string | null;
  cp2_name?: string | null;
  cp2_title?: string | null;
  cp2_phone?: string | null;
  cp2_fax?: string | null;
  cp2_email?: string | null;
  region_id?: string | null;
  geoCoordinates?: string | null;
  zoom?: string | null;
  logo3?: string | null;
  portal_display?: string | null;
}

export interface ShLegacyPartnerName {
  partners_id: string;
  lang: string;
  name: string;
  city?: string;
  department?: string | null;
  description?: string | null;
  how_to_reach?: string | null;
  opening_hours?: string | null;
  partner_related_pages?: string | null;
}

/**
 * Mapping between mwnf3 all_partners and SH partners
 * Used to reuse existing partners when possible
 */
export interface PartnerShPartnerMapping {
  all_partners_id: string;
  partners_id: string;
}

// ============================================================================
// SH Object Types
// ============================================================================

export interface ShLegacyObject {
  project_id: string;
  country: string;
  number: number;
  partners_id: string | null;
  working_number?: string;
  inventory_id?: string | null;
  start_date?: string | null;
  end_date?: string | null;
  display_status?: 'A' | 'N'; // A: Active, N: HB/HCR
  pd_country?: string | null; // Present-day country
}

export interface ShLegacyObjectText {
  project_id: string;
  country: string;
  number: number;
  lang: string;
  name?: string | null;
  name2?: string | null;
  second_name?: string | null;
  third_name?: string | null;
  archival?: string | null;
  typeof?: string | null;
  holding_museum?: string | null;
  holding_institution_org?: string | null;
  location?: string | null;
  province?: string | null;
  date_description?: string | null;
  dynasty?: string | null;
  current_owner?: string | null;
  original_owner?: string | null;
  provenance?: string | null;
  dimensions?: string | null;
  materials?: string | null;
  artist?: string | null;
  birthdate?: string | null;
  birthplace?: string | null;
  deathdate?: string | null;
  deathplace?: string | null;
  period_activity?: string | null;
  production_place?: string | null;
  workshop?: string | null;
  description?: string | null;
  description2?: string | null;
  datationmethod?: string | null;
  provenancemethod?: string | null;
  obtentionmethod?: string | null;
  bibliography?: string | null;
  linkobjects?: string | null;
  linkmonuments?: string | null;
  linkcatalogs?: string | null;
  keywords?: string | null;
  preparedby?: string | null;
  copyeditedby?: string | null;
  translationby?: string | null;
  translationcopyeditedby?: string | null;
  copyright?: string | null;
  notice?: string | null;
  notice_b?: string | null;
  notice_c?: string | null;
}

export interface ShObjectGroup {
  project_id: string;
  country: string;
  number: number;
  partners_id: string | null;
  working_number?: string;
  inventory_id?: string | null;
  start_date?: string | null;
  end_date?: string | null;
  display_status?: 'A' | 'N';
  pd_country?: string | null;
  translations: ShLegacyObjectText[];
}

// ============================================================================
// SH Monument Types
// ============================================================================

export interface ShLegacyMonument {
  project_id: string;
  country: string;
  number: number;
  partners_id: string | null;
  working_number?: string;
  start_date?: string | null;
  end_date?: string | null;
  display_status?: 'A' | 'N';
  pd_country?: string | null;
}

export interface ShLegacyMonumentText {
  project_id: string;
  country: string;
  number: number;
  lang: string;
  name?: string | null;
  name2?: string | null;
  second_name?: string | null;
  third_name?: string | null;
  typeof?: string | null;
  location?: string | null;
  province?: string | null;
  address?: string | null;
  phone?: string | null;
  fax?: string | null;
  email?: string | null;
  institution?: string | null;
  responsible_institution_org?: string | null;
  date_description?: string | null;
  dynasty?: string | null;
  patrons?: string | null;
  architects?: string | null;
  description?: string | null;
  description2?: string | null;
  history?: string | null;
  datationmethod?: string | null;
  bibliography?: string | null;
  external_sources?: string | null;
  linkobjects?: string | null;
  linkmonuments?: string | null;
  linkcatalogs?: string | null;
  keywords?: string | null;
  preparedby?: string | null;
  copyeditedby?: string | null;
  translationby?: string | null;
  translationcopyeditedby?: string | null;
  copyright?: string | null;
  notice?: string | null;
  notice_b?: string | null;
  notice_c?: string | null;
}

export interface ShMonumentGroup {
  project_id: string;
  country: string;
  number: number;
  partners_id: string | null;
  working_number?: string;
  start_date?: string | null;
  end_date?: string | null;
  display_status?: 'A' | 'N';
  pd_country?: string | null;
  translations: ShLegacyMonumentText[];
}

// ============================================================================
// SH Monument Detail Types
// ============================================================================

export interface ShLegacyMonumentDetail {
  project_id: string;
  country: string;
  number: number;
  detail_id: number;
}

export interface ShLegacyMonumentDetailText {
  project_id: string;
  country: string;
  number: number;
  detail_id: number;
  lang: string;
  name: string;
  description: string;
  location: string;
  date: string;
  artist: string;
}

export interface ShMonumentDetailGroup {
  project_id: string;
  country: string;
  number: number;
  detail_id: number;
  translations: ShLegacyMonumentDetailText[];
}

// ============================================================================
// SH Picture Types
// ============================================================================

export interface ShLegacyObjectImage {
  project_id: string;
  country: string;
  number: number;
  type: string;
  image_number: number;
  path: string;
  lastupdate?: string;
}

export interface ShLegacyObjectImageText {
  project_id: string;
  country: string;
  number: number;
  type: string;
  image_number: number;
  lang: string;
  caption?: string | null;
  photographer?: string | null;
  copyright?: string | null;
}

export interface ShLegacyMonumentImage {
  project_id: string;
  country: string;
  number: number;
  type: string;
  image_number: number;
  path: string;
  lastupdate?: string;
}

export interface ShLegacyMonumentImageText {
  project_id: string;
  country: string;
  number: number;
  type: string;
  image_number: number;
  lang: string;
  caption?: string | null;
  photographer?: string | null;
  copyright?: string | null;
}

export interface ShLegacyMonumentDetailPicture {
  project_id: string;
  country: string;
  number: number;
  detail_id: number;
  picture_id: number;
  path: string;
}

export interface ShLegacyMonumentDetailPictureText {
  project_id: string;
  country: string;
  number: number;
  detail_id: number;
  picture_id: number;
  lang: string;
  caption?: string | null;
  photographer?: string | null;
  copyright?: string | null;
}

export interface ShLegacyPartnerPicture {
  partners_id: string;
  image_number: number;
  path: string;
}
