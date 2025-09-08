<template>
  <button
    :class="[
      'text-blue-600 hover:text-blue-900',
      disabled ? 'text-gray-400 cursor-not-allowed' : 'hover:text-blue-900',
    ]"
    :title="tooltip || 'Edit'"
    :disabled="disabled"
    @click="!disabled && $emit('click')"
  >
    <PencilIcon class="h-4 w-4" />
  </button>
</template>

<script setup lang="ts">
  import { PencilIcon } from '@heroicons/vue/24/outline'
  defineProps<{
    disabled?: boolean
    tooltip?: string
  }>()

  defineEmits<{
    click: []
  }>()
</script>
