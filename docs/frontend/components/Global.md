---
layout: default
title: Global Components
parent: Components
nav_order: 1
---

# Global Components

Global components are centrally managed features that provide consistent user interaction patterns across the entire application. These components are rendered globally in `App.vue` and controlled via Pinia stores, ensuring a unified user experience.

## Overview

All global components follow a centralized architecture pattern:

- **Pinia Store Management**: Each component has a dedicated Pinia store for state management
- **Global Rendering**: Components are rendered once in `App.vue` and available application-wide
- **Promise-based Interactions**: Modal components use promise-based workflows for seamless integration
- **Consistent UI Patterns**: Standardized styling and behavior across all global features

---

## LoadingOverlay

**File**: `src/components/global/LoadingOverlay.vue`  
**Store**: `src/stores/loadingOverlay.ts`

A full-screen loading overlay that displays during API operations.

### Features

- Animated spinning SVG with "Loading..." text
- Overlay prevents user interaction during loading states
- Can be disabled to prevent showing during certain operations
- Automatic z-index management (z-50)

### Store API

```typescript
// State
visible: boolean     // Controls overlay visibility
disabled: boolean    // When true, show() calls are ignored

// Actions
show(): void        // Display the loading overlay
hide(): void        // Hide the loading overlay
disable(): void     // Disable overlay and hide if currently visible
enable(): void      // Re-enable overlay functionality
```

### Usage Example

```typescript
import { useLoadingOverlayStore } from "@/stores/loadingOverlay";

const loadingStore = useLoadingOverlayStore();

// Show loading during API call
const fetchData = async () => {
  try {
    loadingStore.show();
    await apiCall();
  } finally {
    loadingStore.hide();
  }
};
```

### Styling

- Full viewport coverage with semi-transparent background
- Centered content with smooth animations
- Consistent with application color scheme
- Accessible with proper ARIA attributes

---

## ErrorDisplay

**File**: `src/components/global/ErrorDisplay.vue`  
**Store**: `src/stores/errorDisplay.ts`

Displays error, warning, and informational messages to users below the application header.

### Features

- **Three Message Types**: Error (red), Warning (yellow), Info (blue)
- **Auto-dismiss**: Info and warning messages disappear after 5 seconds
- **Manual Close**: All messages can be manually dismissed
- **Console Logging**: Messages are logged to browser console for debugging
- **Animation**: Smooth enter/leave transitions for messages

### Store API

```typescript
// Types
interface ErrorMessage {
  id: string
  type: 'error' | 'warning' | 'info'
  text: string
  timestamp: number
}

// State
messages: ErrorMessage[]  // Array of current messages

// Actions
addMessage(type: 'error'|'warning'|'info', text: string): string  // Returns message ID
removeMessage(id: string): void     // Remove specific message
clearAll(): void                    // Clear all messages
```

### Usage Example

```typescript
import { useErrorDisplayStore } from "@/stores/errorDisplay";

const errorStore = useErrorDisplayStore();

// Add different message types
errorStore.addMessage("info", "Data saved successfully!");
errorStore.addMessage("warning", "Please review your input.");
errorStore.addMessage("error", "Failed to save. Please try again.");
```

### Message Behavior

- **Info/Warning**: Auto-dismiss after 5 seconds
- **Error**: Requires manual dismissal
- **Console Output**: All messages logged for debugging
- **Unique IDs**: Each message has a UUID for tracking

### Styling

- Type-specific colors and icons
- Positioned below header with proper spacing
- Responsive design for mobile devices
- Smooth animations for add/remove operations

---

## DeleteConfirmation

**File**: `src/components/global/DeleteConfirmation.vue`  
**Store**: `src/stores/deleteConfirmation.ts`

A modal dialog for confirming destructive delete operations.

### Features

- **Customizable Content**: Dynamic title and description
- **Default Focus**: Cancel button receives focus for safety
- **Keyboard Support**: Enter/Escape keys trigger cancel action
- **Promise-based**: Returns user choice as resolved promise
- **Backdrop Click**: Clicking outside modal cancels action

### Store API

```typescript
// State
visible: boolean              // Modal visibility
title: string                // Modal title
description: string          // Modal description text
result: 'cancel'|'delete'|null  // User's choice

// Actions
trigger(title: string, description: string): Promise<'cancel'|'delete'>
resolve(result: 'cancel'|'delete'): void
cancel(): void               // Shorthand for resolve('cancel')
confirmDelete(): void        // Shorthand for resolve('delete')
```

### Usage Example

```typescript
import { useDeleteConfirmationStore } from "@/stores/deleteConfirmation";

const deleteStore = useDeleteConfirmationStore();

const handleDelete = async (item: Item) => {
  const result = await deleteStore.trigger(
    "Delete Item",
    `Are you sure you want to delete "${item.name}"? This action cannot be undone.`,
  );

  if (result === "delete") {
    await performDelete(item.id);
  }
};
```

