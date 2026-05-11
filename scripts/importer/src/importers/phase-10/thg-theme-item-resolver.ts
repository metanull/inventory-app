/**
 * THG Theme Item Resolver
 *
 * Shared resolver that maps a theme_item legacy row to the backward-compatibility
 * key of the selected child picture item.
 *
 * Each supported source family resolves to the picture item that was imported by
 * the corresponding picture importer (phase-02 / phase-03), NOT to the parent
 * object, monument, or detail item.
 *
 * Target key formats:
 * - mwnf3 object   → mwnf3:objects_pictures:{project}:{country}:{partner}:{item}:{image}
 * - mwnf3 monument → mwnf3:monuments_pictures:{project}:{country}:{partner}:{item}:{image}
 * - mwnf3 detail   → mwnf3:monument_detail_pictures:{project}:{country}:{partner}:{item}:{detail}:{image}
 * - SH object      → mwnf3_sharing_history:sh_object_images:{project}:{country}:{item}:{type|_}:{image}
 * - SH monument    → mwnf3_sharing_history:sh_monument_images:{project}:{country}:{item}:{type|_}:{image}
 * - SH detail      → mwnf3_sharing_history:sh_monument_detail_pictures:{project}:{country}:{item}:{detail}:{image}
 *
 * For SH keys all string parts are lowercased (matching formatShBackwardCompatibility).
 * Missing picture items must be treated as explicit skips with warnings by the
 * calling importer — do not fall back to the parent item key.
 */

/**
 * Legacy theme_item row with both parent-item columns and image-identity columns.
 * Only columns required by the resolver are declared here; callers may extend
 * this type with additional fields (e.g. gallery_id, theme_id).
 */
export interface LegacyThemeItem {
  // mwnf3 object references
  mwnf3_object_project_id: string | null;
  mwnf3_object_country_id: string | null;
  mwnf3_object_partner_id: string | null;
  mwnf3_object_item_id: number | null;
  mwnf3_object_item_type: string | null;
  mwnf3_object_image_id: number | null;
  // mwnf3 monument references
  mwnf3_monument_project_id: string | null;
  mwnf3_monument_country_id: string | null;
  mwnf3_monument_partner_id: string | null;
  mwnf3_monument_item_id: number | null;
  mwnf3_monument_item_type: string | null;
  mwnf3_monument_image_id: number | null;
  // mwnf3 monument detail references
  mwnf3_monument_detail_project_id: string | null;
  mwnf3_monument_detail_country_id: string | null;
  mwnf3_monument_detail_partner_id: string | null;
  mwnf3_monument_detail_item_id: number | null;
  mwnf3_monument_detail_detail_id: number | null;
  mwnf3_monument_detail_image_id: number | null;
  // SH (Sharing History) object references
  sh_object_project_id: string | null;
  sh_object_country_id: string | null;
  sh_object_item_id: number | null;
  sh_object_item_type: string | null;
  sh_object_image_id: number | null;
  // SH monument references
  sh_monument_project_id: string | null;
  sh_monument_country_id: string | null;
  sh_monument_item_id: number | null;
  sh_monument_item_type: string | null;
  sh_monument_image_id: number | null;
  // SH monument detail references
  sh_monument_detail_project_id: string | null;
  sh_monument_detail_country_id: string | null;
  sh_monument_detail_item_id: number | null;
  sh_monument_detail_detail_id: number | null;
  sh_monument_detail_image_id: number | null;
}

/**
 * SQL SELECT fragment for all columns required by LegacyThemeItem.
 * Append this to your query's SELECT list when loading theme_item rows.
 */
export const THEME_ITEM_SELECT_COLUMNS = `
  mwnf3_object_project_id, mwnf3_object_country_id, mwnf3_object_partner_id, mwnf3_object_item_id,
  mwnf3_object_item_type, mwnf3_object_image_id,
  mwnf3_monument_project_id, mwnf3_monument_country_id, mwnf3_monument_partner_id, mwnf3_monument_item_id,
  mwnf3_monument_item_type, mwnf3_monument_image_id,
  mwnf3_monument_detail_project_id, mwnf3_monument_detail_country_id, mwnf3_monument_detail_partner_id,
  mwnf3_monument_detail_item_id, mwnf3_monument_detail_detail_id, mwnf3_monument_detail_image_id,
  sh_object_project_id, sh_object_country_id, sh_object_item_id,
  sh_object_item_type, sh_object_image_id,
  sh_monument_project_id, sh_monument_country_id, sh_monument_item_id,
  sh_monument_item_type, sh_monument_image_id,
  sh_monument_detail_project_id, sh_monument_detail_country_id, sh_monument_detail_item_id,
  sh_monument_detail_detail_id, sh_monument_detail_image_id`.trim();

/**
 * Resolve a theme_item row to the backward-compatibility key of the selected
 * child picture item.
 *
 * Returns null when the row belongs to an unsupported source family (e.g. THG
 * Explore, THG Travels) — the caller should skip those rows silently.
 *
 * Returns null when the row belongs to a supported family but the image-identity
 * columns are missing — the caller should emit a warning and skip.
 */
