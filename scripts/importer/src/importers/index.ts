/**
 * Importers Module Index
 */

// Phase 00: Reference Data
export { DefaultContextImporter } from './phase-00/index.js';
export { LanguageImporter, LanguageTranslationImporter } from './phase-00/index.js';
export { CountryImporter, CountryTranslationImporter } from './phase-00/index.js';

// Phase 01: Core Data
export { ProjectImporter } from './phase-01/index.js';
export { PartnerImporter } from './phase-01/index.js';
export { ObjectImporter } from './phase-01/index.js';
export { MonumentImporter } from './phase-01/index.js';
export { MonumentDetailImporter } from './phase-01/index.js';
export { ItemItemLinkImporter } from './phase-01/index.js';

// Phase 02: Images
export { ObjectPictureImporter } from './phase-02/index.js';
export { MonumentPictureImporter } from './phase-02/index.js';
export { MonumentDetailPictureImporter } from './phase-02/index.js';
export { PartnerPictureImporter } from './phase-02/index.js';
export { PartnerLogoImporter } from './phase-02/index.js';

// Phase 03: Sharing History Data
export {
  ShProjectImporter,
  ShPartnerImporter,
  ShObjectImporter,
  ShMonumentImporter,
  ShMonumentDetailImporter,
  ShObjectPictureImporter,
  ShMonumentPictureImporter,
  ShMonumentDetailPictureImporter,
} from './phase-03/index.js';

// Phase 04: Glossary
export {
  GlossaryImporter,
  GlossaryTranslationImporter,
  GlossarySpellingImporter,
} from './phase-04/index.js';

// Phase 10: Thematic Galleries (runs last, after all other legacy DBs are imported)
export {
  ThgGalleryContextImporter,
  ThgRootCollectionsImporter,
  ThgGalleryImporter,
  ThgGalleryTranslationImporter,
  ThgThemeImporter,
  ThgThemeTranslationImporter,
  ThgThemeItemImporter,
  ThgThemeItemTranslationImporter,
  ThgItemRelatedImporter,
  ThgItemRelatedTranslationImporter,
  // Gallery-Item Link Importers
  ThgGalleryMwnf3ObjectImporter,
  ThgGalleryMwnf3MonumentImporter,
  ThgGalleryShObjectImporter,
  ThgGalleryShMonumentImporter,
  ThgGalleryTravelMonumentImporter,
  ThgGalleryExploreMonumentImporter,
} from './phase-10/index.js';

// Phase 06: Explore
export {
  ExploreContextImporter,
  ExploreRootCollectionsImporter,
  ExploreThematicCycleImporter,
  ExploreThematicCyclePictureImporter,
  ExploreCountryImporter,
  ExploreLocationImporter,
  ExploreLocationPictureImporter,
  ExploreMonumentImporter,
  ExploreMonumentPictureImporter,
  ExploreItineraryImporter,
} from './phase-06/index.js';

// Phase 07: Travels
export {
  TravelsContextImporter,
  TravelsRootCollectionImporter,
  TravelsTrailImporter,
  TravelsTrailTranslationImporter,
  TravelsItineraryImporter,
  TravelsItineraryTranslationImporter,
  TravelsLocationImporter,
  TravelsLocationTranslationImporter,
  TravelsMonumentImporter,
  TravelsMonumentTranslationImporter,
  // Picture Importers
  TravelsTrailPictureImporter,
  TravelsItineraryPictureImporter,
  TravelsLocationPictureImporter,
  TravelsMonumentPictureImporter,
} from './phase-07/index.js';

// Phase 11: Post-Import Linking (runs after all data is imported)
export { PartnerMonumentLinker } from './phase-11/index.js';
