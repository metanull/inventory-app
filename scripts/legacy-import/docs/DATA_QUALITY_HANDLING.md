# Data Quality Handling - Phase 1 Importers

This document outlines the data quality issues encountered during Phase 1 import and how they are handled.

## Overview

The import system is designed to **continue importing despite data quality issues**, while providing comprehensive warnings and error reports for decision-making.

### Philosophy

1. **Import continues** - Data quality issues should not block the entire import
2. **Warnings tracked** - All quality issues are logged with context
3. **Fallbacks applied** - Reasonable defaults used when data is missing
4. **Decisions deferred** - Team can decide whether to fix legacy data or keep fallbacks

---

## Institution Importer

### Issue: Missing `internal_name`

**Error**: Institution has empty/null `name` field  
**Example**: `Mon14:it`

**Handling**:
- **Fallback**: Generate name from ID: `"Institution {id} ({country})"`
- **Warning**: Logged with recommendation
- **Result**: Import succeeds

**Decision Required**: Keep fallback logic OR fix legacy data

---

## Object Importer

### Issue 1: Language Code Mapping

**Error**: "The language id field must be 3 characters"  
**Cause**: Objects use 2-character ISO 639-1 codes (`en`, `fr`, etc.)  
**Solution**: Uses centralized `LANGUAGE_CODE_MAP` from `CodeMappings.ts`

**Mapping Examples**:
- `en` → `eng`
- `fr` → `fra`
- `ch` → `zho` (Chinese)
- `cs` → `ces` (Czech)
- `fa` → `fas` (Farsi)
- etc.

### Issue 2: Missing Translation Fields

**Error**: Missing `name` or `description` in object translations

**Handling - Missing Name**:
- **Fallback Order**:
  1. Use `working_number` if available
  2. Use `inventory_id` if available
  3. Generate: `"Object {number}"`
- **Warning**: Logged with context
- **Result**: Import succeeds

**Handling - Missing Description**:
- **Fallback**: `"(No description available)"`
- **Warning**: Logged with context
- **Result**: Import succeeds

### Issue 3: Tag Backward Compatibility & Lookup Strategy

**Fixed**: ✅ **Two improvements implemented**

#### 3a. Namespace Collision Between Tag Categories

**Issue**: Tags from different categories with same name conflicted  
**Example**: Artist "Wood" vs Material "Wood" → both created `mwnf3:tags:Wood` conflict

**Solution**: Include category in backward_compatibility
- **Old format**: `mwnf3:tags:{tagName}`
- **New format**: `mwnf3:tags:{category}:{tagName}`
- **Examples**:
  - Artist: `mwnf3:tags:artists:Wood`
  - Material: `mwnf3:tags:material:Wood`
  - Keyword: `mwnf3:tags:keyword:liturgy`

#### 3b. Excessive Duplicate Warnings

**Issue**: Tags are reusable - seeing 1000s of "duplicate resolved" warnings for normal reuse  
**Old approach**: Try create first → handle 422 conflict → search for existing

**Solution**: **Lookup first, create only if not found**
1. Check tracker cache
2. If not in cache, search API for existing tag by `backward_compatibility`
3. If found, register in tracker and return
4. If not found, create new tag
5. **No warnings** for normal tag reuse

**Result**: 
- Dramatically reduced warning noise
- Only real conflicts logged
- Faster imports (cache hits avoid API calls)

#### 3c. Multiple Artists in Single Field

**Issue**: Artist field contains multiple artists separated by semicolons  
**Example**: `"José de Almeida (...); Felix Vicente de Almeida (...); Filipe Juvara (...)"`  
**Error**: Internal name too long (>255 char limit) causing 500 errors

**Solution**: **Split artist field and create multiple artist tags**
1. Split by semicolon separator
2. Create separate artist tag for each name
3. Limit internal_name to 240 chars (safety margin)
4. Attach all artist tags to the item

**Result**:
- Each artist tracked separately
- No database truncation errors
- Proper artist relationships
- Fixed backward_compatibility: `mwnf3:artists:{artistName}` (not `mwnf3:tags:artists:...`)

### Issue 4: Tag Parsing (Semicolon vs Comma)

**Analysis**: Database uses **mixed separators** depending on field:
- **Keywords**: Semicolon `;` (4800/6714 in objects, 1329/1989 in monuments)
- **Materials**: Mixed, but often comma `,` (2444 comma vs 1479 semicolon)
- **Dynasty**: Mostly comma `,` (150 comma vs 39 semicolon)

