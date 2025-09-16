<template>
  <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <!-- Page size selector -->
    <div class="flex items-center gap-2">
      <label class="text-sm text-gray-600">Rows per page</label>
      <select
        class="block rounded-md border-gray-300 py-1.5 pl-2 pr-8 text-sm focus:border-indigo-500 focus:ring-indigo-500"
        :value="perPage"
        @change="onPerPageChange(($event.target as HTMLSelectElement).value)"
      >
        <option v-for="opt in perPageOptions" :key="opt" :value="opt">{{ opt }}</option>
      </select>
    </div>

    <!-- Pagination info + controls -->
    <div class="flex w-full items-center justify-between gap-3 sm:w-auto">
      <div class="text-sm text-gray-600">
        <span v-if="hasTotal">Showing {{ startItem }}–{{ endItem }} of {{ total }}</span>
        <span v-else>Showing page {{ page }}</span>
      </div>
      <div class="inline-flex shadow-sm rounded-md" role="group">
        <button
          type="button"
          class="px-3 py-1.5 text-sm font-medium bg-white border border-gray-300 rounded-l-md hover:bg-gray-50 focus:z-10"
          :class="{ 'opacity-50 cursor-not-allowed': isFirstPage }"
          :disabled="isFirstPage"
          @click="emit('update:page', Math.max(1, page - 1))"
        >
          Previous
        </button>
        <button
          type="button"
          class="px-3 py-1.5 text-sm font-medium bg-white border-t border-b border-gray-300 hover:bg-gray-50 focus:z-10"
          :class="{ 'opacity-50 cursor-not-allowed': true }"
          disabled
        >
          Page {{ page }}<span v-if="hasTotal && totalPages"> / {{ totalPages }}</span>
        </button>
        <button
          type="button"
          class="px-3 py-1.5 text-sm font-medium bg-white border border-gray-300 rounded-r-md hover:bg-gray-50 focus:z-10"
          :class="{ 'opacity-50 cursor-not-allowed': isLastPage }"
          :disabled="isLastPage"
          @click="emit('update:page', page + 1)"
        >
          Next
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { computed } from 'vue'
  import { useColors, type ColorName } from '@/composables/useColors'

  const props = withDefaults(
    defineProps<{
      page: number
      perPage: number
      total: number | null
      color?: ColorName
      perPageOptions?: number[]
    }>(),
    {
      color: 'gray',
      perPageOptions: () => [10, 20, 50, 100],
    }
  )

  const emit = defineEmits(['update:page', 'update:per-page'])

  // Keep consistent theming (currently only used for potential future styling)
  useColors(computed(() => props.color))

  const hasTotal = computed(() => typeof props.total === 'number' && props.total >= 0)
  const totalPages = computed(() =>
    hasTotal.value ? Math.max(1, Math.ceil((props.total || 0) / props.perPage)) : undefined
  )
  const isFirstPage = computed(() => props.page <= 1)
  const isLastPage = computed(() =>
    hasTotal.value && totalPages.value ? props.page >= totalPages.value : false
  )
  const startItem = computed(() => (hasTotal.value ? (props.page - 1) * props.perPage + 1 : 0))
  const endItem = computed(() =>
    hasTotal.value ? Math.min(props.page * props.perPage, props.total || 0) : 0
  )

  const onPerPageChange = (val: string) => {
    const next = Number(val)
    if (!Number.isNaN(next) && next > 0) {
      emit('update:per-page', next)
    }
  }
</script>
