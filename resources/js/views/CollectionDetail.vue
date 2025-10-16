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
      <CollectionIcon :class="['h-6 w-6', colorClasses.icon]" />
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
          <DescriptionTerm>Type</DescriptionTerm>
          <DescriptionDetail>
            <select
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.type"
              class="mt-1 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
            >
              <option value="">Select type...</option>
              <option value="collection">Collection</option>
              <option value="exhibition">Exhibition</option>
              <option value="gallery">Gallery</option>
            </select>
            <DisplayText v-else>
              <span class="capitalize">{{ collection?.type || '—' }}</span>
            </DisplayText>
          </DescriptionDetail>
        </DescriptionRow>
        <DescriptionRow variant="gray">
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
                :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                  colorClasses.badgeBackground,
                  colorClasses.badge,
                ]"
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
                :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                  colorClasses.badgeBackground,
                  colorClasses.badge,
                ]"
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
                :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                  colorClasses.badgeBackground,
                  colorClasses.badge,
                ]"
              >
                {{ collection.translations_count }} translations
              </span>
            </div>
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
  import GenericDropdown from '@/components/format/GenericDropdown.vue'
  import { RectangleStackIcon as CollectionIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline'
  import { useCollectionStore } from '@/stores/collection'
  import { useLanguageStore } from '@/stores/language'
  import { useContextStore } from '@/stores/context'
  import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
  import { useErrorDisplayStore } from '@/stores/errorDisplay'
  import { useCancelChangesConfirmationStore } from '@/stores/cancelChangesConfirmation'
  import { useDeleteConfirmationStore } from '@/stores/deleteConfirmation'
  import type {
    StoreCollectionRequest,
    StoreCollectionRequestTypeEnum,
  } from '@metanull/inventory-app-api-client'
  import { useColors, type ColorName } from '@/composables/useColors'

  // Types
  type Mode = 'view' | 'edit' | 'create'

  interface CollectionFormData {
    id?: string
    internal_name: string
    language_id: string
    context_id: string
    backward_compatibility: string
    type: string
  }

  interface Props {
    color?: ColorName
  }

  const props = withDefaults(defineProps<Props>(), {
    color: 'indigo',
  })

  // Color classes from centralized system
  const colorClasses = useColors(computed(() => props.color))

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
    type: '',
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
    color: props.color,
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
        editForm.value.backward_compatibility !== defaultValues.backward_compatibility ||
        editForm.value.type !== defaultValues.type
      )
    }

    // For edit mode, compare with original values
    if (!collection.value) return false

    const originalValues = getFormValuesFromCollection()
    return (
      editForm.value.internal_name !== originalValues.internal_name ||
      editForm.value.language_id !== originalValues.language_id ||
      editForm.value.context_id !== originalValues.context_id ||
      editForm.value.backward_compatibility !== originalValues.backward_compatibility ||
      editForm.value.type !== originalValues.type
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
    type: collection.value?.type || '',
  })

  // Get default form values for create mode
  const getDefaultFormValues = (): CollectionFormData => ({
    internal_name: '',
    language_id: languageStore.defaultLanguage?.id || '',
    context_id: contextStore.defaultContext?.id || '',
    backward_compatibility: '',
    type: 'collection', // Default to 'collection' type
  })

  // Mode management functions
  const enterCreateMode = () => {
    mode.value = 'create'
    editForm.value = getDefaultFormValues()
  }

  const enterEditMode = () => {
    if (!collection.value) return
    mode.value = 'edit'
    editForm.value = getFormValuesFromCollection()
  }

  const enterViewMode = () => {
    mode.value = 'view'
    // Clear form data when returning to view mode
    editForm.value = getDefaultFormValues()
  }

  const saveCollection = async () => {
    try {
      loadingStore.show(mode.value === 'create' ? 'Creating...' : 'Saving...')

      const collectionData: StoreCollectionRequest = {
        internal_name: editForm.value.internal_name,
        language_id: editForm.value.language_id,
        context_id: editForm.value.context_id,
        backward_compatibility: editForm.value.backward_compatibility || undefined,
        type: editForm.value.type as StoreCollectionRequestTypeEnum,
      }

      if (mode.value === 'create') {
        const savedCollection = await collectionStore.createCollection(collectionData)
        errorStore.addMessage('info', 'Collection created successfully.')

        // Load the new collection and enter view mode
        await collectionStore.fetchCollection(savedCollection.id)
        enterViewMode()
      } else if (mode.value === 'edit' && collection.value) {
        // Update existing collection
        await collectionStore.updateCollection(collection.value.id, collectionData)
        errorStore.addMessage('info', 'Collection updated successfully.')

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
        `Failed to ${mode.value === 'create' ? 'create' : 'update'} collection. Please try again.`
      )
    } finally {
      loadingStore.hide()
    }
  }

  const cancelAction = async () => {
    if (hasUnsavedChanges.value) {
      const result = await cancelChangesStore.trigger(
        mode.value === 'create'
          ? 'New Collection has unsaved changes'
          : 'Collection has unsaved changes',
        mode.value === 'create'
          ? 'There are unsaved changes to this new collection. If you navigate away, the changes will be lost. Are you sure you want to navigate away? This action cannot be undone.'
          : `There are unsaved changes to "${collection.value?.internal_name}". If you navigate away, the changes will be lost. Are you sure you want to navigate away? This action cannot be undone.`
      )

      if (result === 'stay') {
        return // Cancel navigation
      } else {
        cancelChangesStore.resetChanges() // Reset changes before leaving
      }
    }

    if (mode.value === 'create') {
      router.push({ name: 'collections' })
    } else {
      enterViewMode()
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
        await router.push({ name: 'collections' })
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
  const initializeComponent = async () => {
    loadingStore.show()

    try {
      // Load dependent data first
      await Promise.all([languageStore.fetchLanguages(), contextStore.fetchContexts()])

      // Determine mode and initialize
      const isCreateMode = route.path.includes('/new')
      const isEditMode = route.query.edit === 'true'

      if (isCreateMode) {
        enterCreateMode()
      } else {
        const collectionId = route.params.id as string
        if (collectionId) {
          await fetchCollection(collectionId)
          if (isEditMode) {
            enterEditMode()
          } else {
            enterViewMode()
          }
        }
      }
    } catch {
      errorStore.addMessage('error', 'Failed to load collection data. Please try again.')
    } finally {
      loadingStore.hide()
    }
  }

  // Initialize component on mount
  onMounted(initializeComponent)

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
    async (
      _to: RouteLocationNormalized,
      _from: RouteLocationNormalized,
      next: NavigationGuardNext
    ) => {
      // Only check for unsaved changes if we're in edit or create mode
      if ((mode.value === 'edit' || mode.value === 'create') && hasUnsavedChanges.value) {
        const result = await cancelChangesStore.trigger(
          mode.value === 'create'
            ? 'New Collection has unsaved changes'
            : 'Collection has unsaved changes',
          mode.value === 'create'
            ? 'There are unsaved changes to this new collection. If you navigate away, the changes will be lost. Are you sure you want to navigate away? This action cannot be undone.'
            : `There are unsaved changes to "${collection.value?.internal_name}". If you navigate away, the changes will be lost. Are you sure you want to navigate away? This action cannot be undone.`
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
