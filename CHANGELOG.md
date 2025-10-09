# Changelog

All notable changes to the Inventory Management API project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- **Backend (Administration)** - Role Management system
    - **RoleManagementController**: Full CRUD operations for roles via web interface
    - **Role Views**: Complete set of Blade views for managing roles (index, create, edit, show, permissions)
    - **Permission Management**: Ability to attach/detach/sync permissions to roles
    - **Navigation**: Added Role Management link in admin navigation for authorized users
    - **Authorization**: Role management requires `manage roles` permission
    - **User Safety**: Prevents deletion of roles that have users assigned
    - **Web Routes**: Added routes under `admin.roles` namespace
    - **Tests**: Comprehensive web interface tests for role management (14 test cases)
    - Note: Role management is web-only (not exposed via API) for security and simplicity

### Changed

- **User Management**: Now uses dynamic role list from database instead of hardcoded roles
- **Home Page**: Added Role Management card for users with appropriate permissions

### Fixed

- **Permission System**: Added missing `MANAGE_SETTINGS` permission to seeders
    - Added `MANAGE_SETTINGS` permission creation in `RolePermissionSeeder` and `ProductionDataSeeder`
    - Updated "Manager of Users" role to include `MANAGE_SETTINGS` permission
    - Permission is now visible in role management interface and can be assigned to roles
    - All 10 permissions from the `Permission` enum are now properly seeded and available

## [5.4.1] - 2025-10-08

### Changed

- Dependencies - Updated composer and npm dependencies to latest versions
    - **Laravel Framework** 12.31.1 → 12.33.0
    - **Laravel Fortify** 1.30.0 → 1.31.1
    - **Symfony components** 7.3.3 → 7.3.4 (console, error-handler, http-foundation, http-kernel, mailer, mime, routing, string, translation, var-dumper)
    - **Removed phiki/phiki** v2.0.4 (dependency cleanup)
    - **NPM packages** - Various updates (56 packages added, 40 changed)

### Security

- All dependencies audited with no vulnerabilities found

### Changed

- **Backend (Architecture)** - Major model simplification and unification initiative
    - **Item Model Enhancement**: Added `type` field (object/monument) and hierarchical `parent_id` support for complex object relationships
    - **Collection Model Unification**: Added `type` field (collection/exhibition/gallery) to replace separate Gallery and Exhibition models
    - **Removed Legacy Models**: Eliminated Picture, Detail, Gallery, Exhibition models (replaced by ItemImage and unified Collection)
    - **ItemImage System**: New direct item-to-image relationship model with display ordering and management capabilities
    - **Theme Model Update**: Replaced Exhibition relationship with optional Collection relationship
    - **API Simplification**: Streamlined endpoints with consistent patterns, eliminated polymorphic relationships
    - **Database Optimization**: Reduced model count from 37+ to 28 focused models with clearer relationships
    - **Frontend Cleanup**: Removed obsolete Vue.js Detail components and updated API client integration

- Dependencies - Updated development and production dependencies to latest versions
    - **Laravel Framework** 12.29.0 → 12.31.1 (includes performance improvements and bug fixes)
    - **Laravel Pint** 1.24.0 → 1.25.1 (code formatting improvements)
    - **OpenAPI Generator CLI** 2.23.3 → 2.23.4 (bug fixes)
    - **@types/node** 24.5.0 → 24.5.2 (TypeScript definitions updates)
    - **ESLint** 9.35.0 → 9.36.0 (new linting rules and fixes)
    - **eslint-plugin-vue** 10.4.0 → 10.5.0 (Vue.js linting improvements)
    - **Vite** 7.1.5 → 7.1.7 (build tool bug fixes and performance improvements)
    - **Removed @types/uuid** (deprecated package - uuid now provides its own types)

### Added

