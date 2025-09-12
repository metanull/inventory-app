<template>
  <button
    :class="[getThemeClass('dangerButton'), 'disabled:opacity-50 disabled:cursor-not-allowed']"
    :disabled="disabled"
    @click="!disabled && $emit('click')"
  >
    <TrashIcon class="h-4 w-4 mr-2" />
    {{ label || 'Delete' }}
  </button>
</template>

<script setup lang="ts">
  import { TrashIcon } from '@heroicons/vue/24/outline'
  import { getThemeClass } from '@/composables/useColors'
  defineProps<{
    disabled?: boolean
    label?: string
  }>()

  defineEmits<{
    click: []
  }>()
</script>
