# Fallback and Error Handling Analysis

## Overview

This document identifies all fallback logic and silent error handling patterns in the legacy import codebase that should be removed or replaced with proper error handling.

---

## Category 1: Data Quality Fallbacks (MUST FIX)

These patterns silently substitute missing/invalid data instead of failing properly.

### 1.1 InstitutionImporter - Missing Name Fallback

**File**: `src/importers/phase-01/InstitutionImporter.ts:108-119`

**Current behavior**:

- If `institution.name` is missing, generates fallback: `Institution {id} ({country})`
- Logs warning but continues import

**Problem**:

- Masks data quality issues
- Creates synthetic data that doesn't exist in source
- User doesn't realize data is incomplete

**Fix**: Throw error instead

```typescript
if (!institution.name || institution.name.trim() === '') {
  throw new Error(
    `Institution ${institution.institution_id}:${institution.country} has missing 'name' field`
  );
}
```

---

## Category 2: Silent 422 Error Handling (PROBLEMATIC)

These patterns catch HTTP 422 (validation error) and silently skip or retry instead of failing properly.

### 2.1 Translation Already Exists - Silent Skip

**Files**:

- `InstitutionImporter.ts:307-309`
- `MuseumImporter.ts:294-296`
- Similar patterns in other importers

**Current behavior**:

```typescript
catch (error) {
  if (axiosError.response?.status === 422) {
    return; // Translation already exists, skip
  }
  throw error;
}
```

**Problem**:

- Silently hides duplicate translation attempts
- No visibility into whether translation was created or skipped
- Assumes ALL 422 errors are duplicates (could be other validation errors)

**Recommendation**:

- Check error message explicitly for duplicate key errors
- Log skipped duplicates for visibility
- Re-throw if 422 is NOT a duplicate error

### 2.2 Backward Compatibility Duplicate - Pagination Search

**Files**:

- `InstitutionImporter.ts:171-203`
- `MuseumImporter.ts:181-213`
- Similar patterns across importers

**Current behavior**:

- On 422 error, checks if message contains "backward_compatibility"
- If yes, paginates through ALL entities to find existing one
- Falls back to pagination if tracker doesn't have UUID

**Problems**:

- Extremely slow (iterates through all pages)
- Masks data quality issues
- Assumes pagination will find entity (may not)
- Complex error recovery logic that shouldn't be needed

**Better approach**:

1. Use tracker exclusively - if not in tracker, entity doesn't exist
2. If 422 on backward_compatibility, throw error indicating duplicate
3. Let operator decide whether to skip or fix data

---

## Category 3: Empty String/Null Checks (OK, but review)

These patterns check for empty data and skip processing. Generally acceptable but should be reviewed.

### 3.1 Empty Description Skip

**Files**:

- `ObjectImporter.ts:379-382`
- `MonumentImporter.ts:348-351`

**Current behavior**:

```typescript
if (!sourceDescription || !sourceDescription.trim()) {
  return; // Skip if description is empty
}
```

**Assessment**: ✅ **Acceptable** - It's reasonable to skip creating translations without content.

### 3.2 Empty Artist/Author/Tag Skip

**Files**:

- `ObjectImporter.ts:547-550` (artist)
- `ObjectImporter.ts:974-976` (author)
- `ObjectImporter.ts:722-724` (tags)
- Similar in `MonumentImporter.ts` and helper files

**Current behavior**:

```typescript
if (!artistField || artistField.trim() === '') {
  return []; // or null
}
```

**Assessment**: ✅ **Acceptable** - Optional fields can be empty. Returning empty array/null is correct.

---

## Category 4: Error Message Parsing (FRAGILE)

These patterns parse error messages instead of using structured error responses.

### 4.1 Backward Compatibility Error Detection

**Files**: Multiple importers (InstitutionImporter, MuseumImporter, etc.)

**Current behavior**:

```typescript
const errorMessage = responseData?.message || '';
if (
  errorMessage.includes('backward_compatibility') ||
  errorMessage.includes('already been taken')
) {
  // Try to find existing entity
}
```

**Problems**:

- Fragile - depends on exact error message text
- Will break if Laravel changes error messages
- Not type-safe

**Better approach**:

- Use Laravel's structured validation error format
- Check `errors.backward_compatibility` array instead of parsing message

---

## Category 5: Fallback Search by Pagination (SLOW)

These patterns search for existing entities by paginating through ALL results.

### 5.1 Find Existing Artist by Backward Compatibility

**File**: `ObjectImporter.ts:657-686`

**Current behavior**:

- Paginates through up to 200 pages (20,000 records)
- Searches for matching `backward_compatibility` field

**Problem**:

