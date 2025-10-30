<template>
  <div
    class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow p-6 flex flex-col h-full"
  >
    <div class="flex items-center">
      <div :class="iconClasses" class="h-8 w-8 lg:h-16 lg:w-16 mr-3 flex-shrink-0">
        <div class="h-full w-full [&>svg]:h-full [&>svg]:w-full">
          <slot name="icon" />
        </div>
      </div>
      <Title variant="card" :description="description">
        {{ title }}
      </Title>
    </div>
    <slot />

    <!-- Footer slot: pinned to bottom by mt-auto when present -->
    <div v-if="$slots.footer" class="mt-auto flex justify-center">
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup lang="ts">
  import { computed } from 'vue'
  import Title from '@/components/format/title/Title.vue'
  import { useColors, type ColorName } from '@/composables/useColors'

  const props = defineProps<{
    title: string
    description: string
    mainColor: ColorName
  }>()

  const colorClasses = useColors(computed(() => props.mainColor))
  const iconClasses = computed(() => colorClasses.value.icon)
</script>
