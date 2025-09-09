---
applyTo: "\**\*.vue"
---

# Vue.js Code Generation and Review Standards

## General Principles
- Strictly comply with Vue.js and Node.js coding standards.
- Organize code in a logical, consistent directory structure.
- Keep code simple, focused, and single-responsibility.
- All code must pass ESLint and Prettier checks; no unused variables or imports.

## Component Structure
- Always use `<script setup lang="ts">` for all Vue Single File Components (SFCs).
- Use `<template>` for markup, `<style scoped>` for styles.
- Prefer the Composition API (`ref`, `computed`, `watch`, etc.) for all logic.
- Type all props, emits, and state; never use the `any` type.

## Component Reuse
- Extract repeated UI patterns into shared components.
- Use slots and props for flexibility and composability.
- Reference and document all shared components in `docs/frontend/components/`.

### Layout Components
- **ListView**: Standard list/table layout (`resources/js/components/layout/list/ListView.vue`)
- **DetailView**: Standard detail/form layout (`resources/js/components/layout/detail/DetailView.vue`)
- **AppHeader**: Application navigation (`resources/js/components/layout/app/AppHeader.vue`)
- **AppFooter**: Application footer (`resources/js/components/layout/app/AppFooter.vue`)

### Card Components (Home/Dashboard)
- **Card**: Base card component (`resources/js/components/format/card/Card.vue`)
- **NavigationCard**: Navigation with call-to-action button
- **StatusCard**: Interactive status toggle with indicators
- **InformationCard**: Static information display with pill badges

### Form Components
- **FormInput**: Styled input with validation support
- **GenericDropdown**: Configurable dropdown with search, priority sorting, default options
- **Toggle**: Boolean toggle switch (regular and compact variants)

### Display Components
- **DisplayText**: Consistent text display with size variants
- **InternalName**: Resource name with icon and legacy ID support (regular and small variants)
- **DateDisplay**: Formatted date/time display
- **Title**: Flexible heading component with variants (page, section, card, system, empty)
- **Uuid**: Formatted UUID display with copy functionality

### Table Components
- **TableElement**: Base table wrapper
- **TableHeader**: Sortable column headers
- **TableRow**: Table row wrapper
- **TableCell**: Table cell with variant support

### Description List Components (Detail Pages)
- **DescriptionList**: Container for structured data
- **DescriptionRow**: Individual data row with alternating colors
- **DescriptionTerm**: Field labels
- **DescriptionDetail**: Field values

## Page Patterns

### Dashboard/Home Page Pattern (`Home.vue`)
- Use `<Title variant="page">` for the main page title with description
- Organize content in themed sections that **MIRROR the navigation menu structure exactly**
- Use grid layouts: `grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6`
- Use section headers with borders: `text-xl font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2`

#### Section Structure (MUST match AppHeader.vue navigation)
**1. Inventory Section:** Items, Partners
**2. Reference Data Section:** Languages, Countries, Contexts, Projects  
**3. Tools Section:** System Status, Additional Features

#### Navigation Tiles
- Use `<NavigationCard>` for each entity with:
  - `title`: Entity name (e.g., "Items", "Contexts")
  - `description`: Clear description of the entity's purpose
  - `main-color`: **Entity-specific color** (see Color Standards below)
  - `button-text`: "Manage [EntityName]"
  - `button-route`: Route to entity list page
  - Icon slot with entity's Heroicon

#### Home.vue ↔ Navigation Menu Consistency
- Each entity that has a tile in Home.vue **MUST** have a corresponding menu item in AppHeader.vue
- Both must use the **same icon** and **same color**
- Section groupings in Home.vue must match dropdown groupings in navigation menu
- Order of items within sections should be consistent between both

### List Page Pattern (`Items.vue`, `Countries.vue`, `Contexts.vue`, etc.)
- Use `<ListView>` component as the main wrapper
- Required props: `title`, `description`, `isEmpty`, `emptyTitle`, `emptyMessage`
- Optional props: `addButtonRoute`, `addButtonLabel`, `color`
- Use slots for: `icon`, `filters`, `search`, `headers`, `rows`, `modals`

