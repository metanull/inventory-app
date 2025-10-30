<template>
  <dd :class="detailClasses">
    <slot />
  </dd>
</template>

<script setup lang="ts">
  import { computed } from 'vue'
  import { getThemeClass } from '@/composables/useColors'

  const props = defineProps<{
    variant?: 'default' | 'small-gray'
  }>()

  const detailClasses = computed(() => {
    switch (props.variant) {
      case 'small-gray':
        return `mt-1 sm:mt-0 sm:col-span-2 text-xs ${getThemeClass('neutralText')}`
      default:
        return `mt-1 text-sm ${getThemeClass('modalTitle')} sm:mt-0 sm:col-span-2`
    }
  })
</script>
