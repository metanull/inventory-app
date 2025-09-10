---
layout: default
title: Application Architecture
parent: Frontend Documentation
nav_order: 2
---

# Application Architecture

## Overview

A Vue.js 3 + TypeScript application for managing inventories of items, specifically designed for museums and cultural institutions. The application provides comprehensive CRUD operations for reference data (contexts, countries, languages, projects), and museum data (items, collections, images).

## Technology Stack

- **Frontend**: Vue.js 3.5.14 with Composition API (`<script setup>`)
- **Build Tool**: Vite
- **Language**: TypeScript (strict mode)
- **Styling**: Tailwind CSS
- **Icons**: Heroicons v2
- **State Management**: Pinia 3.0.3
- **Routing**: Vue Router 4.5.0
- **HTTP Client**: Axios 1.7.9
- **API Client**: @metanull/inventory-app-api-client v1.1.19-dev.715.1753
- **Testing**: Vitest with comprehensive unit and integration tests
- **Code Quality**: ESLint, Prettier, Husky pre-commit hooks

## Application Structure

```
src/
├── components/           # Reusable UI components
│   ├── format/          # Data formatting components
│   ├── global/          # Global overlay components
│   ├── icons/           # Resource-specific icons
│   └── layout/          # Layout and structural components
├── composables/         # Vue composition functions
├── router/              # Vue Router configuration
├── stores/              # Pinia state management stores
├── types/               # TypeScript type definitions
├── utils/               # Utility functions
└── views/               # Page components
```

## Authentication System

### Implementation

- **Storage**: Local Storage (`auth_token` key)
- **API Integration**: Uses `MobileAppAuthenticationApi` from the client library
- **Protection**: Route guards prevent unauthorized access
- **State Management**: Centralized in `auth.ts` Pinia store

### Features

- Token-based authentication with automatic localStorage persistence
- Route-level protection with redirect to `/login`
- Automatic token attachment to API requests
- Logout functionality with token cleanup

## Application Template & Layout

### App.vue Structure

```vue
<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <AppHeader />
    <!-- Navigation and user menu -->
    <ErrorDisplay />
    <!-- Global error notifications -->
    <main>
      <RouterView />
      <!-- Page content -->
    </main>
    <AppFooter />
    <!-- Application footer -->
    <LoadingOverlay />
    <!-- Global loading spinner -->
    <DeleteConfirmation />
    <!-- Delete confirmation modals -->
    <CancelChangesConfirmation />
    <!-- Unsaved changes protection -->
  </div>
</template>
```

### Header Features

- Responsive navigation menu
- User authentication status display
- Resource navigation dropdown
- Mobile-friendly collapsible menu

### Footer

- Basic application information
- Consistent styling across all pages

## Navigation System

### Main Navigation Structure

The application uses a **two-tier navigation system** that must be kept in sync:

#### 1. AppHeader.vue Navigation Menu

Dropdown-based navigation with grouped sections:

- **Inventory**: Items, Partners
- **Reference Data**: Languages, Countries, Contexts, Projects
- **Tools**: Cache management and system utilities

#### 2. Home.vue Dashboard Tiles

Landing page with navigation cards organized identically to the header menu:

- **Inventory Section**: Items, Partners
- **Reference Data Section**: Languages, Countries, Contexts, Projects
- **Tools Section**: System Status, Additional Features

**CRITICAL**: These two navigation systems must mirror each other exactly. Each entity with a dashboard tile must have a corresponding menu item using the same icon and color.

### Entity Standards

Each entity has standardized presentation across all components:

| Entity    | Color  | Icon           | Route        |
| --------- | ------ | -------------- | ------------ |
| Items     | teal   | ArchiveBoxIcon | `/items`     |
| Partners  | yellow | UserGroupIcon  | `/partners`  |
| Languages | purple | LanguageIcon   | `/languages` |
| Countries | blue   | GlobeAltIcon   | `/countries` |
| Contexts  | green  | CogIcon        | `/contexts`  |
| Projects  | orange | FolderIcon     | `/projects`  |

