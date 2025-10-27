---
layout: default
title: Layout Components
parent: Components
grand_parent: Vue.js Sample Frontend
nav_order: 7
---

# Layout Components

High-level layout components for structuring pages, sections, and application-wide elements.

## Application Layout

### AppHeader

Main application header with navigation and branding.

### AppFooter

Application footer component.

# Layout Components

High-level layout components for structuring pages, sections, and application-wide elements.

## Application Layout

### App Components

Located in `src/components/layout/app/`:

- **AppHeader** - Main application header with navigation and branding
- **AppFooter** - Application footer component
- **AppLayout** - Overall application layout wrapper

## Page Layout Components

### ListView

A comprehensive list view component that provides a standardized layout for displaying collections of resources.

**Location**: `src/components/layout/list/ListView.vue`

**Props:**

- `title: string` - Page title
- `description?: string` - Optional page description
- `addButtonRoute?: string` - Route for the add button
- `addButtonLabel?: string` - Label for the add button
- `color?: string` - Theme color for icons and buttons
- `isEmpty: boolean` - Whether the list is empty
- `emptyTitle: string` - Title for empty state
- `emptyMessage: string` - Message for empty state
- `showEmptyAddButton?: boolean` - Show add button in empty state
- `emptyAddButtonLabel?: string` - Label for empty add button

**Slots:**

- `icon` - Icon for the page header
- `filters` - Filter buttons and controls
- `default` - Main table content with headers and rows
- `modals` - Modal dialogs

**Events:**

- `retry` - Emitted when user clicks retry in error state

**Complete Usage Example (Contexts.vue):**

```vue
<ListView
  title="Contexts"
  description="Manage contexts in your inventory system. Contexts can be set as default for the entire database."
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
            <ContextIcon class="h-5 w-5 text-green-600" />
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
            @toggle="updateContextStatus(context, 'is_default', !context.is_default)"
          />
        </div>
      </TableCell>
      <TableCell class="hidden lg:table-cell">
        <DateDisplay :date="context.created_at" format="short" variant="small-dark" />
      </TableCell>
      <TableCell class="hidden sm:table-cell">
        <div class="flex space-x-2" @click.stop>
          <ViewButton @click="router.push(`/contexts/${context.id}`)" />
          <EditButton @click="router.push(`/contexts/${context.id}?edit=true`)" />
          <DeleteButton @click="handleDeleteContext(context)" />
        </div>
      </TableCell>
    </TableRow>
  </template>
</ListView>
```

**Required List Page Features:**

All list pages must implement:

1. **Filtering System**: Filter buttons with counts and active states
2. **Sorting System**: Sortable table headers with direction indicators
3. **Search System**: SearchControl component for text-based filtering
4. **Action Buttons**: ViewButton, EditButton, DeleteButton in every row
5. **Status Toggles**: Toggle components for inline status changes (where applicable)
6. **Row Click Navigation**: Clickable rows with hover effects and @click.stop for actions
7. **Entity Colors**: Consistent color usage (e.g., green for contexts, teal for items)

### DetailView

A comprehensive detail view component for viewing, editing, and creating resources.

**Location**: `src/components/layout/detail/DetailView.vue`

**Props:**

- `storeLoading: boolean` - Loading state from store
- `resource: object | null` - The resource being viewed/edited
- `mode: 'view' | 'edit' | 'create'` - Current view mode
- `saveDisabled?: boolean` - Whether save button is disabled
- `hasUnsavedChanges?: boolean` - Whether there are unsaved changes
- `backLink: string` - Back navigation link
- `statusControls?: StatusControl[]` - Status toggle controls configuration
- `createTitle?: string` - Title for create mode
- `createSubtitle?: string` - Subtitle for create mode
- `informationTitle: string` - Title for information section
- `informationDescription?: string` - Description for information section
- `fetchData: Function` - Function to fetch resource data

**Events:**

- `edit` - Switch to edit mode
- `save` - Save changes
- `cancel` - Cancel changes/exit edit mode
- `delete` - Delete resource
- `status-toggle` - Toggle status (enabled/disabled, launched/not launched)

**Slots:**

