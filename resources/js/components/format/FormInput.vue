<template>
  <input
    :type="type"
    :placeholder="placeholder"
    :required="required"
    :disabled="disabled"
    :value="modelValue"
    :class="[
      'block w-full px-3 py-2 rounded-md shadow-sm sm:text-sm border',
      getThemeClass('formBorder'),
      getThemeClass('inputFocus'),
    ]"
    @input="handleInput"
  />
</template>

<script setup lang="ts">
  import { getThemeClass } from '@/composables/useColors'

  defineProps<{
    type?: string
    placeholder?: string
    required?: boolean
    disabled?: boolean
    modelValue?: string
  }>()

  const emit = defineEmits<{
    'update:modelValue': [value: string]
  }>()

  const handleInput = (event: Event) => {
    const target = event.target as HTMLInputElement
    emit('update:modelValue', target.value)
  }
</script>