### Routing Configuration

- Protected routes requiring authentication
- RESTful URL patterns (`/resources/:id`)
- Support for create operations (`/projects/new`)
- Automatic redirection for unauthenticated users

## Global Components

### Error Display (`ErrorDisplay.vue`)

- Centralized error notification system
- Pinia store integration (`errorDisplay.ts`)
- Dismissible error messages
- Consistent error formatting

### Loading Overlay (`LoadingOverlay.vue`)

- Full-screen loading indicator
- Pinia store integration (`loadingOverlay.ts`)
- Non-blocking overlay design
- Spinner animation

### Confirmation Modals

- **DeleteConfirmation**: Resource deletion confirmation
- **CancelChangesConfirmation**: Unsaved changes protection
- Modal overlay with backdrop click handling
- Keyboard navigation support

## Project Resource Implementation (Reference Implementation)

### Projects List View (`Projects.vue`)

#### Features

- **Responsive Table Layout**: Desktop table, mobile-friendly cards
- **Advanced Filtering**:
  - All Projects, Enabled, Launched, Visible filters
  - Filter counts and dynamic labels
  - URL state preservation
- **Search Functionality**: Real-time search across project fields
- **Column Sorting**: Sortable by internal name, creation date
- **Status Management**: Toggle enabled/launched status inline
- **Bulk Operations**: Multi-select with bulk actions
- **Empty States**: Contextual messages based on active filter
- **Loading States**: Skeleton loaders and loading indicators

#### UI Components Used

- `ListView`: Base layout component
- `FilterButton`: Status filter controls
- `SearchControl`: Search input with debouncing
- `TableHeader`: Sortable column headers
- `Toggle`: Status toggle switches
- Action buttons (View/Edit/Delete)

### Project Detail View (`ProjectDetail.vue`)

#### Three-Mode System

1. **View Mode**: Read-only display with action buttons
2. **Edit Mode**: Inline editing with save/cancel
3. **Create Mode**: New project creation form

#### Features

- **Status Cards**: Visual status indicators (enabled/launched/visible)
- **Inline Editing**: Field-by-field editing without navigation
- **Unsaved Changes Protection**: Automatic detection and confirmation
- **Form Validation**: Real-time validation with error display
- **Responsive Layout**: Mobile-optimized forms and actions
- **Navigation Integration**: Back navigation with state preservation

#### Form Fields

- Internal Name (required)
- Legacy ID (backward compatibility)
- Status toggles (enabled/launched)
- Launch date management
- Metadata (created/updated timestamps)

## Reference Data Resources (Projects, Contexts, Countries, Languages)

### Implementation Status: ✅ **All Four Resources Fully Implemented**

All reference data resources now use **consistent ListView/DetailView architecture** with standardized patterns across the application.

### Unified Features (Implemented Across All Resources)

1. **Consistent UI**: ListView component with responsive table → card layouts
2. **Advanced Functionality**: Search, filtering, sorting with smart caching
3. **Mobile Support**: Responsive design optimized for all screen sizes
4. **Inline Editing**: Three-mode DetailView system (view/edit/create)
5. **Loading States**: Smart loading with background refresh and descriptive messages
6. **Error Handling**: Centralized error management with user-friendly messages
7. **State Management**: Advanced Pinia stores with reactive updates

### Resource-Specific Implementations

#### Projects (`Projects.vue` → `ProjectDetail.vue`)

**Status**: ✅ **Complete Implementation**

- **Filtering**: All/Enabled/Launched/Visible filters with dynamic counts
- **Status Management**: Toggle enabled/launched states with visual indicators
- **Advanced Features**: Launch date management, default context/language assignment
- **Form Fields**: Internal name, legacy ID, status toggles, launch date, metadata

#### Contexts (`Contexts.vue` → `ContextDetail.vue`)

