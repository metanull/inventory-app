<template>
  <DetailView
    :store-loading="collectionStore.loading"
    :resource="mode === 'create' ? null : collection"
    :mode="mode"
    :save-disabled="!hasUnsavedChanges"
    :has-unsaved-changes="hasUnsavedChanges"
    :back-link="backLink"
    :create-title="'New Collection'"
    :create-subtitle="'(Creating)'"
    information-title="Collection Information"
    :information-description="informationDescription"
    :fetch-data="fetchCollectionData"
    @edit="enterEditMode"
    @save="saveCollection"
    @cancel="cancelAction"
    @delete="deleteCollection"
  >
    <template #resource-icon>
      <CollectionIcon class="h-6 w-6 text-indigo-600" />
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
              placeholder="Collection internal name"
            />
            <DisplayText v-else>{{ collection?.internal_name }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>
        <DescriptionRow variant="white">
          <DescriptionTerm>Language</DescriptionTerm>
          <DescriptionDetail>
            <GenericDropdown
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.language_id"
              :options="languageOptions"
              :show-no-default-option="false"
            />
            <DisplayText v-else>{{ collection?.language?.internal_name || '—' }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>
        <DescriptionRow variant="gray">
          <DescriptionTerm>Context</DescriptionTerm>
          <DescriptionDetail>
            <GenericDropdown
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.context_id"
              :options="contextOptions"
              :show-no-default-option="false"
            />
            <DisplayText v-else>{{ collection?.context?.internal_name || '—' }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>
        <DescriptionRow
          v-if="collection?.backward_compatibility || mode === 'edit' || mode === 'create'"
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
            <DisplayText v-else>{{ collection?.backward_compatibility }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>
        <DescriptionRow v-if="collection?.items_count !== undefined" variant="gray">
          <DescriptionTerm>Items</DescriptionTerm>
          <DescriptionDetail>
            <div class="flex items-center">
              <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800"
              >
                {{ collection.items_count }} items
              </span>
            </div>
          </DescriptionDetail>
        </DescriptionRow>
        <DescriptionRow v-if="collection?.partners_count !== undefined" variant="white">
          <DescriptionTerm>Partners</DescriptionTerm>
          <DescriptionDetail>
            <div class="flex items-center">
              <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800"
              >
                {{ collection.partners_count }} partners
              </span>
            </div>
          </DescriptionDetail>
        </DescriptionRow>
        <DescriptionRow v-if="collection?.translations_count !== undefined" variant="gray">
          <DescriptionTerm>Translations</DescriptionTerm>
          <DescriptionDetail>
            <div class="flex items-center">
              <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800"
              >
                {{ collection.translations_count }} translations
              </span>
            </div>
          </DescriptionDetail>
        </DescriptionRow>
        <DescriptionRow v-if="collection?.created_at" variant="white">
          <DescriptionTerm>Created</DescriptionTerm>
          <DescriptionDetail>
            <DateDisplay :date="collection.created_at" format="medium" variant="small-dark" />
          </DescriptionDetail>
        </DescriptionRow>
        <DescriptionRow v-if="collection?.updated_at" variant="gray">
          <DescriptionTerm>Last Updated</DescriptionTerm>
          <DescriptionDetail>
            <DateDisplay :date="collection.updated_at" format="medium" variant="small-dark" />
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
  import GenericDropdown from '@/components/format/GenericDropdown.vue'
  import { RectangleStackIcon as CollectionIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline'
  import { useCollectionStore } from '@/stores/collection'
  import { useLanguageStore } from '@/stores/language'
  import { useContextStore } from '@/stores/context'
  import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
  import { useErrorDisplayStore } from '@/stores/errorDisplay'
  import { useCancelChangesConfirmationStore } from '@/stores/cancelChangesConfirmation'
  import { useDeleteConfirmationStore } from '@/stores/deleteConfirmation'
  import type { CollectionStoreRequest } from '@metanull/inventory-app-api-client'

  // Types
  type Mode = 'view' | 'edit' | 'create'

  interface CollectionFormData {
    id?: string
    internal_name: string
    language_id: string
    context_id: string
    backward_compatibility: string
  }

  // Composables
  const route = useRoute()
  const router = useRouter()
  const collectionStore = useCollectionStore()
  const languageStore = useLanguageStore()
  const contextStore = useContextStore()
  const loadingStore = useLoadingOverlayStore()
  const errorStore = useErrorDisplayStore()
  const deleteStore = useDeleteConfirmationStore()
  const cancelChangesStore = useCancelChangesConfirmationStore()

  // Reactive state - Single source of truth for mode
  const mode = ref<Mode>('view')

  // Computed properties
  const collection = computed(() => collectionStore.currentCollection)

  const editForm = ref<CollectionFormData>({
    id: '',
    internal_name: '',
    language_id: '',
    context_id: '',
    backward_compatibility: '',
  })

  // Information description based on mode
  const informationDescription = computed(() => {
    switch (mode.value) {
      case 'create':
        return 'Create a new collection to organize your museum items.'
      case 'edit':
        return 'Edit detailed information about this collection.'
      default:
        return 'Detailed information about this collection.'
    }
  })

  // Back link configuration
  const backLink = computed(() => ({
    title: 'Back to Collections',
    route: '/collections',
    icon: ArrowLeftIcon,
    color: 'indigo',
  }))

  // Dropdown options
  const languageOptions = computed(() =>
    languageStore.languages.map(lang => ({
      id: lang.id,
      internal_name: lang.internal_name || lang.id,
      is_default: lang.is_default,
    }))
  )

  const contextOptions = computed(() =>
    contextStore.contexts.map(ctx => ({
      id: ctx.id,
      internal_name: ctx.internal_name,
      is_default: ctx.is_default,
    }))
  )

  // Unsaved changes detection
  const hasUnsavedChanges = computed(() => {
    if (mode.value === 'view') return false

    // For create mode, compare with default values
    if (mode.value === 'create') {
      const defaultValues = getDefaultFormValues()
      return (
        editForm.value.internal_name !== defaultValues.internal_name ||
        editForm.value.language_id !== defaultValues.language_id ||
        editForm.value.context_id !== defaultValues.context_id ||
        editForm.value.backward_compatibility !== defaultValues.backward_compatibility
      )
    }

    // For edit mode, compare with original values
    if (!collection.value) return false

    const originalValues = getFormValuesFromCollection()
    return (
      editForm.value.internal_name !== originalValues.internal_name ||
      editForm.value.language_id !== originalValues.language_id ||
      editForm.value.context_id !== originalValues.context_id ||
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

  // Initialize edit form from collection data
  const getFormValuesFromCollection = (): CollectionFormData => ({
    id: collection.value?.id || '',
    internal_name: collection.value?.internal_name || '',
    language_id: collection.value?.language_id || '',
    context_id: collection.value?.context_id || '',
    backward_compatibility: collection.value?.backward_compatibility || '',
  })

  // Get default form values for create mode
  const getDefaultFormValues = (): CollectionFormData => ({
    internal_name: '',
    language_id: languageStore.defaultLanguage?.id || '',
    context_id: contextStore.defaultContext?.id || '',
    backward_compatibility: '',
  })

  // Initialize form with collection data or defaults
  const initializeForm = () => {
    if (mode.value === 'create') {
      editForm.value = getDefaultFormValues()
    } else if (collection.value) {
      editForm.value = getFormValuesFromCollection()
    }
  }

  // Mode management functions
  const enterEditMode = () => {
    mode.value = 'edit'
    initializeForm()
  }

  const saveCollection = async () => {
    try {
      loadingStore.show('Saving...')

      const collectionData: CollectionStoreRequest = {
        internal_name: editForm.value.internal_name,
        language_id: editForm.value.language_id,
        context_id: editForm.value.context_id,
        backward_compatibility: editForm.value.backward_compatibility || undefined,
      }

      if (mode.value === 'create') {
        const newCollection = await collectionStore.createCollection(collectionData)
        // Navigate to the new collection's detail page
        await router.push(`/collections/${newCollection.id}`)
        mode.value = 'view'
        errorStore.addMessage('info', 'Collection created successfully.')
      } else if (collection.value) {
        await collectionStore.updateCollection(collection.value.id, collectionData)
        mode.value = 'view'
        errorStore.addMessage('info', 'Collection updated successfully.')
      }
    } catch {
      errorStore.addMessage('error', 'Failed to save collection. Please try again.')
    } finally {
      loadingStore.hide()
    }
  }

  const cancelAction = async () => {
    if (hasUnsavedChanges.value) {
      const shouldCancel = await cancelChangesStore.trigger(
        'Discard Changes',
        'Are you sure you want to discard your changes?'
      )
      if (!shouldCancel) return
    }

    if (mode.value === 'create') {
      await router.push('/collections')
    } else {
      mode.value = 'view'
    }
  }

  const deleteCollection = async () => {
    if (!collection.value) return

    const result = await deleteStore.trigger(
      'Delete Collection',
      `Are you sure you want to delete "${collection.value.internal_name}"? This action cannot be undone.`
    )

    if (result === 'delete') {
      try {
        loadingStore.show('Deleting...')
        await collectionStore.deleteCollection(collection.value.id)
        await router.push('/collections')
        errorStore.addMessage('info', 'Collection deleted successfully.')
      } catch {
        errorStore.addMessage('error', 'Failed to delete collection. Please try again.')
      } finally {
        loadingStore.hide()
      }
    }
  }

  // Data fetching
  const fetchCollection = async (collectionId: string) => {
    try {
      await collectionStore.fetchCollection(collectionId)
    } catch {
      errorStore.addMessage('error', 'Failed to fetch collection data. Please try again.')
    }
  }

  const fetchCollectionData = async () => {
    if (route.params.id) {
      await fetchCollection(route.params.id as string)
    }
  }

  // Initialize component based on route
  onMounted(async () => {
    loadingStore.show()

    try {
      // Load dependent data first
      await Promise.all([languageStore.fetchLanguages(), contextStore.fetchContexts()])

      // Determine mode and initialize
      const isCreateMode = route.path.includes('/new')
      const isEditMode = route.query.edit === 'true'

      if (isCreateMode) {
        mode.value = 'create'
        initializeForm()
      } else {
        const collectionId = route.params.id as string
        if (collectionId) {
          await fetchCollection(collectionId)
          mode.value = isEditMode ? 'edit' : 'view'
          if (mode.value === 'edit') {
            initializeForm()
          }
        }
      }
    } catch {
      errorStore.addMessage('error', 'Failed to load collection data. Please try again.')
    } finally {
      loadingStore.hide()
    }
  })

  // Watch for route changes
  watch(
    () => route.query.edit,
    editQuery => {
      if (editQuery === 'true' && mode.value === 'view') {
        enterEditMode()
      } else if (editQuery !== 'true' && mode.value === 'edit') {
        mode.value = 'view'
      }
    }
  )

  // Navigation guard for unsaved changes
  onBeforeRouteLeave(
    (_to: RouteLocationNormalized, _from: RouteLocationNormalized, next: NavigationGuardNext) => {
      if (!hasUnsavedChanges.value) {
        next()
        return
      }

      // Block navigation if there are unsaved changes
      next(false)

      // Show confirmation modal
      cancelChangesStore
        .trigger('Discard Changes', 'Are you sure you want to discard your changes?')
        .then(shouldLeave => {
          if (shouldLeave) {
            next()
          }
        })
    }
  )
</script>
