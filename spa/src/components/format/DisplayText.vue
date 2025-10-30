<template>
  <span :class="computedClasses">
    <slot />
  </span>
</template>

<script setup lang="ts">
  import { computed } from 'vue'
  import { getThemeClass } from '@/composables/useColors'

  const props = defineProps<{
    variant?: 'default' | 'gray' | 'muted'
  }>()

  const computedClasses = computed(() => {
    const baseClasses = ''

    switch (props.variant) {
      case 'gray':
      case 'muted':
        return getThemeClass('neutralText')
      case 'default':
      default:
        return baseClasses
    }
  })
</script>

<style scoped>
  /* Using @apply would be here if we had complex styles */
</style>