- `resource-icon` - Icon for the resource
- `information` - Main information content (forms, display fields)

**Usage Example (ProjectDetail.vue):**

```vue
<DetailView
  :store-loading="projectStore.loading"
  :resource="mode === 'create' ? null : project"
  :mode="mode"
  :save-disabled="!hasUnsavedChanges"
  :has-unsaved-changes="hasUnsavedChanges"
  :back-link="backLink"
  :status-cards="statusCardsConfig"
  :create-title="'New Project'"
  :create-subtitle="'(Creating)'"
  information-title="Project Information"
  :information-description="informationDescription"
  :fetch-data="fetchProject"
  @edit="enterEditMode"
  @save="saveProject"
  @cancel="cancelAction"
  @delete="deleteProject"
  @status-toggle="handleStatusToggle"
>
  <template #resource-icon>
    <ProjectIcon class="h-6 w-6 text-orange-600" />
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
          <DisplayText v-else>{{ project?.internal_name }}</DisplayText>
        </DescriptionDetail>
      </DescriptionRow>
      <!-- More fields... -->
    </DescriptionList>
  </template>
</DetailView>
```

<template #headers>
<TableHeader>Name</TableHeader>
<TableHeader>Status</TableHeader>
</template>

<template #rows>
<TableRow v-for="project in projects" :key="project.id">
<TableCell>{{ project.internal_name }}</TableCell>
<TableCell>{{ project.status }}</TableCell>
</TableRow>
</template>
</ListView>

````

### DetailView
A comprehensive detail view component for displaying and editing individual resources.

**Props:**
- `storeLoading?: boolean` - Store's internal loading state
- `resource: Resource | null` - The resource being displayed
- `isEditing: boolean` - Whether in edit mode
- `isCreating?: boolean` - Whether creating a new resource
- `hasUnsavedChanges?: boolean` - Whether there are unsaved changes
- `createTitle?: string` - Custom title for creation mode
- `createSubtitle?: string` - Custom subtitle for creation mode
- `backLink?: BackLinkConfig` - Back navigation configuration
- `statusControls?: StatusControlConfig[]` - Status control configurations
- `informationTitle: string` - Title for the information section
- `informationDescription: string` - Description for the information section
- `fetchData: () => Promise<void>` - Function to fetch resource data

**Events:**
- `edit: []` - Emitted when edit mode is requested
- `save: []` - Emitted when save is requested
- `cancel: []` - Emitted when cancel is requested
- `delete: []` - Emitted when delete is requested
- `statusToggle: [index: number]` - Emitted when a status card is toggled
- `unsavedChanges: [hasChanges: boolean]` - Emitted when unsaved changes state changes

**Slots:**
- `information` - Main information content with field change handler

**Usage:**
```vue
<DetailView
  :resource="project"
  :is-editing="isEditing"
  :is-creating="isNewProject"
  :has-unsaved-changes="hasChanges"
  :back-link="backLink"
  :status-controls="statusControls"
  information-title="Project Information"
  information-description="Detailed project information"
  :fetch-data="fetchProject"
  @edit="startEdit"
  @save="saveChanges"
  @cancel="cancelEdit"
  @delete="deleteProject"
  @status-toggle="handleStatusToggle"
>
  <template #information="{ onFieldChange }">
    <DescriptionList>
      <DescriptionRow variant="gray">
        <DescriptionTerm>Name</DescriptionTerm>
        <DescriptionDetail>
          <FormInput
            v-if="isEditing"
            v-model="editForm.name"
            @input="onFieldChange"
          />
          <DisplayText v-else>{{ project.name }}</DisplayText>
        </DescriptionDetail>
      </DescriptionRow>
    </DescriptionList>
  </template>
</DetailView>
````

## List Action Components

Located in `src/components/layout/list/`:

### FilterButton

Displays filter options with active state and count indicators.

**Props:**

- `label: string` - Filter label
- `isActive: boolean` - Whether filter is currently active
- `count?: number` - Count to display in badge
- `variant?: 'primary' | 'info' | 'warning'` - Visual variant

**Usage:**

```vue
<FilterButton
  label="All Projects"
  :is-active="filterMode === 'all'"
  :count="projects.length"
  variant="primary"
  @click="filterMode = 'all'"
