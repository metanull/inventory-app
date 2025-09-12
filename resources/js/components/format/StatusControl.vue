<template>
  <div
    :aria-label="title || toggleTitle"
    class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow p-6"
  >
    <Toggle
      :title="toggleTitle"
      :status-text="statusText"
      :is-active="isActive"
      :loading="loading"
      :disabled="disabled"
      :active-icon-background-class="activeIconBackgroundClass"
      :inactive-icon-background-class="inactiveIconBackgroundClass"
      :active-icon-class="activeIconClass"
      :inactive-icon-class="inactiveIconClass"
      :active-icon-component="activeIconComponent"
      :inactive-icon-component="inactiveIconComponent"
      @toggle="$emit('toggle')"
    />
  </div>
</template>

<script setup lang="ts">
  import Toggle from '@/components/format/Toggle.vue'

  const {
    title,
    toggleTitle = '',
    statusText,
    isActive,
    loading = false,
    disabled = false,
    activeIconBackgroundClass,
    inactiveIconBackgroundClass,
    activeIconClass,
    inactiveIconClass,
    activeIconComponent,
    inactiveIconComponent,
  } = defineProps<{
    title?: string
    toggleTitle?: string
    statusText: string
    isActive: boolean
    loading?: boolean
    disabled?: boolean
    activeIconBackgroundClass?: string
    inactiveIconBackgroundClass?: string
    activeIconClass?: string
    inactiveIconClass?: string
    activeIconComponent?: any
    inactiveIconComponent?: any
  }>()

  defineEmits<{
    toggle: []
  }>()
</script>
