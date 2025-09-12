<template>
  <dt :class="termClasses">
    <slot />
  </dt>
</template>

<script setup lang="ts">
  import { computed } from 'vue'
  import { getThemeClass } from '@/composables/useColors'

  const props = defineProps<{
    variant?: 'default' | 'small-gray'
  }>()

  const termClasses = computed(() => {
    switch (props.variant) {
      case 'small-gray':
        return getThemeClass('mobileMuted') + ' text-xs font-medium uppercase tracking-wide'
      default:
        return getThemeClass('neutralText') + ' text-sm font-medium'
    }
  })
</script>