export function resolvePictureItemBackwardCompatibility(legacy: LegacyThemeItem): string | null {
  // mwnf3 object picture
  // Matches ObjectPictureImporter: mwnf3:objects_pictures:{project}:{country}:{museum_id}:{number}:{image_number}
  if (
    legacy.mwnf3_object_project_id &&
    legacy.mwnf3_object_country_id &&
    legacy.mwnf3_object_partner_id &&
    legacy.mwnf3_object_item_id !== null &&
    legacy.mwnf3_object_image_id !== null
  ) {
    return `mwnf3:objects_pictures:${legacy.mwnf3_object_project_id}:${legacy.mwnf3_object_country_id}:${legacy.mwnf3_object_partner_id}:${legacy.mwnf3_object_item_id}:${legacy.mwnf3_object_image_id}`;
  }

  // mwnf3 monument picture
  // Matches MonumentPictureImporter: mwnf3:monuments_pictures:{project}:{country}:{institution_id}:{number}:{image_number}
  if (
    legacy.mwnf3_monument_project_id &&
    legacy.mwnf3_monument_country_id &&
    legacy.mwnf3_monument_partner_id &&
    legacy.mwnf3_monument_item_id !== null &&
    legacy.mwnf3_monument_image_id !== null
  ) {
    return `mwnf3:monuments_pictures:${legacy.mwnf3_monument_project_id}:${legacy.mwnf3_monument_country_id}:${legacy.mwnf3_monument_partner_id}:${legacy.mwnf3_monument_item_id}:${legacy.mwnf3_monument_image_id}`;
  }

  // mwnf3 monument detail picture
  // Matches MonumentDetailPictureImporter: mwnf3:monument_detail_pictures:{project}:{country}:{institution}:{monument}:{detail}:{picture_id}
  if (
    legacy.mwnf3_monument_detail_project_id &&
    legacy.mwnf3_monument_detail_country_id &&
    legacy.mwnf3_monument_detail_partner_id &&
    legacy.mwnf3_monument_detail_item_id !== null &&
    legacy.mwnf3_monument_detail_detail_id !== null &&
    legacy.mwnf3_monument_detail_image_id !== null
  ) {
    return `mwnf3:monument_detail_pictures:${legacy.mwnf3_monument_detail_project_id}:${legacy.mwnf3_monument_detail_country_id}:${legacy.mwnf3_monument_detail_partner_id}:${legacy.mwnf3_monument_detail_item_id}:${legacy.mwnf3_monument_detail_detail_id}:${legacy.mwnf3_monument_detail_image_id}`;
  }

  // SH object picture
  // Matches ShObjectPictureImporter: mwnf3_sharing_history:sh_object_images:{project}:{country}:{number}:{type|_}:{image_number}
  // String parts are lowercased to match formatShBackwardCompatibility behaviour.
  if (
    legacy.sh_object_project_id &&
    legacy.sh_object_country_id &&
    legacy.sh_object_item_id !== null &&
    legacy.sh_object_image_id !== null
  ) {
    const project = legacy.sh_object_project_id.toLowerCase();
    const country = legacy.sh_object_country_id.toLowerCase();
    const type = legacy.sh_object_item_type ? legacy.sh_object_item_type.toLowerCase() : '_';
    return `mwnf3_sharing_history:sh_object_images:${project}:${country}:${legacy.sh_object_item_id}:${type}:${legacy.sh_object_image_id}`;
  }

  // SH monument picture
  // Matches ShMonumentPictureImporter: mwnf3_sharing_history:sh_monument_images:{project}:{country}:{number}:{type|_}:{image_number}
  if (
    legacy.sh_monument_project_id &&
    legacy.sh_monument_country_id &&
    legacy.sh_monument_item_id !== null &&
    legacy.sh_monument_image_id !== null
  ) {
    const project = legacy.sh_monument_project_id.toLowerCase();
    const country = legacy.sh_monument_country_id.toLowerCase();
    const type = legacy.sh_monument_item_type ? legacy.sh_monument_item_type.toLowerCase() : '_';
    return `mwnf3_sharing_history:sh_monument_images:${project}:${country}:${legacy.sh_monument_item_id}:${type}:${legacy.sh_monument_image_id}`;
  }

  // SH monument detail picture
  // Matches ShMonumentDetailPictureImporter: mwnf3_sharing_history:sh_monument_detail_pictures:{project}:{country}:{number}:{detail_id}:{picture_id}
  if (
    legacy.sh_monument_detail_project_id &&
    legacy.sh_monument_detail_country_id &&
    legacy.sh_monument_detail_item_id !== null &&
    legacy.sh_monument_detail_detail_id !== null &&
    legacy.sh_monument_detail_image_id !== null
  ) {
    const project = legacy.sh_monument_detail_project_id.toLowerCase();
    const country = legacy.sh_monument_detail_country_id.toLowerCase();
    return `mwnf3_sharing_history:sh_monument_detail_pictures:${project}:${country}:${legacy.sh_monument_detail_item_id}:${legacy.sh_monument_detail_detail_id}:${legacy.sh_monument_detail_image_id}`;
  }

  // Not a supported source family (THG Explore, THG Travels, etc.)
  return null;
}
