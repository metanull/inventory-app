<template>
  <ListView
    title="Available Images"
    description="Manage validated and processed images in your collection."
    add-button-route="/images/upload"
    add-button-label="Upload Images"
    color="pink"
    :is-empty="isEmptyState"
    empty-title="No images found"
    empty-message="Get started by uploading some images for processing."
    :show-empty-add-button="true"
    empty-add-button-label="Upload Images"
    @retry="fetchImages"
  >
    <!-- Icon -->
    <template #icon>
      <PhotoIcon />
    </template>

    <!-- View Mode Toggle -->
    <template #filters>
      <div class="flex items-center space-x-2">
        <FilterButton
          label="List View"
          :is-active="viewMode === 'list'"
          variant="info"
          :color="color"
          @click="viewMode = 'list'"
        />
        <FilterButton
          label="Gallery View"
          :is-active="viewMode === 'gallery'"
          variant="info"
          :color="color"
          @click="viewMode = 'gallery'"
        />
      </div>
    </template>

    <!-- Search -->
    <template #search>
      <SearchControl v-model="searchQuery" placeholder="Search images..." @search="handleSearch" />
    </template>

    <!-- List View Mode -->
    <template v-if="viewMode === 'list'" #headers>
      <TableRow>
        <TableHeader
          sortable
          :sort-direction="sortKey === 'id' ? sortDirection : null"
          @sort="handleSort('id')"
        >
          Image
        </TableHeader>
        <TableHeader
          sortable
          class="hidden md:table-cell"
          :sort-direction="sortKey === 'comment' ? sortDirection : null"
          @sort="handleSort('comment')"
        >
          Comment
        </TableHeader>
        <TableHeader
          sortable
          class="hidden lg:table-cell"
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
      <!-- List View Mode -->
      <template v-if="viewMode === 'list'">
        <TableRow
          v-for="image in filteredImages"
          :key="image.id"
          :class="['cursor-pointer transition', colorClasses.hover]"
          @click="openImageDetail(image.id)"
        >
          <TableCell>
            <div class="flex items-center">
              <div class="h-12 w-12 flex-shrink-0 mr-4">
                <img
                  :src="getImageThumbnailUrl(image)"
                  :alt="image.comment || 'Image'"
                  class="h-12 w-12 rounded-lg object-cover border border-gray-200"
                  @error="handleImageError"
                />
              </div>
              <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-gray-900 truncate">
                  {{ image.comment || 'No comment' }}
                </p>
                <p class="text-xs text-gray-500 font-mono">{{ image.id.substring(0, 8) }}...</p>
              </div>
            </div>
          </TableCell>
          <TableCell class="hidden md:table-cell">
            <DisplayText>{{ image.comment || '—' }}</DisplayText>
          </TableCell>
          <TableCell class="hidden lg:table-cell">
            <DateDisplay :date="image.created_at" format="short" variant="small-dark" />
          </TableCell>
          <TableCell class="hidden sm:table-cell">
            <div class="flex space-x-2" @click.stop>
              <ViewButton
                @click="router.push({ name: 'available-image-detail', params: { id: image.id } })"
              />
              <EditButton
                @click="
                  router.push({
                    name: 'available-image-detail',
                    params: { id: image.id },
                    query: { edit: 'true' },
                  })
                "
              />
              <DeleteButton @click="handleDeleteImage(image)" />
            </div>
          </TableCell>
        </TableRow>
      </template>

      <!-- Gallery View Mode -->
      <template v-if="viewMode === 'gallery'">
        <tr>
          <td colspan="100%" class="p-0">
            <div
              class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 p-4"
            >
              <div
                v-for="(image, index) in filteredImages"
                :key="image.id"
                :class="[
                  'group relative bg-white rounded-lg shadow hover:shadow-lg transition-shadow cursor-pointer',
                  colorClasses.border,
                ]"
                @click="openImageDetail(image.id)"
              >
                <div class="text-xs text-gray-500 p-2">Image {{ index + 1 }}</div>
                <!-- Image -->
                <div class="aspect-square rounded-t-lg overflow-hidden">
                  <img
                    :src="getImageThumbnailUrl(image)"
                    :alt="image.comment || 'Image'"
                    class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-200"
                    @error="handleImageError"
                  />
                </div>

                <!-- Content -->
                <div class="p-3">
                  <p class="text-sm font-medium text-gray-900 truncate mb-1">
                    {{ image.comment || 'No comment' }}
                  </p>
                  <p class="text-xs text-gray-500">
                    <DateDisplay :date="image.created_at" format="short" variant="small-dark" />
                  </p>
                </div>

                <!-- Actions Overlay -->
                <div
                  class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity"
                >
                  <div class="flex space-x-1" @click.stop>
                    <button
                      class="p-1 rounded-full bg-white shadow-sm hover:bg-gray-50"
                      @click="
                        router.push({ name: 'available-image-detail', params: { id: image.id } })
                      "
                    >
                      <EyeIcon class="h-4 w-4 text-gray-600" />
                    </button>
                    <button
                      class="p-1 rounded-full bg-white shadow-sm hover:bg-gray-50"
                      @click="
                        router.push({
                          name: 'available-image-detail',
                          params: { id: image.id },
                          query: { edit: 'true' },
                        })
                      "
                    >
                      <PencilIcon class="h-4 w-4 text-gray-600" />
                    </button>
                    <button
                      class="p-1 rounded-full bg-white shadow-sm hover:bg-gray-50"
                      @click="handleDeleteImage(image)"
                    >
                      <TrashIcon class="h-4 w-4 text-red-600" />
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </td>
        </tr>
      </template>
    </template>

    <!-- Pagination controls -->
    <template #pagination>
      <PaginationControls
        :page="availableImageStore.page"
        :per-page="availableImageStore.perPage"
        :total="availableImageStore.total"
        :color="color"
        @update:page="onPageChange"
        @update:per-page="onPerPageChange"
      />
    </template>
  </ListView>
