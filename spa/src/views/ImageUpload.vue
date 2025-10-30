<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center">
          <router-link
            to="/"
            class="mr-4 p-2 rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          >
            <ArrowLeftIcon class="h-5 w-5" />
          </router-link>
          <div class="flex items-center">
            <CloudArrowUpIcon :class="['h-8 w-8 mr-3', colorClasses.icon]" />
            <div>
              <h1 class="text-2xl font-bold text-gray-900">Image Upload</h1>
              <p class="text-sm text-gray-500">Upload images for processing and validation</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Upload Area -->
      <div class="bg-white rounded-lg shadow">
        <div class="p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Upload Images</h2>

          <!-- Drag & Drop Area -->
          <div
            ref="dropZone"
            :class="[
              'relative border-2 border-dashed rounded-lg p-12 text-center transition-colors',
              isDragOver ? 'border-indigo-400 bg-indigo-50' : 'border-gray-300',
              isUploading ? 'pointer-events-none opacity-50' : 'hover:border-gray-400',
            ]"
            @drop="handleDrop"
            @dragover="handleDragOver"
            @dragenter="handleDragEnter"
            @dragleave="handleDragLeave"
          >
            <CloudArrowUpIcon
              :class="['mx-auto h-12 w-12 mb-4', isDragOver ? 'text-indigo-500' : 'text-gray-400']"
            />
            <div class="space-y-2">
              <p class="text-lg font-medium text-gray-900">
                {{ isDragOver ? 'Drop files here' : 'Drag and drop images here' }}
              </p>
              <p class="text-sm text-gray-500">or</p>
              <button
                :class="[
                  'inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white focus:outline-none focus:ring-2 focus:ring-offset-2',
                  colorClasses.button,
                  colorClasses.focus,
                ]"
                :disabled="isUploading"
                @click="triggerFileInput"
              >
                Browse Files
              </button>
            </div>
            <p class="mt-2 text-xs text-gray-500">PNG, JPG, GIF up to 10MB each</p>
          </div>

          <!-- Hidden File Input -->
          <input
            ref="fileInput"
            type="file"
            multiple
            accept="image/*"
            class="hidden"
            @change="handleFileInput"
          />
        </div>
      </div>

      <!-- Upload Progress -->
      <div v-if="activeUploads.length > 0" class="mt-8 bg-white rounded-lg shadow">
        <div class="p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Upload Progress</h2>

          <div class="space-y-4">
            <div v-for="upload in activeUploads" :key="upload.id" class="border rounded-lg p-4">
              <div class="flex items-center justify-between mb-2">
                <div class="flex items-center">
                  <PhotoIcon class="h-5 w-5 text-gray-400 mr-2" />
                  <span class="text-sm font-medium text-gray-900 truncate">
                    {{ upload.file.name }}
                  </span>
                </div>
                <div class="flex items-center space-x-2">
                  <span :class="getStatusBadgeClass(upload.status)">
                    {{ getStatusText(upload.status) }}
                  </span>
                  <button
                    v-if="upload.status === 'error'"
                    class="text-sm text-indigo-600 hover:text-indigo-500"
                    @click="retryUpload(upload)"
                  >
                    Retry
                  </button>
                </div>
              </div>

              <!-- Progress Bar -->
              <div v-if="upload.status === 'uploading'" class="w-full bg-gray-200 rounded-full h-2">
                <div
                  :class="[
                    'h-2 rounded-full transition-all duration-300',
                    colorClasses.activeBackground,
                  ]"
                  :style="{ width: `${upload.progress}%` }"
                ></div>
              </div>

              <!-- Error Message -->
              <div v-if="upload.error" class="mt-2 text-sm text-red-600">
                {{ upload.error }}
              </div>

              <!-- File Info -->
              <div class="mt-2 text-xs text-gray-500">
                {{ formatFileSize(upload.file.size) }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Processing Status -->
      <div v-if="processingUploads.length > 0" class="mt-8 bg-white rounded-lg shadow">
        <div class="p-6">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-medium text-gray-900">Processing Images</h2>
            <div class="flex items-center">
              <div
                class="animate-spin rounded-full h-4 w-4 border-b-2 border-indigo-600 mr-2"
              ></div>
              <span class="text-sm text-gray-600">
                {{ processingUploads.length }}
                {{ processingUploads.length === 1 ? 'image' : 'images' }} processing
              </span>
            </div>
          </div>
          <p class="text-sm text-gray-500">
            Images are being validated and processed. This may take a few moments.
          </p>
        </div>
      </div>

      <!-- Recent Uploads -->
      <div v-if="recentUploads.length > 0" class="mt-8 bg-white rounded-lg shadow">
        <div class="p-6">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-medium text-gray-900">Recent Uploads</h2>
            <button
              class="text-sm text-gray-600 hover:text-gray-500"
              @click="clearCompletedUploads"
            >
              Clear Completed
            </button>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="upload in recentUploads" :key="upload.id" class="border rounded-lg p-3">
              <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-900 truncate">
                  {{ upload.uploadedResource?.name || upload.file.name }}
                </span>
                <span :class="getStatusBadgeClass(upload.status)">
                  {{ getStatusText(upload.status) }}
                </span>
              </div>
              <div class="text-xs text-gray-500">
                {{ formatFileSize(upload.file.size) }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Summary Stats -->
      <div class="mt-8 bg-white rounded-lg shadow">
        <div class="p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-4">Upload Summary</h2>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="text-center">
              <div class="text-2xl font-bold text-gray-900">{{ totalPendingCount }}</div>
              <div class="text-sm text-gray-500">Pending</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-green-600">{{ completedCount }}</div>
              <div class="text-sm text-gray-500">Completed</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-red-600">{{ errorCount }}</div>
              <div class="text-sm text-gray-500">Errors</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { computed, ref, onMounted, onUnmounted } from 'vue'
  import { CloudArrowUpIcon, PhotoIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline'
  import { useImageUploadStore, type UploadStatus } from '@/stores/imageUpload'
  import { useErrorDisplayStore } from '@/stores/errorDisplay'
  import { useColors, type ColorName } from '@/composables/useColors'

  interface Props {
    color?: ColorName
  }

  const props = withDefaults(defineProps<Props>(), {
    color: 'indigo',
  })

  // Color classes from centralized system
  const colorClasses = useColors(computed(() => props.color))

  // Stores
  const uploadStore = useImageUploadStore()
  const errorStore = useErrorDisplayStore()

  // State
  const isDragOver = ref(false)
  const isUploading = ref(false)
  const dropZone = ref<HTMLElement>()
  const fileInput = ref<HTMLInputElement>()

  // Refs to cleanup function
  let processingMonitorCleanup: (() => void) | null = null

  // Computed
  const activeUploads = computed(() => uploadStore.activeUploads)
  const processingUploads = computed(() => uploadStore.processingUploads)
  const totalPendingCount = computed(() => uploadStore.totalPendingCount)

  const recentUploads = computed(
    () =>
      activeUploads.value
        .filter(upload => upload.status === 'completed' || upload.status === 'error')
        .slice(0, 9) // Show last 9 completed/errored uploads
  )

  const completedCount = computed(
    () => activeUploads.value.filter(upload => upload.status === 'completed').length
  )

  const errorCount = computed(
    () => activeUploads.value.filter(upload => upload.status === 'error').length
  )

  // Methods
  const handleDragOver = (e: Event) => {
    e.preventDefault()
    e.stopPropagation()
  }

  const handleDragEnter = (e: Event) => {
    e.preventDefault()
    e.stopPropagation()
    isDragOver.value = true
  }

  const handleDragLeave = (e: Event) => {
    e.preventDefault()
    e.stopPropagation()
    // Only set isDragOver to false if we're leaving the drop zone entirely
    const dragEvent = e as any
    if (!dropZone.value?.contains(dragEvent.relatedTarget as Node)) {
      isDragOver.value = false
    }
  }

  const handleDrop = (e: Event) => {
    e.preventDefault()
    e.stopPropagation()
    isDragOver.value = false

    const dragEvent = e as any
    const files = Array.from(dragEvent.dataTransfer?.files || []) as File[]
    handleFiles(files)
  }

  const triggerFileInput = () => {
    fileInput.value?.click()
  }

  const handleFileInput = (e: Event) => {
    const input = e.target as HTMLInputElement
    const files = Array.from(input.files || [])
    handleFiles(files)
    // Clear the input so the same file can be selected again
    input.value = ''
  }

  const handleFiles = async (files: File[]) => {
    // Filter for image files only
    const imageFiles = files.filter(file => file.type.startsWith('image/'))

    if (imageFiles.length !== files.length) {
      errorStore.addMessage('warning', 'Only image files are supported.')
    }

    if (imageFiles.length === 0) {
      return
    }

    // Check file sizes (10MB limit)
    const validFiles = imageFiles.filter(file => {
      if (file.size > 10 * 1024 * 1024) {
        errorStore.addMessage('error', `File "${file.name}" is too large. Maximum size is 10MB.`)
        return false
      }
      return true
    })

    if (validFiles.length === 0) {
      return
    }

    try {
      isUploading.value = true
      await uploadStore.uploadFiles(validFiles)
      errorStore.addMessage(
        'info',
        `${validFiles.length} ${validFiles.length === 1 ? 'file' : 'files'} uploaded successfully.`
      )
    } catch {
      errorStore.addMessage('error', 'Failed to upload some files. Please try again.')
    } finally {
      isUploading.value = false
    }
  }

  const retryUpload = async (upload: UploadStatus) => {
    try {
      await uploadStore.uploadFile(upload.file)
    } catch {
      errorStore.addMessage('error', 'Failed to retry upload.')
    }
  }

  const clearCompletedUploads = () => {
    uploadStore.clearCompletedUploads()
  }

  const getStatusText = (status: string): string => {
    switch (status) {
      case 'pending':
        return 'Pending'
      case 'uploading':
        return 'Uploading'
      case 'processing':
        return 'Processing'
      case 'completed':
        return 'Completed'
      case 'error':
        return 'Error'
      default:
        return 'Unknown'
    }
  }

  const getStatusBadgeClass = (status: string): string => {
    const baseClasses = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium'
    switch (status) {
      case 'pending':
        return `${baseClasses} bg-gray-100 text-gray-800`
      case 'uploading':
        return `${baseClasses} bg-blue-100 text-blue-800`
      case 'processing':
        return `${baseClasses} bg-yellow-100 text-yellow-800`
      case 'completed':
        return `${baseClasses} bg-green-100 text-green-800`
      case 'error':
        return `${baseClasses} bg-red-100 text-red-800`
      default:
        return `${baseClasses} bg-gray-100 text-gray-800`
    }
  }

  const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes'
    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB', 'GB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
  }

  // Lifecycle
  onMounted(() => {
    // Start monitoring processing uploads
    processingMonitorCleanup = uploadStore.startProcessingMonitor()
  })

  onUnmounted(() => {
    // Cleanup processing monitor
    if (processingMonitorCleanup) {
      processingMonitorCleanup()
    }
  })
</script>
