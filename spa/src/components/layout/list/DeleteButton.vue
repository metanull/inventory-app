<template>
  <button
    :class="[
      dangerClasses.icon,
      disabled ? getThemeClass('mobileMuted') + ' cursor-not-allowed' : dangerClasses.buttonHover,
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
  import { useColors, getThemeClass } from '@/composables/useColors'
  defineProps<{
    disabled?: boolean
    tooltip?: string
  }>()

  defineEmits<{
    click: []
  }>()

  const dangerClasses = useColors('red')
</script>
