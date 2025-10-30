# New Test Structure

This directory contains the reorganized test suite following Laravel best practices for applications with separate API and Web frontends.

## Directory Structure

```
tests/
├── Api/                              # REST API Backend Tests
│   ├── Resources/                    # Resource/endpoint tests (one file per resource)
│   │   ├── ItemTest.php             # All Item API tests (index, show, store, update, destroy, tags, scopes)
│   │   ├── PartnerTest.php          # All Partner API tests
│   │   └── ...                      # One test file per API resource
│   ├── Traits/                       # Reusable test traits
│   │   ├── AuthenticatesApiRequests.php  # API authentication setup
│   │   ├── TestsApiCrud.php         # Standard CRUD operations
│   │   ├── TestsApiImageResource.php # Image resource operations
│   │   ├── TestsApiImageViewing.php # Image download/view operations
│   │   └── TestsApiTagManagement.php # Tag management operations
│   └── Middleware/                   # API middleware & security tests
│       ├── AuthenticationTest.php   # Sanctum token validation, unauthenticated rejection
│       └── PermissionsTest.php      # Permission-based authorization (VIEW_DATA, CREATE_DATA, etc.)
│
├── Web/                              # Blade/Livewire Frontend Tests
│   ├── Pages/                        # Page rendering & form submission tests
│   │   ├── ItemTest.php             # All Item web pages (index, show, create, edit, store, update, destroy)
│   │   ├── PartnerTest.php          # All Partner web pages
│   │   └── ...                      # One test file per web resource
│   ├── Components/                   # Livewire component tests
│   │   ├── ItemsTableTest.php       # Livewire Items table component
│   │   ├── PartnersTableTest.php    # Livewire Partners table component
│   │   └── ...                      # One test file per Livewire component
│   ├── Traits/                       # Reusable test traits
│   │   ├── AuthenticatesWebRequests.php  # Web authentication setup
│   │   ├── TestsWebCrud.php         # Standard CRUD operations
│   │   └── TestsWebLivewire.php     # Livewire component testing
│   ├── Middleware/                   # Web middleware & security tests
│   │   ├── AuthenticationTest.php   # Session-based auth, guest redirection
│   │   └── PermissionsTest.php      # Permission-based authorization (same permissions as API)
│   ├── Auth/                         # Web authentication flows
│   │   ├── LoginTest.php            # Login, logout, session management
│   │   ├── RegistrationTest.php     # User registration
│   │   ├── TwoFactorTest.php        # 2FA challenges, recovery codes
│   │   ├── PasswordResetTest.php    # Password reset flows
│   │   └── ProfileTest.php          # Profile management
│   └── Admin/                        # Admin interface tests
│       ├── UserManagementTest.php   # User CRUD, role assignment
│       └── RoleManagementTest.php   # Role & permission management
│
├── Unit/                             # Pure unit tests (business logic)
│   ├── Models/                       # Model method tests
│   │   ├── ItemTest.php             # Item model methods, relationships
│   │   ├── ItemScopesTest.php       # Item query scopes
│   │   └── ...                      # One test file per model
│   ├── Services/                     # Service class tests
│   │   └── MarkdownServiceTest.php  # Service business logic
│   ├── Jobs/                         # Queue job tests
│   │   └── SyncSpellingsTest.php    # Job logic (mocked dependencies)
│   ├── Requests/                     # FormRequest validation tests
│   │   ├── StoreItemRequestTest.php # Validation rules only
│   │   └── ...                      # One test file per FormRequest
│   └── Factories/                    # Factory state tests
│       └── FactoriesTest.php         # Factory states (Object, Monument, etc.)
│
├── Integration/                      # Cross-cutting integration tests
│   └── GlossarySyncTest.php         # Complex multi-step workflows
│
└── Console/                          # Artisan command tests
    └── CommandsTest.php             # All console commands
```

## Organization Principles

### 1. Domain Over Technical
- Tests organized by business domain (Items, Partners, etc.)
- NOT by HTTP method (Index, Store, Update, etc.)
- One file contains ALL tests for a resource's feature area

### 2. Clear API/Web Separation
- `Api/` - REST API backend (JSON responses, authentication tokens)
- `Web/` - Blade/Livewire frontend (HTML, sessions, redirects)
- Never mixed - these are independent applications

### 3. Single Responsibility Per File
- Each test file has ONE feature responsibility
- BUT contains ALL tests for that feature
- Each test method still tests ONE thing

### 4. Middleware Tests - Centralized Security Validation
- **Api/Middleware/** and **Web/Middleware/** contain cross-cutting security tests
- Tests ALL routes systematically to ensure proper protection
- **AuthenticationTest.php** - Verifies ALL routes reject unauthenticated requests
- **PermissionsTest.php** - Verifies ALL routes enforce correct permissions (VIEW_DATA, CREATE_DATA, UPDATE_DATA, DELETE_DATA)
- **Why separate from Resources/Pages?** - Resource tests assume authenticated user with proper permissions; Middleware tests verify that assumption is enforced
- **Benefits:**
  - One place to verify security across ALL endpoints
  - Easy to add new routes to security checks
  - Catches missing middleware configurations
  - Complements trait-based tests that use super-users

### 5. Test What You Built, Not The Framework
- No tests for basic Laravel validation
- Focus on business logic, custom rules, workflows
- FormRequest validation tested in Unit/Requests/

## File Naming Conventions

- **Resource tests**: Plural noun + "Test" (e.g., `ItemsTest.php`, `PartnersTest.php`)
- **Feature tests**: Feature name + "Test" (e.g., `LoginTest.php`, `ImageUploadWorkflowTest.php`)
- **Unit tests**: Class name + "Test" (e.g., `ItemTest.php` for Item model, `ItemScopesTest.php` for scopes)

## Documentation and guidelines

- Every directory includes a README.md with specifics details about itself.


## Migration Status

This is a work-in-progress migration from the old `tests/` structure. Each test is being reviewed and moved to its appropriate location in this new structure.

Tests that don't fit the new organization (low-value framework tests) are marked clearly for review/deletion.
