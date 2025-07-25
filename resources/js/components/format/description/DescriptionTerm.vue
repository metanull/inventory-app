<template>
  <dt :class="termClasses">
    <slot />
  </dt>
</template>

<script setup lang="ts">
  import { computed } from 'vue'

  const props = defineProps<{
    variant?: 'default' | 'small-gray'
  }>()

  const termClasses = computed(() => {
    switch (props.variant) {
      case 'small-gray':
        return 'text-xs font-medium text-gray-400 uppercase tracking-wide'
      default:
        return 'text-sm font-medium text-gray-500'
    }
  })
</script>
