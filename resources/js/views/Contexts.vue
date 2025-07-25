<template>
  <ListView
    title="Contexts"
    description="Manage contexts in your inventory system. Contexts can be set as default for the entire database."
    add-button-route="/contexts/new"
    add-button-label="Add Context"
    color="green"
    :is-empty="filteredContexts.length === 0"
    empty-title="No contexts found"
    :empty-message="
      filterMode === 'all'
        ? 'Get started by creating a new context.'
        : filterMode === 'default'
          ? 'No default context found. Please set a context as default.'
          : `No ${filterMode} contexts found.`
    "
    :show-empty-add-button="filterMode === 'all'"
    empty-add-button-label="New Context"
    @retry="fetchContexts"
  >
    <!-- Icon -->
    <template #icon>
      <ContextIcon />
    </template>
    <!-- Filter Buttons -->
    <template #filters>
      <FilterButton
        label="All Contexts"
        :is-active="filterMode === 'all'"
        :count="contexts.length"
        variant="primary"
        @click="filterMode = 'all'"
      />
      <FilterButton
        label="Default"
        :is-active="filterMode === 'default'"
        :count="defaultContexts.length"
        variant="success"
        @click="filterMode = 'default'"
      />
    </template>

    <!-- Search Slot -->
    <template #search>
      <SearchControl v-model="searchQuery" placeholder="Search contexts..." />
    </template>

    <!-- Contexts Table -->
    <template #headers>
      <TableRow>
        <TableHeader
          sortable
          :sort-direction="sortKey === 'internal_name' ? sortDirection : null"
          @sort="handleSort('internal_name')"
        >
          Context
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
        v-for="context in filteredContexts"
        :key="context.id"
        class="cursor-pointer hover:bg-green-50 transition"
        @click="openContextDetail(context.id)"
      >
        <TableCell>
          <InternalName
            small
            :internal-name="context.internal_name"
            :backward-compatibility="context.backward_compatibility"
          >
            <template #icon>
              <ContextIcon class="h-5 w-5 text-green-600" />
            </template>
          </InternalName>
        </TableCell>
        <TableCell class="hidden md:table-cell">
          <div @click.stop>
            <Toggle
              small
              title="Default"
              :status-text="context.is_default ? 'Default' : 'Not default'"
              :is-active="context.is_default"
              @toggle="updateContextStatus(context, 'is_default', !context.is_default)"
            />
          </div>
        </TableCell>
        <TableCell class="hidden lg:table-cell">
          <DateDisplay :date="context.created_at" />
        </TableCell>
        <TableCell class="hidden sm:table-cell">
          <div class="flex space-x-2" @click.stop>
            <ViewButton @click="router.push(`/contexts/${context.id}`)" />
            <EditButton @click="router.push(`/contexts/${context.id}?mode=edit`)" />
            <DeleteButton @click="handleDelete(context.id)" />
          </div>
        </TableCell>
      </TableRow>
    </template>
  </ListView>
</template>

<script setup lang="ts">
  import { ref, computed, onMounted } from 'vue'
  import { useRouter } from 'vue-router'
  import { useContextStore } from '@/stores/context'
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
  import Toggle from '@/components/format/Toggle.vue'
  import InternalName from '@/components/format/InternalName.vue'
  import { CogIcon as ContextIcon } from '@heroicons/vue/24/solid'
  import SearchControl from '@/components/layout/list/SearchControl.vue'

  const router = useRouter()

  const contextStore = useContextStore()
  const loadingStore = useLoadingOverlayStore()
  const errorStore = useErrorDisplayStore()
  const deleteStore = useDeleteConfirmationStore()

  // Reactive state
  const filterMode = ref<'all' | 'default'>('all')
  const searchQuery = ref('')
  const sortKey = ref<string>('internal_name')
  const sortDirection = ref<'asc' | 'desc'>('asc')

  // Computed properties for sorting and filtering
  const contexts = computed(() => contextStore.contexts || [])

  const defaultContexts = computed(() => contexts.value.filter(context => context.is_default))

  const filteredContexts = computed(() => {
    let filtered = contexts.value

    // Apply filter mode
    if (filterMode.value === 'default') {
      filtered = filtered.filter(context => context.is_default)
    }

    // Apply search
    if (searchQuery.value.trim()) {
      const query = searchQuery.value.toLowerCase()
      filtered = filtered.filter(
        context =>
          context.internal_name.toLowerCase().includes(query) ||
          (context.backward_compatibility &&
            context.backward_compatibility.toLowerCase().includes(query))
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
      } else if (typeof aValue === 'boolean') {
        aValue = aValue ? 1 : 0
        bValue = bValue ? 1 : 0
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
  const openContextDetail = (contextId: string) => {
    router.push(`/contexts/${contextId}`)
  }

  // Toggle default status
  const updateContextStatus = async (context: any, field: string, value: boolean) => {
    if (field === 'is_default') {
      try {
        loadingStore.show('Updating...')
        await contextStore.setDefaultContext(context.id, value)
        errorStore.addMessage(
          'info',
          `Context ${value ? 'set as' : 'removed as'} default successfully.`
        )
      } catch {
        errorStore.addMessage('error', 'Failed to update context default status. Please try again.')
      } finally {
        loadingStore.hide()
      }
    }
  }

  // Delete handler
  const handleDelete = async (contextId: string) => {
    const context = contexts.value.find(c => c.id === contextId)
    if (!context) return

    const result = await deleteStore.trigger(
      'Delete Context',
      `Are you sure you want to delete "${context.internal_name}"? This action cannot be undone.`
    )

    if (result === 'delete') {
      try {
        loadingStore.show('Deleting...')
        await contextStore.deleteContext(contextId)
        errorStore.addMessage('info', 'Context deleted successfully.')
      } catch {
        errorStore.addMessage('error', 'Failed to delete context. Please try again.')
      } finally {
        loadingStore.hide()
      }
    }
  }

  // Fetch contexts function for retry
  const fetchContexts = async () => {
    try {
      loadingStore.show()
      await contextStore.fetchContexts()
      errorStore.addMessage('info', 'Contexts refreshed successfully.')
    } catch {
      errorStore.addMessage('error', 'Failed to refresh contexts. Please try again.')
    } finally {
      loadingStore.hide()
    }
  }

  // Fetch contexts on mount
  onMounted(async () => {
    let usedCache = false
    // If cache exists, display immediately and refresh in background
    if (contexts.value && contexts.value.length > 0) {
      usedCache = true
    } else {
      loadingStore.show()
    }
    try {
      // Always refresh in background
      await contextStore.fetchContexts()
      if (usedCache) {
        errorStore.addMessage('info', 'List refreshed')
      }
    } catch {
      errorStore.addMessage('error', 'Failed to fetch contexts. Please try again.')
    } finally {
      if (!usedCache) {
        loadingStore.hide()
      }
    }
  })
</script>
