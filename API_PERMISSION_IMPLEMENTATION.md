# API Permission Control Implementation Summary

## What Was Implemented

### 1. API Route Permission Control (`routes/api.php`)
- **Added Permission enum import** for centralized permission management
- **Organized routes by permission requirements**:
  - `VIEW_DATA` permission: All GET/read operations (context, language, project, country, tag, partner, item, collection, etc.)
  - `CREATE_DATA` permission: All POST/create operations
  - `UPDATE_DATA` permission: All PATCH/PUT/update operations
  - `DELETE_DATA` permission: All DELETE operations
- **Public endpoints maintained**:
  - Info endpoints (`/info`, `/health`, `/version`) - no auth required
  - Mobile authentication endpoints - public for login flow
  - Markdown utility endpoints - stateless utility functions

### 2. Comprehensive Test Suite (`tests/Feature/Api/ApiPermissionControlTest.php`)
- **12 test cases covering**:
  - GET requests require VIEW_DATA permission
  - POST requests require CREATE_DATA permission
  - PATCH/PUT requests require UPDATE_DATA permission
  - DELETE requests require DELETE_DATA permission
  - Proper JSON 403 responses (not HTML redirects)
  - Unauthenticated requests return 401
  - Users with all permissions can perform all operations
  - Special routes (setDefault, setLaunched) require appropriate permissions
  - Markdown routes don't require authentication
  - Public info endpoints remain public
  - Mobile auth routes remain public

### 3. Test Infrastructure Updates (`tests/TestCase.php`)
- **Auto-permission granting**: Override `actingAs()` to automatically grant all data operation permissions to authenticated test users
  - Maintains backward compatibility with existing tests
  - Prevents breaking 1500+ existing test cases
  - Can be bypassed in specific tests (like ApiPermissionControlTest) using direct Sanctum authentication
- **Permission existence**: `ensurePermissionsExist()` method creates all application permissions before each test
  - Handles race conditions in parallel test execution
  - Catches `PermissionAlreadyExists` exceptions gracefully

### 4. Test Fixes
- Updated `UserPermissionsTest.php` to not recreate existing permissions
- Updated `UserPermissionTest.php` to use `findByName()` instead of `create()`
- Updated `AuthorizationMiddlewareTest.php` to use existing permissions

## Test Results
- **Before**: 619 failures (no permission checks on API)
- **After**: 28 failures (minor test setup issues in legacy tests)
- **Passing**: 1,569 tests (96% pass rate)
- **Total Assertions**: 6,224

## Security Improvements
1. **Enforced authorization**: Every data operation now requires appropriate permissions
2. **Consistent with web interface**: API uses same permission model as web routes
3. **Proper API responses**: Returns JSON 403/401 instead of HTML redirects
4. **Granular control**: Separate permissions for read, create, update, and delete operations

## Remaining Work

### Tests to Fix (28 failures)
Most remaining failures are in these test files that manually create permissions:
1. `SelfRegistrationToggleTest` (3 failures)
2. `NonVerifiedUserAuthorizationTest` (6 failures)
3. `PermissionSystemTest` (needs review - uses custom test permissions)

**Fix pattern**: Replace `Permission::create()` with `Permission::findByName()` in setUp methods, similar to what was done for UserPermissionTest and AuthorizationMiddlewareTest.

### Documentation Updates Needed
1. Update `docs/api-documentation.md` to document permission requirements for each endpoint
2. Update `CHANGELOG.md` with this major security enhancement
3. Update API client documentation to inform clients about permission requirements

### Migration Considerations
1. **Existing API clients** may need permission updates if they only had authentication but not specific data permissions
2. **Recommended approach**: Assign "Regular User" role (which has all data permissions) to API token users
3. **Breaking change**: Consider this a security fix that should be deployed carefully

## API Permission Matrix

| HTTP Method | Permission Required | Endpoints |
|-------------|-------------------|-----------|
| GET | `view data` | All read operations (index, show, etc.) |
| POST | `create data` | All create operations (store, attach, etc.) |
| PATCH/PUT | `update data` | All update operations (update, setDefault, etc.) |
| DELETE | `delete data` | All delete operations (destroy, detach, etc.) |
| N/A | None (public) | `/info/*`, `/health`, `/version`, `/mobile/*`, `/markdown/*` |

## Verification Steps

### To verify API permission enforcement:
```powershell
# 1. Create a user without permissions
php artisan tinker
$user = User::factory()->create();
$token = $user->createToken('test')->plainTextToken;

# 2. Try to access API (should fail with 403)
curl -H "Authorization: Bearer $token" http://localhost/api/item

# 3. Grant permission and try again (should succeed)
$user->givePermissionTo('view data');
curl -H "Authorization: Bearer $token" http://localhost/api/item
```

### To run only permission tests:
```powershell
php artisan test --filter=ApiPermissionControlTest
php artisan test --filter=PermissionAuthorizationTest
php artisan test --filter=AuthorizationMiddlewareTest
```

## Notes
- Markdown endpoints remain public as they're stateless utility functions
- Mobile auth endpoints remain public to allow login flow
- The test infrastructure changes are designed to be minimally invasive while adding security
- All lint checks pass (Pint)
- All TypeScript type checks pass

## Next Steps
1. Fix remaining 28 test failures (estimate: 30 minutes)
2. Run full test suite to ensure 100% pass rate
3. Update CHANGELOG.md
4. Create PR with detailed description
5. Consider semantic version bump (this is a security enhancement, suggest minor version bump)
