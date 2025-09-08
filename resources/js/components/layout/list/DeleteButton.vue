<template>
  <button
    :class="[
      'text-red-600 hover:text-red-900',
      disabled ? 'text-gray-400 cursor-not-allowed' : 'hover:text-red-900',
    ]"
    :title="tooltip || 'Delete'"
    :disabled="disabled"
    @click="!disabled && $emit('click')"
  >
    <TrashIcon class="h-4 w-4" />
  </button>
</template>

<script setup lang="ts">
  import { TrashIcon } from '@heroicons/vue/24/outline'
  defineProps<{
    disabled?: boolean
    tooltip?: string
  }>()

  defineEmits<{
    click: []
  }>()
</script>