- **Backend (Models)** - New ItemImage system for enhanced image management
    - **ItemImage Model**: Direct item-to-image relationships with display ordering (replaces polymorphic Picture attachments)
    - **Image Ordering**: Display order management with reordering capabilities (moveUp, moveDown, moveToPosition methods)
    - **Attach/Detach Operations**: Simplified image attachment workflows with automatic ordering
    - **ItemImageController**: RESTful API endpoints for image management (/api/items/{item}/images/*)
    - **ItemImageFactory & Seeder**: Comprehensive testing support with realistic data generation

- **Backend (Database)** - Schema migrations for model simplification
    - **Migration**: Add type and hierarchical support to items table
    - **Migration**: Add type field to collections table for unified collection types
    - **Migration**: Create item_images table with display ordering
    - **Migration**: Create collection_item pivot table for many-to-many relationships
    - **Migration**: Remove legacy picture, detail, gallery, exhibition tables and related data

- **Backend (API)** - Enhanced controllers and resources for simplified models
    - **ItemController**: Enhanced with type filtering, hierarchical queries, and image management
    - **CollectionController**: Unified handling of collections, exhibitions, and galleries via type field
    - **Updated Resources**: ItemResource and CollectionResource with new fields and relationships
    - **Form Requests**: Comprehensive validation for hierarchical item structures and collection types
    - **OpenAPI Specification**: Updated documentation reflecting simplified model structure

- Backend (Testing) - Complete web test standardization and consistency improvements
    - Created comprehensive Languages web test suite with 7 CRUD tests (Create, Destroy, Edit, Index, Show, Store, Update)
    - Added missing Countries web tests (CreateTest.php, EditTest.php) to complete entity coverage
    - Implemented 5 missing Livewire table component tests (Collections, Contexts, Countries, Languages, Projects)
    - Added 4 missing Parity tests (Items, Partners, Countries, Languages) for API-to-Web consistency validation
    - Created 5 missing PaginationTest files (Collections, Contexts, Projects, Countries, Languages)
    - Standardized test patterns across all 7 entities with consistent structure and validation approaches
    - Removed duplicate test directories (Items/, Partners/) while preserving comprehensive test suites

- Backend (API) - Language controller pagination improvements
    - Fixed LanguageController.index() to use proper pagination with PaginationParams instead of Language::all()
    - Resolved API response format inconsistencies for meta.total field in paginated responses

- Backend (API) - AvailableImage controller pagination improvements
    - Fixed AvailableImageController.index() to use proper pagination with PaginationParams instead of AvailableImage::all()
    - Added 'available_image' entry to AllowList configuration to support IncludeParser functionality
    - Resolved pagination issues preventing proper frontend listing of available images

- Backend (Blade) - Complete Laravel web UI standardization with reusable component architecture
    - Created comprehensive Blade component library with 20+ specialized components
    - Form components: field, input, select, actions, checkbox variants, date, context-select, language-select
    - Display components: description-list, field, timestamp, badge, boolean, date, reference components
    - Layout components: show-page, form-page with consistent spacing and styling
    - Standardized all 7 entities (Items, Partners, Countries, Languages, Projects, Contexts, Collections)
    - Advanced field type support: date inputs, boolean checkboxes, relationship selectors
    - Entity-specific theming preservation while maintaining structural consistency

- Backend (Blade) - Parity and tests for new entities
    - Smoke tests for Projects, Contexts, and Collections web CRUD pages
    - Parity tests comparing API index totals with Blade table counts (same seed and perPage)

- Backend (Blade) - Enhanced interactivity and user experience (Phase 3)
    - Modal confirmation system replacing browser confirm() dialogs with modern UI
        - Backdrop click to close, Escape key support, smooth animations
        - Entity-specific messaging and consistent styling across all delete operations
    - Sortable table headers with dynamic sorting capabilities
        - Visual chevron indicators showing sort direction and hover states
        - Backend Livewire integration with query string persistence
        - Consistent sorting fields (internal_name, created_at) across all table components
    - Loading states and user feedback components
        - Configurable loading spinners with size variants (sm/md/lg) and color options
        - Form submission loading indicators with disabled states and "Saving..." text
        - Loading overlay components for long operations
    - Mobile optimization improvements
        - Touch-friendly button sizing with 44px minimum touch targets
        - Mobile-responsive modal dialogs with stacked button layouts
        - Enhanced search inputs with larger touch areas and clear buttons
        - Mobile-optimized pagination with simplified navigation controls

### Changed

- Backend (Blade) - UI consistency and component architecture
    - Converted all entity forms to use standardized component patterns (70-80% code reduction)
    - Unified show pages with consistent layout, spacing, and information display
    - Standardized create/edit pages to use form-page layout component
    - Replaced duplicate field HTML with reusable x-form.field components
    - Implemented alternating gray/white field backgrounds across all entities
    - Consistent error handling and validation display patterns
    - Standardized edit page headers to "Edit {Entity}" for Projects, Contexts, and Collections
    - Show pages now include a visible "Legacy: …" badge and an "Information" section header to match Items/Partners patterns

- Backend (Blade) - Enhanced table functionality
    - All 7 entity tables now support dynamic sorting with visual feedback
    - Modal confirmations replace browser dialogs on all delete operations
    - Improved user experience with loading indicators during form submissions

### Removed

- **Backend (Models)** - Legacy model removal as part of simplification initiative
    - **Picture Model**: Removed polymorphic picture attachments (replaced by ItemImage direct relationships)
    - **Detail Model**: Eliminated detail model (functionality integrated into Item model)
    - **Gallery Model**: Removed separate gallery model (unified into Collection with type='gallery')
    - **Exhibition Model**: Removed separate exhibition model (unified into Collection with type='exhibition')
    - **Translation Models**: Removed PictureTranslation, DetailTranslation, GalleryTranslation, ExhibitionTranslation
    - **Pivot Models**: Removed Galleryable, GalleryPartner models (simplified relationships)

- **Frontend (Vue.js)** - Obsolete component cleanup
    - **Detail Components**: Removed DetailDetail.vue, DetailList.vue, DetailDialog.vue views and components
    - **Detail Store**: Removed detail.ts Pinia store and related state management
    - **Detail Routes**: Removed /items/:itemId/details/* router entries
    - **Detail Tests**: Removed DetailStorePagination.test.ts and DetailDetail integration tests
    - **Test Utilities**: Removed Picture and Detail mock functions from test-utils.ts

- **Backend (Controllers & Routes)** - Obsolete API endpoints removal
    - **PictureController**: Removed entire controller and related /api/pictures/* endpoints
    - **DetailController**: Removed entire controller and related /api/details/* endpoints
    - **GalleryController**: Removed (functionality moved to CollectionController)
    - **ExhibitionController**: Removed (functionality moved to CollectionController)

- **Documentation** - Outdated model documentation cleanup
    - **Model Documentation**: Removed docs/models files for Picture, Detail, Gallery, Exhibition models
    - **API Documentation**: Updated OpenAPI specification to remove obsolete endpoints

### Fixed

- Backend (Testing) - Web test consistency and validation fixes
    - Fixed Languages StoreTest backward_compatibility field validation (shortened 'LANG-LEG' to 'TL' to match 2-character migration constraint)
    - Corrected Languages and Countries CreateTest assertions to match actual blade form fields ('Code (3 letters)', 'Internal Name' instead of 'ISO Code', 'Name')
    - Fixed Livewire pagination tests to use proper `gotoPage()` method calls instead of setting `page` property directly
    - Resolved API pagination issues in LanguageController preventing proper meta.total responses

- Backend (Blade) - Phase 3 component integration and consistency
    - Fixed sortable-header component interface across all 7 table views (partners, collections, countries, languages, items, projects, contexts)
    - Resolved Blade template section duplication errors in contexts/show.blade.php and partners/show.blade.php
    - Standardized display component naming: `display.reference-context` → `display.context-reference`, `display.reference-language` → `display.language-reference`
    - Added backward-compatibility props to collections and projects show pages for proper "Legacy: XXX" badge display
    - These fixes resolved 36 failing tests and ensured all Phase 3 enhanced interactivity features work correctly

- Frontend (Image Upload) - Status monitoring for image processing workflow
    - **Root Cause**: Vue.js image upload view stayed in "Processing" status forever due to flawed monitoring logic
        - API calls failing silently returned `null`, causing monitor confusion
        - Monitor auto-stopped when no processing uploads found, wouldn't restart for new uploads
        - Incorrect status handling logic treated valid responses as errors
    - **Solution**: Comprehensive monitoring system improvements
        - Enhanced `checkUploadStatus()` with proper error handling returning `{ status: 'check_failed' }` instead of `null`
        - Implemented `ensureProcessingMonitor()` that automatically starts/stops based on upload state
        - Fixed status matching to properly detect `status === 'processed' && available_image` condition
        - Added comprehensive debug logging throughout monitoring process
    - **Result**: Upload status now correctly transitions from "Processing" → "Completed" when images are processed
    - **Technical Details**: Monitor lifecycle now persistent, handles API failures gracefully, distinguishes between temporary failures and processing errors
    
- API - Contexts index now returns a paginated resource with `meta.total` to align with parity tests
- Backend (Blade) - Eliminated code duplication across entity pages while maintaining functionality

- Backend (Blade) - Projects, Contexts, Collections
    - Full CRUD pages in Blade following Items/Partners patterns (controllers, FormRequests, Livewire tables, and views)
    - Navigation and Home dashboard tiles integrated for Projects, Contexts, and Collections
    - Centralized theming extended: added entity color mappings for `projects`, `contexts`, and `collections`
    - Web routes registered under `/web/*` with auth middleware

### Changed

- Frontend - Includes + Pagination rollout
    - Centralized include and pagination handling across Items, Partners, Countries, Languages, Contexts, Collections, and Projects
        - Stores now accept `{ page, perPage, include }` for index and `{ include }` for show methods and persist `page/perPage/total` from API meta
        - List views wired to shared `PaginationControls` via `ListView` slot; re-fetch triggers on page/perPage changes
        - Shared helpers in `resources/js/utils/apiQueryParams.ts` with exported `DEFAULT_PAGE` and `DEFAULT_PER_PAGE`
    - Request interceptor
        - Injects default `per_page` for GET list requests when missing; preserves explicit `per_page`
        - Safely initializes headers and avoids undefined Authorization warnings
    - Tests
        - Store-level tests covering minimal includes + pagination state for all entities above
        - Interceptor tests verifying default `per_page` injection behavior

### Fixed

- Authentication and redirect flow hardening across the frontend
### Fixed

- Backend (Blade) - Pagination component robustness and consistency
    - Fixed hidden input rendering for array query parameters in the shared pagination component
    - Defensive validation of `perPage` and `page` query params with safe reset when invalid
    - Ensured non-pagination query params are preserved across page/per-page changes

### Changed

- Backend (Blade) - Pagination options
    - Added `25` to `config('interface.pagination.per_page_options')` to align with existing tests and Livewire table behaviors

### Quality

- Linting passes (Laravel Pint, ESLint/Prettier) and full backend test suite green after pagination fixes

    - Centralized 401 handling with dependency-injected router/auth in `resources/js/utils/errorHandler.ts`; prevents circular imports and build warnings
    - Idempotent 401 redirect to named `login` route with preserved intended route via `redirectName` and encoded `redirectParams`; suppresses error toasts during auth redirects
    - Router guard updated to use named-route redirects and to allow reaching login when `redirectName` is present; otherwise authenticated users are redirected home
    - App shell now gates protected routes by `isAuthenticated` and shows a minimal “Redirecting to login…” placeholder while navigation occurs (`resources/js/App.vue`)
    - Languages view: static import of error utils and suppression of noisy toasts on auth redirects; eliminates dynamic import warnings
    - Modal overlay no longer intercepts clicks; content is layered above backdrop (`resources/views/components/modal.blade.php`)

### Changed

- CI/frontend and linting hardening
    - Node in CI pinned to 22.17.0 for consistency on Windows runner
    - ESLint: `@typescript-eslint/no-explicit-any` elevated to error; Prettier check-only used in CI composer scripts

### Removed

- Safe redirect helper and tests pruned (`resources/js/utils/safeRedirect.ts` and its tests) in favor of strict named-route redirects

### Changed

- **Frontend color & layout refactor**: Centralized color management and card-related refactors
    - Centralized Tailwind color fragments into `resources/js/composables/useColors.ts` and migrated components to use the `useColors` composable and theme helpers (`getThemeClass`, `useEntityColors`). This replaces multiple local color maps and scattered hard-coded Tailwind color fragments.
    - Refactored base `Card` components to expose a pinned `footer` slot for consistent action placement and updated `NavigationCard` to support both router-link and action-based primary controls (`button-route` or `button-action`).
    - Split `StatusCard` into a compact `StatusControl` (used in detail views and compact lists) and a non-compact `StatusCard` variant. Tests and usages were migrated to the new naming (`status-controls`).
    - Added a Clear Cache debug action to the Home/Tools area that reuses `clearCacheAndReload()` (now supports optional non-reload mode and keeps the loading overlay visible during hard reloads).
    - Updated frontend documentation: added `docs/frontend/theme-and-colors.md`, and updated component docs/examples to reference the centralized color system.

### Fixed

- **Mobile Authentication API Client**: Fixed base URL configuration issue causing 404 errors
  - **Root Cause**: Session-aware axios was setting `baseURL: window.location.origin` which interfered with API client's `basePath` configuration
  - **Impact**: Mobile authentication endpoint was resolving to `/mobile/acquire-token` instead of `/api/mobile/acquire-token`
  - **Solution**: Removed conflicting `baseURL` from session-aware axios to allow API client's `basePath` to work correctly
  - **Files Changed**: `resources/js/utils/sessionAwareAxios.ts`

### Added

- **Frontend Color System Streamlining**: Comprehensive centralized color management system
  - **New useColors Composable**: Created `resources/js/composables/useColors.ts` for centralized color management
    - `ENTITY_COLORS` mapping for consistent entity-specific color themes
    - `COLOR_MAP` with complete Tailwind color class definitions for all components
    - `useColors()` composable function for reactive color class generation
    - `ColorName` and `ColorClasses` TypeScript types for type safety
    - Support for buttons, badges, icons, focus states, hover effects, and borders
  - **Component Color Standardization**: Updated all layout and view components to use centralized system
    - `ListView.vue`, `DetailView.vue`, `AddButton.vue`, `FilterButton.vue`, `SearchControl.vue`
    - All entity views: Collections (indigo), Contexts (green), Countries (blue), Items (teal), Languages (purple), Partners (yellow), Projects (orange)
    - Eliminated 7+ local `colorMap` definitions and hardcoded Tailwind classes
    - Added consistent color prop patterns across all components
  - **Test Coverage**: Updated consistency tests to validate centralized color usage
    - Added checks for `useColors` import and proper ColorName type usage
    - Verified removal of local colorMap definitions
    - Ensured proper default color assignments per entity type

### Added

- **Frontend Collections Management**: Complete implementation of Collections and CollectionDetail frontend features
  - **Collections Store**: New Pinia store (`resources/js/stores/collection.ts`) with full CRUD operations
    - `fetchCollections()` - Retrieve all collections with caching support
    - `fetchCollection(id)` - Get single collection with detailed information
    - `createCollection(data)` - Create new collection with validation
    - `updateCollection(id, data)` - Update existing collection
    - `deleteCollection(id)` - Remove collection with confirmation
    - `clearCurrentCollection()` - Reset current collection state
  - **Collections List View**: New Vue component (`resources/js/views/Collections.vue`)
    - **ListView Integration**: Uses shared ListView component with consistent styling
    - **Search & Filter**: Real-time search across collection names and metadata
    - **Sorting**: Sortable columns (internal_name, created_at) with ascending/descending
    - **Responsive Design**: Mobile-friendly table with progressive disclosure
    - **Action Buttons**: View, Edit, Delete actions with proper confirmation flows
    - **Empty States**: Contextual messages for empty lists and search results
    - **Loading States**: Proper loading indicators during data operations
  - **Collection Detail View**: New Vue component (`resources/js/views/CollectionDetail.vue`)
    - **DetailView Integration**: Uses shared DetailView component for consistent UX
    - **Multi-Mode Support**: View, Edit, and Create modes with proper state management
    - **Form Validation**: Real-time validation with user-friendly error messages
    - **Dropdown Integration**: Language and Context selection with proper data loading
    - **Relationship Display**: Shows related items, partners, and translation counts
    - **Status Management**: Proper handling of creation, update, and deletion workflows
    - **Navigation**: Breadcrumb navigation and proper back-link handling
  - **Router Integration**: Updated routes in `resources/js/router/index.ts`
    - `/collections` - Collections list view
    - `/collections/new` - Create new collection
    - `/collections/:id` - View collection details
    - `/collections/:id?edit=true` - Edit collection
  - **Navigation Updates**: Added Collections to main navigation in AppHeader and Home page
  - **Comprehensive Test Coverage**: 87 passing tests across all components and workflows
    - **Unit Tests**: Component logic, validation, and state management
    - **Integration Tests**: Store integration, router navigation, and API client interaction
    - **Resource Tests**: CRUD operations, error handling, and data consistency
    - **Consistent Testing**: All tests follow established patterns with proper mocking and isolation
  - **Type Safety**: Full TypeScript integration with API client types
  - **Performance**: Optimized rendering with computed properties and proper reactivity
  - **Accessibility**: Proper ARIA labels, keyboard navigation, and screen reader support

### Fixed

- **Frontend Test Isolation**: Fixed Collection test suite isolation issues
  - **Resolved Spy Expectations**: Fixed failing spy assertions in integration tests
  - **Corrected Mock Setup**: Ensured consistent store mocking across all test files
  - **Improved Error Handling**: Updated error store mocks to match actual implementation patterns
  - **State Management**: Fixed state mutation issues in resource integration tests
  - **Test Consistency**: Aligned all Collection tests with established testing patterns

- **Deployment Workflow Triggers**: Fixed continuous deployment workflows not triggering after PR merges
  - **Changed Trigger Method**: Replaced `workflow_run` triggers with direct `push` triggers on main branch
  - **Eliminated Duplicate CI Runs**: Prevents CI from running twice (once on PR, once on main after merge)
  - **Consistent Behavior**: Both production deployment and GitHub Pages deployment now use the same trigger pattern
  - **Maintains Security**: Deployments still protected by GitHub rulesets requiring PR approval and CI success
  - **Improved Efficiency**: Deployments now trigger immediately after PR merge without waiting for additional CI runs

- **Automated Version Numbering**: Implemented semantic versioning based on `npm version patch|minor|major`
- **Inventory Menu**: New navigation dropdown for Items and Partners in main application header
- **Items Feature**: Complete CRUD functionality for inventory items management
  - **Items List**: Search, filtering (all/objects/monuments), sorting, and pagination
  - **Item Detail**: Full create/edit/delete functionality with relationships to Partners, Projects, and Countries  
  - **Type System**: Support for "Object" and "Monument" item types with visual badges
  - **Relationship Management**: Optional associations with Partners, Projects, and Countries
  - **Store Integration**: Reactive Pinia store for state management with API client integration
  - **Routing**: Full route structure for list, detail, create, and edit views
- **Partners Feature**: Complete CRUD functionality for partner management
  - **Partners List**: Search, filtering (all/museums/institutions/individuals), sorting, and pagination
  - **Partner Detail**: Full create/edit/delete functionality with country relationships
  - **Type System**: Support for "Museum", "Institution", and "Individual" partner types
  - **Store Integration**: Reactive Pinia store for state management with API client integration
  - **Routing**: Full route structure for list, detail, create, and edit views
- **Date Utilities**: Utility functions for consistent date formatting across the application
- **UI Test Coverage**: Added comprehensive tests for Item relationship updates and dropdown unset operations

### Enhanced

- **Home Dashboard Layout**: Redesigned with organized sections for Inventory, Reference Data, and Tools
  - **Responsive Grid**: Up to 4 tiles per row on extra-large screens (xl:grid-cols-4)
  - **Section Organization**: Clear visual separation with section headers and borders
  - **Color Theming**: Distinct color schemes - Items (teal), Partners (yellow), Reference Data (existing colors)
- **Component Color Support**: Extended color mappings across all UI components
  - **Navigation Cards**: Added teal and yellow color support for consistent theming
  - **Add Buttons**: Extended color map to support teal theme for Items
  - **List Views**: Added teal color support for Items list styling
  - **Card Components**: Enhanced responsive icon sizing (32px→48px→64px across breakpoints)
- **Heroicons Integration**: Replaced all hardcoded SVG elements with proper Heroicon components
  - **Edit/Delete Buttons**: Using PencilIcon and TrashIcon from @heroicons/vue/24/outline
  - **Navigation Cards**: ChevronRightIcon for consistent arrow styling
  - **Accessibility**: Improved screen reader support and icon consistency

### Updated

- **TypeScript API Client**: Updated to version `1.1.23-dev.0908.1218` with latest API changes and improvements

### Enhanced

- **Context and Language Default Management**: Improved default handling with unified API and comprehensive test coverage
    - **Unified API Endpoints**: Consolidated `setDefault`/`unsetDefault` into single `setDefault` method with boolean parameter
        - `PATCH /context/{id}/default` with `{"is_default": true/false}` for set/unset operations
        - `DELETE /context/default` to clear any default context
        - `PATCH /language/{id}/default` with `{"is_default": true/false}` for set/unset operations  
        - `DELETE /language/default` to clear any default language
    - **Model Methods**: Added `setDefault()`, `unsetDefault()`, and `clearDefault()` methods to Context and Language models
        - **Transaction Safety**: All default operations wrapped in database transactions for consistency
        - **Atomic Updates**: Setting default automatically unsets other defaults, unsetting only affects target resource
    - **Controller Updates**: Simplified controllers to handle toggle logic through single `setDefault` method
    - **Vue.js Store Consistency**: Aligned Context and Language stores with identical toggle logic
        - **Efficient Updates**: When setting default, all others set to false; when unsetting, only target updated
        - **State Management**: Proper local state synchronization with server responses
    - **Comprehensive Test Coverage**: Added dedicated test files for default functionality
        - `tests/Feature/Api/Context/DefaultTest.php` - Set/unset/clear default context operations
        - `tests/Feature/Api/Language/DefaultTest.php` - Set/unset/clear default language operations
        - **Anonymous Access Tests**: Verify unauthorized users cannot modify defaults
        - **Complete Scenarios**: Test setting, unsetting, clearing, and edge cases
    - **Cache Management**: Added `clearCacheAndReload` utility with Tools menu integration
        - **Store Clearing**: Resets all Pinia stores while preserving authentication
        - **Data Reloading**: Automatically fetches fresh data for contexts and languages
        - **UI Integration**: Accessible via Tools dropdown in application header

### Fixed

- **Context Vue Tests GUID Format**: Updated Context Vue tests to use proper GUID format instead of numeric IDs
    - **Test Consistency**: Context tests now follow the same GUID ID pattern as Project tests
    - **ID Format Standardization**: Context/Project/Item/Partner/Tag/Picture entities use GUID format (`123e4567-e89b-12d3-a456-426614174000`)
    - **Language/Country Codes**: Maintained lowercase 3-character codes (`eng`, `usa`) for Language/Country entities  
    - **Context Set Default**: Properly implemented Context "set default" functionality with GUID IDs
    - **Test Utilities**: Cleaned up duplicate mock functions and standardized ID formats in `test-utils.ts`
    - **Icon Mocks**: Added missing HeroIcons mocks (`CheckIcon`, `XMarkIcon`) for proper component rendering
    - **Comprehensive ID Replacement**: Systematically replaced ALL numeric IDs in both mock data and test logic across multiple test files

### Added
 
- **Gallery Model Implementation**: Complete Gallery model as the second type of collection system
    - **Gallery Model**: UUID-based primary key with polymorphic many-to-many relationships
    - **Polymorphic Relationships**: Can contain both Items and Details via Galleryable model
    - **Translation Support**: Multi-language translations via GalleryTranslation model
    - **Partner Relationships**: Contribution levels (Partner, Associated Partner, Minor Contributor)
    - **Database Schema**: Complete migrations for galleries, gallery_translations, gallery_partner, and galleryables tables
    - **API Endpoints**: Full CRUD operations with proper validation and resource formatting
        - `GET /api/gallery` - List all galleries with relationships
        - `GET /api/gallery/{id}` - Get specific gallery with translations
        - `POST /api/gallery` - Create new gallery
        - `PUT /api/gallery/{id}` - Update existing gallery
        - `DELETE /api/gallery/{id}` - Delete gallery
    - **Factory & Seeder**: Complete test data generation system
    - **Comprehensive Testing**: Unit and feature tests for all functionality
        - Unit tests for Gallery and GalleryTranslation factories
        - Feature tests for all CRUD operations
        - Anonymous access validation tests
        - Relationship and validation tests
    - **Model Updates**: Enhanced Item and Detail models with Gallery polymorphic relationships
- **Image Upload Status Polling**: Real-time status polling for image processing workflow
    - **Status Endpoint**: `GET /api/image-upload/{id}/status` to check processing progress
    - **Processing States**: Returns `processing`, `processed`, or `not_found` status

### Added

- **CI / Dev tooling**: Add YAML workflow validation script and expose deployment VERSION
    - Added `scripts/validate-workflows.cjs` and npm script `npm run test:yml` which validates all `.github/workflows/*.yml` files using `action-validator`.
    - CI can now write a `VERSION` JSON file into the build artifact (pipeline-side). The backend will return that JSON via `GET /api/version` when present and the web UI footer shows app version, api client version and build timestamp.
    - Config key `app.version` (env: `APP_VERSION`) is introduced and the main Blade layout displays the version information in a small footer.
    - **AvailableImage Integration**: Returns AvailableImage details when processing complete
    - **Automatic Cleanup**: ImageUpload records deleted after successful processing
    - **Comprehensive Testing**: Integration and feature tests for status polling workflow
- **Picture Detachment System**: Complete functionality to detach Pictures from entities and convert them back to AvailableImages
    - **Picture Detachment Endpoints**: RESTful endpoints for detaching Pictures from entities
        - `DELETE /api/picture/{picture}/detach-from-item/{item}` - Detach Picture from Item
        - `DELETE /api/picture/{picture}/detach-from-detail/{detail}` - Detach Picture from Detail
        - `DELETE /api/picture/{picture}/detach-from-partner/{partner}` - Detach Picture from Partner
    - **Complete Detachment Workflow**: Atomic transaction-based detachment process
        - Validates Picture belongs to the specified entity
        - Moves image files from Pictures directory back to AvailableImages directory
        - Creates new AvailableImage record with optional comment
- **Picture Translation Support**: Added translation support to the Picture model
    - Created PictureTranslation model, migration, factory, seeder, resource and controller
    - Updated Picture model with translation handling methods
    - Added corresponding feature and unit tests
- **Info API Endpoints**: Health check and application information endpoints for monitoring
    - **Info Endpoint** (`GET /api/info`): Complete application information including version and health status
    - **Health Endpoint** (`GET /api/health`): Dedicated health check endpoint with database and cache validation
    - **Version Endpoint** (`GET /api/version`): Application version information for deployment tracking
    - **Public Access**: All endpoints are publicly accessible for monitoring systems
    - **Health Checks**: Automated validation of database connectivity and cache operations
    - **Version Detection**: Smart version detection from environment variable, git commit hash, or fallback
    - **Comprehensive Testing**: Full test coverage for all endpoints and scenarios
    - **Documentation**: Updated API documentation with Info endpoint details

### Improved

- **Image Processing Workflow**: Enhanced ImageUploadListener to properly clean up processed uploads
    - ImageUpload records now deleted after successful processing
    - Maintains same ID between ImageUpload and AvailableImage for tracking
    - Improved error handling and logging for failed processing
- **API Client Documentation**: Enhanced documentation for TypeScript API client
    - Added comprehensive API client development guide
    - Detailed versioning strategy explanation
    - Added troubleshooting section and best practices
    - Improved integration instructions with authentication setup

### Fixed

- **API Client Versioning**: Fixed versioning system to prevent conflicts during npm package publishing
    - Added support for proper semantic versioning
    - Improved handling of build metadata for npm compatibility
    - Added parameter to specify version increment type (major, minor, patch)
        - Removes Picture record and polymorphic relationship
        - Preserves file content and metadata throughout the process
    - **Comprehensive Test Coverage**: 150+ new tests covering all detachment functionality
        - Unit tests for detachment validation and file operations
        - Feature tests for all detachment endpoints with authentication testing
        - Integration tests for complete workflow: ImageUpload → AvailableImage → Picture → AvailableImage
        - File content preservation tests throughout attach/detach cycles
        - Multiple attach/detach cycle testing for workflow reliability
        - Cross-model-type workflow validation (Item, Detail, Partner)
    - **Laravel Best Practices**: Implementation follows Laravel 12 recommendations
        - Uses Storage facade for all file operations with atomic transactions
        - Implements database transactions for data consistency
        - Proper validation with comprehensive error handling
        - RESTful API design with appropriate HTTP status codes
        - Polymorphic relationship validation for security

- **Enhanced CI Scripts and Development Tools**: Improved CI/CD pipeline with new PowerShell scripts
    - **New CI Scripts**: Added dedicated PowerShell scripts in `scripts/` directory
        - `ci-test.ps1` - Enhanced test execution with argument passing support
        - `ci-test-with-filter.ps1` - Specialized test filtering capabilities
        - `ci-lint.ps1` - Enhanced linting with configurable options
        - `ci-lint-with-args.ps1` - Flexible linting with argument support
    - **Improved Development Workflow**: Better error handling and developer experience
        - Support for `COMPOSER_ARGS` environment variable for flexible argument passing
        - Enhanced output formatting and error reporting
        - Specialized testing workflows (filtering, grouping, test suites)
        - Configurable linting options for different validation levels
    - **Updated Dependencies**: Enhanced package.json and composer.json configurations
        - Additional dev dependencies for improved tooling
        - Updated script definitions for better CI/CD integration
        - Enhanced pre-push hooks for comprehensive code quality checks

- **Picture Attachment System**: Complete image attachment functionality for Items, Details, and Partners
    - **Polymorphic Picture Model**: New polymorphic Picture model with `uuidMorphs('pictureable')` for attaching images to multiple model types
    - **Picture Attachment Endpoints**: RESTful endpoints for attaching AvailableImages to entities
        - `POST /api/item/{item}/pictures` - Attach images to Items
        - `POST /api/detail/{detail}/pictures` - Attach images to Details
        - `POST /api/partner/{partner}/pictures` - Attach images to Partners
    - **Complete Attachment Workflow**: Atomic transaction-based attachment process
        - Validates AvailableImage existence and file presence
        - Moves image files from AvailableImage directory to Pictures directory
        - Creates Picture record with polymorphic relationship
        - Removes AvailableImage record after successful attachment
        - Handles file metadata extraction (size, mime type, extension)
    - **Picture CRUD Operations**: Full REST API for Picture management
        - `GET /api/pictures` - List all pictures with polymorphic relationships
        - `GET /api/pictures/{id}` - Show specific picture details
        - `PUT /api/pictures/{id}` - Update picture metadata (internal_name, copyright info)
        - `DELETE /api/pictures/{id}` - Delete picture and associated file
        - `GET /api/pictures/{id}/download` - Download picture file with proper Content-Disposition headers
        - `GET /api/pictures/{id}/view` - View picture inline with caching headers for browser display
    - **Enhanced Model Relationships**: Updated Item, Detail, and Partner models with `morphMany` picture relationships
    - **Storage Configuration**: Added dedicated pictures storage configuration in `config/localstorage.php`
    - **Comprehensive Test Coverage**: 81 tests covering all picture functionality
        - Unit tests for Picture factory and model relationships
        - Feature tests for all attachment endpoints with validation testing
        - CRUD operation tests with authentication and authorization
        - File handling tests with Storage facade mocking
        - Download and view endpoint tests with proper header validation
        - Polymorphic relationship validation across all supported models
    - **Laravel Best Practices**: Implementation follows Laravel 12 recommendations
        - Uses Storage facade for all file operations
        - Implements database transactions for data consistency
        - Polymorphic relationships with proper indexing
        - Comprehensive validation with proper error handling
        - RESTful API design with proper HTTP status codes

### Changed

- **Storage Configuration Refactoring**: Improved clarity and organization of storage configuration
    - **Renamed Configuration Keys**: Updated environment variable names for better self-documentation
        - `UPLOAD_IMAGES_*` for ImageUpload storage (was `LOCAL_STORAGE_IMAGE_UPLOAD_*`)
        - `AVAILABLE_IMAGES_*` for AvailableImage storage (was `LOCAL_STORAGE_IMAGE_*`)
        - `PICTURES_*` for Picture storage (was `LOCAL_STORAGE_PICTURES_*`)
    - **Configuration Structure**: Reorganized `config/localstorage.php` structure
        - `uploads.images` for ImageUpload configuration
        - `available.images` for AvailableImage configuration (was `public.images`)
        - `pictures` for Picture configuration
    - **Environment Files**: Updated `.env`, `.env.example`, and `.env.testing` with new variable names
    - **Code Updates**: Updated all controllers, listeners, and factories to use new configuration paths
    - **Clear Separation**: Each storage type now has distinct, self-explanatory configuration namespace

### Fixed

- **Image Upload Event Processing**: Fixed ImageUploadListener path resolution error preventing AvailableImage creation
    - **Root Cause**: ImageUploadListener was incorrectly using original filename instead of stored hash path
        - Controller stores files with hashed names for security (e.g., `image_uploads/hash123.jpg`)
        - Listener was trying to access files using original names (e.g., `image_uploads/Skull.jpg`)
        - This caused `exif_imagetype()` to fail with "No such file or directory" error
    - **Solution**: Updated ImageUploadListener to use `$file->path` (actual stored path) instead of manually constructing path
    - **Factory Fix**: Corrected ImageUploadFactory to store complete file path in `path` field instead of directory only
    - **Test Updates**: Fixed all image upload tests to use proper path structure
    - **Result**: Image upload workflow now properly creates AvailableImage records when ImageUpload events are processed

### Added

- **Image Viewing Endpoint**: New endpoint for direct image display in web applications
    - **New Route**: `GET /api/available-image/{id}/view` for inline image display (complements existing download endpoint)
    - **Browser-Friendly**: Returns images with `Content-Disposition: inline` for direct use in `<img src="">` tags
    - **Caching Headers**: Includes `Cache-Control: public, max-age=3600` for optimal browser caching
    - **Error Handling**: Proper 404 responses for missing image files
    - **Security**: Maintains authentication requirements consistent with other endpoints

### Changed

- **TagItem Pivot Table Refactoring**: Simplified many-to-many relationship between Items and Tags
    - **Removed TagItem Model**: Eliminated over-engineered TagItem model in favor of Laravel's standard pivot table approach
    - **Database Schema**: Replaced `tag_items` table with proper Laravel `item_tag` pivot table
        - Follows Laravel naming conventions (alphabetical order: `item_tag`)
        - Uses composite primary key (`item_id`, `tag_id`) instead of separate UUID primary key
        - Maintains foreign key constraints and timestamps
    - **Removed TagItem Infrastructure**: Deleted all TagItem-related files
        - Removed `TagItem` model, factory, seeder, controller, resource, and tests
        - Updated `DatabaseSeeder` to use new `ItemTagSeeder` for pivot table population
        - Cleaned TagItem imports and references from existing tests
    - **Model Relationships**: Updated Item and Tag models to use standard Laravel `belongsToMany` relationships
        - Removed custom pivot model specification
        - Maintained `withTimestamps()` for created_at/updated_at tracking
        - All existing scopes (`forTag`, `withAllTags`, `withAnyTags`, `forItem`) work unchanged
    - **Test Suite Updates**: Fixed all tests to use Eloquent relationship methods instead of TagItem factory
        - Updated 881 tests to use `$item->tags()->attach()` and `$tag->items()->attach()` methods
        - Maintained all existing functionality and test coverage
        - All tests pass with improved performance and simpler code
    - **Enhanced API**: Added quick tag editing endpoint for efficient tag management
        - New `PATCH /api/item/{item}/tags` endpoint for updating item tags without full item update
        - Supports both `attach` and `detach` operations in single request
        - Prevents duplicate tag attachments and handles non-existent detachments gracefully
        - Comprehensive validation for tag UUIDs and existence checks
        - Returns updated item with all relationships loaded
        - Complete test coverage with 15 test cases covering all scenarios
- **Internationalization Refactoring**: Migrated from `*Language` pivot models to `*Translation` models
    - **Translation Models**: Replaced `ContactLanguage`, `ProvinceLanguage`, `LocationLanguage`, `AddressLanguage` with proper translation models
        - New models: `ContactTranslation`, `ProvinceTranslation`, `LocationTranslation`, `AddressTranslation`
        - Changed from many-to-many relationships with pivot tables to one-to-many relationships with dedicated translation tables
        - Updated all main models (`Contact`, `Province`, `Location`, `Address`) to use `translations()` hasMany relationships
    - **Database Schema**: Updated database structure to follow Laravel translation conventions
        - New migration tables: `contact_translations`, `province_translations`, `location_translations`, `address_translations`
        - Removed old pivot tables: `contact_language`, `province_language`, `location_language`, `address_language`
        - Each translation table includes foreign keys to parent model and language
        - Added unique constraints on (model_id, language_id) combinations
    - **API Endpoints**: Added new translation-specific API endpoints
        - `/api/contact-translation`, `/api/province-translation`, `/api/location-translation`, `/api/address-translation`
        - Full CRUD operations for all translation models
        - Proper API resources and controllers for each translation type
        - Updated main model endpoints to return embedded translations
    - **Testing Coverage**: Complete test suite for all translation models
        - Unit tests for factories with proper state methods and model relationships
        - Feature tests for all CRUD operations and anonymous access validation
        - Fixed factory state methods for default context handling
        - All 924 tests pass successfully with 3632 assertions
        - Comprehensive validation of unique constraints and field requirements
    - **Documentation**: Updated API documentation to reflect translation model changes and new endpoints

### Removed

- **Contextualization and Internationalization Models**: Removed complex contextualization system deemed too complex for current requirements (July 6, 2025)
    - **Contextualization Model**: Removed model, controller, resource, factory, seeder, and all related tests
    - **Internationalization Model**: Removed model, controller, resource, factory, seeder, and all related tests
    - **Database Tables**: Added migrations to drop `contextualizations` and `internationalizations` tables
    - **API Endpoints**: Removed all contextualization and internationalization API routes
    - **Model Relationships**: Cleaned up relationships in Item, Detail, Context, and Author models
    - **Documentation**: Updated API documentation and field documentation

### Added

- **Geographic Models**: Complete geographic data management system with internationalization support
    - **Province Model**: New Province model with UUID primary keys, country relationships, and multi-language support
        - Full CRUD API endpoints (`/api/provinces`)
        - Language-specific content via ProvinceTranslation model
        - Factory, seeder, and comprehensive test coverage (40+ tests)
        - Required relationship to Country model
    - **Location Model**: New Location model for geographic locations within provinces
        - Full CRUD API endpoints (`/api/locations`)
        - Language-specific content via LocationTranslation model
        - Relationships to both Country and Province models
        - Factory, seeder, and comprehensive test coverage (40+ tests)
    - **Address Model**: New Address model for detailed address information
        - Full CRUD API endpoints (`/api/addresses`)
        - Language-specific content via AddressTranslation model
        - Relationships to Country, Province, and Location models
        - Factory, seeder, and comprehensive test coverage (40+ tests)
    - **Internationalization Support**: Multi-language pivot tables for all geographic models
        - ProvinceTranslation, LocationTranslation, and AddressTranslation models
        - Language-specific name and description fields
        - API responses include all available language versions
        - Consistent with existing Contact model internationalization patterns
    - **Database Structure**: Six new migrations with proper foreign key constraints
        - Geographic entity tables with UUID primary keys and internal_name fields
        - Language pivot tables with composite primary keys
        - Proper indexing for optimal query performance
        - backward_compatibility column for data migration support
    - **API Resources**: Dedicated API resources for consistent JSON responses
        - Includes language data in responses for internationalization
        - Follows existing Contact model response patterns
        - Proper resource transformation for all geographic models
    - **Complete Test Coverage**: 120+ new tests across unit and feature test suites
        - Factory tests for data generation validation
        - Full CRUD operation testing for all endpoints
        - Authentication and authorization testing
        - Relationship and constraint validation
- **Item and Detail Internationalization and Contextualization**: Complete internationalization and contextualization system for core inventory models
    - **Translation Models**: New ItemTranslation and DetailTranslation models supporting multi-language, multi-context content
        - ItemTranslation model with comprehensive field set: name, alternate_name, description, type, holder, owner, initial_owner, dates, location, dimensions, place_of_production, method_for_datation, method_for_provenance, obtention, bibliography
        - DetailTranslation model with focused field set: name, alternate_name, description
        - Both models support author relationships (author, text_copy_editor, translator, translation_copy_editor)
        - UUID primary keys with foreign key relationships to items/details, languages, contexts, and authors
        - Unique constraints on (model_id, language_id, context_id) combinations to prevent duplicates
        - JSON extra field for extensible metadata storage
    - **Database Schema**: New translation tables following Laravel internationalization conventions
        - item_translations and detail_translations tables with proper indexing
        - Foreign key constraints with cascade delete for parent models and set null for authors
        - Context-aware translations enabling multiple versions per language
        - Backward compatibility support for data migration
    - **Eloquent Relationships**: Enhanced Item and Detail models with translation support
        - hasMany relationships to translation models with eager loading support
        - Helper methods for retrieving default context and contextualized translations
        - Fallback logic for graceful handling of missing translations
        - Scope methods for filtering by language and context
    - **API Endpoints**: Full CRUD REST API for translation management
        - `/api/item-translation` and `/api/detail-translation` endpoints
        - Filtering capabilities by item_id, detail_id, language_id, context_id
        - Default context filtering for simplified queries
        - Comprehensive validation with proper error handling for unique constraint violations
        - Nested relationship loading for complete translation data
    - **Factory and Seeder Support**: Complete test data generation infrastructure
        - ItemTranslationFactory and DetailTranslationFactory with state methods
        - Support for default context, specific language/context, and author configurations
        - ItemTranslationSeeder and DetailTranslationSeeder for sample data generation
        - Integration with existing Item and Detail factories via withoutTranslations states
    - **API Resource Integration**: Enhanced parent model resources with translation data
        - ItemResource and DetailResource now include embedded translations
        - ItemTranslationResource and DetailTranslationResource for dedicated translation endpoints
        - Proper relationship loading with whenLoaded for performance optimization
        - Complete field mapping with relationship data inclusion
    - **Context and Language Integration**: Full integration with existing Context and Language systems
        - Support for default context identification via Context model
        - Language-specific content with ISO 639-1 language codes
        - Context factory enhanced with default() state method for testing
        - Seamless integration with existing geographic translation patterns
- **Code Quality and Development Workflow Enhancements**: Comprehensive development environment improvements
    - **Husky Integration**: Automated Git hooks for code quality enforcement using [Husky](https://typicode.github.io/husky)
    - **lint-staged Implementation**: Efficient code formatting on staged files using [lint-staged](https://www.npmjs.com/package/lint-staged)
    - **Prettier Integration**: Automatic code formatting for JavaScript, TypeScript, CSS, and Markdown using [Prettier](https://prettier.io/)
    - **Stylelint Configuration**: CSS and SCSS linting with [Stylelint](https://stylelint.io/) for consistent stylesheets
    - **Pre-commit Automation**: Automatic linting and formatting of staged files before commits
    - **Enhanced Composer Scripts**: Updated `ci-lint` script to improve CI/CD reliability
- **Internationalization Feature**: Complete multi-language support system for the API
    - **Language-specific Endpoints**: New API endpoints for retrieving content in specific languages
        - `GET /api/internationalization/english` - Retrieve all English content
        - `GET /api/internationalization/default` - Retrieve content in default language
    - **Enhanced Language Model**: Extended Language model with default language management
    - **Automatic Content Filtering**: Language-specific filtering for all major content models (Items, Partners, Projects, etc.)
    - **Multi-language Data Structure**: Support for language-specific content organization
    - **Default Language Scopes**: Efficient queries for default language content across all models
    - **Comprehensive Test Coverage**: 40+ new tests covering internationalization functionality
    - **Language-aware API Resources**: Updated API responses to include language-specific content
- **Enhanced OpenAPI Documentation Generation**: Improved API documentation automation and deployment
    - **Updated Swagger UI**: Upgraded to swagger-ui-dist@5.11.0 for better interactive documentation
    - **Organized Documentation Structure**: Moved API documentation files to `docs/_openapi/` for clarity
    - **Jekyll Integration**: Enhanced integration with Jekyll for seamless documentation deployment
    - **Automated Documentation Pipeline**: Improved `composer ci-openapi-doc` script for consistent API documentation
    - **Better Error Handling**: Enhanced documentation generation with proper error reporting
- **Dependency and Pipeline Management**: Grouped dependency updates and enhanced automation
    - **Enhanced Dependabot Workflow**: Improved auto-merge workflow with admin privileges and GitHub API fallback
    - **Consolidated Updates**: Grouped multiple dependency commits into single PR #180 for cleaner git history
    - **Updated Dependencies**:
        - `tailwindcss` from 4.1.10 to 4.1.11
        - `@tailwindcss/postcss` from 4.1.10 to 4.1.11
        - `@tailwindcss/vite` from 4.1.10 to 4.1.11
        - `nunomaduro/collision` from 8.8.1 to 8.8.2
    - **Improved Automation**: Better reliability for automated dependency updates with branch protection bypass
    - **Git History Optimization**: Reset main branch and consolidated 8 commits for improved maintainability

### Fixed

- **Jekyll Documentation Site Navigation**: Fixed duplicate navigation links in header
    - Resolved improper Liquid template logic in `docs/_includes/header.html`
    - Replaced invalid `if` condition syntax with proper `unless` negation
    - Eliminated duplicate "API Documentation", "Daily Archive" and other page links
    - Improved user experience with clean, semantic navigation HTML
- **MobileAppAuthenticationController HTTP 500 Error**: Fixed critical validation error preventing mobile authentication
    - Resolved invalid Laravel validation rule `'wipe_tokens' => 'boolean|default:false'`
    - Implemented proper validation using `'wipe_tokens' => 'sometimes|boolean'`
    - Enhanced boolean parameter handling with `$request->boolean('wipe_tokens', false)`
    - Prevented "Method Illuminate\Validation\Validator::validateDefault does not exist" exception
- **Database Reset Script**: Enhanced `composer ci-reset` script for reliable database recreation
    - Improved database deletion and recreation process
    - Better error handling for file system operations
    - More reliable test environment preparation
- **Translation Factory State Methods**: Fixed factory test failures for ItemTranslation and DetailTranslation models
    - Added missing `withDefaultContext()` and `forItem()`/`forDetail()` state methods to factories
    - Fixed default context state method to use existing default context instead of creating new ones
    - Corrected index filter tests to properly create multiple translations for the same parent model
    - Enhanced factory imports and method signatures for proper type handling
    - All translation-related tests now pass consistently

### Added (Previous Features)

- **MarkdownService**: Comprehensive content processing service for markdown and HTML conversion
    - **Bidirectional Conversion**: Convert between Markdown ↔ HTML formats with GitHub Flavored Markdown support
    - **Content Validation**: Built-in validation for markdown and HTML content using Laravel validation rules
    - **Security Features**: HTML sanitization, link safety, and protection against malicious content
    - **API Endpoints**: Complete REST API for content processing:
        - `POST /markdown/to-html` - Convert markdown to HTML
        - `POST /markdown/from-html` - Convert HTML to markdown
        - `POST /markdown/validate` - Validate markdown content
        - `POST /markdown/preview` - Generate HTML preview
        - `POST /markdown/is-markdown` - Detect markdown formatting
        - `GET /markdown/allowed-elements` - Get supported elements
    - **Format Detection**: Automatic detection of markdown formatting in text content
    - **Table Support**: Full bidirectional table conversion between HTML and markdown
    - **Code Block Protection**: Proper handling of code syntax and HTML-like content
    - **Laravel Integration**: MarkdownRule for form validation and dependency injection support
    - **Complete Test Coverage**: 690 tests including unit, feature, and integration tests
    - **Library Integration**: CommonMark library with extensions and HTML-to-Markdown converter
- **Tag Management System**: Complete tagging functionality for content organization
    - **Tag Model**: New Tag model with full CRUD operations, factory, seeder, and comprehensive test suite
    - **TagItem Pivot Model**: Many-to-many relationship management between Tags and Items
    - **Enhanced API Endpoints**: Tag-specific endpoints for item-tag relationship management
    - **Scope Methods**: Added tag-based scopes for efficient querying and filtering
    - **Complete Test Coverage**: 40+ new tests covering all tag functionality
- **API Documentation Consolidation**: Enhanced GitHub Pages integration
    - Consolidated GitHub Pages documentation workflow
    - Improved API documentation generation and deployment
    - Enhanced Swagger UI integration with better error handling
- **Contextualization Feature**: Comprehensive contextualized information storage for Items and Details
    - **New Contextualization Model**: UUID-based model with relationships to Context, Item, and Detail entities
    - **Flexible Association**: Each contextualization belongs to either an Item OR a Detail (mutually exclusive)
    - **Context Integration**: Full integration with existing Context system including default context support
    - **Extensible Design**: JSON `extra` field for storing additional unforeseen data
    - **API Endpoints**: Complete REST API with specialized endpoints:
        - Standard CRUD operations (`index`, `show`, `store`, `update`, `destroy`)
        - Default context operations (`GET/POST /contextualizations/default-context`)
        - Filtered endpoints (`/for-items`, `/for-details`)
    - **Comprehensive Validation**: Application-level constraints ensuring exactly one of `item_id` or `detail_id` is set
    - **Enhanced Models**: Added `contextualizations()` relationships to Item, Detail, and Context models
    - **Complete Test Coverage**: 61 new tests covering all functionality including unit and feature tests
    - **Factory & Seeding**: ContextualizationFactory with state methods and seeder for sample data
- **Database Seeders**: Complete seeding system for all key models
    - **PartnerSeeder**: Seeds 5 business partners/organizations with proper country relationships
    - **ItemSeeder**: Seeds 20 main inventory items with partner, country, and project associations
    - **DetailSeeder**: Seeds 50 detailed information records linked to items
    - **TagItemSeeder**: Seeds 30 tag-item relationships for content organization
    - **PictureSeeder**: Seeds 15 item pictures for visual content testing
    - **ImageUploadSeeder**: Seeds 10 uploaded image files for media functionality
    - **AvailableImageSeeder**: Seeds 8 processed/available images for image workflow testing
    - **Enhanced DatabaseSeeder**: Updated to include all missing seeders in proper dependency order
    - **Complete Sample Data**: Full database population enabling comprehensive API testing and development
- **API Documentation Consolidation**: Enhanced GitHub Pages integration
    - Consolidated GitHub Pages documentation workflow
    - Improved API documentation generation and deployment
    - Enhanced Swagger UI integration with better error handling
- **Contextualization Feature**: Comprehensive contextualized information storage for Items and Details
    - **New Contextualization Model**: UUID-based model with relationships to Context, Item, and Detail entities
    - **Flexible Association**: Each contextualization belongs to either an Item OR a Detail (mutually exclusive)
    - **Context Integration**: Full integration with existing Context system including default context support
    - **Extensible Design**: JSON `extra` field for storing additional unforeseen data
    - **API Endpoints**: Complete REST API with specialized endpoints:
        - Standard CRUD operations (`index`, `show`, `store`, `update`, `destroy`)
        - Default context operations (`GET/POST /contextualizations/default-context`)
        - Filtered endpoints (`/for-items`, `/for-details`)
    - **Comprehensive Validation**: Application-level constraints ensuring exactly one of `item_id` or `detail_id` is set
    - **Enhanced Models**: Added `contextualizations()` relationships to Item, Detail, and Context models
    - **Complete Test Coverage**: 61 new tests covering all functionality including unit and feature tests
    - **Factory & Seeding**: ContextualizationFactory with state methods and seeder for sample data
- **Internationalization Feature**: Complete internationalization management system for Laravel application
    - **Core Model**: `Internationalization` model with UUID primary key and relationships to Contextualization, Language, and Author
    - **Database Schema**: Migration with proper foreign key constraints, unique indexes, and timestamp tracking
    - **API Endpoints**: Full CRUD REST API with resource-based responses:
        - `GET /api/internationalizations` - List all internationalizations with pagination
        - `GET /api/internationalizations/{id}` - Show specific internationalization details
        - `POST /api/internationalizations` - Create new internationalization entries
        - `PUT /api/internationalizations/{id}` - Update existing internationalization
        - `DELETE /api/internationalizations/{id}` - Delete internationalization entries
        - `GET /api/internationalizations/by-contextualization/{contextualization}` - Filter by contextualization
        - `GET /api/internationalizations/by-language/{language}` - Filter by language
        - `GET /api/internationalizations/by-author/{author}` - Filter by author
    - **Resource Controller**: Exception handling for unique constraint violations with proper HTTP status codes
    - **Factory and Seeder**: Test data generation and database seeding with proper relationship handling
    - **Complete Test Suite**: 58 comprehensive tests (57 feature + 1 unit) covering all CRUD operations and edge cases
    - **Performance Optimization**: Fast test execution (~15 seconds for full suite) with in-memory SQLite
    - **Code Quality**: PSR-12 compliant, Pint formatted, comprehensive PHPDoc annotations

### Changed

- **Test Suite Enhancement**: Expanded from 453 to 560 tests (1598 assertions)
    - Added comprehensive Tag and TagItem test coverage
    - Enhanced Item scope testing with tag relationships
    - Improved overall test reliability and coverage
- **Git History Organization**: Cleaned and reorganized git history for better maintainability
    - Consolidated GitHub Pages commits into dedicated feature branch
    - Preserved essential Tag functionality commits in main branch
    - Improved linear git history following repository standards

### Fixed

- **HTTP 503 Test Errors**: Resolved persistent HTTP 503 errors in test suite caused by real HTTP requests during testing
    - Enhanced `LoremPicsumImageProvider` to generate valid PNG images in testing environment
    - Added comprehensive `Http::fake()` to all image-related tests (12 test files)
    - Fixed image decoding compatibility with Intervention Image library
- **Picture UpdateTest Correction**: Fixed incorrectly implemented Picture UpdateTest that was testing for non-existent routes instead of proper CRUD operations
    - Replaced route-not-found tests with comprehensive update test suite (8 test methods)
    - Added validation for allowed update fields (internal_name, backward_compatibility, copyright_text, copyright_url)
    - Includes proper HTTP response, database assertion, and validation error tests
- **Route Testing Enhancement**: Enhanced route testing with dual test patterns for better coverage
    - Added exception-based tests for route resolution validation
    - Added HTTP response tests for proper status code validation
    - Applied to ImageUpload, AvailableImage, and Picture models
- **Test Isolation**: Improved test isolation by preventing external dependencies
    - All tests now use proper faking for HTTP, Events, and Storage
    - No real network requests made during test execution
    - Tests execute reliably without external service dependencies
- Fixed Vite manifest missing in CI/CD tests by adding conditional asset loading in Blade views
- Resolved HTTP 503 errors in parallel feature tests by implementing in-memory SQLite database
- Enhanced SQLite configuration with proper timeout and concurrency settings
- Fixed composer.json syntax errors and regenerated autoload files

### Changed

- **Test Suite Performance**: Test execution time improved to ~5.6 seconds with parallel execution
- **Test Coverage**: Increased total tests from 442 to 560 passing tests (1598 assertions)
- **Test Reliability**: All tests now pass consistently without external dependencies
- Reordered GitHub Actions workflow to build assets before running tests
- Updated all Blade views (welcome, app layout, guest layout) with conditional Vite loading
- Enhanced phpunit.xml configuration with VITE_ENABLED=false for testing environment

### Added

- **GitHub Pages Blog Generation**: Automated Jekyll-based documentation system
    - CI/CD workflow automatically generates blog posts for every commit to main branch
    - Jekyll site with responsive minima theme deployed to GitHub Pages
    - Custom layouts with commit navigation, author information, and GitHub links
    - Archive page with searchable commit history and development timeline
    - No local Ruby/Jekyll installation required - fully CI/CD driven
    - Live documentation at: https://metanull.github.io/inventory-app
- **Documentation Scripts**: Optional composer scripts for local Jekyll development
    - `composer docs-install` - Install Jekyll dependencies locally
    - `composer docs-build` - Build Jekyll site locally
    - `composer docs-serve` - Serve site with live reload for development

## [2.4.0] - 2025-06-27

### Added

- **Comprehensive Test Suite**: 560 tests covering all API endpoints and functionality
- **Unit Tests**: Added comprehensive unit tests for all factories and model validation
- **Feature Tests**: Complete API endpoint testing with proper authentication and validation
- **Test Structure**: Organized tests following Laravel best practices with separate directories for each model

### Changed

- **Reorganized Unit Tests**: Updated test structure to follow coding standards with proper directory organization
- **Enhanced Test Coverage**: Improved test coverage across all models and controllers
- **Test Data Management**: Enhanced factories with proper relationships and validation

### Fixed

- **Data Model Alignment**: Fixed validation consistency across application layers
- **Test Route Issues**: Resolved missing route definitions in feature tests
- **Factory Relationships**: Fixed foreign key relationships in test factories

## [2.3.0] - 2025-06-20

### Added

- **Detail Model**: New Detail model with complete CRUD operations, factory, and tests
- **Image Upload Management**: Enhanced image upload processing with proper event handling
- **Custom Faker Provider**: Added LoremPicsumImageProvider for realistic image URLs in tests
- **GitHub Pages Documentation**: Added documentation site with project information

### Changed

- **TailwindCSS Integration**: Re-installed and properly configured TailwindCSS for modern styling
- **README Updates**: Enhanced project documentation with comprehensive information
- **Image Processing**: Improved image upload and processing workflows

### Fixed

- **Image Upload Events**: Fixed image upload event handling and listener processing
- **Picture Model**: Corrected picture model relationships and validation

## [2.2.0] - 2025-06-15

### Added

- **Comprehensive Testing Framework**:
    - Added extensive test coverage for Language, Country, Context, Partner, and Item APIs
    - Implemented proper test factories with relationships
    - Added custom Pint commands for code quality management
- **Enhanced Validation**: Improved validation consistency across controllers and models
- **Database Seeders**: Enhanced seeders with proper foreign key relationships

### Changed

- **Factory Architecture**: Moved foreign key relationships to dedicated factory methods
- **Code Quality**: Implemented Laravel Pint for consistent code formatting
- **Controller Validation**: Enhanced input validation alignment with model constraints

### Fixed

- **Context Store Method**: Fixed ID handling in context creation endpoints
- **Factory Relationships**: Corrected foreign key relationships in test data generation
- **Validation Rules**: Aligned validation rules across models, controllers, and factories

## [2.1.0] - 2025-06-08

### Added

- **Project Model**: Complete Project model with factory, seeder, resource, and controller
- **Advanced Relationships**:
    - Item belongs to Project relationship
    - Partner belongs to Country relationship
    - Enhanced eager loading for optimal performance
- **Scopes and Defaults**:
    - Added model scopes for common queries
    - Implemented default language and context functionality
    - Added enable/disable toggles for projects

### Changed

- **Resource Optimization**: Enhanced API resources with proper relationship loading
- **Database Schema**: Improved foreign key relationships and constraints
- **Code Quality**: Applied Laravel Pint formatting standards across codebase

### Fixed

- **Migration Issues**: Resolved foreign key constraint problems in migrations
- **Resource Cleanliness**: Removed unnecessary commented code from API resources
- **Linting Compliance**: Fixed all code style issues identified by Laravel Pint

## [2.0.0] - 2025-05-15

### Added

- **GitHub Actions CI/CD**:
    - Comprehensive Windows-based CI/CD pipeline
    - Automated testing, linting, and dependency checking
    - Dependabot integration for automated dependency updates
- **Advanced Testing Setup**:
    - Separate testing environment configuration
    - SQLite database for testing isolation
    - Comprehensive test case framework
- **Security Enhancements**:
    - Laravel Sanctum API authentication
    - Proper permission handling in workflows
    - Trust proxy configuration

### Changed

- **PHP Version**: Upgraded to PHP 8.2 minimum requirement
- **Laravel Framework**: Updated to Laravel 12.x for latest features
- **Dependency Management**: Implemented automated dependency updates via Dependabot

### Fixed

- **Composer Dependencies**: Resolved package compatibility issues
- **Testing Environment**: Fixed database configuration for testing
- **GitHub Workflows**: Corrected CI/CD pipeline configuration

## [1.5.0] - 2025-04-20

### Added

- **Picture Upload System**:
    - Complete image upload and processing workflow
    - Automatic image resizing and optimization
    - Event-driven architecture for file processing
    - Picture model with full CRUD operations
- **Enhanced Seeders**:
    - Context seeders with proper relationships
    - API token generation for testing
    - Improved data consistency across environments

### Changed

- **Model Relationships**: Enhanced foreign key relationships between models
- **API Documentation**: Improved Scramble OpenAPI documentation
- **Resource Controllers**: Optimized controller methods following Laravel guidelines

### Fixed

- **Resource Routing**: Corrected leftover resource route issues
- **Relationship Constraints**: Fixed Partner-Item relationship problems
- **Documentation Links**: Added proper OpenAPI documentation links to dashboard

## [1.0.0] - 2025-03-01

### Added

- **Core Inventory Models**:
    - Language model with ISO 639-1 standard compliance
    - Country model with ISO 3166-1 alpha-3 standard compliance
    - Context model for contextual information management
    - Partner model for business entity management
    - Item model for inventory item management
- **RESTful API Architecture**:
    - Complete CRUD operations for all models
    - Laravel Resource classes for consistent API responses
    - Proper HTTP status codes and error handling
- **Database Architecture**:
    - UUID primary keys for scalability
    - Proper foreign key relationships
    - Migration-based schema management
    - Comprehensive indexing strategy
- **API Documentation**:
    - Scramble integration for automatic OpenAPI documentation
    - Interactive API documentation interface
    - Comprehensive endpoint documentation
- **Authentication & Authorization**:
    - Laravel Sanctum for API token authentication
    - Proper middleware configuration
    - Secure API endpoint protection

### Security

- **Input Validation**: Comprehensive validation rules for all endpoints
- **SQL Injection Protection**: Eloquent ORM usage prevents SQL injection
- **CSRF Protection**: Proper CSRF token handling for web routes
- **Rate Limiting**: API rate limiting configuration

## [0.1.0] - 2025-02-01

### Added

- **Initial Laravel Setup**:
    - Laravel 12.x framework installation
    - Laravel Jetstream for authentication scaffolding
    - Laravel Sanctum for API authentication
    - Basic project structure and configuration
- **Development Environment**:
    - Composer dependency management
    - NPM package management
    - Vite for asset compilation
    - Basic styling with TailwindCSS
- **Version Control**:
    - Git repository initialization
    - Basic .gitignore configuration
    - Initial commit with Laravel skeleton

---

## Migration Guide

### From 1.x to 2.x

- **PHP Version**: Ensure PHP 8.2 or higher is installed
- **Laravel Framework**: Update to Laravel 12.x
- **Testing**: Run `php artisan migrate --env=testing` for test database setup
- **Dependencies**: Run `composer update` to update all dependencies

### From 0.x to 1.x

- **Database**: Run migrations to set up core inventory models
- **Seeders**: Execute seeders to populate initial data (countries, languages)
- **API Documentation**: Access OpenAPI documentation at `/docs/api`
- **Authentication**: Generate API tokens for testing purposes

---

## Development Standards

### Code Quality

- **PSR-12 Compliance**: All code follows PSR-12 coding standards
- **Laravel Pint**: Automated code formatting and style checking
- **Comprehensive Testing**: 560+ tests covering all functionality
- **Type Safety**: Proper type hints and return types throughout codebase

### Architecture Principles

- **N-Tier Architecture**: Clear separation between management and consultation layers
- **RESTful Design**: Consistent REST API patterns across all endpoints
- **Event-Driven**: Use of Laravel events for decoupled functionality
- **UUID Standards**: Consistent use of UUIDs for primary keys (except User, Country, Language)

### Security Standards

- **Input Validation**: All inputs validated using Laravel request validation
- **Authentication**: Sanctum-based API authentication
- **Authorization**: Proper middleware and policy implementation
- **SQL Injection Prevention**: Exclusive use of Eloquent ORM for database operations

---

## Contributors

- **Pascal Havelange** - Project Author and Lead Developer
- **Museum With No Frontiers** - Project Sponsor and Requirements Provider

---

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## Links

- **Documentation**: [GitHub Pages](https://metanull.github.io/inventory-app/)
- **API Documentation**: Available at `/docs/api` when running the application
- **Repository**: [GitHub](https://github.com/metanull/inventory-app)
- **Issues**: [GitHub Issues](https://github.com/metanull/inventory-app/issues)
- **Project Management**: [MWNF Jira](https://mwnf.atlassian.net/jira/software/c/projects/MWNF/boards/2)
- **Documentation Wiki**: [MWNF Confluence](https://mwnf.atlassian.net/wiki)