### Modal Design

- **Green Cancel Button**: Default action, left-aligned
- **Red Delete Button**: Destructive action, right-aligned
- **Warning Icon**: Yellow triangle icon for visual emphasis
- **Responsive**: Adapts to mobile screen sizes

### Accessibility

- Proper ARIA attributes and roles
- Focus management for keyboard navigation
- Screen reader friendly content structure

---

## CancelChangesConfirmation

**File**: `src/components/global/CancelChangesConfirmation.vue`  
**Store**: `src/stores/cancelChangesConfirmation.ts`

Prevents accidental data loss by confirming navigation away from forms with unsaved changes.

### Features

- **Change Tracking**: Monitors form modifications automatically
- **Navigation Protection**: Triggers on route changes or form exits
- **Customizable Messages**: Dynamic titles and descriptions
- **Auto-focus Stay Button**: Safer default for data preservation
- **Promise-based Workflow**: Clean integration with navigation logic

### Store API

```typescript
// State
visible: boolean                    // Modal visibility
pendingChanges: boolean            // Tracks if there are unsaved changes
title: string                      // Modal title
description: string                // Modal description
result: 'stay'|'leave'|null       // User's choice

// Actions
trigger(title: string, description: string): Promise<'stay'|'leave'>
resolve(result: 'stay'|'leave'): void
stay(): void                       // Shorthand for resolve('stay')
leave(): void                      // Shorthand for resolve('leave')
addChange(): void                  // Mark that changes exist
resetChanges(): void               // Clear pending changes flag
```

### Usage Example

```typescript
import { useCancelChangesConfirmationStore } from "@/stores/cancelChangesConfirmation";

const cancelChangesStore = useCancelChangesConfirmationStore();

// Track form changes
watch(
  formData,
  () => {
    cancelChangesStore.addChange();
  },
  { deep: true },
);

// Handle navigation with unsaved changes
const handleNavigation = async () => {
  if (cancelChangesStore.pendingChanges) {
    const result = await cancelChangesStore.trigger(
      "Unsaved Changes",
      "You have unsaved changes. Are you sure you want to leave?",
    );

    if (result === "leave") {
      router.push("/other-page");
    }
  }
};

// Reset after successful save
const saveForm = async () => {
  await saveData();
  cancelChangesStore.resetChanges();
};
```

### Modal Design

- **Green Stay Button**: Safe default action, preserves work
- **Red Leave Button**: Destructive action, loses changes
- **Warning Icon**: Yellow triangle for attention
- **Clear Messaging**: Explains consequences of leaving

### Integration Patterns

- Automatic change detection via Vue watchers
- Integration with Vue Router navigation guards
- Form validation and save workflows
- Computed property monitoring for complex forms

---

## Component Integration

### App.vue Structure

```vue
<template>
  <div id="app">
    <AppHeader />
    <ErrorDisplay />
    <!-- Positioned below header -->
    <main>
      <RouterView />
    </main>
    <AppFooter />

    <!-- Global Modals -->
    <LoadingOverlay />
    <DeleteConfirmation />
    <CancelChangesConfirmation />
  </div>
</template>
```

### Store Dependencies

All global components require their respective Pinia stores to be imported and used in consuming components:

```typescript
// In any Vue component
import { useLoadingOverlayStore } from "@/stores/loadingOverlay";
import { useErrorDisplayStore } from "@/stores/errorDisplay";
import { useDeleteConfirmationStore } from "@/stores/deleteConfirmation";
import { useCancelChangesConfirmationStore } from "@/stores/cancelChangesConfirmation";
```

### Best Practices

1. **Loading States**: Always show loading during async operations
2. **Error Handling**: Provide clear, actionable error messages
3. **Confirmation Dialogs**: Use for all destructive actions
4. **Change Tracking**: Protect user work with unsaved change detection
5. **Consistent Messaging**: Use standardized success/error message patterns
6. **Accessibility**: Ensure proper focus management and keyboard navigation

### Testing Considerations

- Mock Pinia stores in unit tests
- Test promise resolution for modal components
- Verify proper cleanup of watchers and event listeners
- Test keyboard navigation and accessibility features
- Validate auto-dismiss timings for error messages

---

## Technical Notes

### Performance

- Components use `v-if` for conditional rendering to minimize DOM overhead
- Pinia stores use reactive refs for efficient state updates
- CSS transitions are optimized for smooth animations

### Browser Compatibility

- Supports all modern browsers (Chrome, Firefox, Safari, Edge)
- Graceful degradation for older browsers
- No external dependencies beyond Vue 3 and Pinia

### Maintenance

- Centralized styling through Tailwind CSS classes
- TypeScript ensures type safety across all interactions
- Modular store architecture allows independent feature updates
