---
layout: default
title: Page Patterns
parent: Frontend Documentation
nav_order: 3
---

# Vue.js Page Patterns

This guide documents the standardized patterns for creating consistent pages across the entire Vue.js application.

## Overview

All pages in the application follow three main patterns:

- **Dashboard/Home Page**: Navigation hub with entity tiles
- **List Pages**: Resource management with filtering, sorting, and actions
- **Detail Pages**: Individual resource viewing, editing, and creation

## Dashboard/Home Page Pattern

The `Home.vue` page serves as the main navigation hub and **must mirror the navigation menu structure exactly**.

### Section Structure

Organize content in themed sections that match `AppHeader.vue` navigation dropdowns:

1. **Inventory Section**: Items, Partners
2. **Reference Data Section**: Languages, Countries, Contexts, Projects
3. **Tools Section**: System Status, Additional Features

### Implementation

```vue
<template>
  <div>
    <div class="mb-8">
      <Title
        variant="page"
        description="Welcome to the Inventory Management System"
      >
        Dashboard
      </Title>
    </div>

    <!-- Section Example -->
    <div class="mb-8">
      <h2
        class="text-xl font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2"
      >
        Reference Data
      </h2>
      <div
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
      >
        <NavigationCard
          title="Contexts"
          description="Manage system contexts and operational environments"
          main-color="green"
          button-text="Manage Contexts"
          button-route="/contexts"
        >
          <template #icon>
            <ContextIcon />
          </template>
        </NavigationCard>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { CogIcon as ContextIcon } from "@heroicons/vue/24/solid";
import NavigationCard from "@/components/format/card/NavigationCard.vue";
import Title from "@/components/format/title/Title.vue";

// Example showing useColors composable usage
// import { useColors } from '@/composables/useColors'
// const colorClasses = useColors('green')
</script>
```

### Navigation Consistency Requirements

- Each entity with a tile in `Home.vue` **MUST** have a corresponding menu item in `AppHeader.vue`
- Both must use the **same icon** and **same entity color**
- Section groupings must be identical between Home and navigation menu
- Item order within sections should be consistent

## List Page Pattern

List pages display collections of resources with comprehensive management features.

### Core Structure

```vue
<template>
  <ListView
    title="Contexts"
    description="Manage contexts in your inventory system"
    add-button-route="/contexts/new"
    add-button-label="Add Context"
    color="green"
    :is-empty="filteredContexts.length === 0"
    empty-title="No contexts found"
    empty-message="Get started by creating a new context."
    @retry="fetchContexts"
  >
    <template #icon>
      <ContextIcon />
    </template>

    <template #filters>
      <FilterButton
        label="All Contexts"
        :is-active="filterMode === 'all'"
        :count="contexts.length"
        variant="primary"
        @click="filterMode = 'all'"
      />
      <FilterButton
        label="Default"
        :is-active="filterMode === 'default'"
        :count="defaultContexts.length"
        variant="success"
        @click="filterMode = 'default'"
      />
    </template>

    <template #search>
      <SearchControl v-model="searchQuery" placeholder="Search contexts..." />
    </template>

    <template #headers>
      <TableRow>
        <TableHeader
          sortable
          :sort-direction="sortKey === 'internal_name' ? sortDirection : null"
          @sort="handleSort('internal_name')"
        >
          Context
        </TableHeader>
        <TableHeader class="hidden md:table-cell">Default</TableHeader>
        <TableHeader class="hidden lg:table-cell">Created</TableHeader>
        <TableHeader class="hidden sm:table-cell" variant="actions">
          <span class="sr-only">Actions</span>
        </TableHeader>
      </TableRow>
    </template>

    <template #rows>
      <TableRow
        v-for="context in filteredContexts"
        :key="context.id"
        class="cursor-pointer hover:bg-green-50 transition"
        @click="openContextDetail(context.id)"
      >
        <TableCell>
          <InternalName
            small
            :internal-name="context.internal_name"
            :backward-compatibility="context.backward_compatibility"
          >
            <template #icon>
              <!-- Prefer using the centralized color helper -->
              <!-- Script example: -->
              <!-- import { useColors } from '@/composables/useColors' -->
              <!-- const colorClasses = useColors('green') -->
              <!-- Template example: -->
              <!-- <ContextIcon :class="['h-5 w-5', colorClasses.icon]" /> -->
              <ContextIcon :class="['h-5 w-5', colorClasses.icon]" />
            </template>
          </InternalName>
        </TableCell>
        <TableCell class="hidden md:table-cell">
          <div @click.stop>
            <Toggle
              small
              title="Default"
              :status-text="context.is_default ? 'Default' : 'Not default'"
              :is-active="context.is_default"
              @toggle="
                updateContextStatus(context, 'is_default', !context.is_default)
              "
            />
          </div>
        </TableCell>
        <TableCell class="hidden lg:table-cell">
          <DateDisplay
            :date="context.created_at"
            format="short"
            variant="small-dark"
          />
        </TableCell>
        <TableCell class="hidden sm:table-cell">
          <div class="flex space-x-2" @click.stop>
            <ViewButton @click="router.push(`/contexts/${context.id}`)" />
            <EditButton
              @click="router.push(`/contexts/${context.id}?edit=true`)"
            />
            <DeleteButton @click="handleDeleteContext(context)" />
          </div>
        </TableCell>
      </TableRow>
    </template>
  </ListView>
</template>
```

