---
layout: default
title: Quick Reference
parent: Frontend Documentation  
nav_order: 1
---

# Vue.js Quick Reference

Essential patterns and standards for Vue.js development in the Inventory Management system.

## 🚀 Getting Started

1. **Read First**: [Page Patterns](page-patterns.md) - Complete implementation guide
2. **Component Docs**: [Components](components/) - Reusable component reference
3. **Guidelines**: [Coding Guidelines](guidelines/coding-guidelines.md) - Standards and best practices

## 📋 Pre-Development Checklist

Before creating any component:
- [ ] Determine page type: Dashboard, List, or Detail
- [ ] Identify entity color and icon from standards table
- [ ] Plan required features (filtering, sorting, actions, etc.)
- [ ] Ensure navigation consistency (Home.vue ↔ AppHeader.vue)

## 🎨 Entity Standards Quick Reference

| Entity | Color | Icon | Text Class | Hover Class |
|--------|-------|------|------------|-------------|
| Items | teal | `ArchiveBoxIcon` | `text-teal-600` | `hover:bg-teal-50` |
| Partners | yellow | `UserGroupIcon` | `text-yellow-600` | `hover:bg-yellow-50` |
| Languages | purple | `LanguageIcon` | `text-purple-600` | `hover:bg-purple-50` |
| Countries | blue | `GlobeAltIcon` | `text-blue-600` | `hover:bg-blue-50` |
| Contexts | green | `CogIcon` | `text-green-600` | `hover:bg-green-50` |
| Projects | orange | `FolderIcon` | `text-orange-600` | `hover:bg-orange-50` |

## 🔧 Essential Component Patterns

### Page Type Templates

```vue
<!-- DASHBOARD/HOME PAGE -->
<template>
  <div>
    <Title variant="page" description="Welcome message">Dashboard</Title>
    
    <div class="mb-8">
      <h2 class="text-xl font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">
        Section Name
      </h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <NavigationCard
          title="EntityName"
          description="Entity description"
          main-color="entity-color"
          button-text="Manage EntityName"
          button-route="/entities"
        >
          <template #icon><EntityIcon /></template>
        </NavigationCard>
      </div>
    </div>
  </div>
</template>
```

```vue
<!-- LIST PAGE -->
<template>
  <ListView
    title="EntityName"
    description="Manage entities..."
    add-button-route="/entities/new"
    add-button-label="Add EntityName"
    color="entity-color"
    :is-empty="filteredEntities.length === 0"
    empty-title="No entities found"
    empty-message="Get started by creating a new entity."
  >
    <template #icon><EntityIcon /></template>
    <template #filters><!-- Filter buttons --></template>
    <template #search><SearchControl v-model="searchQuery" /></template>
    <template #headers><!-- Table headers --></template>
    <template #rows>
      <TableRow v-for="entity in filteredEntities" :key="entity.id">
        <!-- Row content with actions -->
      </TableRow>
    </template>
  </ListView>
</template>
```

```vue
<!-- DETAIL PAGE -->
<template>
  <DetailView
    :store-loading="entityStore.loading"
    :resource="mode === 'create' ? null : entity"
    :mode="mode"
    :save-disabled="!hasUnsavedChanges"
    :has-unsaved-changes="hasUnsavedChanges"
    :back-link="backLink"
    information-title="Entity Information"
    @edit="enterEditMode"
    @save="saveEntity"
    @cancel="cancelAction"
    @delete="deleteEntity"
  >
    <template #resource-icon>
      <EntityIcon class="h-6 w-6 text-entity-600" />
    </template>
    <template #information>
      <DescriptionList>
        <DescriptionRow variant="gray">
          <DescriptionTerm>Field Name</DescriptionTerm>
          <DescriptionDetail>
            <FormInput v-if="mode === 'edit' || mode === 'create'" v-model="editForm.field" />
            <DisplayText v-else>{{ entity?.field }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>
      </DescriptionList>
    </template>
  </DetailView>
</template>
```

## 🚫 Common Mistakes to Avoid

### Icons
- ❌ `<svg>...</svg>` (inline SVG)
- ❌ `<CustomIcon />` (custom components)  
- ✅ `import { CogIcon } from '@heroicons/vue/24/solid'`

### Colors
- ❌ `text-blue-600` for contexts (wrong color)
- ❌ Different colors in same entity components
- ✅ `text-green-600` for all context components

### Navigation
- ❌ Home tile without corresponding menu item
- ❌ Different icons/colors between Home and menu
- ✅ Synchronized Home.vue and AppHeader.vue

### List Pages
- ❌ Missing action buttons (View/Edit/Delete)
- ❌ No filtering or sorting
- ✅ Complete feature implementation

### Detail Pages  
- ❌ Only handling view mode
- ❌ Missing unsaved changes detection
- ✅ All three modes: view/edit/create

## 📚 Required Imports by Page Type

### List Pages
```typescript
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useEntityStore } from '@/stores/entity'
import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
import { useErrorDisplayStore } from '@/stores/errorDisplay'
import { useDeleteConfirmationStore } from '@/stores/deleteConfirmation'
import ListView from '@/components/layout/list/ListView.vue'
import { EntityIcon } from '@heroicons/vue/24/solid'
```

### Detail Pages
```typescript
import { computed, ref, onMounted, watch } from 'vue'
import { useRoute, useRouter, onBeforeRouteLeave } from 'vue-router'
import { useEntityStore } from '@/stores/entity'
import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
import { useErrorDisplayStore } from '@/stores/errorDisplay'
import { useDeleteConfirmationStore } from '@/stores/deleteConfirmation'
import { useCancelChangesConfirmationStore } from '@/stores/cancelChangesConfirmation'
import DetailView from '@/components/layout/detail/DetailView.vue'
import { EntityIcon } from '@heroicons/vue/24/solid'
```

## 🔍 Validation Checklist

Before submitting:
- [ ] Correct page pattern implemented
- [ ] Entity colors consistent across all usage
- [ ] Only Heroicons used (no inline SVG)
- [ ] Navigation sync (Home.vue ↔ AppHeader.vue)  
- [ ] All required features implemented
- [ ] Proper TypeScript typing (no `any`)
- [ ] Event handling with `@click.stop` where needed
- [ ] Store integration and error handling
- [ ] Responsive design with Tailwind classes

## 🆘 Need Help?

1. **Examples**: Check existing pages (Contexts.vue, ContextDetail.vue) 
2. **Components**: Browse [Component Reference](components/)
3. **Patterns**: Review [Page Patterns](page-patterns.md) guide
4. **Standards**: Check [Coding Guidelines](guidelines/coding-guidelines.md)
