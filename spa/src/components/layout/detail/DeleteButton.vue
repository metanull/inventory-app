<template>
  <button
    :class="[
      'inline-flex items-center px-2 sm:px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed',
    ]"
    :disabled="disabled"
    @click="!disabled && $emit('click')"
  >
    <TrashIcon class="h-4 w-4 sm:mr-2" />
    <span class="hidden sm:inline">{{ label || 'Delete' }}</span>
  </button>
</template>

<script setup lang="ts">
  import { TrashIcon } from '@heroicons/vue/24/outline'

  defineProps<{
    disabled?: boolean
    label?: string
  }>()

  defineEmits<{
    click: []
  }>()
</script>
