<template>
  <router-link :to="to" :class="buttonClasses">
    <PlusIcon class="-ml-1 mr-2 h-5 w-5" aria-hidden="true" />
    {{ label || 'Add Item' }}
  </router-link>
</template>

<script setup lang="ts">
  import { computed } from 'vue'
  import { PlusIcon } from '@heroicons/vue/20/solid'
  import { useColors, type ColorName } from '@/composables/useColors'

  const props = defineProps<{
    to: string
    label?: string
    color?: ColorName
  }>()

  // Color classes from centralized system
  const colorClasses = useColors(computed(() => props.color || 'indigo'))

  const buttonClasses = computed(() => {
    const baseClasses =
      'inline-flex items-center justify-center rounded-md border border-transparent px-4 py-2 text-sm font-medium text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 sm:w-auto'

    return `${baseClasses} ${colorClasses.value.button} ${colorClasses.value.ring}`
  })
</script>
