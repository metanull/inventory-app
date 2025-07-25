<template>
  <!-- Unified Language Detail View -->
  <DetailView
    :store-loading="languageStore.loading"
    :resource="mode === 'create' ? null : language"
    :mode="mode"
    :save-disabled="!hasUnsavedChanges"
    :has-unsaved-changes="hasUnsavedChanges"
    :back-link="backLink"
    :status-cards="statusCardsConfig"
    :create-title="'New Language'"
    :create-subtitle="'(Creating)'"
    information-title="Language Information"
    :information-description="informationDescription"
    :fetch-data="fetchLanguage"
    @edit="enterEditMode"
    @save="saveLanguage"
    @cancel="cancelAction"
    @delete="deleteLanguage"
    @status-toggle="handleStatusToggle"
  >
    <template #resource-icon>
      <LanguageIcon class="h-6 w-6 text-purple-600" />
    </template>
    <template #information>
      <DescriptionList>
        <DescriptionRow variant="gray">
          <DescriptionTerm>Language ID</DescriptionTerm>
          <DescriptionDetail>
            <FormInput
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.id"
              type="text"
              placeholder="ISO language code (e.g., eng)"
              :disabled="mode === 'edit'"
            />
            <DisplayText v-else>{{ language?.id }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>
        <DescriptionRow variant="white">
          <DescriptionTerm>Internal Name</DescriptionTerm>
          <DescriptionDetail>
            <FormInput
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.internal_name"
              type="text"
            />
            <DisplayText v-else>{{ language?.internal_name }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>
        <DescriptionRow
          v-if="language?.backward_compatibility || mode === 'edit' || mode === 'create'"
          variant="gray"
        >
          <DescriptionTerm>Legacy ID</DescriptionTerm>
          <DescriptionDetail>
            <FormInput
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.backward_compatibility"
              type="text"
              placeholder="Optional legacy identifier"
            />
            <DisplayText v-else>{{ language?.backward_compatibility }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>
        <DescriptionRow v-if="language?.created_at" variant="white">
          <DescriptionTerm>Created</DescriptionTerm>
          <DescriptionDetail>
            <DateDisplay :date="language.created_at" format="medium" variant="small-dark" />
          </DescriptionDetail>
        </DescriptionRow>
        <DescriptionRow v-if="language?.updated_at" variant="gray">
          <DescriptionTerm>Last Updated</DescriptionTerm>
          <DescriptionDetail>
            <DateDisplay :date="language.updated_at" format="medium" variant="small-dark" />
          </DescriptionDetail>
        </DescriptionRow>
      </DescriptionList>
    </template>
  </DetailView>
</template>

<script setup lang="ts">
  import { ref, computed, onMounted, watch } from 'vue'
  import {
    useRoute,
    useRouter,
    onBeforeRouteLeave,
    type RouteLocationNormalized,
    type NavigationGuardNext,
  } from 'vue-router'
  import { useLanguageStore } from '@/stores/language'
  import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
  import { useErrorDisplayStore } from '@/stores/errorDisplay'
  import { useDeleteConfirmationStore } from '@/stores/deleteConfirmation'
  import { useCancelChangesConfirmationStore } from '@/stores/cancelChangesConfirmation'
  import DateDisplay from '@/components/format/Date.vue'
  import DisplayText from '@/components/format/DisplayText.vue'
  import DetailView from '@/components/layout/detail/DetailView.vue'
  import FormInput from '@/components/format/FormInput.vue'
  import DescriptionList from '@/components/format/description/DescriptionList.vue'
  import DescriptionRow from '@/components/format/description/DescriptionRow.vue'
  import DescriptionTerm from '@/components/format/description/DescriptionTerm.vue'
  import DescriptionDetail from '@/components/format/description/DescriptionDetail.vue'
  import {
    CheckCircleIcon,
    XCircleIcon,
    LanguageIcon,
    ArrowLeftIcon,
  } from '@heroicons/vue/24/solid'

  // Types
  type Mode = 'view' | 'edit' | 'create'

  interface LanguageFormData {
    id: string
    internal_name: string
    backward_compatibility: string
  }

  // Composables
  const route = useRoute()
  const router = useRouter()
  const languageStore = useLanguageStore()
  const loadingStore = useLoadingOverlayStore()
  const errorStore = useErrorDisplayStore()
  const deleteStore = useDeleteConfirmationStore()
  const cancelChangesStore = useCancelChangesConfirmationStore()

  // Reactive state - Single source of truth for mode
  const mode = ref<Mode>('view')

  // Computed properties
  const language = computed(() => languageStore.currentLanguage)

  // Information description based on mode
  const informationDescription = computed(() => {
    switch (mode.value) {
      case 'create':
        return 'Create a new language in your inventory system.'
      case 'edit':
        return 'Edit detailed information about this language.'
      default:
        return 'Detailed information about this language.'
    }
  })

  // Back link configuration
  const backLink = computed(() => ({
    title: 'Back to Languages',
    route: '/languages',
    icon: ArrowLeftIcon,
    color: 'purple',
  }))

  // Edit form data
  const editForm = ref<LanguageFormData>({
    id: '',
    internal_name: '',
    backward_compatibility: '',
  })

  // Get default form values
  const getDefaultFormValues = (): LanguageFormData => ({
    id: '',
    internal_name: '',
    backward_compatibility: '',
  })

  // Get form values from language
  const getFormValuesFromLanguage = (): LanguageFormData => {
    if (!language.value) return getDefaultFormValues()

    return {
      id: language.value.id,
      internal_name: language.value.internal_name,
      backward_compatibility: language.value.backward_compatibility || '',
    }
  }

  // Track unsaved changes
  const hasUnsavedChanges = computed(() => {
    if (mode.value === 'view') return false

    // For create mode, compare with default values
    if (mode.value === 'create') {
      const defaultValues = getDefaultFormValues()
      return (
        editForm.value.id !== defaultValues.id ||
        editForm.value.internal_name !== defaultValues.internal_name ||
        editForm.value.backward_compatibility !== defaultValues.backward_compatibility
      )
    }

    // For edit mode, compare with original values
    if (!language.value) return false

    const originalValues = getFormValuesFromLanguage()
    return (
      editForm.value.id !== originalValues.id ||
      editForm.value.internal_name !== originalValues.internal_name ||
      editForm.value.backward_compatibility !== originalValues.backward_compatibility
    )
  })

  // Status cards configuration
  const statusCardsConfig = computed(() => {
    if (!language.value) return []

    return [
      {
        title: 'Default Status',
        description: 'Language default status',
        mainColor: 'green',
        statusText: language.value.is_default ? 'Default' : 'Not Default',
        toggleTitle: 'Default Status',
        isActive: language.value.is_default,
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
    if (!language.value) return
    mode.value = 'edit'
    editForm.value = getFormValuesFromLanguage()
  }

  const enterViewMode = () => {
    mode.value = 'view'
    // Clear form data when returning to view mode
    editForm.value = getDefaultFormValues()
  }

  // Action handlers
  const cancelAction = async () => {
    if (mode.value === 'create') {
      // Navigate back to languages list
      router.push('/languages')
      return
    }

    if (mode.value === 'edit') {
      // "Navigate" back to language detail in view mode
      enterViewMode()

      // Remove edit query parameter if present
      if (route.query.edit) {
        const query = { ...route.query }
        delete query.edit
        router.replace({ query })
      }
    }
  }

  const saveLanguage = async () => {
    try {
      loadingStore.show('Saving...')

      if (mode.value === 'create') {
        // Create new language - needs ID
        const languageData = {
          id: editForm.value.id,
          internal_name: editForm.value.internal_name,
          backward_compatibility: editForm.value.backward_compatibility || null,
        }
        const newLanguage = await languageStore.createLanguage(languageData)
        errorStore.addMessage('info', 'Language created successfully.')

        // Load the new language and enter view mode
        await languageStore.fetchLanguage(newLanguage.id)
        enterViewMode()
      } else if (mode.value === 'edit' && language.value) {
        // Update existing language - doesn't need ID in payload
        const languageData = {
          internal_name: editForm.value.internal_name,
          backward_compatibility: editForm.value.backward_compatibility || null,
        }
        await languageStore.updateLanguage(language.value.id, languageData)
        errorStore.addMessage('info', 'Language updated successfully.')

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
        'Failed to save language. Please check your input and try again.'
      )
    } finally {
      loadingStore.hide()
    }
  }

  const deleteLanguage = async () => {
    if (language.value?.id) {
      const result = await deleteStore.trigger(
        'Delete Language',
        `Are you sure you want to delete "${language.value.internal_name}"? This action cannot be undone.`
      )

      if (result === 'delete') {
        try {
          loadingStore.show('Deleting...')
          await languageStore.deleteLanguage(language.value.id)
          errorStore.addMessage('info', 'Language deleted successfully.')
          router.push('/languages')
        } catch {
          errorStore.addMessage('error', 'Failed to delete language. Please try again.')
        } finally {
          loadingStore.hide()
        }
      }
    }
  }

  // Status toggle handlers
  const toggleDefault = async () => {
    if (!language.value) return

    try {
      loadingStore.show('Updating...')
      const newStatus = !language.value.is_default
      await languageStore.setDefaultLanguage(language.value.id, newStatus)
      errorStore.addMessage(
        'info',
        `Language ${newStatus ? 'set as default' : 'removed from default'} successfully.`
      )
    } catch {
      errorStore.addMessage('error', 'Failed to update language status. Please try again.')
    } finally {
      loadingStore.hide()
    }
  }

  const handleStatusToggle = async (index: number) => {
    if (index === 0) {
      await toggleDefault()
    }
  }

  // Fetch language function
  const fetchLanguage = async () => {
    const languageId = route.params.id as string
    if (!languageId || mode.value === 'create') return

    try {
      loadingStore.show()
      await languageStore.fetchLanguage(languageId)
    } catch {
      errorStore.addMessage('error', 'Failed to load language. Please try again.')
    } finally {
      loadingStore.hide()
    }
  }

  // Initialize component
  const initializeComponent = async () => {
    const languageId = route.params.id as string
    const isCreateRoute = route.name === 'language-new' || route.path === '/languages/new'

    try {
      if (isCreateRoute) {
        // Clear current language to avoid showing stale data from previously viewed languages
        languageStore.clearCurrentLanguage()

        enterCreateMode()
      } else if (languageId) {
        // For view/edit mode, fetch language data
        await fetchLanguage()

        // Check if we should start in edit mode from query parameter
        if (route.query.edit === 'true' && language.value) {
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
            ? 'New Language has unsaved changes'
            : 'Language has unsaved changes',
          mode.value === 'create'
            ? 'There are unsaved changes to this new language. If you navigate away, the changes will be lost. Are you sure you want to navigate away? This action cannot be undone.'
            : `There are unsaved changes to "${language.value?.internal_name}". If you navigate away, the changes will be lost. Are you sure you want to navigate away? This action cannot be undone.`
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

  // Expose properties for testing
  defineExpose({
    mode,
    language,
    editForm,
    hasUnsavedChanges,
    informationDescription,
    enterEditMode,
    enterViewMode,
    saveLanguage,
    cancelAction,
    deleteLanguage,
  })
</script>
