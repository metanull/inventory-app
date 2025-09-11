<template>
  <th
    :scope="scope"
    :class="computedClasses"
    :style="sortable ? 'cursor:pointer;' : ''"
    @click="sortable ? $emit('sort') : undefined"
  >
    <div class="flex items-center">
      <slot />
      <span v-if="sortable" class="ml-2">
        <ChevronUpIcon
          v-if="sortDirection && sortDirection === 'asc'"
          :class="['h-4 w-4', colorClasses.badge]"
        />
        <ChevronDownIcon
          v-else-if="sortDirection && sortDirection === 'desc'"
          :class="['h-4 w-4', colorClasses.badge]"
        />
        <ChevronUpDownIcon v-else :class="['h-4 w-4', colorClasses.badge]" />
      </span>
    </div>
  </th>
</template>

<script setup lang="ts">
  import { computed } from 'vue'
  import { ChevronUpIcon, ChevronDownIcon, ChevronUpDownIcon } from '@heroicons/vue/24/outline'
  import { useUIColors } from '@/composables/useColors'

  defineEmits(['sort'])

  interface Props {
    scope?: 'col' | 'row'
    variant?: 'default' | 'actions'
    sortable?: boolean
    sortDirection?: 'asc' | 'desc' | null
  }

  const props = withDefaults(defineProps<Props>(), {
    scope: 'col',
    variant: 'default',
    sortable: false,
    sortDirection: null,
  })

  const colorClasses = useUIColors('primary')

  const computedClasses = computed(() => {
    const baseClasses = 'px-6 py-3'

    switch (props.variant) {
      case 'actions':
        return `${baseClasses} relative`
      case 'default':
      default:
        return `${baseClasses} text-left text-xs font-medium text-gray-500 uppercase tracking-wide`
    }
  })
</script>
