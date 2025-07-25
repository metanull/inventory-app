<template>
  <Card :title="title" :description="description" :main-color="mainColor">
    <template #icon>
      <slot name="icon" />
    </template>

    <div
      :class="pillClasses"
      class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full"
    >
      {{ pillText }}
    </div>
  </Card>
</template>

<script setup lang="ts">
  import { computed } from 'vue'
  import Card from './Card.vue'

  const props = defineProps<{
    title: string
    description: string
    mainColor: string
    pillText: string
  }>()

  const pillClasses = computed(() => {
    const colorMap: Record<string, string> = {
      blue: 'bg-blue-200 text-blue-600',
      green: 'bg-green-200 text-green-600',
      purple: 'bg-purple-200 text-purple-600',
      orange: 'bg-orange-200 text-orange-600',
      red: 'bg-red-200 text-red-600',
      gray: 'bg-gray-200 text-gray-600',
    }
    return colorMap[props.mainColor] || 'bg-blue-200 text-blue-600'
  })
</script>
