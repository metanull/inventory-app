<template>
  <ListView
    title="Item Translations"
    description="Manage translations for items across different languages and contexts."
    add-button-route="/item-translations/new"
    add-button-label="Add Translation"
    color="blue"
    :is-empty="filteredTranslations.length === 0"
    empty-title="No translations found"
    :empty-message="
      filterMode === 'all'
        ? 'Get started by creating a new item translation.'
        : `No ${filterMode} translations found.`
    "
    :show-empty-add-button="filterMode === 'all'"
    empty-add-button-label="New Translation"
    @retry="fetchTranslations"
  >
    <!-- Icon -->
    <template #icon>
      <LanguageIcon />
    </template>

    <!-- Filter Buttons -->
    <template #filters>
      <FilterButton
        label="All Translations"
        :is-active="filterMode === 'all'"
        :count="itemTranslations.length"
        variant="primary"
        :color="color"
        @click="filterMode = 'all'"
      />
      <FilterButton
        label="Default Context"
        :is-active="filterMode === 'default'"
        :count="defaultContextTranslations.length"
        variant="success"
        :color="color"
        @click="filterMode = 'default'"
      />
    </template>

    <!-- Additional Filters -->
    <template #additional-filters>
      <div class="flex flex-wrap gap-4 mt-4">
        <!-- Language Filter -->
        <div class="flex-1 min-w-[200px]">
          <label for="language-filter" class="block text-sm font-medium text-gray-700 mb-1">
            Language
          </label>
          <select
            id="language-filter"
            v-model="selectedLanguage"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
          >
            <option value="">All Languages</option>
            <option v-for="lang in availableLanguages" :key="lang.id" :value="lang.id">
              {{ lang.internal_name }}
            </option>
          </select>
        </div>

        <!-- Context Filter -->
        <div class="flex-1 min-w-[200px]">
          <label for="context-filter" class="block text-sm font-medium text-gray-700 mb-1">
            Context
          </label>
          <select
            id="context-filter"
            v-model="selectedContext"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
          >
            <option value="">All Contexts</option>
            <option v-for="ctx in availableContexts" :key="ctx.id" :value="ctx.id">
              {{ ctx.internal_name }}
            </option>
          </select>
        </div>

        <!-- Item Filter -->
        <div class="flex-1 min-w-[200px]">
          <label for="item-filter" class="block text-sm font-medium text-gray-700 mb-1">
            Item ID or Name
          </label>
          <input
            id="item-filter"
            v-model="itemSearchQuery"
            type="text"
            placeholder="Search by item ID or name..."
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
          />
        </div>
      </div>
    </template>

    <!-- Search Slot -->
    <template #search>
      <SearchControl
        v-model="searchQuery"
        placeholder="Search translations by name, alternate name, or description..."
        :color="color"
      />
    </template>

    <!-- Table -->
    <template #headers>
      <TableRow>
        <TableHeader
          sortable
          :sort-direction="sortKey === 'name' ? sortDirection : null"
          @sort="handleSort('name')"
        >
          Name
        </TableHeader>
        <TableHeader class="hidden md:table-cell">Item</TableHeader>
        <TableHeader class="hidden lg:table-cell">Language</TableHeader>
        <TableHeader class="hidden lg:table-cell">Context</TableHeader>
        <TableHeader
          class="hidden xl:table-cell"
          sortable
          :sort-direction="sortKey === 'created_at' ? sortDirection : null"
          @sort="handleSort('created_at')"
        >
          Created
        </TableHeader>
        <TableHeader class="hidden sm:table-cell" variant="actions">
          <span class="sr-only">Actions</span>
        </TableHeader>
      </TableRow>
    </template>

    <template #rows>
      <TableRow
        v-for="translation in filteredTranslations"
        :key="translation.id"
        :class="['cursor-pointer transition', colorClasses.hover]"
        @click="openTranslationDetail(translation.id)"
      >
        <TableCell>
          <div class="flex items-center space-x-2">
            <LanguageIcon :class="['h-5 w-5', colorClasses.icon]" />
            <div>
              <div class="font-medium text-gray-900">{{ translation.name }}</div>
              <div v-if="translation.alternate_name" class="text-sm text-gray-500">
                {{ translation.alternate_name }}
              </div>
            </div>
          </div>
        </TableCell>
        <TableCell class="hidden md:table-cell">
          <span class="text-sm text-gray-900">
            {{ getItemName(translation.item_id) || translation.item_id }}
          </span>
        </TableCell>
        <TableCell class="hidden lg:table-cell">
          <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-800">
            {{ translation.language_id }}
          </span>
        </TableCell>
        <TableCell class="hidden lg:table-cell">
          <span class="text-sm text-gray-600">
            {{ getContextName(translation.context_id) || translation.context_id }}
          </span>
        </TableCell>
        <TableCell class="hidden xl:table-cell">
          <DateDisplay :date="translation.created_at" format="short" variant="small-dark" />
        </TableCell>
        <TableCell class="hidden sm:table-cell">
          <div class="flex space-x-2" @click.stop>
            <ViewButton
              @click="
                router.push({ name: 'item-translation-detail', params: { id: translation.id } })
              "
            />
            <EditButton
              @click="
                router.push({
                  name: 'item-translation-detail',
                  params: { id: translation.id },
                  query: { edit: 'true' },
                })
              "
            />
            <DeleteButton @click="handleDeleteTranslation(translation)" />
          </div>
        </TableCell>
      </TableRow>
    </template>

    <!-- Pagination controls -->
    <template #pagination>
      <PaginationControls
        :page="translationStore.page"
        :per-page="translationStore.perPage"
        :total="translationStore.total"
        :color="color"
        @update:page="onPageChange"
        @update:per-page="onPerPageChange"
      />
    </template>
  </ListView>
