<template>
  <tr :class="computedClasses">
    <slot />
  </tr>
</template>

<script setup lang="ts">
  import { computed } from 'vue'

  interface Props {
    variant?: 'default' | 'hover'
  }

  const props = withDefaults(defineProps<Props>(), {
    variant: 'hover',
  })

  const computedClasses = computed(() => {
    switch (props.variant) {
      case 'hover':
        return 'hover:bg-gray-50'
      case 'default':
      default:
        return ''
    }
  })
</script>
