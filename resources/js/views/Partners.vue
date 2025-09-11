<template>
  <ListView
    title="Partners"
    description="Manage partners including museums, institutions, and individuals in your network."
    add-button-route="/partners/new"
    add-button-label="Add Partner"
    color="yellow"
    :is-empty="filteredPartners.length === 0"
    empty-title="No partners found"
    :empty-message="
      filterMode === 'all'
        ? 'Get started by creating a new partner.'
        : filterMode === 'museums'
          ? 'No museum partners found.'
          : filterMode === 'institutions'
            ? 'No institution partners found.'
            : 'No individual partners found.'
    "
    :show-empty-add-button="filterMode === 'all'"
    empty-add-button-label="New Partner"
    @retry="fetchPartners"
  >
    <!-- Icon -->
    <template #icon>
      <UserGroupIcon :class="['h-8 w-8', colorClasses.icon]" />
    </template>
    <!-- Filter Buttons -->
    <template #filters>
      <FilterButton
        label="All Partners"
        :is-active="filterMode === 'all'"
        :count="partners.length"
        variant="primary"
        :color="color"
        @click="filterMode = 'all'"
      />
      <FilterButton
        label="Museums"
        :is-active="filterMode === 'museums'"
        :count="museumPartners.length"
        variant="info"
        :color="color"
        @click="filterMode = 'museums'"
      />
      <FilterButton
        label="Institutions"
        :is-active="filterMode === 'institutions'"
        :count="institutionPartners.length"
        variant="success"
        :color="color"
        @click="filterMode = 'institutions'"
      />
      <FilterButton
        label="Individuals"
        :is-active="filterMode === 'individuals'"
        :count="individualPartners.length"
        variant="info"
        :color="color"
        @click="filterMode = 'individuals'"
      />
    </template>

    <!-- Search Slot -->
    <template #search>
      <SearchControl v-model="searchQuery" placeholder="Search partners..." :color="color" />
    </template>

    <!-- Partners Table -->
    <template #headers>
      <TableRow>
        <TableHeader
          sortable
          :sort-direction="sortKey === 'internal_name' ? sortDirection : null"
          @sort="handleSort('internal_name')"
        >
          Partner
        </TableHeader>
        <TableHeader
          class="hidden md:table-cell"
          sortable
          :sort-direction="sortKey === 'type' ? sortDirection : null"
          @sort="handleSort('type')"
        >
          Type
        </TableHeader>
        <TableHeader class="hidden lg:table-cell">Country</TableHeader>
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
        v-for="partner in filteredPartners"
        :key="partner.id"
        :class="['cursor-pointer transition', colorClasses.hover]"
        @click="openPartnerDetail(partner.id)"
      >
        <TableCell>
          <InternalName
            small
            :internal-name="partner.internal_name"
            :backward-compatibility="partner.backward_compatibility"
          >
            <template #icon>
              <UserGroupIcon :class="['h-5 w-5', colorClasses.icon]" />
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
              {{ partner.type.charAt(0).toUpperCase() + partner.type.slice(1) }}
            </span>
          </div>
        </TableCell>
        <TableCell class="hidden lg:table-cell">
          <DisplayText small>{{ partner.country?.internal_name || '—' }}</DisplayText>
        </TableCell>
        <TableCell class="hidden lg:table-cell">
          <DateDisplay :date="partner.created_at" format="short" variant="small-dark" />
        </TableCell>
        <TableCell class="hidden sm:table-cell">
          <div class="flex space-x-2" @click.stop>
            <ViewButton
              @click="router.push({ name: 'partner-detail', params: { id: partner.id } })"
            />
            <EditButton
              @click="
                router.push({
                  name: 'partner-detail',
                  params: { id: partner.id },
                  query: { edit: 'true' },
                })
              "
            />
            <DeleteButton @click="handleDeletePartner(partner)" />
          </div>
        </TableCell>
      </TableRow>
    </template>
  </ListView>
</template>