#### Required Features for List Pages
**1. Filtering System:**
```typescript
const filterMode = ref<'all' | 'specific_filter'>('all')
const filteredResources = computed(() => {
  let filtered = resources.value
  // Apply filter mode logic
  if (filterMode.value === 'specific_filter') {
    filtered = filtered.filter(resource => resource.some_property)
  }
  // Apply search and sorting
  return filtered
})
```

**2. Sorting System:**
```typescript
const sortKey = ref<string>('internal_name')
const sortDirection = ref<'asc' | 'desc'>('asc')

const handleSort = (key: string) => {
  if (sortKey.value === key) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortKey.value = key
    sortDirection.value = 'asc'
  }
}
```

**3. Search System:**
```typescript
const searchQuery = ref('')
// Apply search in computed filteredResources
if (searchQuery.value.trim()) {
  const query = searchQuery.value.toLowerCase()
  filtered = filtered.filter(resource =>
    resource.internal_name.toLowerCase().includes(query) ||
    (resource.backward_compatibility && resource.backward_compatibility.toLowerCase().includes(query))
  )
}
```

**4. Action Buttons (MANDATORY in every row):**
- `<ViewButton @click="router.push(\`/resources/\${resource.id}\`)" />`
- `<EditButton @click="router.push(\`/resources/\${resource.id}?edit=true\`)" />`
- `<DeleteButton @click="handleDeleteResource(resource)" />`
- Wrap action buttons in `<div class="flex space-x-2" @click.stop>`

**5. Status Toggles (where applicable):**
- Use `<Toggle small>` component for inline status changes
- Common patterns: enabled/disabled, launched/not launched, default/not default
- Wrap in `<div @click.stop>` to prevent row click navigation
- Example: `@toggle="updateResourceStatus(resource, 'is_enabled', !resource.is_enabled)"`

**6. Row Click Navigation:**
- Table rows should be clickable: `class="cursor-pointer hover:bg-[color]-50 transition"`
- Use `@click="openResourceDetail(resource.id)"` for navigation
- Prevent event bubbling on action buttons and toggles with `@click.stop`

**7. Filter Buttons:**
- Use `<FilterButton>` with consistent labeling and count display
- Common patterns: All, Enabled, Disabled, Default, etc.
- Show counts: `:count="enabledResources.length"`
- Use appropriate variants: `variant="primary"`, `variant="info"`, etc.

**8. Store Integration:**
- Fetch resources in `onMounted` with cache-first approach
- Handle delete with confirmation dialogs
- Use loading and error stores for consistent UX

### Detail Page Pattern (`ItemDetail.vue`, `CountryDetail.vue`, `ContextDetail.vue`)
- Use `<DetailView>` component as the main wrapper
- Required props: `storeLoading`, `resource`, `mode`, `backLink`, `informationTitle`
- Optional props: `saveDisabled`, `hasUnsavedChanges`, `statusCards`, `createTitle`, `informationDescription`
- Use slots for: `resource-icon`, `information`
- Information section uses `<DescriptionList>`, `<DescriptionRow>`, `<DescriptionTerm>`, `<DescriptionDetail>`
- Alternate row variants: `variant="gray"` and `variant="white"`
- Form fields use: `<FormInput>`, `<GenericDropdown>` for editing; `<DisplayText>`, `<DateDisplay>` for viewing

#### Mode Handling (CRITICAL - Must be consistent across ALL detail pages)
**Three Modes: `view`, `edit`, `create`**

**1. Mode State Management:**
```typescript
type Mode = 'view' | 'edit' | 'create'
const mode = ref<Mode>('view') // Single source of truth
```

**2. Mode Functions (Required in every detail page):**
```typescript
const enterCreateMode = () => {
  mode.value = 'create'
  editForm.value = getDefaultFormValues()
}

const enterEditMode = () => {
  if (!resource.value) return
  mode.value = 'edit'
  editForm.value = getFormValuesFromResource()
}

const enterViewMode = () => {
  mode.value = 'view'
  editForm.value = getDefaultFormValues() // Clear form data
}
```

