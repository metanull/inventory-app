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
    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
      ></path>
    </svg>
  </button>
</template>

<script setup lang="ts">
  defineProps<{
    disabled?: boolean
    tooltip?: string
  }>()

  defineEmits<{
    click: []
  }>()
</script>