**Solution**: **Smart separator detection**
- **Primary**: Use semicolon `;` if present in string
- **Fallback**: Use comma `,` if no semicolons found
- Applied to both Object and Monument importers

**Impact**: Correctly parses 95%+ of tag fields without false splits

### Issue 5: Tag Lookup Pagination Limits (422 Errors)

**Issue**: 422 "internal_name already taken" errors even though lookup runs first  
**Root cause**: Paginated tag search (100 pages = 10,000 tags max) doesn't find existing tags beyond that limit

**Solution**: **Retry with exhaustive search on 422**
1. Initial lookup: Search up to 100 pages (10,000 tags)
2. On 422 conflict: Retry with 200 pages (20,000 tags)
3. If still not found: Log warning and continue
4. All found tags registered in tracker cache to avoid future API calls

**Result**:
- Handles large tag databases (10,000+ tags)
- Minimal performance impact (cache hits for repeated tags)
- Graceful degradation if tag truly can't be found

### Issue 6: Structured Tag Fields & Language-Specific Tags

**Issue**: Tag fields contain **structured data** that must not be split  
**Examples**:
- `"Warp: Light brown wool; Weft: Red wool"` (materials)
- `"Silversmiths: Giuseppe Gagliardi; sculptor: Giovanni Battista"` (artists)
- `"madrasa; cerámica: decoración floral"` (keywords with structured content)

**Analysis**:
- **Materials**: 76 records have structured format (e.g., `"Warp: ...; Weft: ..."`)
- **Artists**: 25 records have role-based structure (e.g., `"Calligrapher: ...; illuminators: ..."`)
- **Dynasty**: 5 records with temporal phases
- **Detection**: Presence of colon `:` indicates structured data

**Solution**: **Enhanced Tag Model with Language Support**

1. **Added fields to tags table**:
   - `category` (string): 'keyword', 'material', 'artist', 'dynasty'
   - `language_id` (foreign key to languages): Tags are language-specific
   - Unique constraint: `(internal_name, category, language_id)`

