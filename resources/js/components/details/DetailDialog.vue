<template>
  <ModalOverlay
    :visible="true"
    variant="content"
    content-class="sm:max-w-2xl"
    @background-click="$emit('close')"
  >
    <div class="bg-white px-6 py-6">
      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <div class="flex items-center">
          <CubeIcon :class="['h-6 w-6 mr-3', colorClasses.icon]" />
          <div>
            <h2 class="text-lg font-medium text-gray-900">
              {{ modalTitle }}
            </h2>
            <p class="text-sm text-gray-500">
              {{ modalSubtitle }}
            </p>
          </div>
        </div>
        <button
          class="rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          @click="$emit('close')"
        >
          <XMarkIcon class="h-6 w-6" />
        </button>
      </div>

      <!-- Form Content -->
      <form class="space-y-6" @submit.prevent="handleSubmit">
        <!-- Internal Name -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Internal Name <span class="text-red-500">*</span>
          </label>
          <FormInput
            v-if="mode === 'edit' || mode === 'create'"
            v-model="formData.internal_name"
            type="text"
            placeholder="Enter detail internal name"
            :disabled="loading"
            required
          />
          <DisplayText v-else>{{ detail?.internal_name }}</DisplayText>
        </div>

        <!-- Legacy ID -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2"> Legacy ID </label>
          <FormInput
            v-if="mode === 'edit' || mode === 'create'"
            v-model="formData.backward_compatibility"
            type="text"
            placeholder="Optional legacy identifier"
            :disabled="loading"
          />
          <DisplayText v-else>{{ detail?.backward_compatibility || '—' }}</DisplayText>
        </div>

        <!-- System Information (view mode only) -->
        <div v-if="mode === 'view' && detail">
          <h3 class="text-sm font-medium text-gray-700 mb-3">System Information</h3>
          <div class="bg-gray-50 rounded-lg p-4 space-y-3">
            <div class="flex justify-between">
              <span class="text-sm text-gray-500">ID:</span>
              <span class="text-sm text-gray-900 font-mono">{{ detail.id }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-500">Created:</span>
              <DateDisplay :date="detail.created_at" format="medium" variant="small-dark" />
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-500">Last Updated:</span>
              <DateDisplay :date="detail.updated_at" format="medium" variant="small-dark" />
            </div>
          </div>
        </div>

        <!-- Error Message -->
        <div v-if="errorMessage" class="rounded-md bg-red-50 p-4">
          <div class="flex">
            <ExclamationTriangleIcon class="h-5 w-5 text-red-400" />
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800">Error</h3>
              <p class="mt-1 text-sm text-red-700">{{ errorMessage }}</p>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
          <button
            type="button"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            :disabled="loading"
            @click="$emit('close')"
          >
            {{ mode === 'view' ? 'Close' : 'Cancel' }}
          </button>

          <button
            v-if="mode === 'view'"
            type="button"
            :class="[
              'px-4 py-2 text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2',
              colorClasses.button,
              colorClasses.focus,
            ]"
            :disabled="loading"
            @click="switchToEditMode"
          >
            Edit Detail
          </button>

          <button
            v-if="mode === 'edit' || mode === 'create'"
            type="submit"
            :class="[
              'px-4 py-2 text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2',
              colorClasses.button,
              colorClasses.focus,
            ]"
            :disabled="loading || !isFormValid"
          >
            <span v-if="loading">Saving...</span>
            <span v-else>{{ mode === 'create' ? 'Create Detail' : 'Save Changes' }}</span>
          </button>
        </div>
      </form>
    </div>
  </ModalOverlay>
</template>

<script setup lang="ts">
  import { computed, ref, watch, onMounted } from 'vue'
  import { CubeIcon, XMarkIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline'
  import { useDetailStore } from '@/stores/detail'
  import { useColors, type ColorName } from '@/composables/useColors'
  import type { DetailResource, DetailStoreRequest } from '@metanull/inventory-app-api-client'
  import ModalOverlay from '@/components/global/ModalOverlay.vue'
  import FormInput from '@/components/format/FormInput.vue'
  import DisplayText from '@/components/format/DisplayText.vue'
  import DateDisplay from '@/components/format/Date.vue'

  interface Props {
    detail?: DetailResource | null
    itemId: string
    mode: 'view' | 'edit' | 'create'
    color?: ColorName
  }

  const props = withDefaults(defineProps<Props>(), {
    detail: null,
    color: 'teal',
  })

  // Emits
  const emit = defineEmits<{
    close: []
    saved: []
  }>()

  // Color classes from centralized system
  const colorClasses = useColors(computed(() => props.color))

  // Stores
  const detailStore = useDetailStore()

  // State
  const mode = ref(props.mode)
  const loading = ref(false)
  const errorMessage = ref('')

  // Form data
  interface DetailFormData {
    internal_name: string
    backward_compatibility: string
  }

  const formData = ref<DetailFormData>({
    internal_name: '',
    backward_compatibility: '',
  })

  // Computed
  const modalTitle = computed(() => {
    switch (mode.value) {
      case 'create':
        return 'New Detail'
      case 'edit':
        return 'Edit Detail'
      case 'view':
        return 'Detail Information'
      default:
        return 'Detail'
    }
  })

  const modalSubtitle = computed(() => {
    switch (mode.value) {
      case 'create':
        return 'Add a new detail to this item'
      case 'edit':
        return 'Modify detail information'
      case 'view':
        return 'View detail information'
      default:
        return ''
    }
  })

  const isFormValid = computed(() => {
    return formData.value.internal_name.trim().length > 0
  })

  // Methods
  const initializeForm = () => {
    if (props.detail) {
      formData.value = {
        internal_name: props.detail.internal_name,
        backward_compatibility: props.detail.backward_compatibility || '',
      }
    } else {
      formData.value = {
        internal_name: '',
        backward_compatibility: '',
      }
    }
  }

  const switchToEditMode = () => {
    mode.value = 'edit'
    initializeForm()
  }

  const handleSubmit = async () => {
    if (!isFormValid.value) return

    try {
      loading.value = true
      errorMessage.value = ''

      const requestData: DetailStoreRequest = {
        item_id: props.itemId,
        internal_name: formData.value.internal_name.trim(),
        backward_compatibility: formData.value.backward_compatibility.trim() || null,
      }

      if (mode.value === 'create') {
        await detailStore.createDetail(requestData)
      } else if (mode.value === 'edit' && props.detail) {
        await detailStore.updateDetail(props.detail.id, requestData)
      }

      emit('saved')
    } catch (error) {
      errorMessage.value =
        error instanceof Error ? error.message : 'An error occurred while saving the detail.'
    } finally {
      loading.value = false
    }
  }

  // Watchers
  watch(() => props.detail, initializeForm, { immediate: true })
  watch(
    () => props.mode,
    newMode => {
      mode.value = newMode
      initializeForm()
    }
  )

  // Initialize
  onMounted(() => {
    initializeForm()
  })
</script>
