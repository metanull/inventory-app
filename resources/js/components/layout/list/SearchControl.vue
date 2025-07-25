<template>
  <div class="flex items-center space-x-2">
    <div class="relative">
      <input
        v-model="searchValue"
        type="text"
        :placeholder="placeholder"
        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pl-3 pr-10 py-2"
        @input="handleInput"
        @keydown.enter="handleSearch"
      />
    </div>
    <button
      type="button"
      class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-3 py-2 text-sm font-medium leading-4 text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
      @click="handleSearch"
    >
      <MagnifyingGlassIcon class="h-5 w-5" />
    </button>
  </div>
</template>

<script setup lang="ts">
  import { ref } from 'vue'
  import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline'

  interface Props {
    placeholder?: string
    modelValue?: string
  }

  const props = withDefaults(defineProps<Props>(), {
    placeholder: 'Search...',
    modelValue: '',
  })

  const emit = defineEmits(['update:modelValue', 'search'])

  const searchValue = ref<string>(props.modelValue)

  const handleInput = (): void => {
    emit('update:modelValue', searchValue.value)
  }

  const handleSearch = (): void => {
    emit('search', searchValue.value)
  }
</script>
