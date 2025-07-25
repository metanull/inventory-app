<template>
  <div :class="rowClasses">
    <slot />
  </div>
</template>

<script setup lang="ts">
  import { computed } from 'vue'

  const props = defineProps<{
    variant?: 'gray' | 'white'
    size?: 'default' | 'small'
  }>()

  const rowClasses = computed(() => {
    const baseClasses =
      props.size === 'small'
        ? 'px-4 py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6'
        : 'px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6'
    const variantClass = props.variant === 'gray' ? 'bg-gray-50' : 'bg-white'
    return `${baseClasses} ${variantClass}`
  })
</script>
