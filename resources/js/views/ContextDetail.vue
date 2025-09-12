<template>
  <DetailView
    :store-loading="contextStore.loading"
    :resource="mode === 'create' ? null : context"
    :mode="mode"
    :save-disabled="!hasUnsavedChanges"
    :has-unsaved-changes="hasUnsavedChanges"
    :back-link="backLink"
    :status-controls="statusControlsConfig"
    :create-title="'New Context'"
    :create-subtitle="'(Creating)'"
    information-title="Context Information"
    :information-description="informationDescription"
    :fetch-data="fetchContext"
    @edit="enterEditMode"
    @save="saveContext"
    @cancel="cancelAction"
    @delete="deleteContext"
    @status-toggle="handleStatusToggle"
  >
    <template #resource-icon>
      <CogIcon :class="['h-6 w-6', colorClasses.icon]" />
    </template>
    <template #information>
      <DescriptionList>
        <DescriptionRow variant="gray">
          <DescriptionTerm>Internal Name</DescriptionTerm>
          <DescriptionDetail>
            <FormInput
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.internal_name"
              type="text"
            />
            <DisplayText v-else>{{ context?.internal_name }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>
        <DescriptionRow
          v-if="context?.backward_compatibility || mode === 'edit' || mode === 'create'"
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
            <DisplayText v-else>{{ context?.backward_compatibility }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>
        <DescriptionRow v-if="context?.created_at" variant="gray">
          <DescriptionTerm>Created</DescriptionTerm>
          <DescriptionDetail>
            <DateDisplay :date="context.created_at" format="medium" variant="small-dark" />
          </DescriptionDetail>
        </DescriptionRow>
        <DescriptionRow v-if="context?.updated_at" variant="white">
          <DescriptionTerm>Last Updated</DescriptionTerm>
          <DescriptionDetail>
            <DateDisplay :date="context.updated_at" format="medium" variant="small-dark" />
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
  import DateDisplay from '@/components/format/Date.vue'
  import { CogIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline'
  import { CheckCircleIcon, XCircleIcon } from '@heroicons/vue/24/solid'
  import { useContextStore } from '@/stores/context'
  import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
  import { useErrorDisplayStore } from '@/stores/errorDisplay'
  import { useCancelChangesConfirmationStore } from '@/stores/cancelChangesConfirmation'
  import { useDeleteConfirmationStore } from '@/stores/deleteConfirmation'
  import { useColors, type ColorName } from '@/composables/useColors'

  // Props
  interface Props {
    color?: ColorName
  }

  const props = withDefaults(defineProps<Props>(), {
    color: 'green',
  })

  // Types
  type Mode = 'view' | 'edit' | 'create'

  interface ContextFormData {
    id?: string
    internal_name: string
    backward_compatibility: string
  }

  // Composables
  const route = useRoute()
  const router = useRouter()
  const contextStore = useContextStore()
  const loadingStore = useLoadingOverlayStore()
  const errorStore = useErrorDisplayStore()
  const deleteStore = useDeleteConfirmationStore()
  const cancelChangesStore = useCancelChangesConfirmationStore()

  // Reactive state - Single source of truth for mode
  const mode = ref<Mode>('view')

  // Computed properties
  const context = computed(() => contextStore.currentContext)

  // Color classes from centralized system
  const colorClasses = useColors(computed(() => props.color))

  const editForm = ref<ContextFormData>({
    id: '',
    internal_name: '',
    backward_compatibility: '',
  })

  // Information description based on mode
  const informationDescription = computed(() => {
    switch (mode.value) {
      case 'create':
        return 'Create a new context in your inventory system.'
      case 'edit':
        return 'Edit detailed information about this context.'
      default:
        return 'Detailed information about this context.'
    }
  })

  // Back link configuration
  const backLink = computed(() => ({
    title: 'Back to Contexts',
    route: '/contexts',
    icon: ArrowLeftIcon,
    color: props.color,
  }))

  // Unsaved changes detection
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
    if (!context.value) return false

    const originalValues = getFormValuesFromContext()
    return (
      editForm.value.internal_name !== originalValues.internal_name ||
      editForm.value.backward_compatibility !== originalValues.backward_compatibility
    )
  })

  // Status controls configuration
  const statusControlsConfig = computed(() => {
    if (!context.value) return []

    return [
      {
        title: 'Default Context',
        description: 'This context is set as the default for the entire database',
        mainColor: props.color,
        statusText: context.value.is_default ? 'Default' : 'Not Default',
        toggleTitle: 'Default Context',
        isActive: context.value.is_default,
        loading: false,
        disabled: false,
        activeIconBackgroundClass: colorClasses.value.activeBackground,
        inactiveIconBackgroundClass: colorClasses.value.inactiveBackground,
        activeIconClass: colorClasses.value.activeBadge,
        inactiveIconClass: colorClasses.value.inactiveIcon,
        activeIconComponent: CheckCircleIcon,
        inactiveIconComponent: XCircleIcon,
      },
    ]
  })

  // Watch for unsaved changes and sync with cancel changes store
  watch(hasUnsavedChanges, (hasChanges: boolean) => {
    if (hasChanges) {
      cancelChangesStore.addChange()
    } else {
      cancelChangesStore.resetChanges()
    }
  })

  // Initialize edit form from context data
  const getDefaultFormValues = (): ContextFormData => ({
    id: '',
    internal_name: '',
    backward_compatibility: '',
  })

  const getFormValuesFromContext = (): ContextFormData => {
    if (!context.value) return getDefaultFormValues()

    return {
      id: context.value.id,
      internal_name: context.value.internal_name,
      backward_compatibility: context.value.backward_compatibility || '',
    }
  }

  // Fetch context function
  const fetchContext = async () => {
    const contextId = route.params.id as string
    if (!contextId || mode.value === 'create') return

    try {
      loadingStore.show()
      await contextStore.fetchContext(contextId)
    } catch {
      errorStore.addMessage(
        'error',
        'Failed to load context. The context may not exist or you may not have permission to view it.'
      )
      router.push({ name: 'contexts' })
    } finally {
      loadingStore.hide()
    }
  }

  // Mode management functions
  const enterCreateMode = () => {
    mode.value = 'create'
    editForm.value = getDefaultFormValues()
  }

  const enterEditMode = () => {
    if (!context.value) return
    mode.value = 'edit'
    editForm.value = getFormValuesFromContext()
  }

  const enterViewMode = () => {
    mode.value = 'view'
    // Clear form data when returning to view mode
    editForm.value = getDefaultFormValues()
  }

  // Action handlers
  const saveContext = async () => {
    try {
      loadingStore.show(mode.value === 'create' ? 'Creating...' : 'Saving...')

      const contextData = {
        internal_name: editForm.value.internal_name,
        backward_compatibility: editForm.value.backward_compatibility || null,
      }

      if (mode.value === 'create') {
        const savedContext = await contextStore.createContext(contextData)
        errorStore.addMessage('info', 'Context created successfully.')

        // Load the new context and enter view mode
        await contextStore.fetchContext(savedContext.id)
        enterViewMode()
      } else if (mode.value === 'edit' && context.value) {
        // Update existing context
        await contextStore.updateContext(context.value.id, contextData)
        errorStore.addMessage('info', 'Context updated successfully.')

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
        `Failed to ${mode.value === 'create' ? 'create' : 'update'} context. Please try again.`
      )
    } finally {
      loadingStore.hide()
    }
  }

  const cancelAction = async () => {
    if (hasUnsavedChanges.value) {
      const result = await cancelChangesStore.trigger(
        mode.value === 'create' ? 'New Context has unsaved changes' : 'Context has unsaved changes',
        mode.value === 'create'
          ? 'There are unsaved changes to this new context. If you navigate away, the changes will be lost. Are you sure you want to navigate away? This action cannot be undone.'
          : `There are unsaved changes to "${context.value?.internal_name}". If you navigate away, the changes will be lost. Are you sure you want to navigate away? This action cannot be undone.`
      )

      if (result === 'stay') {
        return // Cancel navigation
      } else {
        cancelChangesStore.resetChanges() // Reset changes before leaving
      }
    }

    if (mode.value === 'create') {
      router.push({ name: 'contexts' })
    } else {
      enterViewMode()
    }
  }

  const deleteContext = async () => {
    if (!context.value || !context.value.id) return

    const result = await deleteStore.trigger(
      'Delete Context',
      `Are you sure you want to delete "${context.value.internal_name}"? This action cannot be undone.`
    )

    if (result === 'delete') {
      try {
        loadingStore.show('Deleting...')
        await contextStore.deleteContext(context.value.id)
        errorStore.addMessage('info', 'Context deleted successfully.')
        router.push({ name: 'contexts' })
      } catch {
        errorStore.addMessage(
          'error',
          'Failed to delete context. The context may be in use or you may not have permission to delete it.'
        )
      } finally {
        loadingStore.hide()
      }
    }
  }

  // Status toggle handlers
  const handleStatusToggle = async (index: number) => {
    if (!context.value || index !== 0) return // Only handle the first (and only) status card

    try {
      loadingStore.show('Updating...')

      const newStatus = !context.value.is_default
      await contextStore.setDefaultContext(context.value.id, newStatus)
      errorStore.addMessage(
        'info',
        `Context ${newStatus ? 'set as default' : 'removed as default'} successfully.`
      )
    } catch {
      errorStore.addMessage(
        'error',
        'Failed to update context default status. You may not have permission to make this change.'
      )
    } finally {
      loadingStore.hide()
    }
  }

  // Initialize component
  const initializeComponent = async () => {
    const contextId = route.params.id as string
    const isCreateRoute = route.name === 'context-new' || route.path === '/contexts/new'

    try {
      if (isCreateRoute) {
        // Clear current context to avoid showing stale data from previously viewed contexts
        contextStore.clearCurrentContext()
        enterCreateMode()
      } else if (contextId) {
        // For view/edit mode, fetch context data
        await fetchContext()

        // Check if we should start in edit mode from query parameter
        if (route.query.edit === 'true' && context.value) {
          enterEditMode()
        } else {
          enterViewMode()
        }
      }
    } catch {
      // Silent fail - component will work with default behavior
    }
  }

  // Component lifecycle
  onMounted(initializeComponent)

  // Navigation guard for unsaved changes
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
            ? 'New Context has unsaved changes'
            : 'Context has unsaved changes',
          mode.value === 'create'
            ? 'There are unsaved changes to this new context. If you navigate away, the changes will be lost. Are you sure you want to navigate away? This action cannot be undone.'
            : `There are unsaved changes to "${context.value?.internal_name}". If you navigate away, the changes will be lost. Are you sure you want to navigate away? This action cannot be undone.`
        )

        if (result === 'stay') {
          next(false) // Cancel navigation
        } else {
          cancelChangesStore.resetChanges() // Reset changes before leaving
          next() // Allow navigation
        }
      } else {
        next() // Allow navigation
      }
    }
  )
</script>
