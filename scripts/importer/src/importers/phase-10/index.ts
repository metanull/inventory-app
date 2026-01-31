/**
 * Phase 10 Importers - Thematic Gallery Data
 *
 * Imports exhibitions, galleries, themes, and related data from the
 * mwnf3_thematic_gallery legacy database.
 *
 * This phase runs LAST because THG galleries can reference items from
 * ALL other legacy databases (mwnf3, SH, Explore, etc.).
 */

export { ThgGalleryContextImporter } from './thg-gallery-context-importer.js';
export { ThgGalleryImporter } from './thg-gallery-importer.js';
export { ThgGalleryTranslationImporter } from './thg-gallery-translation-importer.js';
export { ThgThemeImporter } from './thg-theme-importer.js';
export { ThgThemeTranslationImporter } from './thg-theme-translation-importer.js';
export { ThgThemeItemImporter } from './thg-theme-item-importer.js';
export { ThgThemeItemTranslationImporter } from './thg-theme-item-translation-importer.js';
export { ThgItemRelatedImporter } from './thg-item-related-importer.js';
export { ThgItemRelatedTranslationImporter } from './thg-item-related-translation-importer.js';

// Gallery-Item Link Importers (direct links from thg_gallery to items)
export { ThgGalleryMwnf3ObjectImporter } from './thg-gallery-mwnf3-object-importer.js';
export { ThgGalleryMwnf3MonumentImporter } from './thg-gallery-mwnf3-monument-importer.js';
export { ThgGalleryShObjectImporter } from './thg-gallery-sh-object-importer.js';
export { ThgGalleryShMonumentImporter } from './thg-gallery-sh-monument-importer.js';