### Required Features

#### 1. Filtering System

```typescript
const filterMode = ref<"all" | "default">("all");

const filteredContexts = computed(() => {
  let filtered = contexts.value;

  // Apply filter mode
  if (filterMode.value === "default") {
    filtered = filtered.filter((context) => context.is_default);
  }

  // Apply search and sorting
  return filtered;
});
```

#### 2. Sorting System

```typescript
const sortKey = ref<string>("internal_name");
const sortDirection = ref<"asc" | "desc">("asc");

const handleSort = (key: string) => {
  if (sortKey.value === key) {
    sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
  } else {
    sortKey.value = key;
    sortDirection.value = "asc";
  }
};
```

#### 3. Search System

```typescript
const searchQuery = ref("");

// In computed filteredContexts:
if (searchQuery.value.trim()) {
  const query = searchQuery.value.toLowerCase();
  filtered = filtered.filter(
    (context) =>
      context.internal_name.toLowerCase().includes(query) ||
      (context.backward_compatibility &&
        context.backward_compatibility.toLowerCase().includes(query)),
  );
}
```

#### 4. Action Buttons (MANDATORY)

Every list page row must include:

- `<ViewButton @click="router.push(\`/contexts/\${context.id}\`)" />`
- `<EditButton @click="router.push(\`/contexts/\${context.id}?edit=true\`)" />`
- `<DeleteButton @click="handleDeleteContext(context)" />`

Wrap in `<div class="flex space-x-2" @click.stop>` to prevent row click propagation.

#### 5. Status Toggles (where applicable)

Use `<Toggle small>` for inline status changes:

```vue
<div @click.stop>
  <Toggle
    small
    title="Default"
    :status-text="context.is_default ? 'Default' : 'Not default'"
    :is-active="context.is_default"
    @toggle="updateContextStatus(context, 'is_default', !context.is_default)"
  />
</div>
```

#### 6. Row Click Navigation

- Make rows clickable: `class="cursor-pointer hover:bg-green-50 transition"`
- Use `@click="openContextDetail(context.id)"` for navigation
- Prevent event bubbling on actions with `@click.stop`

## Detail Page Pattern

Detail pages handle viewing, editing, and creating individual resources.

### Core Structure

```vue
<template>
  <DetailView
    :store-loading="contextStore.loading"
    :resource="mode === 'create' ? null : context"
    :mode="mode"
    :save-disabled="!hasUnsavedChanges"
    :has-unsaved-changes="hasUnsavedChanges"
    :back-link="backLink"
    :status-controls="statusControlsConfig"
    :create-title="'New Context'"
    information-title="Context Information"
    :information-description="informationDescription"
    :fetch-data="fetchContext"
    @edit="enterEditMode"
    @save="saveContext"
    @cancel="cancelAction"
    @delete="deleteContext"
    @status-toggle="handleStatusToggle"
  >
    <template #resource-icon>
      <CogIcon class="h-6 w-6 text-green-600" />
    </template>

    <template #information>
      <DescriptionList>
        <DescriptionRow variant="gray">
          <DescriptionTerm>Internal Name</DescriptionTerm>
          <DescriptionDetail>
            <FormInput
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.internal_name"
              type="text"
            />
            <DisplayText v-else>{{ context?.internal_name }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>
        <!-- More rows with alternating variants... -->
      </DescriptionList>
    </template>
  </DetailView>
</template>
```

