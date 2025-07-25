<template>
  <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow p-6">
    <div class="flex items-center">
      <div :class="iconClasses" class="h-8 w-8 mr-3">
        <slot name="icon" />
      </div>
      <Title variant="card" :description="description">
        {{ title }}
      </Title>
    </div>
    <slot />
  </div>
</template>

<script setup lang="ts">
  import { computed } from 'vue'
  import Title from '@/components/format/title/Title.vue'

  const props = defineProps<{
    title: string
    description: string
    mainColor: string
  }>()

  const iconClasses = computed(() => {
    const colorMap: Record<string, string> = {
      blue: 'text-blue-600',
      green: 'text-green-600',
      purple: 'text-purple-600',
      orange: 'text-orange-600',
      red: 'text-red-600',
      gray: 'text-gray-600',
    }
    return colorMap[props.mainColor] || 'text-blue-600'
  })
</script>
