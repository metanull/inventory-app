<template>
  <DetailView
    :store-loading="detailStore.loading"
    :resource="mode === 'create' ? null : detail"
    :mode="mode"
    :save-disabled="!hasUnsavedChanges"
    :has-unsaved-changes="hasUnsavedChanges"
    :back-link="backLink"
    :create-title="'New Detail'"
    :create-subtitle="'(Creating)'"
    information-title="Detail Information"
    :information-description="informationDescription"
    :fetch-data="fetchDetail"
    @edit="enterEditMode"
    @save="saveDetail"
    @cancel="cancelAction"
    @delete="deleteDetail"
  >
    <template #resource-icon>
      <CubeIcon :class="['h-6 w-6', colorClasses.icon]" />
    </template>

    <template #information>
      <!-- Parent Item Information (shown when viewing/editing existing detail) -->
      <div v-if="parentItem && mode !== 'create'" class="mb-6">
        <ParentItemInfo 
          :item-id="parentItem.id" 
          :item-internal-name="parentItem.internal_name" 
        />
      </div>

      <DescriptionList>
        <DescriptionRow variant="gray">
          <DescriptionTerm>Internal Name</DescriptionTerm>
          <DescriptionDetail>
            <FormInput
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.internal_name"
              type="text"
              placeholder="Enter detail internal name"
              required
            />
            <DisplayText v-else>{{ detail?.internal_name }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>
        <DescriptionRow
          v-if="detail?.backward_compatibility || mode === 'edit' || mode === 'create'"
          variant="white"
        >
          <DescriptionTerm>Legacy ID</DescriptionTerm>
          <DescriptionDetail>
            <FormInput
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.backward_compatibility"
              type="text"
              placeholder="Optional legacy identifier"
            />
            <DisplayText v-else>{{ detail?.backward_compatibility || '—' }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>
      </DescriptionList>
    </template>
  </DetailView>
</template>

<script setup lang="ts">
  import { computed, ref, onMounted, watch, nextTick } from 'vue'
  import {
    useRoute,
    useRouter,
    onBeforeRouteLeave,
    type NavigationGuardNext,
    type RouteLocationNormalized,
  } from 'vue-router'
  import DetailView from '@/components/layout/detail/DetailView.vue'
  import ParentItemInfo from '@/components/layout/detail/ParentItemInfo.vue'
  import DescriptionList from '@/components/format/description/DescriptionList.vue'
  import DescriptionRow from '@/components/format/description/DescriptionRow.vue'
  import DescriptionTerm from '@/components/format/description/DescriptionTerm.vue'
  import DescriptionDetail from '@/components/format/description/DescriptionDetail.vue'
  import FormInput from '@/components/format/FormInput.vue'
  import DisplayText from '@/components/format/DisplayText.vue'
  import { CubeIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline'
  import { useDetailStore } from '@/stores/detail'
  import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
  import { useErrorDisplayStore } from '@/stores/errorDisplay'
  import { useCancelChangesConfirmationStore } from '@/stores/cancelChangesConfirmation'
  import { useDeleteConfirmationStore } from '@/stores/deleteConfirmation'
  import type { DetailStoreRequest } from '@metanull/inventory-app-api-client'
  import { useColors, type ColorName } from '@/composables/useColors'

  // Types
  type Mode = 'view' | 'edit' | 'create'

  interface DetailFormData {
    internal_name: string
    backward_compatibility: string
  }

  interface Props {
    color?: ColorName
  }

  const props = withDefaults(defineProps<Props>(), {
    color: 'teal',
  })

  // Color classes from centralized system
  const colorClasses = useColors(computed(() => props.color))

  // Composables
  const route = useRoute()
  const router = useRouter()
  const detailStore = useDetailStore()
  const loadingStore = useLoadingOverlayStore()
  const errorStore = useErrorDisplayStore()
  const deleteStore = useDeleteConfirmationStore()
  const cancelChangesStore = useCancelChangesConfirmationStore()

  // Reactive state - Single source of truth for mode
  const mode = ref<Mode>('view')

  // Computed properties
  const detail = computed(() => detailStore.currentDetail)
  const parentItem = computed(() => detail.value?.item)

  // Get the item ID from route params (for create mode or when detail doesn't have item loaded)
  const itemId = computed(() => {
    const routeItemId = route.params.itemId as string
    return routeItemId
  })

  // Information description based on mode
  const informationDescription = computed(() => {
    switch (mode.value) {
      case 'create':
        return 'Create a new detail for this item.'
      case 'edit':
        return 'Edit detailed information about this detail.'
      default:
        return 'Detailed information about this detail.'
    }
  })

  // Back link configuration
  const backLink = computed(() => ({
    title: 'Back to Item',
    route: `/items/${itemId.value}`,
    icon: ArrowLeftIcon,
    color: props.color,
  }))

  // Edit form data
  const editForm = ref<DetailFormData>({
    internal_name: '',
    backward_compatibility: '',
  })

  // Get default form values
  const getDefaultFormValues = (): DetailFormData => ({
    internal_name: '',
    backward_compatibility: '',
  })

  // Get form values from detail
  const getFormValuesFromDetail = (): DetailFormData => {
    if (!detail.value) return getDefaultFormValues()

    return {
      internal_name: detail.value.internal_name,
      backward_compatibility: detail.value.backward_compatibility || '',
    }
  }

  // Track unsaved changes
  const hasUnsavedChanges = computed(() => {
    if (mode.value === 'view') return false

    // For create mode, compare with default values
    if (mode.value === 'create') {
      const defaultValues = getDefaultFormValues()
      return (
        editForm.value.internal_name !== defaultValues.internal_name ||
        editForm.value.backward_compatibility !== defaultValues.backward_compatibility
      )
    }

    // For edit mode, compare with original values
    if (!detail.value) return false

    const originalValues = getFormValuesFromDetail()
    return (
      editForm.value.internal_name !== originalValues.internal_name ||
      editForm.value.backward_compatibility !== originalValues.backward_compatibility
    )
  })

  // Watch for unsaved changes and sync with cancel changes store
  watch(hasUnsavedChanges, (hasChanges: boolean) => {
    if (hasChanges) {
      cancelChangesStore.addChange()
    } else {
      cancelChangesStore.resetChanges()
    }
  })

  // Mode management functions
  const enterCreateMode = () => {
    mode.value = 'create'
    editForm.value = getDefaultFormValues()
  }

  const enterEditMode = () => {
    if (!detail.value) return
    mode.value = 'edit'
    editForm.value = getFormValuesFromDetail()
  }

  const enterViewMode = () => {
    mode.value = 'view'
    // Clear form data when returning to view mode
    editForm.value = getDefaultFormValues()
  }

  // Action handlers
  const saveDetail = async () => {
    try {
      loadingStore.show(mode.value === 'create' ? 'Creating...' : 'Saving...')

      const detailData: DetailStoreRequest = {
        item_id: itemId.value,
        internal_name: editForm.value.internal_name.trim(),
        backward_compatibility: editForm.value.backward_compatibility.trim() || null,
      }

      if (mode.value === 'create') {
        const savedDetail = await detailStore.createDetail(detailData, { include: ['item'] })
        errorStore.addMessage('info', 'Detail created successfully.')

        // Switch to view mode and reset form to prevent navigation guard triggering
        mode.value = 'view'
        editForm.value = getDefaultFormValues()
        cancelChangesStore.resetChanges()

        // Wait for reactive updates to process before navigation
        await nextTick()

        // Navigate to the new detail in view mode
        await router.push(`/items/${itemId.value}/details/${savedDetail.id}`)
      } else if (mode.value === 'edit' && detail.value) {
        // Update existing detail
        await detailStore.updateDetail(detail.value.id, detailData, { include: ['item'] })
        errorStore.addMessage('info', 'Detail updated successfully.')

        enterViewMode()

        // Remove edit query parameter if present
        if (route.query.edit) {
          const query = { ...route.query }
          delete query.edit
          await router.replace({ query })
        }
      }
    } catch {
      errorStore.addMessage(
        'error',
        `Failed to ${mode.value === 'create' ? 'create' : 'update'} detail. Please try again.`
      )
    } finally {
      loadingStore.hide()
    }
  }

  const cancelAction = async () => {
    if (mode.value === 'create') {
      // Reset form state to prevent navigation guard triggering
      editForm.value = getDefaultFormValues()
      cancelChangesStore.resetChanges()
      
      // Wait for reactive updates to process before navigation
      await nextTick()
      
      // Navigate back to item detail page
      router.push(`/items/${itemId.value}`)
      return
    }

    if (mode.value === 'edit') {
      // "Navigate" back to detail view mode
      enterViewMode()

      // Remove edit query parameter if present
      if (route.query.edit) {
        const query = { ...route.query }
        delete query.edit
        router.replace({ query })
      }
    }
  }

  const deleteDetail = async () => {
    if (!detail.value) return

    const result = await deleteStore.trigger(
      'Delete Detail',
      `Are you sure you want to delete "${detail.value.internal_name}"? This action cannot be undone.`
    )

    if (result === 'delete') {
      try {
        loadingStore.show('Deleting...')
        await detailStore.deleteDetail(detail.value.id)
        errorStore.addMessage('info', 'Detail deleted successfully.')
        router.push(`/items/${itemId.value}`)
      } catch {
        errorStore.addMessage('error', 'Failed to delete detail. Please try again.')
      } finally {
        loadingStore.hide()
      }
    }
  }

  // Fetch detail function
  const fetchDetail = async () => {
    const detailId = route.params.id as string
    if (!detailId || mode.value === 'create') return

    try {
      loadingStore.show()
      await detailStore.fetchDetail(detailId, { include: ['item'] })
    } catch {
      errorStore.addMessage('error', 'Failed to load detail. Please try again.')
      router.push(`/items/${itemId.value}`)
    } finally {
      loadingStore.hide()
    }
  }

  // Initialize component
  const initializeComponent = async () => {
    const detailId = route.params.id as string
    const isCreateRoute = route.name === 'detail-new' || route.path.includes('/details/new')

    try {
      if (isCreateRoute) {
        // Clear current detail to avoid showing stale data from previously viewed details
        detailStore.clearCurrentDetail()

        enterCreateMode()
      } else if (detailId) {
        // For view/edit mode, fetch detail data
        await fetchDetail()

        // Check if we should start in edit mode from query parameter
        if (route.query.edit === 'true' && detail.value) {
          enterEditMode()
        } else {
          enterViewMode()
        }
      }
    } catch {
      // Silent fail - component will work with default behavior
    }
  }

  // Navigation guard to prevent accidental navigation away from unsaved changes
  onBeforeRouteLeave(
    async (
      _to: RouteLocationNormalized,
      _from: RouteLocationNormalized,
      next: NavigationGuardNext
    ) => {
      // Only check for unsaved changes if we're in edit or create mode
      if ((mode.value === 'edit' || mode.value === 'create') && hasUnsavedChanges.value) {
        const result = await cancelChangesStore.trigger(
          mode.value === 'create'
            ? 'New Detail has unsaved changes'
            : 'Detail has unsaved changes',
          mode.value === 'create'
            ? 'There are unsaved changes to this new detail. If you navigate away, the changes will be lost. Are you sure you want to navigate away? This action cannot be undone.'
            : `There are unsaved changes to "${detail.value?.internal_name}". If you navigate away, the changes will be lost. Are you sure you want to navigate away? This action cannot be undone.`
        )

        if (result === 'stay') {
          next(false) // Cancel navigation
        } else {
          cancelChangesStore.resetChanges() // Reset changes before leaving
          next() // Allow navigation
        }
      } else {
        next() // No unsaved changes, allow navigation
      }
    }
  )

  // Lifecycle
  onMounted(initializeComponent)

  // Watch for route changes to handle navigation between different details or create/edit modes
  watch(() => route.params.id, initializeComponent)
  watch(() => route.query.edit, initializeComponent)

  // Expose properties for testing
  defineExpose({
    mode,
    detail,
    parentItem,
    editForm,
    hasUnsavedChanges,
    informationDescription,
    enterEditMode,
    enterViewMode,
    saveDetail,
    cancelAction,
    deleteDetail,
  })
</script>