### Mode Handling (CRITICAL)

All detail pages must implement exactly three modes:

```typescript
type Mode = "view" | "edit" | "create";
const mode = ref<Mode>("view"); // Single source of truth

// Required mode functions:
const enterCreateMode = () => {
  mode.value = "create";
  editForm.value = getDefaultFormValues();
};

const enterEditMode = () => {
  if (!context.value) return;
  mode.value = "edit";
  editForm.value = getFormValuesFromContext();
};

const enterViewMode = () => {
  mode.value = "view";
  editForm.value = getDefaultFormValues(); // Clear form data
};
```

### Component Initialization Pattern

```typescript
const initializeComponent = async () => {
  const contextId = route.params.id as string;
  const isCreateRoute =
    route.name === "context-new" || route.path === "/contexts/new";

  if (isCreateRoute) {
    contextStore.clearCurrentContext();
    enterCreateMode();
  } else if (contextId) {
    await fetchContext();
    if (route.query.edit === "true" && context.value) {
      enterEditMode();
    } else {
      enterViewMode();
    }
  }
};

onMounted(initializeComponent);
```

### Required Features

#### 1. Form Data Management

```typescript
interface ContextFormData {
  id?: string;
  internal_name: string;
  backward_compatibility: string;
}

const editForm = ref<ContextFormData>({
  id: "",
  internal_name: "",
  backward_compatibility: "",
});

const getDefaultFormValues = (): ContextFormData => ({
  id: "",
  internal_name: "",
  backward_compatibility: "",
});

const getFormValuesFromContext = (): ContextFormData => {
  if (!context.value) return getDefaultFormValues();

  return {
    id: context.value.id,
    internal_name: context.value.internal_name,
    backward_compatibility: context.value.backward_compatibility || "",
  };
};
```

#### 2. Unsaved Changes Detection

```typescript
const hasUnsavedChanges = computed(() => {
  if (mode.value === "view") return false;

  if (mode.value === "create") {
    const defaultValues = getDefaultFormValues();
    return editForm.value.internal_name !== defaultValues.internal_name;
    // ... compare all fields
  }

  if (!context.value) return false;
  const originalValues = getFormValuesFromContext();
  return editForm.value.internal_name !== originalValues.internal_name;
  // ... compare all fields
});

// Watch for changes and sync with stores
watch(hasUnsavedChanges, (hasChanges: boolean) => {
  if (hasChanges) {
    cancelChangesStore.addChange();
  } else {
    cancelChangesStore.resetChanges();
  }
});
```

#### 3. Status Cards Configuration (where applicable)

```typescript
const statusControlsConfig = computed(() => {
  if (!context.value) return [];

  return [
    {
      title: "Default Context",
      description: "This context is set as the default for the entire database",
      mainColor: "green",
      statusText: context.value.is_default ? "Default" : "Not Default",
      toggleTitle: "Default Context",
      isActive: context.value.is_default,
      loading: false,
      disabled: false,
      activeIconBackgroundClass: "bg-green-100",
      inactiveIconBackgroundClass: "bg-gray-100",
      activeIconClass: "text-green-600",
      inactiveIconClass: "text-gray-600",
      activeIconComponent: CheckCircleIcon,
      inactiveIconComponent: XCircleIcon,
    },
  ];
});
```

#### 4. Navigation Guards

```typescript
onBeforeRouteLeave(
  async (
    _to: RouteLocationNormalized,
    _from: RouteLocationNormalized,
    next: NavigationGuardNext,
  ) => {
    if (
      (mode.value === "edit" || mode.value === "create") &&
      hasUnsavedChanges.value
    ) {
      const result = await cancelChangesStore.trigger(
        mode.value === "create"
          ? "New Context has unsaved changes"
          : "Context has unsaved changes",
        // ... confirmation message
      );

      if (result === "stay") {
        next(false); // Cancel navigation
      } else {
        cancelChangesStore.resetChanges();
        next(); // Allow navigation
      }
    } else {
      next();
    }
  },
);
```

## Entity Standards

### Color Consistency (CRITICAL)

Each entity has a unique color used across ALL components:

