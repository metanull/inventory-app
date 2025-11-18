# Phase 2 Integration Tests: Sharing History & Thematic Gallery

This directory contains integration tests for schemas that extend/customize mwnf3.

## Import Order

1. **ShExhibitionImporter** - Sharing History exhibitions/themes/subthemes
2. **ThgGalleryImporter** - Thematic Gallery galleries and themes
3. **ContextualTranslationImporter** - Context-specific item descriptions

## Test Files

- `ShExhibitionImporter.test.ts` - SH exhibition hierarchy
- `ThgGalleryImporter.test.ts` - THG gallery hierarchy
- `ContextualTranslationImporter.test.ts` - Contextualized item texts

## Key Concepts Tested

### Deduplication
Tests verify:
- Check tracker before creating entities
- Reuse existing Context/Collection/Partner/Item UUIDs
- Only create new entities when not referenced from mwnf3

### Hierarchical Collections
Tests verify:
- Parent-child relationships (Exhibition → Theme → Subtheme)
- Gallery → Theme hierarchies
- Proper parent_id assignments

### Contextual Translations
Tests verify:
- Same item, different descriptions per context
- ItemTranslation with appropriate context_id
- Gallery-specific image captions
