<template>
  <GenericDropdown
    :model-value="modelValue"
    :options="transformedOptions"
    :show-no-default-option="clearable"
    :no-default-label="placeholder"
    no-default-value=""
    :disabled="disabled"
    @update:model-value="$emit('update:modelValue', $event)"
  />
</template>

<script setup lang="ts">
  import { computed } from 'vue'
  import GenericDropdown from '@/components/format/GenericDropdown.vue'

  interface SelectOption {
    value: string
    label: string
  }

  interface Props {
    modelValue: string
    options: SelectOption[]
    placeholder?: string
    clearable?: boolean
    disabled?: boolean
  }

  const props = withDefaults(defineProps<Props>(), {
    placeholder: 'Select an option',
    clearable: false,
    disabled: false,
  })

  // Transform our simple options into GenericDropdown format
  const transformedOptions = computed(() => {
    return props.options.map(opt => ({
      id: opt.value,
      internal_name: opt.label,
    }))
  })

  defineEmits<{
    'update:modelValue': [value: string]
  }>()
</script>
