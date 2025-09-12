<template>
  <button
    :class="[
      'inline-flex items-center px-4 py-2 border shadow-sm text-sm font-medium rounded-md disabled:opacity-50 disabled:cursor-not-allowed',
      getThemeClass('formBorder'),
      getThemeClass('secondaryButton'),
      getThemeClass('inputFocus'),
    ]"
    :disabled="disabled"
    @click="!disabled && $emit('click')"
  >
    <XMarkIcon class="h-4 w-4 mr-2" />
    {{ label || 'Cancel' }}
  </button>
</template>

<script setup lang="ts">
  import { XMarkIcon } from '@heroicons/vue/24/outline'
  import { getThemeClass } from '@/composables/useColors'

  defineProps<{
    disabled?: boolean
    label?: string
  }>()

  defineEmits<{
    click: []
  }>()
</script>
