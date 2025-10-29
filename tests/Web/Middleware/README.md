# Web Middleware Tests

## Purpose

These tests verify that **ALL** Web routes are properly protected by authentication and authorization middleware. They complement the page-specific tests by ensuring security is enforced at the middleware layer.

## Test Files

### AuthenticationTest.php
**Verifies:** Every Web route redirects unauthenticated users to login

**Test Structure:**
```php
/**
 * @dataProvider protectedRoutesProvider
 */
public function test_route_requires_authentication(string $method, string $route, ?string $model = null): void
{
    // Attempt request without session authentication
    $response = $this->{strtolower($method)}(route($route, $this->getRouteParams($model)));
    
    // Should redirect to login
    $response->assertRedirect(route('login')); // 302
}
```

**Data Provider:**
- Lists ALL Web routes systematically
- Grouped by HTTP method (GET index/show/create/edit, POST store, PUT update, DELETE destroy)
- Each route includes model class if it requires a resource parameter
- Excludes public routes (login, register, password reset)

### PermissionsTest.php
**Verifies:** Every Web route enforces the correct permission

**Test Structure:**
```php
/**
 * @dataProvider routePermissionsProvider
 */
public function test_route_enforces_correct_permission(
    string $method,
    string $route,
    string $permission,
    ?string $model = null
): void
{
    // Create user WITHOUT the required permission
    $user = User::factory()->create();
    // Explicitly NOT giving the required permission
    
    $this->actingAs($user);
    
    $response = $this->{strtolower($method)}(route($route, $this->getRouteParams($model)));
    
    // Should be forbidden
    $response->assertForbidden(); // 403
    
    // Now give the permission
    $user->givePermissionTo($permission);
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    
    $response = $this->{strtolower($method)}(route($route, $this->getRouteParams($model)));
    
    // Should now be allowed (may still fail validation, but not 403)
    $response->assertStatus(fn($status) => $status !== 403);
}
```

**Data Provider:**
- Maps each route to its required permission
- index/show routes → VIEW_DATA
- create/store routes → CREATE_DATA
- edit/update routes → UPDATE_DATA
- destroy routes → DELETE_DATA
- Admin routes → MANAGE_USERS or MANAGE_SETTINGS

## Key Differences from Page Tests

| Aspect | Page Tests (Web/Pages/) | Middleware Tests (Web/Middleware/) |
|--------|------------------------|-----------------------------------|
| **Focus** | Page rendering, form submission, redirects | Security enforcement |
| **Auth** | Authenticated user with ALL permissions | Various permission combinations |
| **Scope** | One resource at a time | ALL routes systematically |
| **Assertions** | View names, data presence, redirects | HTTP status codes (302, 403) |
| **Coverage** | Deep testing of one page | Broad testing of all pages |

## Maintenance

**When adding a new Web route:**
1. Add route to `AuthenticationTest::protectedRoutesProvider()`
2. Add route + permission to `PermissionsTest::routePermissionsProvider()`
3. Run middleware tests to verify security is enforced
4. Create page-specific tests for functionality

**Benefits:**
- Single point of truth for route security
- Easy to spot missing middleware
- Catches copy-paste errors in controller middleware
- Documents permission requirements
- Ensures consistent behavior between API and Web (same permissions)
