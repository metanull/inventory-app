# Phase 1 Integration Tests: mwnf3 Core Schema

This directory contains integration tests for importing the foundational mwnf3 schema.

## Import Order

Tests must run in dependency order:

1. **ProjectImporter** - Creates Contexts and Collections
2. **PartnerImporter** - Creates Partners (museums, institutions)
3. **ItemImporter** - Creates Items (objects, monuments, details)
4. **ImageImporter** - Creates ImageUploads and attaches to items
5. **AuthorImporter** - Creates Authors and links to items
6. **TagImporter** - Creates Tags and links to items
7. **ItemLinkImporter** - Creates relationships between items

## Test Files

- `ProjectImporter.test.ts` - Project → Context + Collection mapping
- `PartnerImporter.test.ts` - Museum/Institution → Partner mapping
- `ItemImporter.test.ts` - Object/Monument/Detail → Item mapping
- `ImageImporter.test.ts` - Picture tables → ImageUpload + ItemImage
- `AuthorImporter.test.ts` - Author fields → Author model
- `TagImporter.test.ts` - Tag lists → Tag model
- `ItemLinkImporter.test.ts` - Cross-references → ItemItemLink

## Key Concepts Tested

### Denormalization Handling

mwnf3 tables include language in PK. Tests verify:

- Grouping rows by non-language PK columns
- Creating ONE entity per grouped record
- Creating multiple translations per language

### Backward Compatibility

Tests verify correct format:

- `mwnf3:projects:{project_id}`
- `mwnf3:objects:{project}:{country}:{museum}:{number}`
- `mwnf3:objects_pictures:{project}:{country}:{museum}:{number}:{index}`

### Image Import

Tests verify:

- Image #1 → ItemImage directly on parent item
- ALL images → Child items with type='picture'
- Deduplication by file hash
- Backward compatibility tracking