</template>

<script setup lang="ts">
  import { ref, computed, onMounted } from 'vue'
  import { useRouter } from 'vue-router'
  import { useItemTranslationStore } from '@/stores/itemTranslation'
  import { useContextStore } from '@/stores/context'
  import { useLanguageStore } from '@/stores/language'
  import { useItemStore } from '@/stores/item'
  import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
  import { useErrorDisplayStore } from '@/stores/errorDisplay'
  import { useDeleteConfirmationStore } from '@/stores/deleteConfirmation'
  import ViewButton from '@/components/layout/list/ViewButton.vue'
  import EditButton from '@/components/layout/list/EditButton.vue'
  import DeleteButton from '@/components/layout/list/DeleteButton.vue'
  import DateDisplay from '@/components/format/Date.vue'
  import FilterButton from '@/components/layout/list/FilterButton.vue'
  import ListView from '@/components/layout/list/ListView.vue'
  import TableHeader from '@/components/format/table/TableHeader.vue'
  import TableRow from '@/components/format/table/TableRow.vue'
  import TableCell from '@/components/format/table/TableCell.vue'
  import { LanguageIcon } from '@heroicons/vue/24/solid'
  import SearchControl from '@/components/layout/list/SearchControl.vue'
  import type { ItemTranslationResource } from '@metanull/inventory-app-api-client'
  import { useColors, type ColorName } from '@/composables/useColors'
  import PaginationControls from '@/components/layout/list/PaginationControls.vue'

  // Props
  interface Props {
    color?: ColorName
  }

  const props = withDefaults(defineProps<Props>(), {
    color: 'blue',
  })

  const router = useRouter()

  const translationStore = useItemTranslationStore()
  const contextStore = useContextStore()
  const languageStore = useLanguageStore()
  const itemStore = useItemStore()
  const loadingStore = useLoadingOverlayStore()
  const errorStore = useErrorDisplayStore()
  const deleteStore = useDeleteConfirmationStore()

  // Filter state
  const filterMode = ref<'all' | 'default'>('all')
  const selectedLanguage = ref<string>('')
  const selectedContext = ref<string>('')
  const itemSearchQuery = ref<string>('')

  // Sorting state
  const sortKey = ref<string>('name')
  const sortDirection = ref<'asc' | 'desc'>('asc')

  // Search state
  const searchQuery = ref('')

  // Color classes from centralized system
  const colorClasses = useColors(computed(() => props.color))

  // Available options for filters
  const availableLanguages = computed(() => languageStore.languages || [])
  const availableContexts = computed(() => contextStore.contexts || [])

  // Computed
  const itemTranslations = computed(() => translationStore.itemTranslations || [])

  const defaultContextTranslations = computed(() => {
    const defaultContext = contextStore.defaultContext
    if (!defaultContext) return []
    return itemTranslations.value.filter(t => t.context_id === defaultContext.id)
  })

  const filteredTranslations = computed(() => {
    let filtered = itemTranslations.value

    // Apply filter mode
    if (filterMode.value === 'default') {
      const defaultContext = contextStore.defaultContext
      if (defaultContext) {
        filtered = filtered.filter(t => t.context_id === defaultContext.id)
      }
    }

    // Apply language filter
    if (selectedLanguage.value) {
      filtered = filtered.filter(t => t.language_id === selectedLanguage.value)
    }

    // Apply context filter
    if (selectedContext.value) {
      filtered = filtered.filter(t => t.context_id === selectedContext.value)
    }

    // Apply item search (by ID or name)
    if (itemSearchQuery.value.trim()) {
      const query = itemSearchQuery.value.trim().toLowerCase()
      filtered = filtered.filter(t => {
        // Search by item ID
        if (t.item_id && t.item_id.toLowerCase().includes(query)) {
          return true
        }
        // Search by item internal_name if available
        const item = itemStore.items.find(i => i.id === t.item_id)
        if (item && item.internal_name.toLowerCase().includes(query)) {
          return true
        }
        return false
      })
    }

    // Apply general search
    if (searchQuery.value.trim()) {
      const query = searchQuery.value.toLowerCase()
      filtered = filtered.filter(
        t =>
          t.name.toLowerCase().includes(query) ||
          (t.alternate_name && t.alternate_name.toLowerCase().includes(query)) ||
          (t.description && t.description.toLowerCase().includes(query))
      )
    }

    // Apply sorting
    return filtered.sort((a, b) => {
      let aValue: unknown = a[sortKey.value as keyof typeof a]
      let bValue: unknown = b[sortKey.value as keyof typeof b]

      // Handle different data types
      if (sortKey.value === 'created_at') {
        aValue = new Date(aValue as string).getTime()
        bValue = new Date(bValue as string).getTime()
      } else if (typeof aValue === 'string') {
        aValue = aValue.toLowerCase()
        bValue = (bValue as string).toLowerCase()
      }

      if ((aValue as number) < (bValue as number)) return sortDirection.value === 'asc' ? -1 : 1
      if ((aValue as number) > (bValue as number)) return sortDirection.value === 'asc' ? 1 : -1
      return 0
    })
  })

  // Helper functions to get names
  const getContextName = (contextId: string) => {
    const context = contextStore.contexts?.find(c => c.id === contextId)
    return context?.internal_name
  }

  const getItemName = (itemId: string) => {
    // We'll implement this once we have items loaded
    // For now, just return the ID
    return itemId.substring(0, 8)
  }

  // Sort handler
  const handleSort = (key: string) => {
    if (sortKey.value === key) {
      sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
    } else {
      sortKey.value = key
      sortDirection.value = 'asc'
    }
  }

  // Navigation handler
  const openTranslationDetail = (translationId: string) => {
    if (translationId === 'new') {
      router.push({ name: 'item-translation-new' })
    } else {
      router.push({ name: 'item-translation-detail', params: { id: translationId } })
    }
  }

  // Delete handler
  const handleDeleteTranslation = async (translation: ItemTranslationResource) => {
    const result = await deleteStore.trigger(
      'Delete Translation',
      `Are you sure you want to delete the translation "${translation.name}"? This action cannot be undone.`
    )

    if (result === 'delete') {
      try {
        loadingStore.show('Deleting...')
        await translationStore.deleteItemTranslation(translation.id)
        errorStore.addMessage('info', 'Translation deleted successfully.')
      } catch {
        errorStore.addMessage('error', 'Failed to delete translation. Please try again.')
      } finally {
        loadingStore.hide()
      }
    }
  }

  // Fetch translations function for retry
  const fetchTranslations = async () => {
    try {
      loadingStore.show()
      await translationStore.fetchItemTranslations({
        page: translationStore.page,
        perPage: translationStore.perPage,
      })
      errorStore.addMessage('info', 'Translations refreshed successfully.')
    } catch {
      errorStore.addMessage('error', 'Failed to refresh translations. Please try again.')
    } finally {
      loadingStore.hide()
    }
  }

  // Fetch translations on mount
  onMounted(async () => {
    let usedCache = false
    // If cache exists, display immediately and refresh in background
    if (itemTranslations.value && itemTranslations.value.length > 0) {
      usedCache = true
    } else {
      loadingStore.show()
    }
    try {
      // Load data for filtering
      await Promise.all([
        contextStore.contexts?.length
          ? Promise.resolve()
          : contextStore.fetchContexts({ page: 1, perPage: 100 }),
        languageStore.languages?.length
          ? Promise.resolve()
          : languageStore.fetchLanguages({ page: 1, perPage: 100 }),
        itemStore.items?.length
          ? Promise.resolve()
          : itemStore.fetchItems({ page: 1, perPage: 100 }),
      ])

      // Always refresh translations in background
      await translationStore.fetchItemTranslations({
        page: translationStore.page,
        perPage: translationStore.perPage,
      })
      if (usedCache) {
        errorStore.addMessage('info', 'List refreshed')
      }
    } catch {
      errorStore.addMessage('error', 'Failed to fetch translations. Please try again.')
    } finally {
      if (!usedCache) {
        loadingStore.hide()
      }
    }
  })

  const onPageChange = async (p: number) => {
    translationStore.page = p
    await translationStore.fetchItemTranslations({
      page: translationStore.page,
      perPage: translationStore.perPage,
    })
  }

  const onPerPageChange = async (pp: number) => {
    translationStore.perPage = pp
    translationStore.page = 1
    await translationStore.fetchItemTranslations({
      page: translationStore.page,
      perPage: translationStore.perPage,
    })
  }
</script>
