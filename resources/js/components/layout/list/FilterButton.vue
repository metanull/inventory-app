<template>
  <button
    :class="[
      isActive ? activeClasses : inactiveClasses,
      'px-3 py-2 font-medium text-sm rounded-md',
    ]"
    @click="$emit('click')"
  >
    {{ label }}
    <!-- Count pill/text responsive display -->
    <span
      v-if="count !== undefined"
      :class="[
        'ml-1',
        // Hide on xs
        'hidden sm:inline',
        // Pill style on md+
        count !== undefined
          ? 'md:inline-flex md:items-center md:justify-center md:px-2 md:py-0.5 md:text-xs md:font-semibold md:rounded-full'
          : '',
        // Color for pill
        count === 0 ? 'md:bg-orange-100 md:text-orange-700' : '',
        count > 0 ? 'md:bg-blue-100 md:text-blue-700' : '',
      ]"
    >
      <!-- Pill on md+, text on sm+ -->
      <span class="md:hidden">({{ count }})</span>
      <span class="hidden md:inline">{{ count }}</span>
    </span>
  </button>
</template>

<script setup lang="ts">
  import { computed } from 'vue'

  const props = defineProps<{
    label: string
    isActive: boolean
    count?: number
    variant?: 'primary' | 'success' | 'info'
  }>()

  defineEmits<{
    click: []
  }>()

  const activeClasses = computed(() => {
    switch (props.variant) {
      case 'success':
        return 'bg-green-100 text-green-700'
      case 'info':
        return 'bg-blue-100 text-blue-700'
      default:
        return 'bg-indigo-100 text-indigo-700'
    }
  })

  const inactiveClasses = 'text-gray-500 hover:text-gray-700'
</script>