| Entity    | Color  | Text Class        | Background Classes                  |
| --------- | ------ | ----------------- | ----------------------------------- |
| Items     | teal   | `text-teal-600`   | `bg-teal-*`, `hover:bg-teal-50`     |
| Partners  | yellow | `text-yellow-600` | `bg-yellow-*`, `hover:bg-yellow-50` |
| Languages | purple | `text-purple-600` | `bg-purple-*`, `hover:bg-purple-50` |
| Countries | blue   | `text-blue-600`   | `bg-blue-*`, `hover:bg-blue-50`     |
| Contexts  | green  | `text-green-600`  | `bg-green-*`, `hover:bg-green-50`   |
| Projects  | orange | `text-orange-600` | `bg-orange-*`, `hover:bg-orange-50` |

### Icon Standards (STRICT)

**ONLY Heroicons allowed** - No inline SVG or custom icon components.

| Entity    | Icon           | Import                             |
| --------- | -------------- | ---------------------------------- |
| Items     | ArchiveBoxIcon | `from '@heroicons/vue/24/solid'`   |
| Partners  | UserGroupIcon  | `from '@heroicons/vue/24/solid'`   |
| Languages | LanguageIcon   | `from '@heroicons/vue/24/outline'` |
| Countries | GlobeAltIcon   | `from '@heroicons/vue/24/outline'` |
| Contexts  | CogIcon        | `from '@heroicons/vue/24/outline'` |
| Projects  | FolderIcon     | `from '@heroicons/vue/24/outline'` |

**Icon Size Standards:**

- Navigation menu: `w-4 h-4`
- List page icons: `h-5 w-5`
- Detail page resource icons: `h-6 w-6`

## Store Integration

All pages must integrate with Pinia stores following these patterns:

### Required Store Imports

```typescript
import { useContextStore } from "@/stores/context";
import { useLoadingOverlayStore } from "@/stores/loadingOverlay";
import { useErrorDisplayStore } from "@/stores/errorDisplay";
import { useDeleteConfirmationStore } from "@/stores/deleteConfirmation";
import { useCancelChangesConfirmationStore } from "@/stores/cancelChangesConfirmation";
```

### Data Fetching Pattern

```typescript
onMounted(async () => {
  let usedCache = false;

  // If cache exists, display immediately and refresh in background
  if (contexts.value && contexts.value.length > 0) {
    usedCache = true;
  } else {
    loadingStore.show();
  }

  try {
    await contextStore.fetchContexts();
    if (usedCache) {
      errorStore.addMessage("info", "List refreshed");
    }
  } catch {
    errorStore.addMessage(
      "error",
      "Failed to fetch contexts. Please try again.",
    );
  } finally {
    if (!usedCache) {
      loadingStore.hide();
    }
  }
});
```

## Adding New Entities

When adding a new entity, you **MUST** update both:

### 1. Home.vue Dashboard Tile

Add to the appropriate section (Inventory/Reference Data):

```vue
<NavigationCard
  title="NewEntity"
  description="Clear description of entity purpose"
  main-color="entity-color"
  button-text="Manage NewEntity"
  button-route="/new-entities"
>
  <template #icon>
    <NewEntityIcon />
  </template>
</NavigationCard>
```

### 2. AppHeader.vue Navigation Menu

Add to the corresponding dropdown section:

```vue
<RouterLink
  to="/new-entities"
  class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2"
  @click="closeDropdown"
>
  <NewEntityIcon class="w-4 h-4 text-entitycolor-600" />
  NewEntity
</RouterLink>
```

## Validation Checklist

Before submitting any page:

- [ ] **Pattern Compliance**: Page follows correct pattern (Dashboard/List/Detail)
- [ ] **Entity Colors**: Consistent color usage across all components
- [ ] **Icons**: Only Heroicons used, no inline SVG or custom components
- [ ] **Navigation**: Home.vue tiles match AppHeader.vue menu structure
- [ ] **Required Features**: All mandatory features implemented (filtering, sorting, actions, etc.)
- [ ] **Mode Handling**: Detail pages implement all three modes correctly
- [ ] **Store Integration**: Proper Pinia store usage and error handling
- [ ] **Event Handling**: Correct `@click.stop` usage for nested clickables
- [ ] **TypeScript**: No `any` types, proper interfaces defined
- [ ] **Responsive Design**: Proper Tailwind responsive classes used
