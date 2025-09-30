<template>
  <ListView
    title="Collections"
    description="Manage collections of museum items with context and translation support."
    add-button-route="/collections/new"
    add-button-label="Add Collection"
    color="indigo"
    :is-empty="filteredCollections.length === 0"
    empty-title="No collections found"
    :empty-message="
      searchQuery.trim()
        ? `No collections found matching '${searchQuery.trim()}'`
        : 'Get started by creating a new collection.'
    "
    :show-empty-add-button="!searchQuery.trim()"
    empty-add-button-label="New Collection"
    @retry="fetchCollections"
  >
    <!-- Icon -->
    <template #icon>
      <CollectionIcon />
    </template>

    <!-- Filter Buttons -->
    <template #filters>
      <FilterButton
        label="All Collections"
        :is-active="filterMode === 'all'"
        :count="collections.length"
        variant="primary"
        color="indigo"
        @click="filterMode = 'all'"
      />
      <FilterButton
        label="Collections"
        :is-active="filterMode === 'collections'"
        :count="collectionItems.length"
        variant="info"
        color="indigo"
        @click="filterMode = 'collections'"
      />
      <FilterButton
        label="Exhibitions"
        :is-active="filterMode === 'exhibitions'"
        :count="exhibitionItems.length"
        variant="success"
        color="indigo"
        @click="filterMode = 'exhibitions'"
      />
      <FilterButton
        label="Galleries"
        :is-active="filterMode === 'galleries'"
        :count="galleryItems.length"
        variant="info"
        color="indigo"
        @click="filterMode = 'galleries'"
      />
    </template>

    <!-- Search Slot -->
    <!-- Search Slot -->
    <template #search="slotProps">
      <SearchControl
        v-model="searchQuery"
        placeholder="Search collections..."
        :color="slotProps.color"
      />
    </template>

    <!-- Collections Table -->
    <template #headers>
      <TableRow>
        <TableHeader
          sortable
          :sort-direction="sortKey === 'internal_name' ? sortDirection : null"
          @sort="handleSort('internal_name')"
        >
          Collection
        </TableHeader>
        <TableHeader class="hidden sm:table-cell">Type</TableHeader>
        <TableHeader class="hidden md:table-cell">Language</TableHeader>
        <TableHeader class="hidden lg:table-cell">Context</TableHeader>
        <TableHeader class="hidden lg:table-cell">Items</TableHeader>
        <TableHeader class="hidden lg:table-cell">Partners</TableHeader>
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
        v-for="collection in filteredCollections"
        :key="collection.id"
        :class="['cursor-pointer transition', colorClasses!.hover]"
        @click="openCollectionDetail(collection.id)"
      >
        <TableCell>
          <InternalName
            small
            :internal-name="collection.internal_name"
            :backward-compatibility="collection.backward_compatibility"
          >
            <template #icon>
              <CollectionIcon :class="['h-5 w-5', colorClasses!.icon]" />
            </template>
          </InternalName>
        </TableCell>
        <TableCell class="hidden sm:table-cell">
          <span
            :class="[
              'inline-flex items-center px-2 py-1 rounded text-xs font-medium capitalize',
              getTypeColorClasses(collection.type),
            ]"
          >
            {{ collection.type || '—' }}
          </span>
        </TableCell>
        <TableCell class="hidden md:table-cell">
          <DisplayText small>{{
            collection.language?.internal_name || collection.language_id || '—'
          }}</DisplayText>
        </TableCell>
        <TableCell class="hidden lg:table-cell">
          <DisplayText small>{{ collection.context?.internal_name || '—' }}</DisplayText>
        </TableCell>
        <TableCell class="hidden lg:table-cell">
          <div class="flex items-center">
            <span
              :class="[
                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                colorClasses!.badge,
              ]"
            >
              {{ collection.items_count || '0' }}
            </span>
          </div>
        </TableCell>
        <TableCell class="hidden lg:table-cell">
          <div class="flex items-center">
            <span
              :class="[
                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                colorClasses!.badge,
              ]"
            >
              {{ collection.partners_count || '0' }}
            </span>
          </div>
        </TableCell>
        <TableCell class="hidden lg:table-cell">
          <DateDisplay :date="collection.created_at" format="short" variant="small-dark" />
        </TableCell>
        <TableCell class="hidden sm:table-cell">
          <div class="flex space-x-2" @click.stop>
            <ViewButton
              @click="router.push({ name: 'collection-detail', params: { id: collection.id } })"
            />
            <EditButton
              @click="
                router.push({
                  name: 'collection-detail',
                  params: { id: collection.id },
                  query: { edit: 'true' },
                })
              "
            />
            <DeleteButton @click="handleDeleteCollection(collection)" />
          </div>
        </TableCell>
      </TableRow>
    </template>

    <!-- Pagination controls -->
    <template #pagination>
      <PaginationControls
        :page="collectionStore.page"
        :per-page="collectionStore.perPage"
        :total="collectionStore.total"
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
  import { useCollectionStore } from '@/stores/collection'
  import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
  import { useErrorDisplayStore } from '@/stores/errorDisplay'
  import { useDeleteConfirmationStore } from '@/stores/deleteConfirmation'
  import ViewButton from '@/components/layout/list/ViewButton.vue'
  import EditButton from '@/components/layout/list/EditButton.vue'
  import DeleteButton from '@/components/layout/list/DeleteButton.vue'
  import DateDisplay from '@/components/format/Date.vue'
  import DisplayText from '@/components/format/DisplayText.vue'
  import ListView from '@/components/layout/list/ListView.vue'
  import TableHeader from '@/components/format/table/TableHeader.vue'
  import TableRow from '@/components/format/table/TableRow.vue'
  import TableCell from '@/components/format/table/TableCell.vue'
  import InternalName from '@/components/format/InternalName.vue'
  import { RectangleStackIcon as CollectionIcon } from '@heroicons/vue/24/solid'
  import SearchControl from '@/components/layout/list/SearchControl.vue'
  import FilterButton from '@/components/layout/list/FilterButton.vue'
  import type { CollectionResource } from '@metanull/inventory-app-api-client'
  import { useColors, type ColorName } from '@/composables/useColors'
  import PaginationControls from '@/components/layout/list/PaginationControls.vue'

  interface Props {
    color?: ColorName
  }

  const props = withDefaults(defineProps<Props>(), {
    color: 'indigo',
  })

  // Color classes from centralized system
  const colorClasses = useColors(computed(() => props.color))

  const router = useRouter()

  const collectionStore = useCollectionStore()
  const loadingStore = useLoadingOverlayStore()
  const errorStore = useErrorDisplayStore()
  const deleteStore = useDeleteConfirmationStore()

  // Sorting state
  const sortKey = ref<string>('internal_name')
  const sortDirection = ref<'asc' | 'desc'>('asc')

  // Search state
  const searchQuery = ref('')

  // Filter state - default to 'all'
  const filterMode = ref<'all' | 'collections' | 'exhibitions' | 'galleries'>('all')

  // Computed filtered and sorted collections
  const collections = computed(() => collectionStore.collections || [])

  const collectionItems = computed(() =>
    collections.value.filter((collection: CollectionResource) => collection.type === 'collection')
  )
  const exhibitionItems = computed(() =>
    collections.value.filter((collection: CollectionResource) => collection.type === 'exhibition')
  )
  const galleryItems = computed(() =>
    collections.value.filter((collection: CollectionResource) => collection.type === 'gallery')
  )

  const filteredCollections = computed(() => {
    let filtered = collections.value

    // Apply filter mode
    if (filterMode.value === 'collections') {
      filtered = filtered.filter(
        (collection: CollectionResource) => collection.type === 'collection'
      )
    } else if (filterMode.value === 'exhibitions') {
      filtered = filtered.filter(
        (collection: CollectionResource) => collection.type === 'exhibition'
      )
    } else if (filterMode.value === 'galleries') {
      filtered = filtered.filter((collection: CollectionResource) => collection.type === 'gallery')
    }

    // Apply search
    if (searchQuery.value.trim()) {
      const query = searchQuery.value.toLowerCase()
      filtered = filtered.filter(
        collection =>
          collection.internal_name.toLowerCase().includes(query) ||
          (collection.backward_compatibility &&
            collection.backward_compatibility.toLowerCase().includes(query)) ||
          (collection.language?.internal_name &&
            collection.language.internal_name.toLowerCase().includes(query)) ||
          (collection.context?.internal_name &&
            collection.context.internal_name.toLowerCase().includes(query))
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

  // Sort handler
  const handleSort = (key: string) => {
    if (sortKey.value === key) {
      sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
    } else {
      sortKey.value = key
      sortDirection.value = 'asc'
    }
  }

  // Get color classes for collection type badges
  const getTypeColorClasses = (type: string) => {
    switch (type) {
      case 'collection':
        return 'bg-blue-100 text-blue-800'
      case 'exhibition':
        return 'bg-purple-100 text-purple-800'
      case 'gallery':
        return 'bg-green-100 text-green-800'
      default:
        return 'bg-gray-100 text-gray-800'
    }
  }

  // Navigation handler
  const openCollectionDetail = (collectionId: string) => {
    if (collectionId === 'new') {
      router.push({ name: 'collection-new' })
    } else {
      router.push({ name: 'collection-detail', params: { id: collectionId } })
    }
  }

  // Delete handler
  const handleDeleteCollection = async (collectionToDelete: CollectionResource) => {
    const result = await deleteStore.trigger(
      'Delete Collection',
      `Are you sure you want to delete "${collectionToDelete.internal_name}"? This action cannot be undone.`
    )

    if (result === 'delete') {
      try {
        loadingStore.show('Deleting...')
        await collectionStore.deleteCollection(collectionToDelete.id)
        errorStore.addMessage('info', 'Collection deleted successfully.')
      } catch {
        errorStore.addMessage('error', 'Failed to delete collection. Please try again.')
      } finally {
        loadingStore.hide()
      }
    }
  }

  // Fetch collections function for retry
  const fetchCollections = async () => {
    try {
      loadingStore.show()
      await collectionStore.fetchCollections({
        page: collectionStore.page,
        perPage: collectionStore.perPage,
      })
      errorStore.addMessage('info', 'Collections refreshed successfully.')
    } catch {
      errorStore.addMessage('error', 'Failed to refresh collections. Please try again.')
    } finally {
      loadingStore.hide()
    }
  }

  // Fetch collections on mount
  onMounted(async () => {
    let usedCache = false
    // If cache exists, display immediately and refresh in background
    if (collections.value && collections.value.length > 0) {
      usedCache = true
    } else {
      loadingStore.show()
    }
    try {
      // Always refresh in background
      await collectionStore.fetchCollections({
        page: collectionStore.page,
        perPage: collectionStore.perPage,
      })
      if (usedCache) {
        errorStore.addMessage('info', 'List refreshed')
      }
    } catch {
      errorStore.addMessage('error', 'Failed to fetch collections. Please try again.')
    } finally {
      if (!usedCache) {
        loadingStore.hide()
      }
    }
  })

  const onPageChange = async (p: number) => {
    collectionStore.page = p
    await collectionStore.fetchCollections({
      page: collectionStore.page,
      perPage: collectionStore.perPage,
    })
  }

  const onPerPageChange = async (pp: number) => {
    collectionStore.perPage = pp
    collectionStore.page = 1
    await collectionStore.fetchCollections({
      page: collectionStore.page,
      perPage: collectionStore.perPage,
    })
  }
</script>
