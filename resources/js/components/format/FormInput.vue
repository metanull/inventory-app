<template>
  <input
    :type="type"
    :placeholder="placeholder"
    :required="required"
    :disabled="disabled"
    :value="modelValue"
    class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
    @input="handleInput"
  />
</template>

<script setup lang="ts">
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
