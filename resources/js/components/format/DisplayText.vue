<template>
  <span :class="computedClasses">
    <slot />
  </span>
</template>

<script setup lang="ts">
  import { computed } from 'vue'

  const props = defineProps<{
    variant?: 'default' | 'gray' | 'muted'
  }>()

  const computedClasses = computed(() => {
    const baseClasses = ''

    switch (props.variant) {
      case 'gray':
      case 'muted':
        return 'text-gray-500'
      case 'default':
      default:
        return baseClasses
    }
  })
</script>

<style scoped>
  /* Using @apply would be here if we had complex styles */
</style>
