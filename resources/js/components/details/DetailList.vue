<template>
  <div class="mt-8">
    <div class="mb-4">
      <h3 class="text-lg font-medium text-gray-900">Item Details</h3>
      <p class="mt-1 text-sm text-gray-500">Individual details and specifications for this item.</p>
    </div>

    <!-- Add Detail Button -->
    <div class="mb-4">
      <button
        :class="[
          'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white focus:outline-none focus:ring-2 focus:ring-offset-2',
          colorClasses.button,
          colorClasses.buttonHover,
          colorClasses.focus,
        ]"
        @click="handleAddDetail"
      >
        <PlusIcon class="h-4 w-4 mr-2" />
        Add Detail
      </button>
    </div>

    <!-- Details List -->
    <div v-if="details.length > 0" class="space-y-3">
      <div
        v-for="detail in details"
        :key="detail.id"
        :class="[
          'relative rounded-lg border p-4 hover:shadow-md transition-shadow',
          colorClasses.border,
        ]"
      >
        <div class="flex items-start justify-between">
          <div class="flex-1 min-w-0">
            <h4 class="text-sm font-medium text-gray-900 truncate">
              {{ detail.internal_name }}
            </h4>
            <div class="mt-1 flex items-center text-sm text-gray-500">
              <span class="truncate">
                Created
                <DateDisplay :date="detail.created_at" format="short" variant="small-dark" />
              </span>
            </div>
          </div>
          <div class="flex items-center space-x-2 ml-4">
            <ViewButton @click="handleViewDetail(detail)" />
            <EditButton @click="handleEditDetail(detail)" />
            <DeleteButton @click="handleDeleteDetail(detail)" />
          </div>
        </div>
      </div>
    </div>

    <!-- Empty state -->
    <div
      v-else
      class="text-center py-12 border-2 border-dashed border-gray-300 rounded-lg"
    >
      <CubeIcon class="mx-auto h-12 w-12 text-gray-400" />
      <h3 class="mt-2 text-sm font-medium text-gray-900">No details yet</h3>
      <p class="mt-1 text-sm text-gray-500">Get started by adding a detail to this item.</p>
      <div class="mt-6">
        <button
          :class="[
            'inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white focus:outline-none focus:ring-2 focus:ring-offset-2',
            colorClasses.button,
            colorClasses.buttonHover,
            colorClasses.focus,
          ]"
          @click="handleAddDetail"
        >
          <PlusIcon class="h-4 w-4 mr-2" />
          Add Detail
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { computed, onMounted } from 'vue'
  import { useRouter } from 'vue-router'
  import { PlusIcon, CubeIcon } from '@heroicons/vue/24/outline'
  import { useDetailStore } from '@/stores/detail'
  import { useDeleteConfirmationStore } from '@/stores/deleteConfirmation'
  import { useErrorDisplayStore } from '@/stores/errorDisplay'
  import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
  import { useColors, type ColorName } from '@/composables/useColors'
  import type { DetailResource } from '@metanull/inventory-app-api-client'
  import ViewButton from '@/components/layout/list/ViewButton.vue'
  import EditButton from '@/components/layout/list/EditButton.vue'
  import DeleteButton from '@/components/layout/list/DeleteButton.vue'
  import DateDisplay from '@/components/format/Date.vue'

  interface Props {
    itemId: string
    color?: ColorName
  }

  const props = withDefaults(defineProps<Props>(), {
    color: 'teal',
  })

  // Color classes from centralized system
  const colorClasses = useColors(computed(() => props.color))

  // Composables
  const router = useRouter()
  const detailStore = useDetailStore()
  const deleteStore = useDeleteConfirmationStore()
  const errorStore = useErrorDisplayStore()
  const loadingStore = useLoadingOverlayStore()

  // Computed
  const details = computed(() => detailStore.details)

  // Methods
  const fetchDetails = async () => {
    try {
      await detailStore.fetchDetails({ itemId: props.itemId })
    } catch {
      errorStore.addMessage('error', 'Failed to load details. Please try again.')
    }
  }

  const handleAddDetail = () => {
    router.push(`/items/${props.itemId}/details/new`)
  }

  const handleViewDetail = (detail: DetailResource) => {
    router.push(`/items/${props.itemId}/details/${detail.id}`)
  }

  const handleEditDetail = (detail: DetailResource) => {
    router.push(`/items/${props.itemId}/details/${detail.id}?edit=true`)
  }

  const handleDeleteDetail = async (detail: DetailResource) => {
    const result = await deleteStore.trigger(
      'Delete Detail',
      `Are you sure you want to delete "${detail.internal_name}"? This action cannot be undone.`
    )

    if (result === 'delete') {
      try {
        loadingStore.show('Deleting detail...')
        await detailStore.deleteDetail(detail.id)
        errorStore.addMessage('info', 'Detail deleted successfully.')
        // Refresh the details list after deletion
        await fetchDetails()
      } catch {
        errorStore.addMessage('error', 'Failed to delete detail. Please try again.')
      } finally {
        loadingStore.hide()
      }
    }
  }

  // Initialize
  onMounted(() => {
    fetchDetails()
  })
</script>
