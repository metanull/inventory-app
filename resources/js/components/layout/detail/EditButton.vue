<template>
  <button
    :class="[
      'inline-flex items-center px-2 sm:px-4 py-2 border shadow-sm text-sm font-medium rounded-md disabled:opacity-50 disabled:cursor-not-allowed',
      getThemeClass('formBorder'),
      getThemeClass('secondaryButton'),
      colorClasses.buttonHover,
      colorClasses.focus,
    ]"
    :disabled="disabled"
    @click="!disabled && $emit('click')"
  >
    <PencilIcon class="h-4 w-4 sm:mr-2" />
    <span class="hidden sm:inline">{{ label || 'Edit' }}</span>
  </button>
</template>

<script setup lang="ts">
  import { PencilIcon } from '@heroicons/vue/24/outline'
  import { useUIColors, getThemeClass } from '@/composables/useColors'

  defineProps<{
    disabled?: boolean
    label?: string
  }>()

  defineEmits<{
    click: []
  }>()

  const colorClasses = useUIColors('primary')
</script>
