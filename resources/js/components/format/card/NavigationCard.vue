<template>
  <Card :title="title" :description="description" :main-color="mainColor">
    <template #icon>
      <slot name="icon" />
    </template>

    <router-link
      :to="buttonRoute"
      :class="buttonClasses"
      class="inline-flex items-center px-4 py-2 text-white text-sm font-medium rounded-md transition-colors"
    >
      {{ buttonText }}
      <ChevronRightIcon class="ml-2 h-4 w-4" />
    </router-link>
  </Card>
</template>

<script setup lang="ts">
  import { computed } from 'vue'
  import { ChevronRightIcon } from '@heroicons/vue/24/outline'
  import Card from './Card.vue'

  const props = defineProps<{
    title: string
    description: string
    mainColor: string
    buttonText: string
    buttonRoute: string
  }>()

  const buttonClasses = computed(() => {
    const colorMap: Record<string, string> = {
      blue: 'bg-blue-600 hover:bg-blue-700',
      teal: 'bg-teal-600 hover:bg-teal-700',
      green: 'bg-green-600 hover:bg-green-700',
      purple: 'bg-purple-600 hover:bg-purple-700',
      orange: 'bg-orange-600 hover:bg-orange-700',
      red: 'bg-red-600 hover:bg-red-700',
      yellow: 'bg-yellow-600 hover:bg-yellow-700',
      gray: 'bg-gray-600 hover:bg-gray-700',
    }
    return colorMap[props.mainColor] || 'bg-blue-600 hover:bg-blue-700'
  })
</script>