</template>

<script setup lang="ts">
  import { computed, ref, onMounted, watch } from 'vue'
  import { useRouter } from 'vue-router'
  import ListView from '@/components/layout/list/ListView.vue'
  import TableHeader from '@/components/format/table/TableHeader.vue'
  import TableRow from '@/components/format/table/TableRow.vue'
  import TableCell from '@/components/format/table/TableCell.vue'
  import ViewButton from '@/components/layout/list/ViewButton.vue'
  import EditButton from '@/components/layout/list/EditButton.vue'
  import DeleteButton from '@/components/layout/list/DeleteButton.vue'
  import FilterButton from '@/components/layout/list/FilterButton.vue'
  import SearchControl from '@/components/layout/list/SearchControl.vue'
  import PaginationControls from '@/components/layout/list/PaginationControls.vue'
  import DisplayText from '@/components/format/DisplayText.vue'
  import DateDisplay from '@/components/format/Date.vue'
  import { PhotoIcon, EyeIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline'
  import { useAvailableImageStore } from '@/stores/availableImage'
  import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
  import { useErrorDisplayStore } from '@/stores/errorDisplay'
  import { useDeleteConfirmationStore } from '@/stores/deleteConfirmation'
  import { useColors, type ColorName } from '@/composables/useColors'
  import { useImageFallback } from '@/composables/useImageFallback'
  import type { AvailableImageResource } from '@metanull/inventory-app-api-client'

  interface Props {
    color?: ColorName
  }

  const props = withDefaults(defineProps<Props>(), {
    color: 'purple',
  })

  const router = useRouter()

  const availableImageStore = useAvailableImageStore()
  const loadingStore = useLoadingOverlayStore()
  const errorStore = useErrorDisplayStore()
  const deleteStore = useDeleteConfirmationStore()
  const { fallbackImageUrl } = useImageFallback()

  // Color classes from centralized system
  const colorClasses = useColors(computed(() => props.color))

  // State
  const viewMode = ref<'list' | 'gallery'>('gallery')
  const searchQuery = ref('')
  const sortKey = ref<string>('created_at')
  const sortDirection = ref<'asc' | 'desc'>('desc')

  // Computed
  const availableImages = computed(() => availableImageStore.availableImages)

  const filteredImages = computed(() => {
    const sourceImages = availableImages.value

    if (sourceImages.length === 0) {
      return []
    }

    let filtered = [...sourceImages]

    // Apply search filter
    if (searchQuery.value.trim()) {
      const query = searchQuery.value.toLowerCase()
      filtered = filtered.filter(
        image =>
          image.comment?.toLowerCase().includes(query) || image.id.toLowerCase().includes(query)
      )
    }

    // Apply sorting
    filtered.sort((a, b) => {
      let aValue: any
      let bValue: any

      switch (sortKey.value) {
        case 'comment':
          aValue = a.comment || ''
          bValue = b.comment || ''
          break
        case 'created_at':
          aValue = new Date(a.created_at || 0)
          bValue = new Date(b.created_at || 0)
          break
        default:
          aValue = a.id
          bValue = b.id
      }

      if (typeof aValue === 'string') {
        const comparison = aValue.localeCompare(bValue)
        return sortDirection.value === 'asc' ? comparison : -comparison
      } else {
        const comparison = aValue < bValue ? -1 : aValue > bValue ? 1 : 0
        return sortDirection.value === 'asc' ? comparison : -comparison
      }
    })

    return filtered
  })

  const isEmptyState = computed(() => {
    return filteredImages.value.length === 0
  })

  // Methods
  const fetchImages = async () => {
    try {
      loadingStore.show()
      await availableImageStore.fetchAvailableImages({
        page: availableImageStore.page,
        perPage: availableImageStore.perPage,
      })
      errorStore.addMessage('info', 'Images refreshed successfully.')
    } catch {
      errorStore.addMessage('error', 'Failed to load images. Please try again.')
    } finally {
      loadingStore.hide()
    }
  }

  const handleSort = (key: string) => {
    if (sortKey.value === key) {
      sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
    } else {
      sortKey.value = key
      sortDirection.value = 'asc'
    }
  }

  const handleSearch = (query: string) => {
    searchQuery.value = query
  }

  const openImageDetail = (imageId: string) => {
    router.push({ name: 'available-image-detail', params: { id: imageId } })
  }

  const handleDeleteImage = async (image: AvailableImageResource) => {
    const result = await deleteStore.trigger(
      'Delete Image',
      `Are you sure you want to delete this image? This action cannot be undone.`
    )

    if (result === 'delete') {
      try {
        loadingStore.show('Deleting image...')
        await availableImageStore.deleteAvailableImage(image.id)
        errorStore.addMessage('info', 'Image deleted successfully.')
      } catch {
        errorStore.addMessage('error', 'Failed to delete image. Please try again.')
      } finally {
        loadingStore.hide()
      }
    }
  }

  const onPageChange = async (page: number) => {
    try {
      loadingStore.show()
      await availableImageStore.fetchAvailableImages({ page, perPage: availableImageStore.perPage })
      errorStore.addMessage('info', 'Page loaded successfully.')
    } catch {
      errorStore.addMessage('error', 'Failed to load page. Please try again.')
    } finally {
      loadingStore.hide()
    }
  }

  const onPerPageChange = async (perPage: number) => {
    try {
      loadingStore.show()
      await availableImageStore.fetchAvailableImages({ page: 1, perPage })
      errorStore.addMessage('info', 'Page size updated successfully.')
    } catch {
      errorStore.addMessage('error', 'Failed to update page size. Please try again.')
    } finally {
      loadingStore.hide()
    }
  }

  // Image URL management
  const imageUrls = ref<Record<string, string>>({})
  const loadingImages = ref<Record<string, boolean>>({})

  const getImageThumbnailUrl = (image: AvailableImageResource): string => {
    // Return existing URL if available
    if (imageUrls.value[image.id]) {
      return imageUrls.value[image.id]!
    }

    // If not loading and not loaded, start loading
    if (!loadingImages.value[image.id]) {
      loadingImages.value[image.id] = true
      availableImageStore
        .getImageUrl(image)
        .then(url => {
          imageUrls.value[image.id] = url
          loadingImages.value[image.id] = false
        })
        .catch(error => {
          console.error('Failed to load image URL:', error)
          loadingImages.value[image.id] = false
          // Set a placeholder URL
          imageUrls.value[image.id] = fallbackImageUrl
        })
    }

    // Return placeholder while loading
    return fallbackImageUrl
  }

  const handleImageError = (event: Event) => {
    const img = event.target as HTMLImageElement
    img.src = fallbackImageUrl
  }

  // Watch for changes in available images and preload image URLs
  watch(
    availableImages,
    (newImages: AvailableImageResource[] | null) => {
      if (newImages) {
        // Preload image URLs for visible images
        newImages.forEach((image: AvailableImageResource) => {
          if (!imageUrls.value[image.id] && !loadingImages.value[image.id]) {
            getImageThumbnailUrl(image) // This will trigger loading
          }
        })
      }
    },
    { immediate: true }
  )

  // Initialize
  onMounted(async () => {
    let usedCache = false
    // If cache exists, display immediately and refresh in background
    if (availableImages.value && availableImages.value.length > 0) {
      usedCache = true
    } else {
      loadingStore.show()
    }
    try {
      // Always refresh in background
      await availableImageStore.fetchAvailableImages({
        page: availableImageStore.page,
        perPage: availableImageStore.perPage,
      })
      if (usedCache) {
        errorStore.addMessage('info', 'List refreshed')
      }
    } catch (error) {
      console.error('AvailableImages.vue: Error in fetchAvailableImages:', error)
      errorStore.addMessage('error', 'Failed to fetch images. Please try again.')
    } finally {
      if (!usedCache) {
        loadingStore.hide()
      }
    }
  })
</script>
