<template>
  <textarea
    :value="modelValue"
    :rows="rows"
    :placeholder="placeholder"
    :class="[
      'block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset placeholder:text-gray-400 focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6',
      error ? 'ring-red-300 focus:ring-red-600' : 'ring-gray-300 focus:ring-indigo-600',
      disabled ? 'cursor-not-allowed bg-gray-50 text-gray-500' : '',
    ]"
    :disabled="disabled"
    @input="$emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
  />
  <p v-if="error" class="mt-2 text-sm text-red-600">{{ error }}</p>
</template>

<script setup lang="ts">
  interface Props {
    modelValue: string
    rows?: number
    placeholder?: string
    error?: string
    disabled?: boolean
  }

  withDefaults(defineProps<Props>(), {
    rows: 3,
    placeholder: '',
    error: '',
    disabled: false,
  })

  defineEmits<{
    'update:modelValue': [value: string]
  }>()
</script>
