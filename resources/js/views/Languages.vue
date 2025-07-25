<template>
  <ListView
    title="Languages"
    description="Manage languages available in the inventory system. Languages can be set as default for new items."
    add-button-route="/languages/new"
    add-button-label="Add Language"
    color="purple"
    :is-empty="filteredLanguages.length === 0"
    empty-title="No languages found"
    :empty-message="
      filterMode === 'all'
        ? 'Get started by creating a new language.'
        : filterMode === 'default'
          ? 'No default languages found. Mark a language as default to make it the fallback choice.'
          : `No ${filterMode} languages found.`
    "
    :show-empty-add-button="filterMode === 'all'"
    empty-add-button-label="New Language"
    @retry="fetchLanguages"
  >
    <!-- Icon -->
    <template #icon>
      <LanguageIcon />
    </template>
    <!-- Filter Buttons -->
    <template #filters>
      <FilterButton
        label="All Languages"
        :is-active="filterMode === 'all'"
        :count="languages.length"
        variant="primary"
        @click="filterMode = 'all'"
      />
      <FilterButton
        label="Default"
        :is-active="filterMode === 'default'"
        :count="defaultLanguages.length"
        variant="success"
        @click="filterMode = 'default'"
      />
    </template>

    <!-- Search Slot -->
    <template #search>
      <SearchControl v-model="searchQuery" placeholder="Search languages..." />
    </template>

    <!-- Languages Table -->
    <template #headers>
      <TableRow>
        <TableHeader
          sortable
          :sort-direction="sortKey === 'internal_name' ? sortDirection : null"
          @sort="handleSort('internal_name')"
        >
          Language
        </TableHeader>
        <TableHeader
          class="hidden md:table-cell"
          sortable
          :sort-direction="sortKey === 'is_default' ? sortDirection : null"
          @sort="handleSort('is_default')"
        >
          Default
        </TableHeader>
        <TableHeader
          class="hidden lg:table-cell"
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
        v-for="language in filteredLanguages"
        :key="language.id"
        class="cursor-pointer hover:bg-purple-50 transition"
        @click="openLanguageDetail(language.id)"
      >
        <TableCell>
          <InternalName
            small
            :internal-name="language.internal_name"
            :backward-compatibility="language.backward_compatibility"
          >
            <template #icon>
              <LanguageIcon class="h-5 w-5 text-purple-600" />
            </template>
          </InternalName>
        </TableCell>
        <TableCell class="hidden md:table-cell">
          <div @click.stop>
            <Toggle
              small
              title="Default"
              :status-text="language.is_default ? 'Default' : 'Not default'"
              :is-active="language.is_default"
              @toggle="updateLanguageStatus(language, 'is_default', !language.is_default)"
            />
          </div>
        </TableCell>
        <TableCell class="hidden lg:table-cell">
          <DateDisplay :date="language.created_at" format="short" variant="small-dark" />
        </TableCell>
        <TableCell class="hidden sm:table-cell">
          <div class="flex space-x-2" @click.stop>
            <ViewButton @click="router.push(`/languages/${language.id}`)" />
            <EditButton @click="router.push(`/languages/${language.id}?edit=true`)" />
            <DeleteButton @click="handleDeleteLanguage(language)" />
          </div>
        </TableCell>
      </TableRow>
    </template>
  </ListView>
</template>

