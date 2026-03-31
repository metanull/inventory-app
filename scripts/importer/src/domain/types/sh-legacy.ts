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

// ============================================================================
// SH HCR (Heritage Conservation Resources) Types
// ============================================================================

export interface ShLegacyHcr {
  hcr_id: number;
  country: string; // 2-char legacy country code
  exhibition_id: number;
  name: string;
  date_from_year: string; // varchar(4) in DB
  date_to_year: string;
  date_from_month: number | null;
  date_to_month: number | null;
  date_from_date: number | null;
  date_to_date: number | null;
}

export interface ShLegacyHcrEvent {
  hcr_id: number;
  lang: string; // 2-char legacy language code
  name: string;
  description: string;
  date_from: string;
  date_to: string;
}

export interface ShLegacyHcrImage {
  hcr_img_id: number;
  hcr_id: number;
  ref_item: string; // 'PROJECT;COUNTRY;NUMBER' or '' for standalone
  item_type: string; // 'obj' or 'mon'
  picture: string;
  sort_order: number;
}

export interface ShLegacyHcrImageText {
  hcr_img_id: number;
  lang: string;
  name: string;
  sname: string;
  name_detail: string;
  detail_justification: string;
  date: string;
  dynasty: string;
  museum: string;
  location: string;
  artist: string;
  material: string;
}

export interface ShLegacyBibliographyHcrCountry {
  country: string;
  exhibition_id: number;
  biblio_id: number;
  sort_status: string; // 'Y' or 'N'
  sort_order: number;
}

export interface ShLegacyBibliography {
  biblio_id: number;
  original_title: string;
  lang: string;
  status: string; // 'A' = Active, 'H' = Hidden
}

export interface ShLegacyBibliographyLang {
  biblio_id: number;
  lang: string;
  desc: string;
}

// ============================================================================
// SH Exhibition Types
// ============================================================================

export interface ShLegacyExhibition {
  exhibition_id: number;
  project_id: string;
  name: string;
  sort: number | null;
  show: 'y' | 'n';
  geoCoordinates: string | null;
  zoom: number | null;
  exh_thumb: string | null;
  logo1: string | null;
  url1: string | null;
  logo2: string | null;
  url2: string | null;
  logo3: string | null;
  url3: string | null;
  homeimage: string | null;
  portal_image: string | null;
}

export interface ShLegacyExhibitionName {
  exhibition_id: number;
  lang: string;
  subtitle: string;
  title: string;
  introduction: string | null;
  see_also_links: string | null;
  further_reading: string | null;
  curated_by: string | null;
  cover_images: string | null;
}

export interface ShLegacyExhibitionTheme {
  theme_id: number;
  exhibition_id: number;
  name: string;
  sort: number | null;
  geoCoordinates: string | null;
  zoom: number | null;
}

export interface ShLegacyExhibitionThemeName {
  theme_id: number;
  lang: string;
  title: string;
  introduction: string | null;
  see_also_links: string | null;
  further_reading: string | null;
}

export interface ShLegacyExhibitionSubtheme {
  subtheme_id: number;
  theme_id: number;
  name: string;
  sort: number | null;
  geoCoordinates: string | null;
  zoom: number | null;
}

export interface ShLegacyExhibitionSubthemeName {
  subtheme_id: number;
  lang: string;
  title: string;
  introduction: string | null;
  quotation: string | null;
  see_also_links: string | null;
  further_reading: string | null;
}

// ============================================================================
// SH Exhibition Relationship Types (item ↔ exhibition/theme/subtheme)
// ============================================================================

export interface ShLegacyRelObjectsExhibitions {
  id: number;
  project_id: string;
  country: string;
  number: number;
  exhibition_id: number;
  curator_status: string | null;
}

export interface ShLegacyRelObjectsExhibitionsJustification {
  relation_id: number;
  lang: string;
  justification_partner: string | null;
  justification_curator: string | null;
}

export interface ShLegacyRelMonumentsExhibitions {
  id: number;
  project_id: string;
  country: string;
  number: number;
  exhibition_id: number;
  curator_status: string | null;
}

export interface ShLegacyRelMonumentsExhibitionsJustification {
  relation_id: number;
  lang: string;
  justification_partner: string | null;
  justification_curator: string | null;
}

export interface ShLegacyRelObjectsThemes {
  id: number;
  project_id: string;
  country: string;
  number: number;
  theme_id: number;
  curator_status: string | null;
}

export interface ShLegacyRelMonumentsThemes {
  id: number;
  project_id: string;
  country: string;
  number: number;
  theme_id: number;
  curator_status: string | null;
}

export interface ShLegacyRelObjectsSubthemes {
  id: number;
  project_id: string;
  country: string;
  number: number;
  subtheme_id: number;
  curator_status: string | null;
  sort_order: number;
  rel_sort_order: number;
}

