<template>
  <td :class="computedClasses">
    <slot />
  </td>
</template>

<script setup lang="ts">
  import { computed } from 'vue'

  interface Props {
    variant?: 'default' | 'actions'
  }

  const props = withDefaults(defineProps<Props>(), {
    variant: 'default',
  })

  const computedClasses = computed(() => {
    const baseClasses = 'px-6 py-4 whitespace-nowrap'

    switch (props.variant) {
      case 'actions':
        return `${baseClasses} text-right text-sm font-medium`
      case 'default':
      default:
        return baseClasses
    }
  })
</script>