<script setup lang="ts">
  import { ref, computed, onMounted } from 'vue'
  import { useRouter } from 'vue-router'
  import { useLanguageStore } from '@/stores/language'
  import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
  import { useErrorDisplayStore } from '@/stores/errorDisplay'
  import { useDeleteConfirmationStore } from '@/stores/deleteConfirmation'
  import type { LanguageResource } from '@metanull/inventory-app-api-client'
  import ViewButton from '@/components/layout/list/ViewButton.vue'
  import EditButton from '@/components/layout/list/EditButton.vue'
  import DeleteButton from '@/components/layout/list/DeleteButton.vue'
  import DateDisplay from '@/components/format/Date.vue'
  import FilterButton from '@/components/layout/list/FilterButton.vue'
  import ListView from '@/components/layout/list/ListView.vue'
  import TableHeader from '@/components/format/table/TableHeader.vue'
  import TableRow from '@/components/format/table/TableRow.vue'
  import TableCell from '@/components/format/table/TableCell.vue'
  import Toggle from '@/components/format/Toggle.vue'
  import InternalName from '@/components/format/InternalName.vue'
  import { LanguageIcon } from '@heroicons/vue/24/solid'
  import SearchControl from '@/components/layout/list/SearchControl.vue'

  const router = useRouter()

  const languageStore = useLanguageStore()
  const loadingStore = useLoadingOverlayStore()
  const errorStore = useErrorDisplayStore()
  const deleteStore = useDeleteConfirmationStore()

  const languages = computed(() => languageStore.languages)
  const defaultLanguages = computed(() => languageStore.defaultLanguages)

  // Filter state - default to 'all'
  const filterMode = ref<'all' | 'default'>('all')

  // Sorting state
  const sortKey = ref<string>('internal_name')
  const sortDirection = ref<'asc' | 'desc'>('asc')

  // Search state
  const searchQuery = ref<string>('')

  // Handle sort event from TableHeader
  function handleSort(key: string) {
    if (sortKey.value === key) {
      sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
    } else {
      sortKey.value = key
      sortDirection.value = 'asc'
    }
  }

  // Computed filtered and sorted languages
  const filteredLanguages = computed(() => {
    let list: LanguageResource[]
    switch (filterMode.value) {
      case 'default':
        list = defaultLanguages.value
        break
      default:
        list = languages.value
    }
    // Search filter
    const query = searchQuery.value.trim().toLowerCase()
    if (query.length > 0) {
      list = list.filter(language => {
        const name = language.internal_name?.toLowerCase() ?? ''
        const compat = language.backward_compatibility?.toLowerCase() ?? ''
        return name.includes(query) || compat.includes(query)
      })
    }
    // Simple sorting logic
    return [...list].sort((a, b) => {
      const key = sortKey.value
      let valA: unknown
      let valB: unknown
      if (key === 'internal_name') {
        valA = a.internal_name ?? ''
        valB = b.internal_name ?? ''
      } else {
        valA = (a as any)[key]
        valB = (b as any)[key]
      }
      if (valA == null && valB == null) return 0
      if (valA == null) return 1
      if (valB == null) return -1
      if (valA < valB) return sortDirection.value === 'asc' ? -1 : 1
      if (valA > valB) return sortDirection.value === 'asc' ? 1 : -1
      return 0
    })
  })

  // Fetch languages on mount
  onMounted(async () => {
    let usedCache = false
    // If cache exists, display immediately and refresh in background
    if (languages.value && languages.value.length > 0) {
      usedCache = true
    } else {
      loadingStore.show()
    }
    try {
      // Always refresh in background
      await languageStore.fetchLanguages()
      if (usedCache) {
        errorStore.addMessage('info', 'List refreshed')
      }
    } catch {
      errorStore.addMessage('error', 'Failed to fetch languages. Please try again.')
    } finally {
      if (!usedCache) {
        loadingStore.hide()
      }
    }
  })

  // Open LanguageDetail for clicked language
  function openLanguageDetail(languageId: string | number) {
    router.push(`/languages/${languageId}`)
  }

  // Update language status
  const updateLanguageStatus = async (
    language: LanguageResource,
    field: string,
    value: boolean
  ) => {
    try {
      loadingStore.show('Updating...')
      if (field === 'is_default') {
        await languageStore.setDefaultLanguage(language.id, value)
        errorStore.addMessage(
          'info',
          `Language ${value ? 'set as default' : 'removed from default'} successfully.`
        )
      }
    } catch {
      errorStore.addMessage('error', `Failed to update language status. Please try again.`)
    } finally {
      loadingStore.hide()
    }
  }

  // Fetch languages function for retry
  const fetchLanguages = async () => {
    try {
      loadingStore.show()
      await languageStore.fetchLanguages()
      errorStore.addMessage('info', 'Languages refreshed successfully.')
    } catch {
      errorStore.addMessage('error', 'Failed to refresh languages. Please try again.')
    } finally {
      loadingStore.hide()
    }
  }

  // Delete language with confirmation
  const handleDeleteLanguage = async (languageToDelete: LanguageResource) => {
    const result = await deleteStore.trigger(
      'Delete Language',
      `Are you sure you want to delete "${languageToDelete.internal_name}"? This action cannot be undone.`
    )

    if (result === 'delete') {
      try {
        loadingStore.show('Deleting...')
        await languageStore.deleteLanguage(languageToDelete.id)
        errorStore.addMessage('info', 'Language deleted successfully.')
      } catch {
        errorStore.addMessage('error', 'Failed to delete language. Please try again.')
      } finally {
        loadingStore.hide()
      }
    }
  }

  // Expose properties for testing
  defineExpose({
    languages,
    filteredLanguages,
    defaultLanguages,
    filterMode,
    searchQuery,
    sortKey,
    sortDirection,
    openLanguageDetail,
    updateLanguageStatus,
    handleDeleteLanguage,
    handleSort,
    fetchLanguages,
  })
</script>
