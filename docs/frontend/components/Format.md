---
layout: default
title: Format Components
parent: Components
nav_order: 3
---

# Format Components

Components for displaying and formatting data, handling user input, and presenting information in structured ways.

## Text Display Components

### DisplayText

A simple text display component with variant styling options.

**Props:**

- `variant?: 'default' | 'gray' | 'muted'` - Style variant for the text

**Usage:**

```vue
<DisplayText variant="gray">Some muted text</DisplayText>
<DisplayText>Default styled text</DisplayText>
```

### InternalName

Displays internal names with consistent formatting.

### InternalNameSmall

A smaller variant of the InternalName component for compact displays.

## Form Components

### FormInput

A styled input component that works with v-model for form data binding.

**Props:**

- `type?: string` - HTML input type (default: 'text')
- `placeholder?: string` - Placeholder text
- `required?: boolean` - Whether the field is required
- `disabled?: boolean` - Whether the input is disabled
- `modelValue?: string` - The input value (for v-model)

**Events:**

- `update:modelValue: [value: string]` - Emitted when input value changes

**Usage:**

```vue
<FormInput
  v-model="formData.name"
  type="text"
  placeholder="Enter name"
  :required="true"
/>
```

### GenericDropdown

A highly configurable dropdown component with advanced features like priority sorting, default options, and custom styling.

**Props:**

- `modelValue: string` - Selected value (for v-model)
- `options: GenericDropdownOption[]` - Array of dropdown options
- `disabled?: boolean` - Whether the dropdown is disabled
- `showNoDefaultOption?: boolean` - Show "no default" option
- `noDefaultValue?: string` - Value for "no default" option
- `noDefaultLabel?: string` - Label for "no default" option
- `sortFunction?: (options) => options` - Custom sorting function
- `highlightFunction?: (option, currentValue) => styles` - Custom highlighting

**Events:**

- `update:modelValue: [value: string]` - Emitted when selection changes

**Usage:**

```vue
<GenericDropdown
  v-model="selectedId"
  :options="contextOptions"
  :show-no-default-option="true"
  no-default-label="No default context"
  no-default-value=""
/>
```

### Toggle

A toggle switch component for boolean values.

### ToggleSmall

A compact version of the toggle component.

## Date and Time Components

### Date

Formats and displays dates with various format options.

**Props:**

- `date: string` - ISO date string to format
- `format?: string` - Date format ('medium', 'short', etc.)
- `variant?: string` - Display variant styling

**Usage:**

```vue
<DateDisplay :date="project.launch_date" format="medium" variant="small-dark" />
```

## Data Display Components

### Uuid

Displays UUID values with appropriate formatting and truncation.

## Title Components

### Title

A flexible title component that renders appropriate heading tags based on context.

**Props:**

- `variant?: 'page' | 'section' | 'card' | 'system' | 'empty'` - Title variant
- `description?: string` - Optional description text
- `level?: 1 | 2 | 3 | 4 | 5 | 6` - Override heading level

**Slots:**

- `default` - Main title content
- `description` - Description content (alternative to description prop)

**Usage:**

```vue
<Title variant="page" description="Page subtitle">
  Main Page Title
</Title>

<Title variant="section">
  Section Title
  <template #description>
    Custom description with <strong>HTML</strong>
  </template>
</Title>
```

## Description List Components

A set of components for creating structured description lists, commonly used in detail views.

### DescriptionList

Container component for description lists.

**Usage:**

```vue
<DescriptionList>
  <DescriptionRow variant="gray">
    <DescriptionTerm>Label</DescriptionTerm>
    <DescriptionDetail>Value</DescriptionDetail>
  </DescriptionRow>
</DescriptionList>
```

### DescriptionRow

Individual row in a description list with alternating background colors.

**Props:**

- `variant?: 'white' | 'gray'` - Background color variant

### DescriptionTerm

The label/term part of a description row.

### DescriptionDetail

The value/detail part of a description row.

## Table Components

Components for creating structured data tables.

### TableElement

Main table container component.

**Slots:**

- `headers` - Table header content
- `rows` - Table row content

### TableHeader

Table header cell component.

### TableRow

Table row component.

### TableCell

Individual table cell component.

**Usage:**

```vue
<TableElement>
  <template #headers>
    <TableHeader>Name</TableHeader>
    <TableHeader>Status</TableHeader>
  </template>
  <template #rows>
    <TableRow>
      <TableCell>Item 1</TableCell>
      <TableCell>Active</TableCell>
    </TableRow>
  </template>
</TableElement>
```

## Card Components

### Card

Base card component for containing related content.

### InformationCard

Specialized card for displaying information with consistent styling.

### NavigationCard

Card component optimized for navigation elements.

### StatusCard

Interactive card component for displaying and toggling status information.

**Props:**

- `title: string` - Card title
- `description: string` - Card description
- `main-color: string` - Primary color theme
- `status-text: string` - Status text to display
- `toggle-title: string` - Title for toggle action
- `is-active: boolean` - Current active state
- `loading: boolean` - Loading state
- `disabled?: boolean` - Disabled state
- `active-icon-background-class: string` - CSS class for active icon background
- `inactive-icon-background-class: string` - CSS class for inactive icon background
- `active-icon-class: string` - CSS class for active icon
- `inactive-icon-class: string` - CSS class for inactive icon
- `active-icon-component: Component` - Vue component for active icon
- `inactive-icon-component: Component` - Vue component for inactive icon

**Events:**

- `toggle: []` - Emitted when the status is toggled

**Usage:**

```vue
<StatusCard
  title="Project Status"
  description="Current project status"
  main-color="green"
  status-text="Active"
  toggle-title="Toggle Status"
  :is-active="true"
  :loading="false"
  :disabled="false"
  active-icon-background-class="bg-green-100"
  inactive-icon-background-class="bg-red-100"
  active-icon-class="text-green-600"
  inactive-icon-class="text-red-600"
  :active-icon-component="CheckIcon"
  :inactive-icon-component="XIcon"
  @toggle="handleToggle"
/>
For details about the centralized color system, available color names, and how
to use `useColors` and `getThemeClass`, see the Theme and Colors guide: [Theme
and Colors](./theme-and-colors).
```
