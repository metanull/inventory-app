<template>
  <ListView
    title="Countries"
    description="Manage countries available in the inventory system."
    add-button-route="/countries/new"
    add-button-label="Add Country"
    color="blue"
    :is-empty="filteredCountries.length === 0"
    empty-title="No countries found"
    :empty-message="
      searchQuery.length > 0
        ? `No countries match your search: '${searchQuery}'`
        : 'Get started by creating a new country.'
    "
    :show-empty-add-button="searchQuery.length === 0"
    empty-add-button-label="New Country"
    @retry="fetchCountries"
  >
    <!-- Icon -->
    <template #icon>
      <CountryIcon />
    </template>

    <!-- Search Slot -->
    <template #search>
      <SearchControl v-model="searchQuery" placeholder="Search countries..." />
    </template>

    <!-- Countries Table Headers -->
    <template #headers>
      <TableRow>
        <TableHeader
          sortable
          :sort-direction="sortKey === 'internal_name' ? sortDirection : null"
          @sort="handleSort('internal_name')"
        >
          Country
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

    <!-- Countries Table Rows -->
    <template #rows>
      <TableRow
        v-for="country in filteredCountries"
        :key="country.id"
        class="cursor-pointer hover:bg-blue-50 transition"
        @click="openCountryDetail(country.id)"
      >
        <TableCell>
          <InternalName
            small
            :internal-name="country.internal_name"
            :backward-compatibility="country.backward_compatibility"
          >
            <template #icon>
              <CountryIcon class="h-5 w-5 text-blue-600" />
            </template>
          </InternalName>
        </TableCell>
        <TableCell class="hidden lg:table-cell">
          <DateDisplay :date="country.created_at" />
        </TableCell>
        <TableCell class="hidden sm:table-cell">
          <div class="flex space-x-2" @click.stop>
            <ViewButton @click="router.push(`/countries/${country.id}`)" />
            <EditButton @click="router.push(`/countries/${country.id}?edit=true`)" />
            <DeleteButton @click="handleDeleteCountry(country)" />
          </div>
        </TableCell>
      </TableRow>
    </template>
  </ListView>
</template>

<script setup lang="ts">
  import { ref, computed, onMounted } from 'vue'
  import { useRouter } from 'vue-router'
  import type { CountryResource } from '@metanull/inventory-app-api-client'
  import { useCountryStore } from '@/stores/country'
  import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
  import { useErrorDisplayStore } from '@/stores/errorDisplay'
  import { useDeleteConfirmationStore } from '@/stores/deleteConfirmation'
  import ListView from '@/components/layout/list/ListView.vue'
  import TableRow from '@/components/format/table/TableRow.vue'
  import TableHeader from '@/components/format/table/TableHeader.vue'
  import TableCell from '@/components/format/table/TableCell.vue'
  import SearchControl from '@/components/layout/list/SearchControl.vue'
  import InternalName from '@/components/format/InternalName.vue'
  import DateDisplay from '@/components/format/Date.vue'
  import ViewButton from '@/components/layout/list/ViewButton.vue'
  import EditButton from '@/components/layout/list/EditButton.vue'
  import DeleteButton from '@/components/layout/list/DeleteButton.vue'
  import { GlobeAltIcon as CountryIcon } from '@heroicons/vue/24/solid'

  const router = useRouter()
  const countryStore = useCountryStore()
  const loadingStore = useLoadingOverlayStore()
  const errorStore = useErrorDisplayStore()
  const deleteStore = useDeleteConfirmationStore()

  // State
  const searchQuery = ref('')
  const sortKey = ref<keyof CountryResource>('internal_name')
  const sortDirection = ref<'asc' | 'desc'>('asc')

  // Computed
  const countries = computed(() => countryStore.countries)

  const filteredCountries = computed(() => {
    let filtered = [...countries.value]

    // Apply search filter
    if (searchQuery.value.trim()) {
      const query = searchQuery.value.toLowerCase().trim()
      filtered = filtered.filter(
        country =>
          country.internal_name.toLowerCase().includes(query) ||
          (country.backward_compatibility &&
            country.backward_compatibility.toLowerCase().includes(query))
      )
    }

    // Apply sorting - inline logic like Projects.vue
    return [...filtered].sort((a, b) => {
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

  // Methods
  const handleSort = (key: keyof CountryResource): void => {
    if (sortKey.value === key) {
      sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
    } else {
      sortKey.value = key
      sortDirection.value = 'asc'
    }
  }

  const openCountryDetail = (id: string): void => {
    router.push(`/countries/${id}`)
  }

  const fetchCountries = async (): Promise<void> => {
    let usedCache = false
    // If cache exists, display immediately and refresh in background
    if (countries.value && countries.value.length > 0) {
      usedCache = true
    } else {
      loadingStore.show()
    }
    try {
      // Always refresh in background
      await countryStore.fetchCountries()
      if (usedCache) {
        errorStore.addMessage('info', 'List refreshed')
      }
    } catch {
      errorStore.addMessage('error', 'Failed to fetch countries. Please try again.')
    } finally {
      if (!usedCache) {
        loadingStore.hide()
      }
    }
  }

  // Delete country with confirmation
  const handleDeleteCountry = async (countryToDelete: CountryResource) => {
    const result = await deleteStore.trigger(
      'Delete Country',
      `Are you sure you want to delete "${countryToDelete.internal_name}"? This action cannot be undone.`
    )

    if (result === 'delete') {
      try {
        loadingStore.show('Deleting...')
        await countryStore.deleteCountry(countryToDelete.id)
        errorStore.addMessage('info', 'Country deleted successfully.')
      } catch {
        errorStore.addMessage('error', 'Failed to delete country. Please try again.')
      } finally {
        loadingStore.hide()
      }
    }
  }

  // Lifecycle
  onMounted(async () => {
    let usedCache = false
    // If cache exists, display immediately and refresh in background
    if (countries.value && countries.value.length > 0) {
      usedCache = true
    } else {
      loadingStore.show()
    }
    try {
      // Always refresh in background
      await countryStore.fetchCountries()
      if (usedCache) {
        errorStore.addMessage('info', 'List refreshed')
      }
    } catch {
      errorStore.addMessage('error', 'Failed to fetch countries. Please try again.')
    } finally {
      if (!usedCache) {
        loadingStore.hide()
      }
    }
  })
</script>
