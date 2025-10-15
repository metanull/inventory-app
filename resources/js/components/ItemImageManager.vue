<template>
  <div class="mt-8 space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <h3 class="text-lg font-medium leading-6 text-gray-900">Item Images</h3>
      <button
        type="button"
        :class="[
          'inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2',
          colorClasses.button,
          colorClasses.ring,
        ]"
        @click="showAttachDialog = true"
      >
        <PlusIcon class="h-5 w-5 mr-2" />
        Attach Image
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
    </div>

    <!-- Empty State -->
    <div
      v-else-if="images.length === 0"
      class="text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300"
    >
      <PhotoIcon class="mx-auto h-12 w-12 text-gray-400" />
      <h3 class="mt-2 text-sm font-medium text-gray-900">No images</h3>
      <p class="mt-1 text-sm text-gray-500">Get started by attaching an image to this item.</p>
      <div class="mt-6">
        <button
          type="button"
          :class="[
            'inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2',
            colorClasses.button,
            colorClasses.ring,
          ]"
          @click="showAttachDialog = true"
        >
          <PlusIcon class="h-5 w-5 mr-2" />
          Attach Image
        </button>
      </div>
    </div>

    <!-- Images Grid -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="(image, index) in images"
        :key="image.id"
        class="relative group bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow"
      >
        <!-- Image -->
        <div class="aspect-w-16 aspect-h-9 bg-gray-100">
          <img
            :src="`/storage/${image.path}`"
            :alt="image.alt_text || `Image ${index + 1}`"
            class="object-cover w-full h-48"
          />
        </div>

        <!-- Info Overlay -->
        <div class="p-4">
          <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-900">Order: {{ image.display_order }}</span>
            <div class="flex space-x-1">
              <!-- Move Up -->
              <button
                v-if="index > 0"
                type="button"
                class="p-1 rounded hover:bg-gray-100"
                title="Move Up"
                @click="handleMoveUp(image.id)"
              >
                <ChevronUpIcon class="h-5 w-5 text-gray-600" />
              </button>
              <!-- Move Down -->
              <button
                v-if="index < images.length - 1"
                type="button"
                class="p-1 rounded hover:bg-gray-100"
                title="Move Down"
                @click="handleMoveDown(image.id)"
              >
                <ChevronDownIcon class="h-5 w-5 text-gray-600" />
              </button>
            </div>
          </div>

          <!-- Alt Text -->
          <div class="mb-3">
            <label class="block text-xs font-medium text-gray-700 mb-1">Alt Text</label>
            <input
              v-model="image.alt_text"
              type="text"
              placeholder="Describe this image"
              class="block w-full px-2 py-1 text-sm border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
              @blur="handleUpdateAltText(image.id, image.alt_text)"
            />
          </div>

          <!-- File Info -->
          <div class="text-xs text-gray-500 mb-3">
            <div>{{ image.original_name }}</div>
            <div>{{ formatFileSize(image.size) }} • {{ image.mime_type }}</div>
          </div>

          <!-- Actions -->
          <div class="flex space-x-2">
            <button
              type="button"
              class="flex-1 px-3 py-1.5 text-xs font-medium text-white bg-yellow-600 rounded hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
              @click="handleDetach(image)"
            >
              Detach
            </button>
            <button
              type="button"
              class="flex-1 px-3 py-1.5 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
              @click="handleDelete(image)"
            >
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Attach Image Dialog -->
    <Dialog :open="showAttachDialog" @close="showAttachDialog = false">
      <div class="fixed inset-0 bg-black bg-opacity-25" />
      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4">
          <DialogPanel
            class="w-full max-w-4xl transform overflow-hidden rounded-2xl bg-white p-6 shadow-xl transition-all"
          >
            <DialogTitle class="text-lg font-medium leading-6 text-gray-900 mb-4">
              Attach Image from Available Images
            </DialogTitle>

            <!-- Available Images Grid -->
            <div class="max-h-96 overflow-y-auto">
              <div v-if="availableImages.length === 0" class="text-center py-8 text-gray-500">
                No available images found. Please upload images first.
              </div>
              <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                <div
                  v-for="availImage in availableImages"
                  :key="availImage.id"
                  class="relative cursor-pointer group"
                  @click="handleAttachImage(availImage.id)"
                >
                  <div class="aspect-w-1 aspect-h-1 bg-gray-100 rounded-lg overflow-hidden">
                    <img
                      :src="`/storage/${availImage.path}`"
                      :alt="availImage.comment || 'Available image'"
                      class="object-cover w-full h-32 group-hover:opacity-75"
                    />
                  </div>
                  <div class="mt-1 text-xs text-gray-500 truncate">
                    {{ availImage.comment || availImage.path?.split('/').pop() || 'Unnamed' }}
                  </div>
                </div>
              </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
              <button
                type="button"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                @click="showAttachDialog = false"
              >
                Cancel
              </button>
            </div>
          </DialogPanel>
        </div>
      </div>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
  import { ref, computed, onMounted, watch } from 'vue'
  import { Dialog, DialogPanel, DialogTitle } from '@headlessui/vue'
  import { PlusIcon, PhotoIcon, ChevronUpIcon, ChevronDownIcon } from '@heroicons/vue/24/outline'
  import { useItemImageStore } from '@/stores/itemImage'
  import { useAvailableImageStore } from '@/stores/availableImage'
  import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
  import { useErrorDisplayStore } from '@/stores/errorDisplay'
  import { useDeleteConfirmationStore } from '@/stores/deleteConfirmation'
  import { useColors, type ColorName } from '@/composables/useColors'
  import type { ItemImageResource } from '@metanull/inventory-app-api-client'

  interface Props {
    itemId: string
    color?: ColorName
  }

  const props = withDefaults(defineProps<Props>(), {
    color: 'indigo',
  })

  const itemImageStore = useItemImageStore()
  const availableImageStore = useAvailableImageStore()
  const loadingStore = useLoadingOverlayStore()
  const errorStore = useErrorDisplayStore()
  const deleteStore = useDeleteConfirmationStore()

  const showAttachDialog = ref(false)
  const colorClasses = useColors(computed(() => props.color))

  const images = computed(() => itemImageStore.itemImages)
  const loading = computed(() => itemImageStore.loading)
  const availableImages = computed(() => availableImageStore.availableImages || [])

  // Format file size
  const formatFileSize = (bytes: number): string => {
    if (bytes < 1024) return `${bytes} B`
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
  }

  // Load images when component mounts or itemId changes
  const loadImages = async () => {
    if (props.itemId) {
      try {
        await itemImageStore.fetchItemImages(props.itemId)
      } catch {
        errorStore.addMessage('error', 'Failed to load images')
      }
    }
  }

  // Load available images for attach dialog
  const loadAvailableImages = async () => {
    try {
      await availableImageStore.fetchAvailableImages({ page: 1, perPage: 100 })
    } catch {
      errorStore.addMessage('error', 'Failed to load available images')
    }
  }

  // Attach image
  const handleAttachImage = async (availableImageId: string) => {
    try {
      loadingStore.show('Attaching image...')
      await itemImageStore.attachImageToItem(props.itemId, availableImageId)
      showAttachDialog.value = false
      errorStore.addMessage('info', 'Image attached successfully')
      // Refresh available images
      await loadAvailableImages()
    } catch {
      errorStore.addMessage('error', 'Failed to attach image')
    } finally {
      loadingStore.hide()
    }
  }

  // Move image up
  const handleMoveUp = async (imageId: string) => {
    try {
      await itemImageStore.moveImageUp(imageId)
      errorStore.addMessage('info', 'Image moved up')
    } catch {
      errorStore.addMessage('error', 'Failed to move image')
    }
  }

  // Move image down
  const handleMoveDown = async (imageId: string) => {
    try {
      await itemImageStore.moveImageDown(imageId)
      errorStore.addMessage('info', 'Image moved down')
    } catch {
      errorStore.addMessage('error', 'Failed to move image')
    }
  }

  // Update alt text
  const handleUpdateAltText = async (imageId: string, altText: string | null) => {
    try {
      await itemImageStore.updateItemImage(imageId, { alt_text: altText })
    } catch {
      errorStore.addMessage('error', 'Failed to update alt text')
    }
  }

  // Detach image (move back to available)
  const handleDetach = async (image: ItemImageResource) => {
    const result = await deleteStore.trigger(
      'Detach Image',
      `Are you sure you want to detach "${image.original_name}"? It will be moved back to available images.`
    )

    if (result === 'delete') {
      try {
        loadingStore.show('Detaching image...')
        await itemImageStore.detachImageFromItem(image.id)
        errorStore.addMessage('info', 'Image detached successfully')
        // Refresh available images
        await loadAvailableImages()
      } catch {
        errorStore.addMessage('error', 'Failed to detach image')
      } finally {
        loadingStore.hide()
      }
    }
  }

  // Delete image permanently
  const handleDelete = async (image: ItemImageResource) => {
    const result = await deleteStore.trigger(
      'Delete Image',
      `Are you sure you want to permanently delete "${image.original_name}"? This action cannot be undone.`
    )

    if (result === 'delete') {
      try {
        loadingStore.show('Deleting image...')
        await itemImageStore.deleteItemImage(image.id)
        errorStore.addMessage('info', 'Image deleted successfully')
      } catch {
        errorStore.addMessage('error', 'Failed to delete image')
      } finally {
        loadingStore.hide()
      }
    }
  }

  // Watch for attach dialog open
  watch(showAttachDialog, isOpen => {
    if (isOpen) {
      loadAvailableImages()
    }
  })

  // Load images on mount
  onMounted(() => {
    loadImages()
  })

  // Reload when itemId changes
  watch(
    () => props.itemId,
    () => {
      loadImages()
    }
  )
</script>