**Status**: ✅ **Complete Implementation**

- **Filtering**: All/Default contexts with filter counts
- **Status Management**: Default context toggle with system-wide impact
- **Advanced Features**: Backward compatibility ID management
- **Form Fields**: Internal name, legacy ID, default status toggle

#### Countries (`Countries.vue` → `CountryDetail.vue`)

**Status**: ✅ **Complete Implementation**

- **Search**: Real-time search across country names and codes
- **Geographic Data**: ISO country code management
- **Form Validation**: Country name and code validation
- **Form Fields**: Internal name, legacy ID, metadata

#### Languages (`Languages.vue` → `LanguageDetail.vue`)

**Status**: ✅ **Complete Implementation**

- **Filtering**: All/Default languages with filter counts
- **Status Management**: Default language toggle for application-wide settings
- **Locale Support**: Language code and locale management
- **Form Fields**: Internal name, legacy ID, default status toggle

## API Integration & Client Library

### API Client Configuration

```typescript
// Base configuration pattern used across all stores
const createApiClient = () => {
  const baseURL =
    import.meta.env.VITE_API_BASE_URL || "http://127.0.0.1:8000/api";
  const configuration = new Configuration({
    basePath: baseURL,
    accessToken: authStore.token,
  });
  return new ResourceApi(configuration);
};
```

### API-to-Application Feature Mapping

#### Authentication API (`MobileAppAuthenticationApi`)

| API Feature      | Application Usage                             |
| ---------------- | --------------------------------------------- |
| `tokenAcquire()` | Login functionality in auth store             |
| `tokenRefresh()` | **Not implemented** - Manual token management |
| `tokenRevoke()`  | Logout functionality                          |

#### Project API (`ProjectApi`)

| API Feature                            | Application Usage                             |
| -------------------------------------- | --------------------------------------------- |
| `projectIndex()`                       | Projects list view data loading               |
| `projectStore()`                       | Create new project functionality              |
| `projectShow()`                        | Project detail view data loading              |
| `projectUpdate()`                      | Project editing and saving                    |
| `projectDestroy()`                     | Project deletion with confirmation            |
| `projectSetEnabled()`                  | Toggle project enabled status                 |
| `projectSetLaunched()`                 | Toggle project launched status                |
| **Missing**: `projectBulkOperations()` | **Not implemented** - Bulk project operations |
| **Missing**: `projectExport()`         | **Not implemented** - Project data export     |

#### Context API (`ContextApi`)

| API Feature            | Application Usage                               |
| ---------------------- | ----------------------------------------------- |
| `contextIndex()`       | Context list loading with caching               |
| `contextStore()`       | Create context via DetailView three-mode system |
| `contextShow()`        | Context detail view data loading                |
| `contextUpdate()`      | Update context via inline editing               |
| `contextDestroy()`     | Delete context with confirmation                |
| `contextSetDefault()`  | Set default context functionality               |
| **Advanced filtering** | ✅ **Implemented** - Default context filtering  |

#### Country API (`CountryApi`)

| API Feature              | Application Usage                                     |
| ------------------------ | ----------------------------------------------------- |
| `countryIndex()`         | Country list loading with smart caching               |
| `countryStore()`         | Create country via DetailView system                  |
| `countryShow()`          | Country detail view data loading                      |
| `countryUpdate()`        | Update country via inline editing                     |
| `countryDestroy()`       | Delete country with confirmation                      |
| **Search functionality** | ✅ **Implemented** - Real-time country search         |
| **Form validation**      | ✅ **Implemented** - Country name and code validation |

#### Language API (`LanguageApi`)

| API Feature            | Application Usage                               |
| ---------------------- | ----------------------------------------------- |
| `languageIndex()`      | Language list loading with caching              |
| `languageStore()`      | Create language via DetailView system           |
| `languageShow()`       | Language detail view data loading               |
| `languageUpdate()`     | Update language via inline editing              |
| `languageDestroy()`    | Delete language with confirmation               |
| `languageSetDefault()` | Set default language functionality              |
| **Advanced filtering** | ✅ **Implemented** - Default language filtering |

