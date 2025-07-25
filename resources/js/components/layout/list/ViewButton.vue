<template>
  <button
    class="inline-flex items-center justify-center w-8 h-8 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-full transition-colors"
    :class="{ 'opacity-50 cursor-not-allowed': disabled }"
    :disabled="disabled"
    :title="tooltip || 'View'"
    @click="!disabled && $emit('click')"
  >
    <EyeIcon class="w-5 h-5" />
  </button>
</template>

<script setup lang="ts">
  import { EyeIcon } from '@heroicons/vue/24/outline'
  defineProps<{
    disabled?: boolean
    tooltip?: string
  }>()

  defineEmits<{
    click: []
  }>()
</script>
