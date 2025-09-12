<template>
  <Card :title="title" :description="description" :main-color="mainColor">
    <template #icon>
      <slot name="icon" />
    </template>

    <template #footer>
      <div :class="pillClasses">
        {{ pillText }}
      </div>
    </template>
  </Card>
</template>

<script setup lang="ts">
  import { computed } from 'vue'
  import Card from './Card.vue'
  import { useColors, type ColorName } from '@/composables/useColors'

  const props = defineProps<{
    title: string
    description: string
    mainColor: ColorName
    pillText: string
  }>()

  const colorClasses = useColors(computed(() => props.mainColor))
  const pillClasses = computed(
    () =>
      `${colorClasses.value.badgeBackground} ${colorClasses.value.badgeText} inline-flex items-center px-3 py-1 text-sm font-medium rounded-full`
  )
</script>