#### System Information API (`InfoApi`)

| API Feature     | Application Usage                              |
| --------------- | ---------------------------------------------- |
| `infoSystem()`  | API status monitoring in composables           |
| `infoHealth()`  | **Not implemented** - Health check integration |
| `infoVersion()` | **Not implemented** - Version display          |

#### Partner API (`PartnerApi`)

| API Status             | Application Status                        |
| ---------------------- | ----------------------------------------- |
| ✅ Available in client | ❌ **Not implemented** - Planned resource |

#### Item API (`ItemApi`)

| API Status             | Application Status                              |
| ---------------------- | ----------------------------------------------- |
| ✅ Available in client | ❌ **Not implemented** - Core inventory feature |

#### Tag API (`TagApi`)

| API Status             | Application Status                           |
| ---------------------- | -------------------------------------------- |
| ✅ Available in client | ❌ **Not implemented** - Metadata management |

#### Picture API (`PictureApi`)

| API Status             | Application Status                        |
| ---------------------- | ----------------------------------------- |
| ✅ Available in client | ❌ **Not implemented** - Media management |

## Component Architecture

### Layout Components

#### ListView Component

**Purpose**: Standardized list view layout for resource collections
**Features**:

- Responsive design (table → cards on mobile)
- Integrated search functionality
- Filter button integration
- Empty state handling
- Loading state management
- Action button placement
- Add button integration

#### DetailView Component

**Purpose**: Standardized detail view for individual resources
**Features**:

- Three-mode system (view/edit/create)
- Status card integration
- Unsaved changes detection
- Form validation integration
- Responsive layout
- Navigation integration
- Action button management

### Format Components

- `DisplayText`: Consistent text display with formatting
- `FormInput`: Standardized form inputs with validation
- `DescriptionList`: Key-value pair display layout
- `DateDisplay`: Consistent date formatting
- `UuidDisplay`: UUID formatting with copy functionality
- `Toggle`: Status toggle switches with variants

### Global Components

- `StatusCard`: Resource status indicators
- `SearchControl`: Debounced search input
- `FilterButton`: Resource filtering controls
- Action buttons (View/Edit/Delete/Save/Cancel)

## State Management (Pinia Stores)

### Store Pattern

All stores follow consistent patterns:

- Reactive state with `ref()`
- Computed properties for derived state
- Async actions with error handling
- Loading state management
- Centralized API client configuration

### Store Implementations

#### Project Store (`project.ts`)

**Status**: ✅ **Complete Implementation**

- Full CRUD operations with status management (enabled/launched/visible)
- Advanced filtering and computed filtered lists
- Caching with reactive updates and loading states
- Comprehensive error handling

#### Context Store (`context.ts`)

**Status**: ✅ **Complete Implementation**

- Full CRUD operations with default context management
- Smart caching with background refresh patterns
- Reactive state updates and loading optimization
- Centralized error handling

#### Country Store (`country.ts`)

**Status**: ✅ **Complete Implementation**

- Full CRUD operations with ISO country code support
- Reactive state management with caching
- Loading states and error handling
- Search functionality integration

#### Language Store (`language.ts`)

**Status**: ✅ **Complete Implementation**

- Full CRUD operations with default language management
- Advanced caching and reactive updates
- Loading state optimization and error handling
- Filter and search integration

#### Auth Store (`auth.ts`)

**Status**: ✅ **Complete Implementation**

- Token management with localStorage persistence
- Authentication state tracking
- API client configuration
- Login/logout functionality

### Global Feature Stores

- `errorDisplay.ts`: Centralized error notification management
- `loadingOverlay.ts`: Global loading state management
- `deleteConfirmation.ts`: Delete confirmation modal state
- `cancelChangesConfirmation.ts`: Unsaved changes protection