- Extremely slow for large datasets
- Should use tracker exclusively

**Fix**: Remove pagination search, rely only on tracker

### 5.2 Find Existing Author by Backward Compatibility

**File**: `ObjectImporter.ts:1027-1056`

**Same issues as 5.1**

### 5.3 Find Existing Tag by Backward Compatibility

**File**: `ApiTagHelper.ts:258-319`

**Same issues as 5.1**

---

## Summary of Required Actions

### High Priority (Data Integrity Issues)

1. ✅ **Remove unsafe regex fallback in `HtmlToMarkdownConverter.ts`** (COMPLETED)
2. ✅ **Remove institution name fallback** - throw error instead (COMPLETED)
3. ✅ **Remove all pagination-based entity searches** - use tracker only (COMPLETED)
4. ✅ **Make 422 error handling explicit** - removed all silent 422 handling (COMPLETED)

### Medium Priority (Performance Issues)

5. ✅ **Remove or optimize pagination fallbacks** in Artist/Author/Tag helpers (COMPLETED)
6. ⚠️ **Add better logging** for skipped duplicates (NOT NEEDED - duplicates now cause errors)

### Low Priority (Code Quality)

7. ✅ **Replace error message parsing with structured error checks** (REMOVED - no longer needed)
8. ✅ **Add explicit data quality validation before import** - fail fast (COMPLETED)

---

## Recommended Pattern

### For Required Fields

```typescript
if (!entity.required_field || entity.required_field.trim() === '') {
  throw new Error(`Entity ${entity.id} missing required field 'required_field'`);
}
```

### For 422 Duplicate Errors

```typescript
catch (error) {
  if (error && typeof error === 'object' && 'response' in error) {
    const axiosError = error as AxiosError;
    if (axiosError.response?.status === 422) {
      const validationErrors = axiosError.response?.data?.errors;

      // Check specific validation error
      if (validationErrors?.backward_compatibility) {
        throw new Error(`Duplicate entity with backward_compatibility: ${backwardCompat}`);
      }

      // Other validation error - re-throw
      throw error;
    }
  }
  throw error;
}
```

### For Optional Fields

```typescript
// This is fine - optional field can be empty
if (!entity.optional_field || entity.optional_field.trim() === '') {
  return null; // or skip processing
}
```

---

## Implementation Summary (COMPLETED)

**Approach**: Option A - Remove all fallbacks immediately

### Changes Made

1. **HtmlToMarkdownConverter.ts**
   - Removed unsafe regex fallback `/<[^>]+>/g`
   - Now throws proper error if Turndown conversion fails

2. **InstitutionImporter.ts**
   - Removed fake name generation fallback
   - Now throws error if `name` field is missing
   - Removed pagination search for backward_compatibility duplicates
   - Removed silent 422 handling in translation creation

3. **MuseumImporter.ts**
   - Removed pagination search for backward_compatibility duplicates
   - Removed silent 422 handling in translation creation

4. **ApiArtistHelper.ts**
   - Removed `findExistingByBackwardCompat()` pagination method
   - Removed 422 error handling fallback
   - Now relies solely on tracker for duplicate detection

5. **ApiAuthorHelper.ts**
   - Removed `findExistingByBackwardCompat()` pagination method
   - Removed 422 error handling fallback
   - Now relies solely on tracker for duplicate detection

6. **ApiTagHelper.ts**
   - Removed `findExistingByBackwardCompat()` pagination method
   - Removed `findExistingByFields()` fallback method
   - Removed 422 and 500 error handling fallback
   - Now relies solely on tracker for duplicate detection

7. **ObjectImporter.ts**
   - Removed `findExistingArtistByBackwardCompat()` method
   - Removed `findExistingAuthorByBackwardCompat()` method
   - Removed all 422 error handling in artist/author creation

8. **MonumentImporter.ts**
   - Removed `findExistingAuthorByBackwardCompat()` method
   - Removed all 422 error handling in author creation

### Impact

- **Data Quality**: Import will now fail fast on any data quality issues
- **Performance**: Eliminated slow pagination searches (potential 20,000+ record scans)
- **Reliability**: No silent failures or masked errors
- **Backward Compatibility**: Field still tracked but not used for fallback searches
- **Breaking Change**: Requires clean database state - duplicate runs will fail

### Testing

All changes pass ESLint validation. Import script should now:

- Fail immediately on missing required fields
- Fail on duplicate entities (422 errors)
- Fail on any unexpected errors (no silent fallbacks)

### Next Steps

1. ✅ Test import on clean database
2. ⚠️ Document data quality requirements for operators
3. ⚠️ Add pre-flight validation script to check legacy data before import
4. ⚠️ Update README with new error handling behavior
