<template>
  <span :class="[baseClasses, props.className]" :title="fullDate">
    {{ formattedDate }}
  </span>
</template>

<script setup lang="ts">
  import { computed } from 'vue'

  const props = defineProps<{
    date: string | null | undefined
    format?: 'short' | 'medium' | 'long' | 'full'
    className?: string
    showTime?: boolean
    variant?: 'default' | 'small-dark'
  }>()

  const baseClasses = computed(() => {
    switch (props.variant) {
      case 'small-dark':
        return 'text-sm text-gray-900'
      case 'default':
      default:
        return ''
    }
  })

  const formattedDate = computed(() => {
    if (!props.date) return 'N/A'

    try {
      const date = new Date(props.date)

      if (isNaN(date.getTime())) {
        return 'Invalid Date'
      }

      const options: Intl.DateTimeFormatOptions = {}

      // Set date format options
      switch (props.format || 'medium') {
        case 'short':
          options.dateStyle = 'short'
          break
        case 'medium':
          options.dateStyle = 'medium'
          break
        case 'long':
          options.dateStyle = 'long'
          break
        case 'full':
          options.dateStyle = 'full'
          break
      }

      // Add time if requested
      if (props.showTime) {
        options.timeStyle = 'short'
      }

      return new Intl.DateTimeFormat(navigator.language, options).format(date)
    } catch {
      return 'Invalid Date'
    }
  })

  const fullDate = computed(() => {
    if (!props.date) return 'No date available'

    try {
      const date = new Date(props.date)
      if (isNaN(date.getTime())) {
        return 'Invalid date'
      }
      return new Intl.DateTimeFormat(navigator.language, {
        dateStyle: 'full',
        timeStyle: 'medium',
      }).format(date)
    } catch {
      return 'Invalid date'
    }
  })
</script>
