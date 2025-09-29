<template>
  <ListView
    title="Items"
    description="Manage inventory items including objects and monuments in your collection."
    add-button-route="/items/new"
    add-button-label="Add Item"
    color="teal"
    :is-empty="filteredItems.length === 0"
    empty-title="No items found"
    :empty-message="
      filterMode === 'all'
        ? 'Get started by creating a new item.'
        : filterMode === 'objects'
          ? 'No object items found.'
          : `No ${filterMode} items found.`
    "
    :show-empty-add-button="filterMode === 'all'"
    empty-add-button-label="New Item"
    @retry="fetchItems"
  >
    <!-- Icon -->
    <template #icon>
      <ItemIcon />
    </template>
    <!-- Filter Buttons -->
    <template #filters>
      <FilterButton
        label="All Items"
        :is-active="filterMode === 'all'"
        :count="items.length"
        variant="primary"
        :color="color"
        @click="filterMode = 'all'"
      />
      <FilterButton
        label="Objects"
        :is-active="filterMode === 'objects'"
        :count="objectItems.length"
        variant="info"
        :color="color"
        @click="filterMode = 'objects'"
      />
      <FilterButton
        label="Monuments"
        :is-active="filterMode === 'monuments'"
        :count="monumentItems.length"
        variant="success"
        :color="color"
        @click="filterMode = 'monuments'"
      />
    </template>

    <!-- Search Slot -->
    <template #search>
      <SearchControl v-model="searchQuery" placeholder="Search items..." :color="color" />
    </template>

    <!-- Items Table -->
    <template #headers>
      <TableRow>
        <TableHeader
          sortable
          :sort-direction="sortKey === 'internal_name' ? sortDirection : null"
          @sort="handleSort('internal_name')"
        >
          Item
        </TableHeader>
        <TableHeader
          class="hidden md:table-cell"
          sortable
          :sort-direction="sortKey === 'type' ? sortDirection : null"
          @sort="handleSort('type')"
        >
          Type
        </TableHeader>
        <TableHeader class="hidden lg:table-cell">Partner</TableHeader>
        <TableHeader class="hidden lg:table-cell">Project</TableHeader>
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
        v-for="item in filteredItems"
        :key="item.id"
        :class="['cursor-pointer transition', colorClasses.hover]"
        @click="openItemDetail(item.id)"
      >
        <TableCell>
          <InternalName
            small
            :internal-name="item.internal_name"
            :backward-compatibility="item.backward_compatibility"
          >
            <template #icon>
              <ItemIcon :class="['h-5 w-5', colorClasses.icon]" />
            </template>
          </InternalName>
        </TableCell>
        <TableCell class="hidden md:table-cell">
          <div class="flex items-center">
            <span
              :class="[
                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                colorClasses.badgeBackground,
                colorClasses.badge,
              ]"
            >
              {{ item.type === 'object' ? 'Object' : 'Monument' }}
            </span>
          </div>
        </TableCell>
        <TableCell class="hidden lg:table-cell">
          <DisplayText small>{{ item.partner?.internal_name || '—' }}</DisplayText>
        </TableCell>
        <TableCell class="hidden lg:table-cell">
          <DisplayText small>{{ item.project?.internal_name || '—' }}</DisplayText>
        </TableCell>
        <TableCell class="hidden lg:table-cell">
          <DateDisplay :date="item.created_at" format="short" variant="small-dark" />
        </TableCell>
        <TableCell class="hidden sm:table-cell">
          <div class="flex space-x-2" @click.stop>
            <ViewButton @click="router.push({ name: 'item-detail', params: { id: item.id } })" />
            <EditButton
              @click="
                router.push({
                  name: 'item-detail',
                  params: { id: item.id },
                  query: { edit: 'true' },
                })
              "
            />
            <DeleteButton @click="handleDeleteItem(item)" />
          </div>
        </TableCell>
      </TableRow>
    </template>

    <!-- Pagination controls -->
    <template #pagination>
      <PaginationControls
        :page="itemStore.page"
        :per-page="itemStore.perPage"
        :total="itemStore.total"
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
  import { useItemStore } from '@/stores/item'
  import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
  import { useErrorDisplayStore } from '@/stores/errorDisplay'
  import { useDeleteConfirmationStore } from '@/stores/deleteConfirmation'
  import ViewButton from '@/components/layout/list/ViewButton.vue'
  import EditButton from '@/components/layout/list/EditButton.vue'
  import DeleteButton from '@/components/layout/list/DeleteButton.vue'
  import DateDisplay from '@/components/format/Date.vue'
  import DisplayText from '@/components/format/DisplayText.vue'
  import FilterButton from '@/components/layout/list/FilterButton.vue'
  import ListView from '@/components/layout/list/ListView.vue'
  import TableHeader from '@/components/format/table/TableHeader.vue'
  import TableRow from '@/components/format/table/TableRow.vue'
  import TableCell from '@/components/format/table/TableCell.vue'
  import InternalName from '@/components/format/InternalName.vue'
  import { ArchiveBoxIcon as ItemIcon } from '@heroicons/vue/24/solid'
  import SearchControl from '@/components/layout/list/SearchControl.vue'
  import type { ItemResource } from '@metanull/inventory-app-api-client'
  import { useColors, type ColorName } from '@/composables/useColors'
  import PaginationControls from '@/components/layout/list/PaginationControls.vue'

  interface Props {
    color?: ColorName
  }

  const props = withDefaults(defineProps<Props>(), {
    color: 'teal',
  })

  const router = useRouter()

  const itemStore = useItemStore()
  const loadingStore = useLoadingOverlayStore()
  const errorStore = useErrorDisplayStore()
  const deleteStore = useDeleteConfirmationStore()

  // Color classes from centralized system
  const colorClasses = useColors(computed(() => props.color))

  // Filter state - default to 'all'
  const filterMode = ref<'all' | 'objects' | 'monuments'>('all')

  // Sorting state
  const sortKey = ref<string>('internal_name')
  const sortDirection = ref<'asc' | 'desc'>('asc')

  // Search state
  const searchQuery = ref('')

  // Computed filtered and sorted items
  const items = computed(() => itemStore.items || [])

  const objectItems = computed(() => items.value.filter((item: ItemResource) => item.type === 'object'))
  const monumentItems = computed(() => items.value.filter((item: ItemResource) => item.type === 'monument'))

  const filteredItems = computed(() => {
    let filtered = items.value

    // Apply filter mode
    if (filterMode.value === 'objects') {
      filtered = filtered.filter((item: ItemResource) => item.type === 'object')
    } else if (filterMode.value === 'monuments') {
      filtered = filtered.filter((item: ItemResource) => item.type === 'monument')
    }

    // Apply search
    if (searchQuery.value.trim()) {
      const query = searchQuery.value.toLowerCase()
      filtered = filtered.filter(
        (item: ItemResource) =>
          item.internal_name.toLowerCase().includes(query) ||
          (item.backward_compatibility &&
            item.backward_compatibility.toLowerCase().includes(query)) ||
          (item.partner?.internal_name &&
            item.partner.internal_name.toLowerCase().includes(query)) ||
          (item.project?.internal_name && item.project.internal_name.toLowerCase().includes(query))
      )
    }

    // Apply sorting
    return filtered.sort((a: ItemResource, b: ItemResource) => {
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

  // Navigation handler
  const openItemDetail = (itemId: string) => {
    if (itemId === 'new') {
      router.push({ name: 'item-new' })
    } else {
      router.push({ name: 'item-detail', params: { id: itemId } })
    }
  }

  // Delete handler
  const handleDeleteItem = async (itemToDelete: ItemResource) => {
    const result = await deleteStore.trigger(
      'Delete Item',
      `Are you sure you want to delete "${itemToDelete.internal_name}"? This action cannot be undone.`
    )

    if (result === 'delete') {
      try {
        loadingStore.show('Deleting...')
        await itemStore.deleteItem(itemToDelete.id)
        errorStore.addMessage('info', 'Item deleted successfully.')
      } catch {
        errorStore.addMessage('error', 'Failed to delete item. Please try again.')
      } finally {
        loadingStore.hide()
      }
    }
  }

  // Fetch items function for retry
  const fetchItems = async () => {
    try {
      loadingStore.show()
      // Request minimal includes needed for this list view
      await itemStore.fetchItems({
        include: ['partner', 'project'],
        page: itemStore.page,
        perPage: itemStore.perPage,
      })
      errorStore.addMessage('info', 'Items refreshed successfully.')
    } catch {
      errorStore.addMessage('error', 'Failed to refresh items. Please try again.')
    } finally {
      loadingStore.hide()
    }
  }

  const onPageChange = async (p: number) => {
    itemStore.page = p
    await itemStore.fetchItems({
      include: ['partner', 'project'],
      page: itemStore.page,
      perPage: itemStore.perPage,
    })
  }

  const onPerPageChange = async (pp: number) => {
    itemStore.perPage = pp
    itemStore.page = 1
    await itemStore.fetchItems({
      include: ['partner', 'project'],
      page: itemStore.page,
      perPage: itemStore.perPage,
    })
  }

  // Fetch items on mount
  onMounted(async () => {
    let usedCache = false
    // If cache exists, display immediately and refresh in background
    if (items.value && items.value.length > 0) {
      usedCache = true
    } else {
      loadingStore.show()
    }
    try {
      // Always refresh in background
      await itemStore.fetchItems({
        include: ['partner', 'project'],
        page: itemStore.page,
        perPage: itemStore.perPage,
      })
      if (usedCache) {
        errorStore.addMessage('info', 'List refreshed')
      }
    } catch {
      errorStore.addMessage('error', 'Failed to fetch items. Please try again.')
    } finally {
      if (!usedCache) {
        loadingStore.hide()
      }
    }
  })
</script>
