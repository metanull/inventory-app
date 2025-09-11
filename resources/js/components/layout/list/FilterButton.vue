<template>
  <button
    :class="[
      isActive ? activeClasses : inactiveClasses,
      'px-3 py-2 font-medium text-sm rounded-md',
    ]"
    @click="$emit('click')"
  >
    {{ label }}
    <!-- Count pill/text responsive display -->
    <span
      v-if="count !== undefined"
      :class="[
        'ml-1',
        // Hide on xs
        'hidden sm:inline',
        // Pill style on md+
        count !== undefined
          ? 'md:inline-flex md:items-center md:justify-center md:px-2 md:py-0.5 md:text-xs md:font-semibold md:rounded-full'
          : '',
        // Color for pill - use centralized colors when color prop is provided
        count === 0 && props.color ? `md:${colorClasses.badgeBackground} md:${colorClasses.badgeText}` : '',
        count > 0 && props.color ? `md:${colorClasses.badgeBackground} md:${colorClasses.badgeText}` : '',
        // Fall back to blue/orange when no color prop (default behavior)
        count === 0 && !props.color ? 'md:bg-orange-100 md:text-orange-700' : '',
        count > 0 && !props.color ? 'md:bg-blue-100 md:text-blue-700' : '',
      ]"
    >
      <!-- Pill on md+, text on sm+ -->
      <span class="md:hidden">({{ count }})</span>
      <span class="hidden md:inline">{{ count }}</span>
    </span>
  </button>
</template>

<script setup lang="ts">
  import { computed } from 'vue'
  import { useColors, type ColorName } from '@/composables/useColors'

  const props = defineProps<{
    label: string
    isActive: boolean
    count?: number
    variant?: 'primary' | 'success' | 'info'
    color?: ColorName
  }>()

  defineEmits<{
    click: []
  }>()

  // Color classes from centralized system
  const colorClasses = useColors(computed(() => props.color || 'indigo'))

  const activeClasses = computed(() => {
    // If color prop is provided, use centralized color system
    if (props.color) {
      return `${colorClasses.value.badgeBackground} ${colorClasses.value.badgeText}`
    }

    // Fall back to variant-based semantic logic when no color is provided
    switch (props.variant) {
      case 'success':
        return 'bg-green-100 text-green-700'
      case 'info':
        return 'bg-blue-100 text-blue-700'
      default:
        return 'bg-indigo-100 text-indigo-700'
    }
  })

  const inactiveClasses = 'text-gray-500 hover:text-gray-700'
</script>