## Git Commit History Summary

### Migration to External API Client (feature/migrate-to-external-api-client)

#### Initial Setup & Authentication (c05beb7 → ea3ff89)

- Setup GitHub packages authentication for `@metanull/inventory-app-api-client`
- Replaced custom auth client with official API client library
- Removed all non-authentication features to establish clean baseline

#### Core Resource Implementation (6e0c8b1 → e800942)

- Implemented Language API CRUD operations with TypeScript
- Added Country API features with Vue 3 + Pinia integration
- Implemented Context API with CRUD operations and default management
- Added Project API integration with comprehensive CRUD and translation management

#### UI Architecture Refactoring (a7eceb0 → 50c2243)

- Replaced grid/tile layouts with consistent table-based layouts
- Implemented responsive navigation with Reference Data dropdown
- Created reusable StatusCard and action button components
- Developed comprehensive UI refactor for better UX and maintainability

#### Advanced Component Development (edca074 → abc452e)

- Created reusable list view components and refactored Projects view
- Implemented reusable UI components with improved loading states
- Added comprehensive detail view sections with better field visibility
- Integrated consistent ResourceNameDisplay across all list views

#### Layout Standardization (7851fa6 → 8948954)

- Moved DetailLayout to layout folder, renamed to DetailView
- Removed ProjectEditForm.vue in favor of inline editing
- Fixed new project creation blank page issues
- Resolved component refactoring side effects

#### Documentation & Code Quality (c291592 → 1a1c49b)

- Updated API references to use @metanull/inventory-app-api-client
- Reorganized component files for consistency
- Implemented API status monitoring with StatusCard integration
- Added unsaved changes tracking for detail views

#### Advanced Features Implementation (0f8f80c → a2e6d42)

- Enhanced Projects view with cached list for faster loading
- Added 'back' navigation feature on DetailView
- Improved ListView and Projects UI consistency
- Updated API client to v1.1.19-dev.715.1753 with alignment improvements

#### Interactive Features & Testing (2940d2f → 663d8c6)

- Replaced Status column with three separate toggle columns
- Created ToggleSmall component for table display
- Added reusable GenericDropdown component
- Comprehensive component documentation for GitHub Pages

#### Advanced UI Components (524e9f8 → 57de204)

- Reorganized layout components and added reusable components
- Fixed DetailView component creation mode and navigation issues
- Migrated DetailViewHeader into DetailView
- Implemented centralized global features with Pinia stores

#### Single Mode State Pattern (98fcc74 → 929a2a1)

- Implemented single mode state pattern for ProjectDetail and DetailView
- Added comprehensive Global components documentation
- Enhanced unsaved changes protection logic
- Centralized modal overlay components

#### Search & Filter Features (83b1d30 → 544a68c)

- Added search functionality to list views with SearchControl component
- Implemented Projects list view search functionality
- Made ListView filter bar adaptive for mobile devices
- Added comprehensive test suites for format components

#### Icon Standardization & Testing (1eb4427 → eb06da8)

- Replaced custom icons with HeroIcons throughout application
- Implemented comprehensive test suites for ListView and DetailView components
- Fixed event warnings for backgroundClick in confirmation modals
- Made table rows open ProjectDetail.vue with proper ToggleSmall behavior

#### Code Quality & Documentation (3d50f81 → cde10c2)

- Refactored Toggle and InternalName components for consistency
- Eliminated code duplication in Toggle component
- Enhanced DetailView components with comprehensive store initialization
- Achieved comprehensive linting compliance
- Updated comprehensive documentation reflecting current project state

## Comparison: Implementation Status Across All Resources

### Implementation Maturity

