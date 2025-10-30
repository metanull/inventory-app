<template>
  <div>
    <!-- Header -->
    <div class="sm:flex sm:items-center">
      <div class="sm:flex-auto">
        <div class="flex items-center">
          <div v-if="$slots.icon" :class="iconClasses" class="h-8 w-8 mr-3">
            <slot name="icon" />
          </div>
          <Title variant="page" :description="description">
            {{ title }}
          </Title>
        </div>
      </div>
      <div v-if="addButtonRoute" class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
        <AddButton :to="addButtonRoute" :label="addButtonLabel" :color="color" />
      </div>
    </div>

    <!-- Filter Buttons -->
    <div
      v-if="(filters && filters.length > 0) || $slots.filters || $slots.search"
      class="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-between sm:items-center"
    >
      <div class="flex flex-wrap gap-1">
        <slot name="filters" :color="color" />
      </div>
      <div v-if="$slots.search" class="flex-shrink-0 w-full sm:w-auto">
        <slot name="search" :color="color" />
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="isEmpty" class="text-center py-12">
      <div v-if="$slots.icon" :class="iconClasses" class="mx-auto h-12 w-12 mb-4">
        <slot name="icon" />
      </div>
      <FolderIcon
        v-else
        :class="['mx-auto h-12 w-12', getThemeClass('mobileMuted')]"
        aria-hidden="true"
      />
      <Title variant="empty" :description="emptyMessage">
        {{ emptyTitle }}
      </Title>
    </div>

    <!-- Content Slot -->
    <div v-else class="mt-8">
      <div class="flow-root">
        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
              <TableElement>
                <template #headers>
                  <slot name="headers" />
                </template>
                <template #rows>
                  <slot name="rows" />
                </template>
              </TableElement>
            </div>
            <!-- Optional pagination area -->
            <div v-if="$slots.pagination" class="mt-4">
              <slot name="pagination" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Slot -->
    <slot name="modals" />
  </div>
</template>

<script setup lang="ts">
  import { computed } from 'vue'
  import AddButton from '@/components/layout/list/AddButton.vue'
  import Title from '@/components/format/title/Title.vue'
  import TableElement from '@/components/format/table/TableElement.vue'
  import { FolderIcon } from '@heroicons/vue/24/outline'
  import { useColors, type ColorName, getThemeClass } from '@/composables/useColors'

  const props = defineProps<{
    title: string
    description?: string
    addButtonRoute?: string
    addButtonLabel?: string
    isEmpty: boolean
    emptyTitle: string
    emptyMessage: string
    filters?: Array<{ label: string; count?: number; isActive: boolean; variant?: string }>
    color?: ColorName
  }>()

  // Use centralized color system
  const colorClasses = useColors(computed(() => props.color || 'gray'))
  const iconClasses = computed(() => colorClasses.value.icon)
</script>
