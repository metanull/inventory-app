<template>
  <DetailView
    :store-loading="availableImageStore.loading"
    :resource="availableImage as any"
    :mode="mode"
    :save-disabled="!hasUnsavedChanges"
    :has-unsaved-changes="hasUnsavedChanges"
    :back-link="backLink"
    :create-title="'New Image'"
    :create-subtitle="'(Creating)'"
    information-title="Image Information"
    :information-description="informationDescription"
    :fetch-data="fetchAvailableImage"
    :disable-create-mode="true"
    @edit="enterEditMode"
    @save="saveAvailableImage"
    @cancel="cancelAction"
    @delete="deleteAvailableImage"
  >
    <template #resource-icon>
      <PhotoIcon :class="`h-6 w-6 ${colorClasses.icon}`" />
    </template>

    <template #information>
      <!-- Image Preview -->
      <div v-if="availableImage" class="mb-6">
        <div class="aspect-video bg-gray-100 rounded-lg overflow-hidden">
          <img
            :src="getImageUrl(availableImage)"
            :alt="availableImage.comment || 'Image'"
            class="h-full w-full object-contain"
            @error="handleImageError"
          />
        </div>

        <!-- Image Actions -->
        <div class="mt-4 flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <button
              :class="[
                'inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white focus:outline-none focus:ring-2 focus:ring-offset-2',
                colorClasses.button,
                colorClasses.focus,
              ]"
              @click="downloadImage"
            >
              <ArrowDownTrayIcon class="h-4 w-4 mr-2" />
              Download
            </button>
          </div>
          <div class="text-sm text-gray-500">
            {{ formatFileSize(getImageSize()) }}
          </div>
        </div>
      </div>

      <DescriptionList>
        <DescriptionRow variant="gray">
          <DescriptionTerm>Comment</DescriptionTerm>
          <DescriptionDetail>
            <FormInput
              v-if="mode === 'edit'"
              v-model="editForm.comment"
              type="text"
              placeholder="Add a comment for this image"
            />
            <DisplayText v-else>{{ availableImage?.comment || '—' }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>

        <DescriptionRow v-if="availableImage?.path" variant="white">
          <DescriptionTerm>File Path</DescriptionTerm>
          <DescriptionDetail>
            <span class="font-mono text-sm text-gray-600">{{ availableImage.path }}</span>
          </DescriptionDetail>
        </DescriptionRow>

        <!-- System Information -->
        <DescriptionRow v-if="availableImage?.id" variant="gray">
          <DescriptionTerm>Image ID</DescriptionTerm>
          <DescriptionDetail>
            <span class="font-mono text-sm text-gray-600">{{ availableImage.id }}</span>
          </DescriptionDetail>
        </DescriptionRow>
      </DescriptionList>
    </template>
  </DetailView>
</template>

<script setup lang="ts">
  import { computed, ref, onMounted, watch } from 'vue'
  import {
    useRoute,
    useRouter,
    onBeforeRouteLeave,
    type NavigationGuardNext,
    type RouteLocationNormalized,
  } from 'vue-router'
  import DetailView from '@/components/layout/detail/DetailView.vue'
  import DescriptionList from '@/components/format/description/DescriptionList.vue'
  import DescriptionRow from '@/components/format/description/DescriptionRow.vue'
  import DescriptionTerm from '@/components/format/description/DescriptionTerm.vue'
  import DescriptionDetail from '@/components/format/description/DescriptionDetail.vue'
  import FormInput from '@/components/format/FormInput.vue'
  import DisplayText from '@/components/format/DisplayText.vue'
  import { PhotoIcon, ArrowDownTrayIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline'
  import { useAvailableImageStore } from '@/stores/availableImage'
  import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
  import { useErrorDisplayStore } from '@/stores/errorDisplay'
  import { useCancelChangesConfirmationStore } from '@/stores/cancelChangesConfirmation'
  import { useDeleteConfirmationStore } from '@/stores/deleteConfirmation'
  import { useColors, type ColorName } from '@/composables/useColors'
  import { useImageFallback } from '@/composables/useImageFallback'
  import type {
    AvailableImageResource,
    AvailableImageUpdateRequest,
  } from '@metanull/inventory-app-api-client'

  // Types
  type Mode = 'view' | 'edit'

  interface AvailableImageFormData {
    id?: string
    comment: string
  }

  interface Props {
    color?: ColorName
  }

  const props = withDefaults(defineProps<Props>(), {
    color: 'pink',
  })

  // Color classes from centralized system
  const colorClasses = useColors(computed(() => props.color))

  // Composables
  const route = useRoute()
  const router = useRouter()
  const availableImageStore = useAvailableImageStore()
  const loadingStore = useLoadingOverlayStore()
  const errorStore = useErrorDisplayStore()
  const cancelChangesStore = useCancelChangesConfirmationStore()
  const deleteStore = useDeleteConfirmationStore()
  const { fallbackImageUrl } = useImageFallback()

  // State
  const mode = ref<Mode>('view')
  const originalFormData = ref<AvailableImageFormData>()
  const editForm = ref<AvailableImageFormData>({
    comment: '',
  })

  // Computed properties
  const availableImage = computed(() => availableImageStore.currentAvailableImage)

  const hasUnsavedChanges = computed(() => {
    if (mode.value !== 'edit' || !originalFormData.value) return false

    return editForm.value.comment !== originalFormData.value.comment
  })

  const backLink = computed(() => ({
    title: 'Available Images',
    route: '/images',
    icon: ArrowLeftIcon,
    color: 'pink' as ColorName,
  }))

  const informationDescription = computed(() => {
    if (mode.value === 'edit') {
      return 'Edit image information and metadata.'
    }
    return 'View image details and metadata.'
  })

  // Navigation guard - prevent navigation if there are unsaved changes
  onBeforeRouteLeave(
    async (
      _to: RouteLocationNormalized,
      _from: RouteLocationNormalized,
      next: NavigationGuardNext
    ) => {
      if (hasUnsavedChanges.value) {
        const shouldLeave = await cancelChangesStore.trigger(
          'Unsaved Changes',
          'You have unsaved changes. Are you sure you want to leave this page?'
        )
        if (shouldLeave === 'leave') {
          next() // Allow navigation and discard changes
        } else {
          next(false) // Block navigation
        }
      } else {
        next() // Allow navigation if no unsaved changes
      }
    }
  )

  // Initialize edit form from available image data
  const getDefaultFormValues = (): AvailableImageFormData => ({
    id: '',
    comment: '',
  })

  const getFormValuesFromAvailableImage = (): AvailableImageFormData => {
    if (!availableImage.value) return getDefaultFormValues()

    return {
      id: availableImage.value.id,
      comment: availableImage.value.comment || '',
    }
  }

  // Fetch available image function
  const fetchAvailableImage = async () => {
    const availableImageId = route.params.id as string
    if (!availableImageId) return

    try {
      loadingStore.show()
      await availableImageStore.fetchAvailableImage(availableImageId)
    } catch {
      errorStore.addMessage('error', 'Failed to load image. Please try again.')
    } finally {
      loadingStore.hide()
    }
  }

  // Mode management
  const enterEditMode = () => {
    mode.value = 'edit'
    const formData = getFormValuesFromAvailableImage()
    editForm.value = { ...formData }
    originalFormData.value = { ...formData }
  }

  const exitEditMode = () => {
    mode.value = 'view'
    editForm.value = getDefaultFormValues()
    originalFormData.value = undefined
  }

  // Actions
  const saveAvailableImage = async () => {
    if (!availableImage.value || mode.value !== 'edit') return

    try {
      loadingStore.show('Saving...')

      const requestData: AvailableImageUpdateRequest = {
        comment: editForm.value.comment.trim() || null,
      }

      await availableImageStore.updateAvailableImage(availableImage.value.id, requestData)
      exitEditMode()
      errorStore.addMessage('info', 'Image updated successfully.')
    } catch {
      errorStore.addMessage('error', 'Failed to update image. Please try again.')
    } finally {
      loadingStore.hide()
    }
  }

  const cancelAction = () => {
    if (hasUnsavedChanges.value) {
      cancelChangesStore.resetChanges()
    }
    exitEditMode()
  }

  const deleteAvailableImage = async () => {
    if (!availableImage.value) return

    const result = await deleteStore.trigger(
      'Delete Image',
      'Are you sure you want to delete this image? This action cannot be undone.'
    )

    if (result === 'delete') {
      try {
        loadingStore.show('Deleting...')
        await availableImageStore.deleteAvailableImage(availableImage.value.id)
        errorStore.addMessage('info', 'Image deleted successfully.')
        router.push({ name: 'available-images' })
      } catch {
        errorStore.addMessage('error', 'Failed to delete image. Please try again.')
      } finally {
        loadingStore.hide()
      }
    }
  }

  // Image utilities
  // Image URL management
  const imageUrl = ref<string>('')
  const loadingImage = ref<boolean>(false)

  const getImageUrl = (image: AvailableImageResource): string => {
    // Return existing URL if available
    if (imageUrl.value) {
      return imageUrl.value
    }

    // If not loading, start loading
    if (!loadingImage.value) {
      loadingImage.value = true
      availableImageStore
        .getImageUrl(image)
        .then(url => {
          imageUrl.value = url
          loadingImage.value = false
        })
        .catch(error => {
          console.error('Failed to load image URL:', error)
          loadingImage.value = false
          // Set a placeholder URL
          imageUrl.value = fallbackImageUrl
        })
    }

    // Return placeholder while loading
    return fallbackImageUrl
  }

  const getImageSize = (): number => {
    // This would ideally come from the API, but for now return a placeholder
    return 1024 * 1024 // 1MB placeholder
  }

  const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes'
    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
  }

  const downloadImage = async () => {
    if (!availableImage.value) return

    try {
      loadingStore.show('Downloading image...')
      // Use the available image store to get the image data
      const url = await availableImageStore.getImageUrl(availableImage.value)

      // Create a temporary link element to trigger the download
      const link = document.createElement('a')
      link.href = url
      link.download = `image-${availableImage.value.id}.jpg`
      document.body.appendChild(link)
      link.click()

      // Clean up
      document.body.removeChild(link)

      errorStore.addMessage('info', 'Image downloaded successfully.')
    } catch (error) {
      console.error('Download failed:', error)
      errorStore.addMessage('error', 'Failed to download image. Please try again.')
    } finally {
      loadingStore.hide()
    }
  }

  const handleImageError = (event: Event) => {
    const img = event.target as HTMLImageElement
    img.src = fallbackImageUrl
  }

  // Watch for changes in available image and reload image URL
  watch(
    availableImage,
    (newImage: AvailableImageResource | null) => {
      if (newImage) {
        // Reset image URL and trigger loading
        imageUrl.value = ''
        loadingImage.value = false
        getImageUrl(newImage) // This will trigger loading
      }
    },
    { immediate: true }
  )

  // Initialize component
  const initializeComponent = async () => {
    const availableImageId = route.params.id as string
    const isEditMode = route.query.edit === 'true'

    try {
      if (availableImageId) {
        await fetchAvailableImage()

        if (isEditMode && availableImage.value) {
          enterEditMode()
        } else {
          exitEditMode()
        }
      }
    } catch {
      errorStore.addMessage('error', 'Failed to initialize page.')
    }
  }

  // Initialize on mount
  onMounted(initializeComponent)

  // Watch for route changes to handle navigation between different images or edit modes
  watch(() => route.params.id, initializeComponent)
  watch(() => route.query.edit, initializeComponent)
</script>
