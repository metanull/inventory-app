<template>
  <div class="flex items-center space-x-2">
    <div class="relative">
      <input
        v-model="searchValue"
        type="text"
        :placeholder="placeholder"
        :class="[
          'block w-full rounded-md border-gray-300 shadow-sm sm:text-sm pl-3 pr-10 py-2',
          colorClasses.focus,
        ]"
        @input="handleInput"
        @keydown.enter="handleSearch"
      />
    </div>
    <button
      type="button"
      :class="[
        'inline-flex items-center rounded-md border border-transparent px-3 py-2 text-sm font-medium leading-4 text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2',
        colorClasses.button,
        colorClasses.ring,
      ]"
      @click="handleSearch"
    >
      <MagnifyingGlassIcon class="h-5 w-5" />
    </button>
  </div>
</template>

<script setup lang="ts">
  import { ref, computed } from 'vue'
  import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline'
  import { useColors, type ColorName } from '@/composables/useColors'

  interface Props {
    placeholder?: string
    modelValue?: string
    color?: ColorName
  }

  const props = withDefaults(defineProps<Props>(), {
    placeholder: 'Search...',
    modelValue: '',
    color: 'indigo',
  })

  const emit = defineEmits(['update:modelValue', 'search'])

  const searchValue = ref<string>(props.modelValue)

  // Color classes from centralized system
  const colorClasses = useColors(computed(() => props.color || 'indigo'))

  const handleInput = (): void => {
    emit('update:modelValue', searchValue.value)
  }

  const handleSearch = (): void => {
    emit('search', searchValue.value)
  }
</script>