/>
```

### Action Buttons

- **ViewButton** - View resource action
- **EditButton** - Edit resource action
- **DeleteButton** - Delete resource action
- **AddButton** - Add new resource action

All action buttons accept:

- `@click` event handler
- `disabled?: boolean` prop
- Standard button styling via Tailwind classes

### SearchControl

Search input component for filtering lists.

**Props:**

- `modelValue: string` - Search query
- `placeholder?: string` - Input placeholder

**Events:**

- `update:modelValue` - Emitted when search query changes

## Detail Action Components

Located in `src/components/layout/detail/`:

### Action Buttons

- **EditButton** - Switch to edit mode
- **SaveButton** - Save changes (shows loading state)
- **CancelButton** - Cancel changes
- **DeleteButton** - Delete resource

### SystemProperties

Displays system fields like created_at, updated_at with proper formatting.

**Props:**

- `resource: object` - Resource with system properties
- `variant?: string` - Display variant

## Layout Best Practices

### Responsive Design

All layout components use Tailwind CSS with mobile-first approach:

```vue
<!-- Table columns with responsive visibility -->
<th class="hidden sm:table-cell">Desktop Only</th>
<th class="table-cell">Always Visible</th>

<!-- Responsive spacing -->
<div class="p-4 md:p-6 lg:p-8"></div>
```

### Component Organization

Layout components follow this structure:

- **App layout**: Overall application shell
- **Page layout**: ListView for collections, DetailView for individual resources
- **Action components**: Reusable buttons and controls
- **Supporting components**: Search, filters, system properties

### Consistent Styling

- Use `color` prop for theme consistency across resources
- Follow Tailwind utility patterns for spacing and typography
- Implement proper loading and error states
- Maintain accessibility with proper ARIA labels

### AddButton

Button for adding new items to a list.

**Props:**

- `to: string` - Router link destination
- `label?: string` - Button label
- `color?: string` - Theme color

**Usage:**

```vue
<AddButton to="/projects/new" label="New Project" color="blue" />
```

### ViewButton

Button for viewing item details.

### EditButton

Button for editing items.

### DeleteButton

Button for deleting items.

### FilterButton

Button for filtering list items.

## Detail Action Components

### EditButton (Detail)

Edit button specifically styled for detail views.

**Events:**

- `click: []` - Emitted when button is clicked

### SaveButton

Save button for detail view forms.

**Props:**

- `disabled?: boolean` - Whether the button is disabled

**Events:**

- `click: []` - Emitted when button is clicked

### CancelButton

Cancel button for detail view forms.

**Events:**

- `click: []` - Emitted when button is clicked

### DeleteButton (Detail)

Delete button specifically styled for detail views.

**Events:**

- `click: []` - Emitted when button is clicked

**Usage:**

```vue
<div class="flex space-x-3">
  <SaveButton :disabled="!hasChanges" @click="save" />
  <CancelButton @click="cancel" />
</div>
```

## System Components

### SystemProperties

Displays system-level metadata like creation and modification timestamps.

**Props:**

- `id: string` - Resource ID
- `created-at: string` - Creation timestamp
- `updated-at: string` - Last update timestamp

**Usage:**

```vue
<SystemProperties
  :id="resource.id"
  :created-at="resource.created_at"
  :updated-at="resource.updated_at"
/>
```

## Interface Definitions

### Resource Interface

```typescript
interface Resource {
  id: string
  internal_name: string
  backward_compatibility?: string | null
  created_at: string | null
  updated_at: string | null
}
```

### BackLinkConfig Interface

```typescript
interface BackLinkConfig {
  title: string
  route: string
  icon: Component
  color?: string
}
```

### StatusCardConfig Interface

```typescript
interface StatusCardConfig {
  title: string
  description: string
  mainColor: string
  statusText: string
  toggleTitle: string
  isActive: boolean
  loading: boolean
  disabled?: boolean
  activeIconBackgroundClass: string
  inactiveIconBackgroundClass: string
  activeIconClass: string
  inactiveIconClass: string
  activeIconComponent: Component
  inactiveIconComponent: Component
}
```
