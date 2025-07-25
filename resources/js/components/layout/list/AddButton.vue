<template>
  <router-link :to="to" :class="buttonClasses">
    <PlusIcon class="-ml-1 mr-2 h-5 w-5" aria-hidden="true" />
    {{ label || 'Add Item' }}
  </router-link>
</template>

<script setup lang="ts">
  import { computed } from 'vue'
  import { PlusIcon } from '@heroicons/vue/20/solid'

  const props = defineProps<{
    to: string
    label?: string
    color?: string
  }>()

  const buttonClasses = computed(() => {
    const baseClasses =
      'inline-flex items-center justify-center rounded-md border border-transparent px-4 py-2 text-sm font-medium text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 sm:w-auto'

    if (!props.color) {
      return `${baseClasses} bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500`
    }

    const colorMap: Record<string, string> = {
      blue: 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500',
      green: 'bg-green-600 hover:bg-green-700 focus:ring-green-500',
      purple: 'bg-purple-600 hover:bg-purple-700 focus:ring-purple-500',
      orange: 'bg-orange-600 hover:bg-orange-700 focus:ring-orange-500',
      red: 'bg-red-600 hover:bg-red-700 focus:ring-red-500',
      yellow: 'bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500',
      indigo: 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500',
      pink: 'bg-pink-600 hover:bg-pink-700 focus:ring-pink-500',
      gray: 'bg-gray-600 hover:bg-gray-700 focus:ring-gray-500',
    }

    return `${baseClasses} ${colorMap[props.color] || 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500'}`
  })
</script>
