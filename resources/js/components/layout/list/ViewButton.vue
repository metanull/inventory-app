<template>
  <button
    :class="[
      'inline-flex items-center justify-center w-8 h-8 rounded-full transition-colors',
      colorClasses.badge,
      colorClasses.buttonHover,
      { 'opacity-50 cursor-not-allowed': disabled }
    ]"
    :disabled="disabled"
    :title="tooltip || 'View'"
    @click="!disabled && $emit('click')"
  >
    <EyeIcon class="w-5 h-5" />
  </button>
</template>

<script setup lang="ts">
  import { EyeIcon } from '@heroicons/vue/24/outline'
  import { useUIColors } from '@/composables/useColors'

  defineProps<{
    disabled?: boolean
    tooltip?: string
  }>()

  defineEmits<{
    click: []
  }>()

  const colorClasses = useUIColors('secondary')
</script>
