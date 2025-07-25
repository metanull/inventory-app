<template>
  <button
    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
    :disabled="disabled"
    @click="!disabled && $emit('click')"
  >
    <XMarkIcon class="h-4 w-4 mr-2" />
    {{ label || 'Cancel' }}
  </button>
</template>

<script setup lang="ts">
  import { XMarkIcon } from '@heroicons/vue/24/outline'
  defineProps<{
    disabled?: boolean
    label?: string
  }>()

  defineEmits<{
    click: []
  }>()
</script>