**3. Mode-Based Information Description:**
```typescript
const informationDescription = computed(() => {
  switch (mode.value) {
    case 'create': return 'Create a new [resource] in your inventory system.'
    case 'edit': return 'Edit detailed information about this [resource].'
    default: return 'Detailed information about this [resource].'
  }
})
```

**4. Unsaved Changes Detection:**
- Must compare form values with original/default values based on mode
- Must watch for changes and sync with `useCancelChangesConfirmationStore`
- Must handle navigation guards with `onBeforeRouteLeave`

**5. Component Initialization:**
```typescript
const initializeComponent = async () => {
  const resourceId = route.params.id as string
  const isCreateRoute = route.name === '[resource]-new' || route.path === '/[resources]/new'

  if (isCreateRoute) {
    store.clearCurrentResource()
    enterCreateMode()
  } else if (resourceId) {
    await fetchResource()
    if (route.query.edit === 'true' && resource.value) {
      enterEditMode()
    } else {
      enterViewMode()
    }
  }
}
```

#### Required Features for Detail Pages
**1. Pinia Store Integration:**
- Import and use appropriate store (e.g., `useItemStore`, `useContextStore`)
- Import related stores for dropdown options (e.g., `usePartnerStore`, `useProjectStore`)
- Use `store.loading` for loading states
- Use `store.currentResource` for current resource data

**2. Dropdown Options with Computed Properties:**
```typescript
const relatedOptions = computed(() =>
  (relatedStore.items || []).map(item => ({
    id: item.id,
    internal_name: item.internal_name,
    is_default: false,
  }))
)
```

**3. Status Cards Configuration (where applicable):**
- Use `computed` property for dynamic status card configuration
- Handle status toggles with async functions
- Common patterns: enabled/disabled, launched/not launched, default/not default

**4. Form Data Management:**
- Define TypeScript interface for form data
- Implement `getDefaultFormValues()` and `getFormValuesFromResource()` functions
- Use reactive `editForm` ref with proper typing

**5. Error Handling and Loading States:**
- Use `useLoadingOverlayStore` for loading states
- Use `useErrorDisplayStore` for success/error messages
- Use `useDeleteConfirmationStore` for delete confirmations
- Use `useCancelChangesConfirmationStore` for unsaved changes

**6. Navigation and Route Handling:**
- Proper back link configuration with icon and color
- Handle query parameters (e.g., `?edit=true`)
- Navigation guards for unsaved changes protection

## TypeScript
- All components must use TypeScript.
- No use of `any` type; always provide explicit types for props, emits, and state.

## Testing
- All components and pages must have unit tests in `components/__tests__/` or the appropriate test directory.
- Follow the documented testing guidelines in `docs/frontend/guidelines/testing.md`.

## Linting & Formatting
- Code must pass ESLint and Prettier checks before commit.
- No unused variables, imports, or code.

## Documentation
- Every new or changed component/page must be documented in `docs/frontend/components/` or `docs/frontend/guidelines/`.
- Documentation must include usage examples, prop tables, and clear explanations of component purpose and patterns.

## State Management and Reactivity
- Use Vue 3 Composition API: `ref`, `computed`, `watch`, `onMounted`, etc.
- Import composables from `@/composables/` for shared logic (e.g., `useApiStatus`)
- Store management: Import and use Pinia stores for data persistence
- Route management: Use `useRoute()` and `useRouter()` from Vue Router

## Styling and Theme Consistency
- Use Tailwind CSS classes for all styling
- Responsive design: Use Tailwind responsive prefixes (`sm:`, `md:`, `lg:`, `xl:`)
- Spacing: Use Tailwind spacing classes for consistent margins and padding

### Entity Color Standards (CRITICAL - Must be consistent across all components)
Each entity has a **unique color** that must be used consistently in:
- Home.vue navigation cards (`main-color` prop)
- AppHeader.vue navigation menu (icon color classes)
- List pages (`color` prop and icon colors)
- Detail pages (icon colors)
- All related components

