<template>
  <select
    :value="modelValue"
    :class="[
      'block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm',
      disabled ? 'bg-gray-100 cursor-not-allowed' : '',
    ]"
    :disabled="disabled"
    @change="handleChange"
  >
    <!-- Priority items first -->
    <template v-if="sortedOptions.priorityItems.length > 0">
      <option
        v-for="option in sortedOptions.priorityItems"
        :key="option.id"
        :value="option.id"
        :class="getOptionClasses(option)"
      >
        {{ formatOptionText(option) }}
      </option>
    </template>

    <!-- No default option (if enabled) -->
    <option v-if="showNoDefaultOption" :value="noDefaultValue" :class="getNoDefaultOptionClasses()">
      {{ formatNoDefaultText() }}
    </option>

    <!-- Separator (if priority items exist or no default option is shown) -->
    <option v-if="sortedOptions.priorityItems.length > 0 || showNoDefaultOption" disabled>
      ────────────────
    </option>

    <!-- Remaining items -->
    <option
      v-for="option in sortedOptions.remainingItems"
      :key="option.id"
      :value="option.id"
      :class="getOptionClasses(option)"
    >
      {{ formatOptionText(option) }}
    </option>
  </select>
</template>

<script setup lang="ts">
  import { computed } from 'vue'
  import {
    getDropdownOptionClasses,
    getDropdownOptionLabel,
    DROPDOWN_OPTION_LABELS,
    type DropdownOption,
  } from '@/utils/dropdownStyles'

  // Generic interface for dropdown options
  interface GenericDropdownOption extends DropdownOption {
    id: string
    internal_name: string
    is_default?: boolean
  }

  // Props interface
  interface Props {
    modelValue: string
    options: GenericDropdownOption[]
    disabled?: boolean
    showNoDefaultOption?: boolean
    noDefaultValue?: string
    noDefaultLabel?: string
    sortFunction?: (options: GenericDropdownOption[]) => GenericDropdownOption[] // eslint-disable-line no-unused-vars
    highlightFunction?: (
      option: GenericDropdownOption, // eslint-disable-line no-unused-vars
      currentValue: string // eslint-disable-line no-unused-vars
    ) => { classes: string; label: string } | null
  }

  // Default props
  const props = withDefaults(defineProps<Props>(), {
    disabled: false,
    showNoDefaultOption: true,
    noDefaultValue: '',
    noDefaultLabel: 'No default',
    sortFunction: undefined,
    highlightFunction: undefined,
  })

  // Emits
  const emit = defineEmits<{
    'update:modelValue': [value: string]
  }>()

  // Computed properties
  const sortedOptions = computed(() => {
    if (!props.options.length) return { priorityItems: [], remainingItems: [] }

    const priorityItems: GenericDropdownOption[] = []
    const remainingItems: GenericDropdownOption[] = []

    // Add current selection if it exists (regardless of whether it's default)
    const currentOption = props.options.find(
      (o: GenericDropdownOption) => o.id === props.modelValue
    )
    if (currentOption) {
      priorityItems.push(currentOption)
    }

    // Add system default if it exists and is not current
    const defaultOption = props.options.find((o: GenericDropdownOption) => o.is_default)
    if (defaultOption && defaultOption.id !== props.modelValue) {
      priorityItems.push(defaultOption)
    }

    // Add remaining items, sorted by the sort function
    const remainingUnsorted = props.options.filter((option: GenericDropdownOption) => {
      const isAlreadyInPriority = priorityItems.some(p => p.id === option.id)
      return !isAlreadyInPriority
    })

    if (props.sortFunction) {
      remainingItems.push(...props.sortFunction(remainingUnsorted))
    } else {
      remainingItems.push(
        ...remainingUnsorted.sort((a: GenericDropdownOption, b: GenericDropdownOption) =>
          a.internal_name.localeCompare(b.internal_name)
        )
      )
    }

    return { priorityItems, remainingItems }
  })

  // Methods
  const handleChange = (event: Event) => {
    const target = event.target as unknown as { value: string }
    emit('update:modelValue', target.value)
  }

  const getOptionClasses = (option: GenericDropdownOption) => {
    if (props.highlightFunction) {
      const result = props.highlightFunction(option, props.modelValue)
      return result?.classes || ''
    }

    const isCurrent = option.id === props.modelValue
    const isSystemDefault = option.is_default || false
    return getDropdownOptionClasses(isCurrent, isSystemDefault, false)
  }

  const formatOptionText = (option: GenericDropdownOption) => {
    if (props.highlightFunction) {
      const result = props.highlightFunction(option, props.modelValue)
      return option.internal_name + (result?.label || '')
    }

    const isCurrent = option.id === props.modelValue
    const isSystemDefault = option.is_default || false
    return option.internal_name + getDropdownOptionLabel(isCurrent, isSystemDefault)
  }

  const getNoDefaultOptionClasses = () => {
    const isCurrent = props.modelValue === props.noDefaultValue
    return getDropdownOptionClasses(isCurrent, false, true)
  }

  const formatNoDefaultText = () => {
    const isCurrent = props.modelValue === props.noDefaultValue
    return props.noDefaultLabel + (isCurrent ? DROPDOWN_OPTION_LABELS.current : '')
  }
</script>
