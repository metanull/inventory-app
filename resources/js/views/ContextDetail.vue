<template>
  <DetailView
    :store-loading="contextStore.loading"
    :resource="mode === 'create' ? null : context"
    :mode="mode"
    :save-disabled="!hasUnsavedChanges"
    :has-unsaved-changes="hasUnsavedChanges"
    :back-link="backLink"
    :status-cards="statusCardsConfig"
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
      <CogIcon class="h-6 w-6 text-orange-600" />
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
      </DescriptionList>
    </template>
  </DetailView>
</template>

<script setup lang="ts">
  import { computed, ref, onMounted } from 'vue'
  import {
    useRoute,
    useRouter,
    onBeforeRouteLeave,
    type NavigationGuardNext,
    type RouteLocationNormalized,
  } from 'vue-router'

  // Components
  import DetailView from '@/components/layout/detail/DetailView.vue'
  import DescriptionList from '@/components/format/description/DescriptionList.vue'
  import DescriptionRow from '@/components/format/description/DescriptionRow.vue'
  import DescriptionTerm from '@/components/format/description/DescriptionTerm.vue'
  import DescriptionDetail from '@/components/format/description/DescriptionDetail.vue'
  import FormInput from '@/components/format/FormInput.vue'
  import DisplayText from '@/components/format/DisplayText.vue'

  // Icons
  import { CogIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline'
  import { CheckCircleIcon, XCircleIcon } from '@heroicons/vue/24/solid'

  // Types
  import type { ContextResource } from '@metanull/inventory-app-api-client'

  // Stores
  import { useContextStore } from '@/stores/context'
  import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
  import { useErrorDisplayStore } from '@/stores/errorDisplay'
  import { useCancelChangesConfirmationStore } from '@/stores/cancelChangesConfirmation'
  import { useDeleteConfirmationStore } from '@/stores/deleteConfirmation'

  // Store instances
  const route = useRoute()
  const router = useRouter()
  const contextStore = useContextStore()
  const loadingOverlayStore = useLoadingOverlayStore()
  const errorDisplayStore = useErrorDisplayStore()
  const cancelChangesConfirmationStore = useCancelChangesConfirmationStore()
  const deleteConfirmationStore = useDeleteConfirmationStore()

  // Route parameters
  const contextId = computed(() => route.params.id as string)
  const isCreate = computed(() => contextId.value === 'new')
  const mode = ref<'view' | 'edit' | 'create'>(
    isCreate.value ? 'create' : route.query.mode === 'edit' ? 'edit' : 'view'
  )

  // Current context
  const context = computed(() => contextStore.currentContext)

  // Edit form
  interface ContextEditForm {
    id?: string
    internal_name: string
    backward_compatibility: string
  }

  const editForm = ref<ContextEditForm>({
    id: '',
    internal_name: '',
    backward_compatibility: '',
  })

  // Back link
  const backLink = computed(() => ({
    title: 'Back to Contexts',
    route: '/contexts',
    icon: ArrowLeftIcon,
    color: 'orange',
  }))

  // Information description
  const informationDescription = computed(() => {
    if (mode.value === 'create') {
      return 'Configure the basic properties for this new context.'
    }
    return 'View and edit the basic properties of this context.'
  })

  // Status cards configuration
  const statusCardsConfig = computed(() => {
    if (!context.value) return []

    return [
      {
        title: 'Default Context',
        description: 'This context is set as the default for the entire database',
        mainColor: 'green',
        statusText: context.value.is_default ? 'Default' : 'Not Default',
        toggleTitle: 'Default Context',
        isActive: context.value.is_default,
        loading: false,
        disabled: false,
        activeIconBackgroundClass: 'bg-green-100',
        inactiveIconBackgroundClass: 'bg-gray-100',
        activeIconClass: 'text-green-600',
        inactiveIconClass: 'text-gray-600',
        activeIconComponent: CheckCircleIcon,
        inactiveIconComponent: XCircleIcon,
      },
    ]
  })

  // Unsaved changes detection
  const hasUnsavedChanges = computed(() => {
    if (mode.value === 'create') {
      return editForm.value.internal_name.trim() !== ''
    }

    if (mode.value === 'edit' && context.value) {
      return (
        editForm.value.internal_name !== context.value.internal_name ||
        editForm.value.backward_compatibility !== (context.value.backward_compatibility || '')
      )
    }

    return false
  })

  // Initialize edit form from context data
  const initializeEditForm = () => {
    if (mode.value === 'create') {
      editForm.value = {
        id: '',
        internal_name: '',
        backward_compatibility: '',
      }
    } else if (context.value) {
      editForm.value = {
        id: context.value.id,
        internal_name: context.value.internal_name,
        backward_compatibility: context.value.backward_compatibility || '',
      }
    }
  }

  // Fetch context data
  const fetchContext = async () => {
    if (isCreate.value) return

    try {
      loadingOverlayStore.show()
      await contextStore.fetchContext(contextId.value)
      initializeEditForm()
    } catch {
      errorDisplayStore.addMessage('error', 'Failed to load context. Please try again.')
      router.push({ name: 'contexts' })
    } finally {
      loadingOverlayStore.hide()
    }
  }

  // Mode management
  const enterEditMode = () => {
    mode.value = 'edit'
    initializeEditForm()
    router.replace({ query: { mode: 'edit' } })
  }

  const exitEditMode = () => {
    mode.value = 'view'
    initializeEditForm()
    router.replace({ query: {} })
  }

  // Save context
  const saveContext = async () => {
    try {
      loadingOverlayStore.show(mode.value === 'create' ? 'Creating...' : 'Saving...')

      let savedContext: ContextResource
      if (mode.value === 'create') {
        const createData = {
          internal_name: editForm.value.internal_name,
          backward_compatibility: editForm.value.backward_compatibility || null,
        }
        savedContext = await contextStore.createContext(createData)
        router.push(`/contexts/${savedContext.id}`)
        errorDisplayStore.addMessage('info', 'Context created successfully.')
      } else {
        const updateData = {
          internal_name: editForm.value.internal_name,
          backward_compatibility: editForm.value.backward_compatibility || null,
        }
        savedContext = await contextStore.updateContext(contextId.value, updateData)
        exitEditMode()
        errorDisplayStore.addMessage('info', 'Context updated successfully.')
      }
    } catch {
      errorDisplayStore.addMessage(
        'error',
        `Failed to ${mode.value === 'create' ? 'create' : 'update'} context. Please try again.`
      )
    } finally {
      loadingOverlayStore.hide()
    }
  }

  // Cancel action
  const cancelAction = async () => {
    if (hasUnsavedChanges.value) {
      const result = await cancelChangesConfirmationStore.trigger(
        mode.value === 'create' ? 'New Context has unsaved changes' : 'Context has unsaved changes',
        mode.value === 'create'
          ? 'There are unsaved changes to this new context. If you navigate away, the changes will be lost. Are you sure you want to navigate away? This action cannot be undone.'
          : `There are unsaved changes to "${context.value?.internal_name}". If you navigate away, the changes will be lost. Are you sure you want to navigate away? This action cannot be undone.`
      )

      if (result === 'stay') {
        return // Cancel navigation
      } else {
        cancelChangesConfirmationStore.resetChanges() // Reset changes before leaving
      }
    }

    if (mode.value === 'create') {
      router.push({ name: 'contexts' })
    } else {
      exitEditMode()
    }
  }

  // Delete context
  const deleteContext = async () => {
    if (!context.value || !context.value.id) return

    const result = await deleteConfirmationStore.trigger(
      'Delete Context',
      `Are you sure you want to delete "${context.value.internal_name}"? This action cannot be undone.`
    )

    if (result === 'delete') {
      try {
        loadingOverlayStore.show('Deleting...')
        await contextStore.deleteContext(context.value.id)
        errorDisplayStore.addMessage('info', 'Context deleted successfully.')
        router.push({ name: 'contexts' })
      } catch {
        errorDisplayStore.addMessage('error', 'Failed to delete context. Please try again.')
      } finally {
        loadingOverlayStore.hide()
      }
    }
  }

  // Handle status toggle for context (only Default Status since contexts don't have active/inactive status)
  const handleStatusToggle = async (index: number) => {
    if (!context.value || index !== 0) return // Only handle the first (and only) status card

    try {
      loadingOverlayStore.show('Updating...')

      const newStatus = !context.value.is_default
      const updateData = {
        internal_name: context.value.internal_name,
        backward_compatibility: context.value.backward_compatibility,
        is_default: newStatus,
      }

      await contextStore.updateContext(context.value.id, updateData)
      errorDisplayStore.addMessage(
        'info',
        `Context ${newStatus ? 'set as default' : 'removed as default'} successfully.`
      )
    } catch {
      errorDisplayStore.addMessage('error', 'Failed to update context status. Please try again.')
    } finally {
      loadingOverlayStore.hide()
    }
  }

  // Initialize component
  const initializeComponent = async () => {
    const isCreateRoute = route.name === 'context-new' || route.path === '/contexts/new'

    try {
      if (isCreateRoute) {
        // For create mode, clear current context and enter create mode
        contextStore.clearCurrentContext()
        mode.value = 'create'
        initializeEditForm()
      } else if (contextId.value) {
        // For view/edit mode, fetch context data
        await fetchContext()

        // Check if we should start in edit mode from query parameter
        if (route.query.mode === 'edit' && context.value) {
          enterEditMode()
        } else {
          mode.value = 'view'
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
        const result = await cancelChangesConfirmationStore.trigger(
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
          cancelChangesConfirmationStore.resetChanges() // Reset changes before leaving
          next() // Allow navigation
        }
      } else {
        next() // Allow navigation
      }
    }
  )
</script>
