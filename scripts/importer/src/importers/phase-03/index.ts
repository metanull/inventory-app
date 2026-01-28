/**
 * Phase 03 Importers - Sharing History Data
 *
 * Imports data from the mwnf3_sharing_history legacy database.
 * Creates:
 * - Contexts, Collections, Projects for each SH project
 * - Partners (reusing mwnf3 partners where possible via mapping)
 * - Objects, Monuments, Monument Details as Items
 * - Pictures for all item types
 */

export { ShProjectImporter } from './sh-project-importer.js';
export { ShPartnerImporter } from './sh-partner-importer.js';
export { ShObjectImporter } from './sh-object-importer.js';
export { ShMonumentImporter } from './sh-monument-importer.js';
export { ShMonumentDetailImporter } from './sh-monument-detail-importer.js';
export { ShObjectPictureImporter } from './sh-object-picture-importer.js';
export { ShMonumentPictureImporter } from './sh-monument-picture-importer.js';
export { ShMonumentDetailPictureImporter } from './sh-monument-detail-picture-importer.js';