**Entity Color Mapping:**
- **Items**: `teal` / `text-teal-600` / `bg-teal-*` / `hover:bg-teal-50`
- **Partners**: `yellow` / `text-yellow-600` / `bg-yellow-*` / `hover:bg-yellow-50`  
- **Languages**: `purple` / `text-purple-600` / `bg-purple-*` / `hover:bg-purple-50`
- **Countries**: `blue` / `text-blue-600` / `bg-blue-*` / `hover:bg-blue-50`
- **Contexts**: `green` / `text-green-600` / `bg-green-*` / `hover:bg-green-50`
- **Projects**: `orange` / `text-orange-600` / `bg-orange-*` / `hover:bg-orange-50`

### Icon Usage (CRITICAL - NO exceptions)
**FORBIDDEN:**
- ❌ Inline SVG code in components
- ❌ Creating separate components just for SVG icons
- ❌ Custom SVG files or icon libraries

**REQUIRED:**
- ✅ **ONLY use Heroicons**: Import from `@heroicons/vue/24/solid` or `@heroicons/vue/24/outline`
- ✅ Import icons directly in the component where they're used
- ✅ Use icon aliases for semantic naming: `import { CogIcon as ContextIcon } from '@heroicons/vue/24/solid'`

**Entity Icon Mapping (MUST be consistent):**
- **Items**: `ArchiveBoxIcon` (from 24/solid)
- **Partners**: `UserGroupIcon` (from 24/solid)
- **Languages**: `LanguageIcon` (from 24/outline)
- **Countries**: `GlobeAltIcon` (from 24/outline) 
- **Contexts**: `CogIcon` (from 24/outline or 24/solid)
- **Projects**: `FolderIcon` (from 24/outline or 24/solid)

**Icon Size Standards:**
- Navigation menu: `w-4 h-4`
- List page icons: `h-5 w-5` 
- Detail page resource icons: `h-6 w-6`
- Home page card icons: Handled by Card component (responsive sizing)

## Event Handling and Navigation
- Use `@click.stop` to prevent event bubbling in nested clickable elements
- Router navigation: Use `router.push()` for programmatic navigation
- Event emits: Define emits with TypeScript interfaces when needed
- Form handling: Use `v-model` for two-way binding with form inputs

## Navigation and Menu Integration
When adding a new entity, you **MUST** update both:

### 1. Home.vue Dashboard Tile
```vue
<!-- In appropriate section (Inventory/Reference Data) -->
<NavigationCard
  title="EntityName"
  description="Clear description of entity purpose"
  main-color="entity-color"
  button-text="Manage EntityName"
  button-route="/entities"
>
  <template #icon>
    <EntityIcon />
  </template>
</NavigationCard>
```

### 2. AppHeader.vue Navigation Menu
```vue
<!-- In appropriate dropdown section -->
<RouterLink
  to="/entities"
  class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2"
  @click="closeDropdown"
>
  <EntityIcon class="w-4 h-4 text-entity-600" />
  EntityName
</RouterLink>
```

## Review Checklist for Copilot Agents
- [ ] Component structure matches project standards (`<template>`, `<script setup lang="ts">`, `<style scoped>`)
- [ ] Uses appropriate layout component (ListView for lists, DetailView for details, Cards for dashboard)
- [ ] Reuse of shared components is maximized (no duplicate UI patterns)
- [ ] Page layout and detail patterns are consistent with existing pages
- [ ] **Entity colors are consistent** across Home.vue, AppHeader.vue, list pages, and detail pages
- [ ] **Icons are Heroicons only** - no inline SVG or custom icon components
- [ ] **Navigation consistency**: Home.vue tiles match AppHeader.vue menu structure exactly
- [ ] **List page features**: Filtering, sorting, search, action buttons, status toggles implemented
- [ ] **Detail page mode handling**: Three modes (view/edit/create) with proper state management
- [ ] TypeScript is used everywhere, with no `any` types
- [ ] Proper imports from `@/components/`, `@/composables/`, etc.
- [ ] Event handling follows established patterns (`@click.stop` for nested clickables)
- [ ] All code passes linting and formatting (ESLint + Prettier)
- [ ] Unit tests exist and follow guidelines in `__tests__/` directories
- [ ] Documentation is present and comprehensive in `docs/frontend/`
