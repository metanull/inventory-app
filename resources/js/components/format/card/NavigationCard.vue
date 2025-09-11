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
  import { useColors, type ColorName } from '@/composables/useColors'

  const props = defineProps<{
    title: string
    description: string
    mainColor: ColorName
    buttonText: string
    buttonRoute: string
  }>()

  const colorClasses = useColors(computed(() => props.mainColor))
  const buttonClasses = computed(() => colorClasses.value.button)
</script>