export interface ShLegacyRelMonumentsSubthemes {
  id: number;
  project_id: string;
  country: string;
  number: number;
  subtheme_id: number;
  curator_status: string | null;
  sort_order: number;
  rel_sort_order: number;
}

// ============================================================================
// SH Exhibition Image Types (item references used as slideshow images)
// ============================================================================

export interface ShLegacyExhibitionImage {
  image_id: number;
  exhibition_id: number;
  image_item: string; // 'project_id;country;number'
  item_type: string; // 'obj' or 'mon'
  sort_order: number;
}

export interface ShLegacyExhibitionThemeImage {
  image_id: number;
  theme_id: number;
  image_item: string; // 'project_id;country;number'
  picture: string;
  item_type: string; // 'obj' or 'mon'
  sort_order: number;
}

export interface ShLegacyExhibitionSubthemeImage {
  image_id: number;
  subtheme_id: number;
  image_item: string; // 'project_id;country;number'
  picture: string;
  item_type: string; // 'obj' or 'mon'
  sort_order: number;
  rel_sort_order: number;
}

// ============================================================================
// SH National Context Types
// ============================================================================

export interface ShLegacyNCExhibition {
  country: string; // 2-char country code
  exhibition_id: number;
}

export interface ShLegacyNCExhibitionText {
  country: string;
  exhibition_id: number;
  lang: string; // 2-char
  context: string;
}

export interface ShLegacyNCExhibitionImage {
  image_id: number;
  country: string;
  exhibition_id: number;
  image_item: string; // 'project_id;country;number'
  item_type: string; // 'obj' or 'mon'
  sort_order: number;
}

export interface ShLegacyRelObjectsNCExhibitions {
  id: number;
  nc_country: string;
  nc_exhibition_id: number;
  project_id: string;
  country: string;
  number: number;
}

export interface ShLegacyRelObjectsNCExhibitionJustification {
  relation_id: number;
  lang: string;
  justification_text: string;
}

export interface ShLegacyRelMonumentsNCExhibitions {
  id: number;
  nc_country: string;
  nc_exhibition_id: number;
  project_id: string;
  country: string;
  number: number;
}

export interface ShLegacyRelMonumentsNCExhibitionJustification {
  relation_id: number;
  lang: string;
  justification_text: string;
}

// ============================================================================
// SH Bibliography Junction Types (non-HCR — HCR types already above)
// ============================================================================

export interface ShLegacyBibliographyExhibition {
  biblio_id: number;
  exhibition_id: number;
  sort_order: number;
  sort_status: string; // 'Y' or 'N'
}

export interface ShLegacyBibliographyObject {
  project_id: string;
  country: string;
  number: number;
  biblio_id: number;
  sort_order: number;
  sort_status: string;
}

export interface ShLegacyBibliographyMonument {
  project_id: string;
  country: string;
  number: number;
  biblio_id: number;
  sort_order: number;
  sort_status: string;
}

export interface ShLegacyBibliographyHb {
  hb_id: number;
  biblio_id: number;
  sort_order: number;
  sort_status: string;
}

// ============================================================================
// SH Historical Background Types
// ============================================================================

export interface ShLegacyHistoricalBackground {
  hb_id: number;
  countryId: string; // 2-char country code (column name in legacy)
  gn: string | null; // 'yes' for general, null for country
  project_id: string;
}

export interface ShLegacyHistoricalBackgroundText {
  hb_id: number;
  lang: string; // 2-char
  name: string;
}

export interface ShLegacyHistoricalBackgroundPage {
  page_id: number;
  hb_id: number;
  sort_order: number;
  remark: string | null;
}

export interface ShLegacyHistoricalBackgroundPageText {
  page_id: number;
  lang: string; // 2-char
  subtitle: string | null;
  text: string | null;
}

export interface ShLegacyHistoricalBackgroundImage {
  hb_img_id: number;
  page_id: number;
  ref_item: string; // 'project_id;country;number' or empty for custom
  item_type: string; // 'obj' or 'mon'
  picture: string;
  sort_order: number;
}

export interface ShLegacyHistoricalBackgroundImageText {
  hb_img_id: number;
  lang: string; // 2-char
  name: string | null;
  sname: string | null;
  name_detail: string | null;
  date: string | null;
  dynasty: string | null;
  museum: string | null;
  location: string | null;
  artist: string | null;
  material: string | null;
}

export interface ShLegacyHistoricalBackgroundMap {
  map_id: number;
  hb_id: number;
  map_path: string;
  sort_order: number;
}

export interface ShLegacyHistoricalBackgroundMapText {
  map_id: number;
  hb_id: number;
  lang: string; // 2-char
  desc: string | null;
}
