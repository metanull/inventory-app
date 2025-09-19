<template>
  <button
    :class="[
      'inline-flex items-center px-2 sm:px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white disabled:opacity-50 disabled:cursor-not-allowed',
      successColors.button,
      successColors.buttonHover,
      'focus:outline-none focus:ring-2 focus:ring-offset-2',
      successColors.focus,
    ]"
    :disabled="disabled || loading"
    @click="!disabled && !loading && $emit('click')"
  >
    <CheckIcon class="h-4 w-4 sm:mr-2" />
    <span class="hidden sm:inline">{{ label || 'Save' }}</span>
  </button>
</template>

<script setup lang="ts">
  import { CheckIcon } from '@heroicons/vue/24/outline'
  defineProps<{
    disabled?: boolean
    loading?: boolean
    label?: string
  }>()

  defineEmits<{
    click: []
  }>()

  import { useUIColors } from '@/composables/useColors'

  const successColors = useUIColors('success')
</script>
