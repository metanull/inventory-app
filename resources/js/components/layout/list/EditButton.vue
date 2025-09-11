<template>
  <button
    :class="[
      colorClasses.badge,
      colorClasses.buttonHover,
      disabled ? 'text-gray-400 cursor-not-allowed' : '',
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
  import { useUIColors } from '@/composables/useColors'

  defineProps<{
    disabled?: boolean
    tooltip?: string
  }>()

  defineEmits<{
    click: []
  }>()

  const colorClasses = useUIColors('primary')
</script>
