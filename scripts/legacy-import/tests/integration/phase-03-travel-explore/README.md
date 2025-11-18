# Phase 3 Integration Tests: Travel & Explore Schemas

This directory contains integration tests for travel and explore schemas with different data models.

## Import Order

1. **TravelTrailImporter** - Travel trails/itineraries/locations hierarchy
2. **ExploreMonumentImporter** - Explore monuments (CRITICAL - large dataset)
3. **ExploreItineraryImporter** - Explore itineraries and relationships

## Test Files

- `TravelTrailImporter.test.ts` - Travel hierarchy (Trail → Itinerary → Location)
- `ExploreMonumentImporter.test.ts` - Explore monuments (normalized model)
- `ExploreItineraryImporter.test.ts` - Explore itinerary structure

## Key Concepts Tested

### Travel Schema Denormalization
Tests verify:
- ALL travel tables have language in PK (like mwnf3)
- Grouping and deduplication patterns
- 3-level hierarchy mapping

### Explore Schema Normalization
Tests verify:
- Base + Translated table patterns
- Monument with 5 description fields per language
- Proper translation mapping

### Critical Dataset
Tests verify:
- Explore contains ~1,808 monuments (must not lose data)
- Deduplication with mwnf3/sh/thg monuments
- Image imports for all monuments
