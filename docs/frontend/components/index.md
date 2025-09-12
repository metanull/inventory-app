---
layout: default
title: Components
parent: Frontend Documentation
nav_order: 5
has_children: true
---

# Component Documentation

This documentation covers all Vue components in the Inventory Management UI application.

## Component Categories

### [Global Components](./Global)

Centrally managed features that provide consistent user interaction patterns across the entire application. These components are rendered globally in App.vue and controlled via Pinia stores, including loading overlays, error displays, and confirmation modals.

### [Format Components](./Format)

Components for displaying and formatting data, including text display, form inputs, dropdowns, and specialized formatters.

### [Layout Components](./Layout)

High-level layout components for structuring pages and sections, including detail views, list views, and application layout.

### [Icon Components](./Icons)

SVG icon components used throughout the application.

### Theme and Colors

Guidelines and helpers for consistent color usage across the frontend: [Theme and Colors](./theme-and-colors)

### [Action Components](./Actions)

Button components for various user actions like editing, saving, deleting, etc.

## Component Structure

All components follow these conventions:

- **TypeScript**: All components are written in TypeScript with strict type definitions
- **Composition API**: Using Vue 3's `<script setup>` syntax
- **Props Interface**: Clear TypeScript interfaces for all props
- **Emits**: Typed emit definitions for component events
- **Tailwind CSS**: Utility-first CSS framework for styling
- **Slots**: Named and scoped slots for flexible content composition

## Common Patterns

### Props Validation

```typescript
interface Props {
  required: string;
  optional?: boolean;
  withDefault?: string;
}

const props = withDefaults(defineProps<Props>(), {
  optional: false,
  withDefault: "default value",
});
```

### Event Emissions

```typescript
const emit = defineEmits<{
  click: [];
  change: [value: string];
  customEvent: [data: CustomType];
}>();
```

### Computed Properties

```typescript
const computedValue = computed(() => {
  return props.someValue ? "active" : "inactive";
});
```
