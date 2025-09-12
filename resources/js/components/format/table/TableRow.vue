<template>
  <tr :class="computedClasses">
    <slot />
  </tr>
</template>

<script setup lang="ts">
  import { computed } from 'vue'
  import { getThemeClass } from '@/composables/useColors'

  interface Props {
    variant?: 'default' | 'hover'
  }

  const props = withDefaults(defineProps<Props>(), {
    variant: 'hover',
  })

  const computedClasses = computed(() => {
    switch (props.variant) {
      case 'hover':
        return `hover:${getThemeClass('modalActionsBg')}`
      case 'default':
      default:
        return ''
    }
  })
</script>