<script setup lang="ts">
  import { ref, computed, onMounted } from 'vue'
  import { useRouter } from 'vue-router'
  import { usePartnerStore } from '@/stores/partner'
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
  import InternalName from '@/components/format/InternalName.vue'
  import DisplayText from '@/components/format/DisplayText.vue'
  import { UserGroupIcon } from '@heroicons/vue/24/solid'
  import SearchControl from '@/components/layout/list/SearchControl.vue'
  import type { PartnerResource } from '@metanull/inventory-app-api-client'
  import { useColors, type ColorName } from '@/composables/useColors'

  interface Props {
    color?: ColorName
  }

  const props = withDefaults(defineProps<Props>(), {
    color: 'yellow',
  })

  const router = useRouter()

  const partnerStore = usePartnerStore()
  const loadingStore = useLoadingOverlayStore()
  const errorStore = useErrorDisplayStore()
  const deleteStore = useDeleteConfirmationStore()

  // Color classes from centralized system
  const colorClasses = useColors(computed(() => props.color))

  // State
  const filterMode = ref<'all' | 'museums' | 'institutions' | 'individuals'>('all')
  const searchQuery = ref('')
  const sortKey = ref<'internal_name' | 'type' | 'created_at'>('internal_name')
  const sortDirection = ref<'asc' | 'desc'>('asc')

  // Computed
  const partners = computed(() => partnerStore.partners)

  const museumPartners = computed(() => partners.value.filter(p => p.type === 'museum'))
  const institutionPartners = computed(() => partners.value.filter(p => p.type === 'institution'))
  const individualPartners = computed(() => partners.value.filter(p => p.type === 'individual'))

  const filteredByMode = computed(() => {
    switch (filterMode.value) {
      case 'museums':
        return museumPartners.value
      case 'institutions':
        return institutionPartners.value
      case 'individuals':
        return individualPartners.value
      default:
        return partners.value
    }
  })

  const searchFiltered = computed(() => {
    if (!searchQuery.value.trim()) {
      return filteredByMode.value
    }

    const query = searchQuery.value.toLowerCase().trim()
    return filteredByMode.value.filter(
      partner =>
        partner.internal_name.toLowerCase().includes(query) ||
        partner.type.toLowerCase().includes(query) ||
        (partner.country?.internal_name || '').toLowerCase().includes(query) ||
        (partner.backward_compatibility || '').toLowerCase().includes(query)
    )
  })

  const filteredPartners = computed(() => {
    const sorted = [...searchFiltered.value]

    sorted.sort((a, b) => {
      let result = 0

      switch (sortKey.value) {
        case 'internal_name':
          result = a.internal_name.localeCompare(b.internal_name)
          break
        case 'type':
          result = a.type.localeCompare(b.type)
          break
        case 'created_at':
          result = new Date(b.created_at || '').getTime() - new Date(a.created_at || '').getTime()
          break
      }

      return sortDirection.value === 'desc' ? -result : result
    })

    return sorted
  })

  // Actions
  const openPartnerDetail = (partnerId: string) => {
    if (partnerId === 'new') {
      router.push({ name: 'partner-new' })
    } else {
      router.push({ name: 'partner-detail', params: { id: partnerId } })
    }
  }

  const handleSort = (key: typeof sortKey.value) => {
    if (sortKey.value === key) {
      sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
    } else {
      sortKey.value = key
      sortDirection.value = 'asc'
    }
  }

  const fetchPartners = async () => {
    try {
      loadingStore.show('Loading partners...')
      await partnerStore.fetchPartners()
    } catch {
      errorStore.addMessage('error', 'Failed to load partners. Please try again.')
    } finally {
      loadingStore.hide()
    }
  }

  const handleDeletePartner = async (partner: PartnerResource) => {
    const result = await deleteStore.trigger(
      'Delete Partner',
      `Are you sure you want to delete "${partner.internal_name}"? This action cannot be undone.`
    )

    if (result === 'delete') {
      try {
        loadingStore.show('Deleting partner...')
        await partnerStore.deletePartner(partner.id)
        errorStore.addMessage('info', 'Partner deleted successfully.')
      } catch {
        errorStore.addMessage('error', 'Failed to delete partner. Please try again.')
      } finally {
        loadingStore.hide()
      }
    }
  }

  // Lifecycle
  onMounted(fetchPartners)
</script>
