<template>
  <Card :title="title" :description="description" :main-color="mainColor">
    <template #icon>
      <slot name="icon" />
    </template>

    <template #footer>
      <button
        type="button"
        disabled
        :class="[
          colorClasses.button,
          'inline-flex items-center px-4 py-2 text-sm font-medium rounded-md transition-colors',
        ]"
      >
        <component
          :is="isActive ? activeIconComponent : inactiveIconComponent"
          :class="[iconSize, 'text-current']"
        />
        <span class="ml-2">{{ statusText }}</span>
      </button>
    </template>
  </Card>
</template>

<script setup lang="ts">
  import Card from './Card.vue'
  import { computed } from 'vue'
  import { useColors } from '@/composables/useColors'
  import type { ColorName } from '@/composables/useColors'

  const {
    title,
    description,
    mainColor,
    statusText,
    isActive,
    activeIconComponent,
    inactiveIconComponent,
  } = defineProps<{
    title: string
    description: string
    mainColor: ColorName
    statusText: string
    isActive: boolean
    activeIconComponent?: any
    inactiveIconComponent?: any
  }>()

  // Compute color classes for the button/icon
  const colorClasses = useColors(computed(() => mainColor))
  const iconSize = 'h-5 w-5'
</script>
