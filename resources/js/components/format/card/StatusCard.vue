<template>
  <!-- Compact mode: Only show the Toggle without Card wrapper -->
  <div v-if="compact" class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow p-6">
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

  <!-- Standard mode: Show Card with title, description, and Toggle -->
  <Card v-else :title="title" :description="description" :main-color="mainColor">
    <template #icon>
      <slot name="icon" />
    </template>

    <!-- API Status Toggle -->
    <div class="mt-4">
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
  </Card>
</template>

<script setup lang="ts">
  import Card from './Card.vue'
  import Toggle from '@/components/format/Toggle.vue'

  defineProps<{
    title: string
    description: string
    mainColor: string
    statusText: string
    toggleTitle: string
    isActive: boolean
    loading: boolean
    disabled?: boolean
    compact?: boolean // New prop for compact mode
    activeIconBackgroundClass: string
    inactiveIconBackgroundClass: string
    activeIconClass: string
    inactiveIconClass: string
    activeIconComponent: any
    inactiveIconComponent: any
  }>()

  defineEmits<{
    toggle: []
  }>()
</script>