| Feature                  | Projects               | Contexts               | Countries              | Languages              |
| ------------------------ | ---------------------- | ---------------------- | ---------------------- | ---------------------- |
| **Layout Component**     | ✅ ListView/DetailView | ✅ ListView/DetailView | ✅ ListView/DetailView | ✅ ListView/DetailView |
| **Three-Mode System**    | ✅ View/Edit/Create    | ✅ View/Edit/Create    | ✅ View/Edit/Create    | ✅ View/Edit/Create    |
| **Search Functionality** | ✅ Real-time search    | ✅ Real-time search    | ✅ Real-time search    | ✅ Real-time search    |
| **Advanced Filtering**   | ✅ 4 filter types      | ✅ Default filtering   | ❌ No specific filters | ✅ Default filtering   |
| **Responsive Design**    | ✅ Mobile optimized    | ✅ Mobile optimized    | ✅ Mobile optimized    | ✅ Mobile optimized    |
| **Inline Editing**       | ✅ Full inline editing | ✅ Full inline editing | ✅ Full inline editing | ✅ Full inline editing |
| **Unsaved Changes**      | ✅ Protection enabled  | ✅ Protection enabled  | ✅ Protection enabled  | ✅ Protection enabled  |
| **Status Management**    | ✅ Toggle controls     | ✅ Default toggle      | ❌ No status fields    | ✅ Default toggle      |
| **Loading States**       | ✅ Smart caching       | ✅ Smart caching       | ✅ Smart caching       | ✅ Smart caching       |
| **Error Handling**       | ✅ Centralized         | ✅ Centralized         | ✅ Centralized         | ✅ Centralized         |
| **State Management**     | ✅ Advanced Pinia      | ✅ Advanced Pinia      | ✅ Advanced Pinia      | ✅ Advanced Pinia      |
| **Testing Coverage**     | ✅ Comprehensive       | ✅ Comprehensive       | ✅ Comprehensive       | ✅ Comprehensive       |

### Architectural Consistency Achievement ✅

All four core resources now demonstrate **complete architectural alignment**:

1. **Unified UI Patterns**: All resources use ListView/DetailView components
2. **Consistent Interactions**: Three-mode system implemented across all resources
3. **Responsive Design**: Mobile-optimized layouts for all resource types
4. **Smart Caching**: Background refresh patterns reduce loading perception
5. **Error Management**: Centralized error handling with consistent user feedback
6. **State Consistency**: Advanced Pinia store patterns across all resources

### Completed Implementation Goals

#### ✅ High Priority Features (Complete)

- **Layout Migration**: All resources migrated to ListView/DetailView components
- **Mode System**: Three-mode (view/edit/create) pattern implemented everywhere
- **Responsive Design**: Mobile-optimized layouts across all resources
- **Search Integration**: SearchControl components integrated in all list views
- **Inline Editing**: Modal forms completely replaced with inline editing

#### ✅ Enhanced Features (Complete)

- **Advanced Filtering**: Context/Language default filters implemented
- **State Management**: All stores upgraded to match advanced Project store patterns
- **Loading States**: Comprehensive loading and error states implemented
- **Form Validation**: Real-time validation and error display across all resources

#### ✅ Quality Improvements (Complete)

- **Testing Coverage**: Comprehensive unit and integration tests for all resources
- **Performance**: Caching and reactive updates implemented
- **Consistency**: Unified user experience across all resource management

## Future Development Priorities

### Next Phase: Core Inventory Features

1. **Items**: Primary inventory management with metadata and media
2. **Partners**: Institution relationship management
3. **Collections**: Grouping and organization features
4. **Tags**: Advanced metadata and categorization

### Long-Term Enhancements

1. **Accessibility**: ARIA labels and enhanced keyboard navigation
2. **Internationalization**: Multi-language support integration
3. **Advanced Search**: Cross-resource search capabilities
4. **Bulk Operations**: Multi-select actions across all resources

---

_Generated on: July 23, 2025_  
_Branch: feature/migrate-to-external-api-client_  
_API Client Version: @metanull/inventory-app-api-client@1.1.19-dev.715.1753_
