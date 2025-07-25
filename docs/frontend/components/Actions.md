# Action Components

Button and interactive components for user actions throughout the application.

## Overview

Action components provide consistent styling and behavior for user interactions. They are categorized by context and usage patterns.

## List Action Components

Action buttons used in list views and table interfaces.

### AddButton

Primary action button for creating new resources.

**Props:**

- `to: string` - Router link destination for navigation
- `label?: string` - Button text (defaults to "Add")
- `color?: string` - Theme color for styling

**Usage:**

```vue
<AddButton to="/projects/new" label="New Project" color="blue" />
```

### ViewButton

Action button for viewing resource details.

**Props:**

- `to: string` - Router link destination
- `label?: string` - Button text

**Usage:**

```vue
<ViewButton to="/projects/123" label="View Details" />
```

### EditButton (List)

Action button for editing resources from list views.

**Props:**

- `to: string` - Router link destination for edit page

**Usage:**

```vue
<EditButton to="/projects/123?edit=true" />
```

### DeleteButton (List)

Action button for deleting resources from lists.

**Events:**

- `click: []` - Emitted when delete is requested

**Usage:**

```vue
<DeleteButton @click="confirmDelete(item.id)" />
```

### FilterButton

Toggle button for filtering list content.

**Props:**

- `label: string` - Filter label
- `count?: number` - Number of items matching filter
- `isActive: boolean` - Whether filter is currently active
- `variant?: string` - Visual style variant

**Events:**

- `click: []` - Emitted when filter is toggled

**Usage:**

```vue
<FilterButton
  label="Active"
  :count="activeCount"
  :is-active="activeFilter"
  variant="green"
  @click="toggleFilter('active')"
/>
```

## Detail Action Components

Action buttons used in detail views and forms.

### EditButton (Detail)

Initiates edit mode in detail views.

**Props:**

- `disabled?: boolean` - Whether the button is disabled

**Events:**

- `click: []` - Emitted when edit mode is requested

**Usage:**

```vue
<EditButton :disabled="!canEdit" @click="startEdit" />
```

### SaveButton

Saves changes in forms and detail views.

**Props:**

- `disabled?: boolean` - Whether the button is disabled
- `loading?: boolean` - Shows loading state (deprecated - no longer used)

**Events:**

- `click: []` - Emitted when save is requested

**Usage:**

```vue
<SaveButton :disabled="!hasUnsavedChanges" @click="saveChanges" />
```

### CancelButton

Cancels edit mode or form changes.

**Events:**

- `click: []` - Emitted when cancel is requested

**Usage:**

```vue
<CancelButton @click="cancelEdit" />
```

### DeleteButton (Detail)

Deletes the current resource in detail views.

**Events:**

- `click: []` - Emitted when delete is requested

**Usage:**

```vue
<DeleteButton @click="confirmDelete" />
```

## Button Patterns

### Action Button Groups

#### List Row Actions

```vue
<div class="flex space-x-2">
  <ViewButton :to="`/projects/${project.id}`" />
  <EditButton :to="`/projects/${project.id}?edit=true`" />
  <DeleteButton @click="deleteProject(project.id)" />
</div>
```

#### Detail View Actions

```vue
<div class="flex space-x-3">
  <template v-if="!isEditing">
    <EditButton @click="startEdit" />
    <DeleteButton @click="confirmDelete" />
  </template>
  <template v-else>
    <SaveButton :disabled="!hasChanges" @click="save" />
    <CancelButton @click="cancel" />
  </template>
</div>
```

#### Filter Bar

```vue
<div class="flex space-x-1">
  <FilterButton
    label="All"
    :count="totalCount"
    :is-active="!activeFilter"
    @click="clearFilter"
  />
  <FilterButton
    label="Active"
    :count="activeCount"
    :is-active="activeFilter === 'active'"
    variant="green"
    @click="setFilter('active')"
  />
  <FilterButton
    label="Inactive"
    :count="inactiveCount"
    :is-active="activeFilter === 'inactive'"
    variant="red"
    @click="setFilter('inactive')"
  />
</div>
```

## Styling and Theming

### Color Variants

- **Blue**: Primary actions (Add, Save)
- **Green**: Positive actions (Enable, Activate)
- **Red**: Destructive actions (Delete, Disable)
- **Gray**: Secondary actions (Cancel, View)
- **Orange**: Navigation actions (Edit from list)

### Size Variants

- **Small**: Compact buttons for table rows
- **Medium**: Standard buttons for forms
- **Large**: Primary page actions

### State Variants

- **Default**: Normal interactive state
- **Disabled**: Non-interactive state
- **Loading**: Processing state (deprecated)
- **Active**: Currently selected/active state

## Accessibility

All action components include:

- Proper ARIA labels
- Keyboard navigation support
- Focus management
- Screen reader compatibility
- Semantic HTML elements

## Examples

### Complete List Action Example

```vue
<template>
  <tr>
    <td>{{ project.name }}</td>
    <td>{{ project.status }}</td>
    <td>
      <div class="flex justify-end space-x-2">
        <ViewButton :to="`/projects/${project.id}`" />
        <EditButton :to="`/projects/${project.id}?edit=true`" />
        <DeleteButton @click="handleDelete(project.id)" />
      </div>
    </td>
  </tr>
</template>
```

### Complete Detail Action Example

```vue
<template>
  <div class="flex items-center justify-between">
    <Title>{{ project.name }}</Title>
    <div class="flex space-x-3">
      <template v-if="!isEditing">
        <EditButton @click="isEditing = true" />
        <DeleteButton @click="confirmDelete" />
      </template>
      <template v-else>
        <SaveButton :disabled="!hasUnsavedChanges" @click="saveProject" />
        <CancelButton @click="cancelEdit" />
      </template>
    </div>
  </div>
</template>
```
