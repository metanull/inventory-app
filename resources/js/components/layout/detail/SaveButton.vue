<template>
  <button
    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
    :disabled="disabled || loading"
    @click="!disabled && !loading && $emit('click')"
  >
    <CheckIcon class="h-4 w-4 mr-2" />
    {{ label || 'Save' }}
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
</script>