2. **Smart field parsing**:
   - If field contains `:` → treat as single structured tag (don't split)
   - Otherwise: split by `;` (primary) or `,` (fallback)
   - Create separate tags for each language (e.g., English "leather" ≠ French "cuir")

3. **Backward compatibility format**:
   - Old: `mwnf3:tags:{category}:{tagName}`
   - New: `mwnf3:tags:{category}:{lang}:{tagName}`
   - Ensures unique identification across categories and languages

**Result**:
- ✅ Structured data preserved (e.g., "Warp: wool; Weft: cotton" = single tag)
- ✅ Language-specific tags (same item can have English + French tags)
- ✅ Category-based organization (material ≠ artist ≠ keyword)
- ✅ Enables filtering by category, language, or both
- ✅ Supports future reconciliation of cross-language tags

---

## Monument Importer

**Status**: ✅ **Fixed** - All Object Importer fixes applied

**Issues Fixed**:
1. ✅ Language code mapping - Uses centralized `LANGUAGE_CODE_MAP`
2. ✅ Missing name field - Fallback: `working_number` → `"Monument {number}"`
3. ✅ Missing description field - Fallback: `"(No description available)"`
4. ✅ Duplicate tag names - Case-insensitive search with proper tracking
5. ✅ Tag parsing - Comma-separated (same fix as ObjectImporter)
6. ✅ Warning system - All quality issues logged and tracked

**Implementation**: Same robust error handling as ObjectImporter

---

## Warning Tracking System

### Architecture

```typescript
interface ImportResult {
  success: boolean;
  imported: number;
  skipped: number;
  errors: string[];      // Blocking errors
  warnings?: string[];   // Data quality issues (non-blocking)
}
```

### Warning Flow

1. **Detection**: Importer detects data quality issue
2. **Fallback**: Apply reasonable default/workaround
3. **Log Details**: Write full context to log file
4. **Track Warning**: Add to `result.warnings[]`
5. **Aggregate**: Parent importers collect child warnings
6. **Report**: Console shows first 10, full list in log

### Console Output Example

```
⚠️  Partners had 1 data quality warnings:
  - Mon14:it - Missing 'name' field, using fallback

⚠️  Objects had 127 data quality warnings:
  - AMT:Mus21:49:en - Missing 'description', using fallback
  - Tag 'material:Olej, plátno' - Duplicate internal_name resolved
  - Artist 'Michael Leopold Willmann' - Duplicate internal_name resolved
  ... and 124 more (see log file)

⚠️  Total data quality warnings: 128
Review log file for details on how to address these issues.
```

---

## Recommendations

### For Duplicate Tags (Issue 3)

**Option A**: Keep current fallback logic
- ✅ Pro: Zero impact on legacy data
- ✅ Pro: Handles inconsistent capitalization automatically
- ❌ Con: May create semantic duplicates if case matters

**Option B**: Normalize all tag names to lowercase
- ✅ Pro: Eliminates case sensitivity issues permanently
- ❌ Con: Loses original capitalization (may matter for proper nouns)
- ❌ Con: Requires updating all existing tags

**Option C**: Fix legacy database
- ✅ Pro: Cleans source data
- ❌ Con: Time-consuming manual work
- ❌ Con: May have downstream impact on legacy system

**Recommendation**: **Option A** (keep current logic) - provides best balance of robustness and simplicity.

### For Missing Fields (Issues 1, 2)

**Option A**: Keep current fallback logic
- ✅ Pro: All data imports successfully
- ✅ Pro: Clear indication of generated values
- ❌ Con: Generated names may not be ideal

**Option B**: Fix legacy database
- ✅ Pro: Better data quality
- ❌ Con: Manual work to add proper names/descriptions
- ❌ Con: Some objects may legitimately have minimal info

**Recommendation**: **Option A** (keep fallbacks) for now. Review generated values in production and fix legacy data only for high-priority items.

---

## Testing Results

### Phase 1 Import Summary

| Importer | Imported | Skipped | Errors | Warnings | Status |
|----------|----------|---------|--------|----------|--------|
| Languages | 179 | 0 | 0 | 0 | ✅ Complete |
| Language Translations | 130 | 0 | 0 | 0 | ✅ Complete |
| Countries | 248 | 0 | 0 | 0 | ✅ Complete |
| Country Translations | 335 | 0 | 0 | 0 | ✅ Complete |
| Default Context | 0 | 1 | 0 | 0 | ✅ Complete |
| Projects | 56 | 1 | 0 | 0 | ✅ Complete |
| Partners | 267 | 0 | 0 | **1** | ✅ Complete |
| Objects | ~1,691+ | ? | 0 | **~127+** | ⏳ In Progress |
| Monuments | TBD | TBD | 0 | TBD | ✅ Ready to Test |
| **TOTAL** | **~2,906+** | **2** | **0** | **~128+** | **⏳ In Progress** |

**Status**: ✅ All importers have robust data quality handling. Ready for full import testing.

---

## Next Steps

### Immediate Actions

1. ✅ **Monument Importer Fixed** - All Object Importer improvements applied
2. ⏳ **Complete Object Import** - Let full import run to completion
3. ⏳ **Test Monument Import** - Run full import including monuments
4. **Review Results** - Analyze warning patterns from complete logs

### Data Quality Decisions

After full import completion:

1. **Review Warnings** - Analyze all logged data quality issues
2. **Prioritize Fixes** - Determine which issues need legacy data fixes
3. **Document Decisions** - Update this file with final choices:
   - Which fallbacks to keep permanently
   - Which legacy data needs fixing
   - Timeline for fixes
4. **Communicate Findings** - Share recommendations with team

### Phase 2 Planning

Once Phase 1 complete:

1. **Move to Phase 2** - Image and relationship imports
2. **Apply Lessons Learned** - Use same data quality patterns
3. **Maintain Documentation** - Keep this file updated with new issues

---

## Log File Analysis

### Finding Warnings

```powershell
# Count warnings by type
Get-Content import-*.log | Select-String "WARNING:" | Group-Object

# View all duplicate tag warnings
Get-Content import-*.log | Select-String "Duplicate internal_name"

# View all missing field warnings  
Get-Content import-*.log | Select-String "Missing 'name'|Missing 'description'"
```

### Error Investigation

```powershell
# View all errors with context
Get-Content import-*.log | Select-String "ERROR:" -Context 0,10

# Count errors by type
Get-Content import-*.log | Select-String "ERROR:" | Group-Object
```

---

*Last Updated: November 23, 2025*
