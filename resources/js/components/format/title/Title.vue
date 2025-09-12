<template>
  <div :class="wrapperClasses">
    <component :is="headingTag" :class="headingClasses">
      <slot />
    </component>
    <p v-if="description || $slots.description" :class="descriptionClasses">
      <slot name="description">{{ description }}</slot>
    </p>
  </div>
</template>

<script setup lang="ts">
  import { computed } from 'vue'
  import { getThemeClass } from '@/composables/useColors'

  const props = defineProps<{
    variant?: 'page' | 'section' | 'card' | 'system' | 'empty'
    description?: string
    level?: 1 | 2 | 3 | 4 | 5 | 6
  }>()

  const headingTag = computed(() => {
    if (props.level) {
      return `h${props.level}`
    }

    switch (props.variant) {
      case 'page':
        return 'h1'
      case 'card':
        return 'h2'
      case 'section':
      case 'system':
      case 'empty':
      default:
        return 'h3'
    }
  })

  const wrapperClasses = computed(() => {
    switch (props.variant) {
      case 'page':
        return 'mb-2'
      case 'card':
        return 'mb-4'
      case 'empty':
        return 'mt-2'
      default:
        return ''
    }
  })

  const headingClasses = computed(() => {
    switch (props.variant) {
      case 'page':
        return `text-2xl font-semibold ${getThemeClass('modalTitle')}`
      case 'section':
        return `text-lg leading-6 font-medium ${getThemeClass('modalTitle')}`
      case 'card':
        return `text-xl font-semibold ${getThemeClass('modalTitle')}`
      case 'system':
        return `text-base leading-6 font-medium ${getThemeClass('appTitleColor')}`
      case 'empty':
        return `text-sm font-medium ${getThemeClass('modalTitle')}`
      default:
        return `text-lg leading-6 font-medium ${getThemeClass('modalTitle')}`
    }
  })

  const descriptionClasses = computed(() => {
    switch (props.variant) {
      case 'page':
        return `mt-2 text-sm ${getThemeClass('appTitleColor')}`
      case 'section':
        return `mt-1 max-w-2xl text-sm ${getThemeClass('neutralText')}`
      case 'card':
        return `${getThemeClass('neutralText')} mb-4`
      case 'system':
        return `mt-1 max-w-2xl text-xs ${getThemeClass('mobileMutedText')}`
      case 'empty':
        return `mt-1 text-sm ${getThemeClass('neutralText')}`
      default:
        return `mt-1 max-w-2xl text-sm ${getThemeClass('neutralText')}`
    }
  })
</script>
