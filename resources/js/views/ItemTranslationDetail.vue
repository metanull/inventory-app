<template>
  <DetailView
    :store-loading="translationStore.loading"
    :resource="mode === 'create' ? null : translationAsResource"
    :mode="mode"
    :save-disabled="!hasUnsavedChanges || !isFormValid"
    :has-unsaved-changes="hasUnsavedChanges"
    :back-link="backLink"
    :create-title="'New Item Translation'"
    :create-subtitle="'(Creating)'"
    information-title="Translation Information"
    :information-description="informationDescription"
    :fetch-data="fetchTranslation"
    @edit="enterEditMode"
    @save="saveTranslation"
    @cancel="cancelAction"
    @delete="deleteTranslation"
  >
    <template #resource-icon>
      <LanguageIcon :class="['h-6 w-6', colorClasses.icon]" />
    </template>
    <template #information>
      <DescriptionList>
        <!-- Item Selection -->
        <DescriptionRow variant="gray">
          <DescriptionTerm required>Item</DescriptionTerm>
          <DescriptionDetail>
            <FormSelect
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.item_id"
              :options="itemOptions"
              placeholder="Select an item"
              :disabled="mode === 'edit'"
            />
            <DisplayText v-else>{{ getItemDisplay(translation?.item_id) }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>

        <!-- Language Selection -->
        <DescriptionRow variant="white">
          <DescriptionTerm required>Language</DescriptionTerm>
          <DescriptionDetail>
            <FormSelect
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.language_id"
              :options="languageOptions"
              placeholder="Select a language"
              :disabled="mode === 'edit'"
            />
            <DisplayText v-else>{{ getLanguageDisplay(translation?.language_id) }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>

        <!-- Context Selection -->
        <DescriptionRow variant="gray">
          <DescriptionTerm required>Context</DescriptionTerm>
          <DescriptionDetail>
            <FormSelect
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.context_id"
              :options="contextOptions"
              placeholder="Select a context"
              :disabled="mode === 'edit'"
            />
            <DisplayText v-else>{{ getContextDisplay(translation?.context_id) }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>

        <!-- Name -->
        <DescriptionRow variant="white">
          <DescriptionTerm required>Name</DescriptionTerm>
          <DescriptionDetail>
            <FormInput
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.name"
              type="text"
              placeholder="Enter item name"
            />
            <DisplayText v-else>{{ translation?.name }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>

        <!-- Alternate Name -->
        <DescriptionRow variant="gray">
          <DescriptionTerm>Alternate Name</DescriptionTerm>
          <DescriptionDetail>
            <FormInput
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.alternate_name"
              type="text"
              placeholder="Optional alternate name"
            />
            <DisplayText v-else>{{ translation?.alternate_name || 'N/A' }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>

        <!-- Description -->
        <DescriptionRow variant="white">
          <DescriptionTerm required>Description</DescriptionTerm>
          <DescriptionDetail>
            <FormTextarea
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.description"
              placeholder="Enter item description"
              :rows="4"
            />
            <DisplayText v-else multiline>{{ translation?.description }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>

        <!-- Type -->
        <DescriptionRow variant="gray">
          <DescriptionTerm>Type</DescriptionTerm>
          <DescriptionDetail>
            <FormInput
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.type"
              type="text"
              placeholder="Item type"
            />
            <DisplayText v-else>{{ translation?.type || 'N/A' }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>

        <!-- Holder -->
        <DescriptionRow variant="white">
          <DescriptionTerm>Holder</DescriptionTerm>
          <DescriptionDetail>
            <FormTextarea
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.holder"
              placeholder="Current holder information"
              :rows="2"
            />
            <DisplayText v-else multiline>{{ translation?.holder || 'N/A' }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>

        <!-- Owner -->
        <DescriptionRow variant="gray">
          <DescriptionTerm>Owner</DescriptionTerm>
          <DescriptionDetail>
            <FormTextarea
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.owner"
              placeholder="Current owner information"
              :rows="2"
            />
            <DisplayText v-else multiline>{{ translation?.owner || 'N/A' }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>

        <!-- Initial Owner -->
        <DescriptionRow variant="white">
          <DescriptionTerm>Initial Owner</DescriptionTerm>
          <DescriptionDetail>
            <FormTextarea
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.initial_owner"
              placeholder="Initial owner information"
              :rows="2"
            />
            <DisplayText v-else multiline>{{ translation?.initial_owner || 'N/A' }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>

        <!-- Dates -->
        <DescriptionRow variant="gray">
          <DescriptionTerm>Dates</DescriptionTerm>
          <DescriptionDetail>
            <FormTextarea
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.dates"
              placeholder="Associated dates"
              :rows="2"
            />
            <DisplayText v-else multiline>{{ translation?.dates || 'N/A' }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>

        <!-- Location -->
        <DescriptionRow variant="white">
          <DescriptionTerm>Location</DescriptionTerm>
          <DescriptionDetail>
            <FormTextarea
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.location"
              placeholder="Location information"
              :rows="2"
            />
            <DisplayText v-else multiline>{{ translation?.location || 'N/A' }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>

        <!-- Dimensions -->
        <DescriptionRow variant="gray">
          <DescriptionTerm>Dimensions</DescriptionTerm>
          <DescriptionDetail>
            <FormInput
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.dimensions"
              type="text"
              placeholder="Item dimensions"
            />
            <DisplayText v-else>{{ translation?.dimensions || 'N/A' }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>

        <!-- Place of Production -->
        <DescriptionRow variant="white">
          <DescriptionTerm>Place of Production</DescriptionTerm>
          <DescriptionDetail>
            <FormTextarea
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.place_of_production"
              placeholder="Where the item was produced"
              :rows="2"
            />
            <DisplayText v-else multiline>{{
              translation?.place_of_production || 'N/A'
            }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>

        <!-- Method for Datation -->
        <DescriptionRow variant="gray">
          <DescriptionTerm>Method for Datation</DescriptionTerm>
          <DescriptionDetail>
            <FormTextarea
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.method_for_datation"
              placeholder="Method used for dating"
              :rows="2"
            />
            <DisplayText v-else multiline>{{
              translation?.method_for_datation || 'N/A'
            }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>

        <!-- Method for Provenance -->
        <DescriptionRow variant="white">
          <DescriptionTerm>Method for Provenance</DescriptionTerm>
          <DescriptionDetail>
            <FormTextarea
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.method_for_provenance"
              placeholder="Method used for provenance"
              :rows="2"
            />
            <DisplayText v-else multiline>{{
              translation?.method_for_provenance || 'N/A'
            }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>

        <!-- Obtention -->
        <DescriptionRow variant="gray">
          <DescriptionTerm>Obtention</DescriptionTerm>
          <DescriptionDetail>
            <FormTextarea
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.obtention"
              placeholder="How the item was obtained"
              :rows="2"
            />
            <DisplayText v-else multiline>{{ translation?.obtention || 'N/A' }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>

        <!-- Bibliography -->
        <DescriptionRow variant="white">
          <DescriptionTerm>Bibliography</DescriptionTerm>
          <DescriptionDetail>
            <FormTextarea
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.bibliography"
              placeholder="Bibliographic references"
              :rows="3"
            />
            <DisplayText v-else multiline>{{ translation?.bibliography || 'N/A' }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>

        <!-- Author -->
        <DescriptionRow variant="gray">
          <DescriptionTerm>Author</DescriptionTerm>
          <DescriptionDetail>
            <FormSelect
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.author_id"
              :options="authorOptions"
              placeholder="Select author (optional)"
              clearable
            />
            <DisplayText v-else>{{ getAuthorDisplay(translation?.author_id) }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>

        <!-- Text Copy Editor -->
        <DescriptionRow variant="white">
          <DescriptionTerm>Text Copy Editor</DescriptionTerm>
          <DescriptionDetail>
            <FormSelect
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.text_copy_editor_id"
              :options="authorOptions"
              placeholder="Select text copy editor (optional)"
              clearable
            />
            <DisplayText v-else>{{
              getAuthorDisplay(translation?.text_copy_editor_id)
            }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>

        <!-- Translator -->
        <DescriptionRow variant="gray">
          <DescriptionTerm>Translator</DescriptionTerm>
          <DescriptionDetail>
            <FormSelect
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.translator_id"
              :options="authorOptions"
              placeholder="Select translator (optional)"
              clearable
            />
            <DisplayText v-else>{{ getAuthorDisplay(translation?.translator_id) }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>

        <!-- Translation Copy Editor -->
        <DescriptionRow variant="white">
          <DescriptionTerm>Translation Copy Editor</DescriptionTerm>
          <DescriptionDetail>
            <FormSelect
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.translation_copy_editor_id"
              :options="authorOptions"
              placeholder="Select translation copy editor (optional)"
              clearable
            />
            <DisplayText v-else>{{
              getAuthorDisplay(translation?.translation_copy_editor_id)
            }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>

        <!-- Backward Compatibility -->
        <DescriptionRow variant="gray">
          <DescriptionTerm>Legacy ID</DescriptionTerm>
          <DescriptionDetail>
            <FormInput
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.backward_compatibility"
              type="text"
              placeholder="Optional legacy identifier"
            />
            <DisplayText v-else>{{ translation?.backward_compatibility || 'N/A' }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>

        <!-- Extra (JSON) -->
        <DescriptionRow
          v-if="translation?.extra || mode === 'edit' || mode === 'create'"
          variant="white"
        >
          <DescriptionTerm>Extra Data (JSON)</DescriptionTerm>
          <DescriptionDetail>
            <FormTextarea
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.extra"
              placeholder='Optional JSON data, e.g., {"key": "value"}'
              :rows="3"
              :error="jsonError"
            />
            <DisplayText v-else multiline>{{ translation?.extra || 'N/A' }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>

        <!-- System Fields (View Only) -->
        <DescriptionRow v-if="translation?.created_at && mode === 'view'" variant="gray">
          <DescriptionTerm>Created</DescriptionTerm>
          <DescriptionDetail>
            <DateDisplay :date="translation.created_at" format="long" />
          </DescriptionDetail>
        </DescriptionRow>

        <DescriptionRow v-if="translation?.updated_at && mode === 'view'" variant="white">
          <DescriptionTerm>Last Updated</DescriptionTerm>
          <DescriptionDetail>
            <DateDisplay :date="translation.updated_at" format="long" />
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
  import FormSelect from '@/components/format/FormSelect.vue'
  import FormTextarea from '@/components/format/FormTextarea.vue'
  import DisplayText from '@/components/format/DisplayText.vue'
  import DateDisplay from '@/components/format/Date.vue'
  import { LanguageIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline'
  import { useItemTranslationStore } from '@/stores/itemTranslation'
  import { useContextStore } from '@/stores/context'
  import { useLanguageStore } from '@/stores/language'
  import { useItemStore } from '@/stores/item'
  import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
  import { useErrorDisplayStore } from '@/stores/errorDisplay'
  import { useCancelChangesConfirmationStore } from '@/stores/cancelChangesConfirmation'
  import { useDeleteConfirmationStore } from '@/stores/deleteConfirmation'
  import { useColors, type ColorName } from '@/composables/useColors'
  import type {
    ItemTranslationResource,
    StoreItemTranslationRequest,
    UpdateItemTranslationRequest,
  } from '@metanull/inventory-app-api-client'

  // Props
  interface Props {
    color?: ColorName
  }

  const props = withDefaults(defineProps<Props>(), {
    color: 'blue',
  })

  // Types
  type Mode = 'view' | 'edit' | 'create'

  interface TranslationFormData {
    item_id: string
    language_id: string
    context_id: string
    name: string
    alternate_name: string
    description: string
    type: string
    holder: string
    owner: string
    initial_owner: string
    dates: string
    location: string
    dimensions: string
    place_of_production: string
    method_for_datation: string
    method_for_provenance: string
    obtention: string
    bibliography: string
    author_id: string
    text_copy_editor_id: string
    translator_id: string
    translation_copy_editor_id: string
    backward_compatibility: string
    extra: string
  }

  // Composables
  const route = useRoute()
  const router = useRouter()
  const translationStore = useItemTranslationStore()
  const contextStore = useContextStore()
  const languageStore = useLanguageStore()
  const itemStore = useItemStore()
  const loadingStore = useLoadingOverlayStore()
  const errorStore = useErrorDisplayStore()
  const deleteStore = useDeleteConfirmationStore()
  const cancelChangesStore = useCancelChangesConfirmationStore()

  // Reactive state
  const mode = ref<Mode>('view')
  const jsonError = ref<string>('')

  // Computed properties
  const translation = computed(() => translationStore.currentItemTranslation)

  // Adapter for DetailView which expects internal_name but ItemTranslation has name
  const translationAsResource = computed(() => {
    if (!translation.value) return null
    return {
      ...translation.value,
      internal_name: translation.value.name, // Map name to internal_name for DetailView
    }
  })

  const colorClasses = useColors(computed(() => props.color))

  const editForm = ref<TranslationFormData>({
    item_id: '',
    language_id: '',
    context_id: '',
    name: '',
    alternate_name: '',
    description: '',
    type: '',
    holder: '',
    owner: '',
    initial_owner: '',
    dates: '',
    location: '',
    dimensions: '',
    place_of_production: '',
    method_for_datation: '',
    method_for_provenance: '',
    obtention: '',
    bibliography: '',
    author_id: '',
    text_copy_editor_id: '',
    translator_id: '',
    translation_copy_editor_id: '',
    backward_compatibility: '',
    extra: '',
  })

  // Information description based on mode
  const informationDescription = computed(() => {
    switch (mode.value) {
      case 'create':
        return 'Create a new translation for an item in a specific language and context.'
      case 'edit':
        return 'Edit the translation details.'
      default:
        return 'Detailed information about this item translation.'
    }
  })

  // Back link configuration
  const backLink = computed(() => ({
    title: 'Back to Translations',
    route: '/item-translations',
    icon: ArrowLeftIcon,
    color: props.color,
  }))

  // Options for dropdowns
  const contextOptions = computed(() => {
    const contexts = contextStore.contexts || []
    return contexts.map(c => ({
      value: c.id,
      label: c.internal_name,
    }))
  })

  const languageOptions = computed(() => {
    const languages = languageStore.languages || []
    return languages.map(l => ({
      value: l.id,
      label: `${l.internal_name} (${l.id})`,
    }))
  })

  const itemOptions = computed(() => {
    const items = itemStore.items || []
    return items.map(i => ({
      value: i.id,
      label: i.internal_name || i.id.substring(0, 8),
    }))
  })

  const authorOptions = computed(() => {
    // TODO: Load authors when we implement author store
    // For now, return empty array
    return []
  })

  // Display helpers
  const getContextDisplay = (contextId?: string) => {
    if (!contextId) return 'N/A'
    const context = contextStore.contexts?.find(c => c.id === contextId)
    return context?.internal_name || contextId
  }

  const getLanguageDisplay = (languageId?: string) => {
    if (!languageId) return 'N/A'
    const language = languageStore.languages?.find(l => l.id === languageId)
    return language ? `${language.internal_name} (${language.id})` : languageId
  }

  const getItemDisplay = (itemId?: string) => {
    if (!itemId) return 'N/A'
    const item = itemStore.items?.find(i => i.id === itemId)
    return item?.internal_name || itemId.substring(0, 8)
  }

  const getAuthorDisplay = (authorId?: string | null) => {
    if (!authorId) return 'N/A'
    // TODO: Implement when author store is available
    return authorId.substring(0, 8)
  }

  // Validate JSON separately to avoid side effects in computed
  const validateJson = () => {
    if (editForm.value.extra.trim() !== '') {
      try {
        JSON.parse(editForm.value.extra)
        jsonError.value = ''
        return true
      } catch {
        jsonError.value = 'Invalid JSON format'
        return false
      }
    } else {
      jsonError.value = ''
      return true
    }
  }

  // Form validation
  const isFormValid = computed(() => {
    if (mode.value === 'view') return true

    // Required fields
    const hasRequiredFields =
      editForm.value.item_id.trim() !== '' &&
      editForm.value.language_id.trim() !== '' &&
      editForm.value.context_id.trim() !== '' &&
      editForm.value.name.trim() !== '' &&
      editForm.value.description.trim() !== ''

    return hasRequiredFields && validateJson()
  })

  // Unsaved changes detection
  const hasUnsavedChanges = computed(() => {
    if (mode.value === 'view') return false

    if (mode.value === 'create') {
      const defaultValues = getDefaultFormValues()
      return Object.keys(editForm.value).some(
        key =>
          editForm.value[key as keyof TranslationFormData] !==
          defaultValues[key as keyof TranslationFormData]
      )
    }

    // For edit mode
    if (!translation.value) return false
    return Object.keys(editForm.value).some(key => {
      const formValue = editForm.value[key as keyof TranslationFormData]
      const translationValue = translation.value?.[key as keyof ItemTranslationResource] ?? ''
      return formValue !== (translationValue || '')
    })
  })

  // Default form values
  const getDefaultFormValues = (): TranslationFormData => ({
    item_id: (route.query.item_id as string) || '',
    language_id: '',
    context_id: contextStore.defaultContext?.id || '',
    name: '',
    alternate_name: '',
    description: '',
    type: '',
    holder: '',
    owner: '',
    initial_owner: '',
    dates: '',
    location: '',
    dimensions: '',
    place_of_production: '',
    method_for_datation: '',
    method_for_provenance: '',
    obtention: '',
    bibliography: '',
    author_id: '',
    text_copy_editor_id: '',
    translator_id: '',
    translation_copy_editor_id: '',
    backward_compatibility: '',
    extra: '',
  })

  // Initialize form from translation
  const initializeForm = () => {
    if (mode.value === 'create') {
      editForm.value = getDefaultFormValues()
    } else if (translation.value) {
      editForm.value = {
        item_id: translation.value.item_id || '',
        language_id: translation.value.language_id || '',
        context_id: translation.value.context_id || '',
        name: translation.value.name || '',
        alternate_name: translation.value.alternate_name || '',
        description: translation.value.description || '',
        type: translation.value.type || '',
        holder: translation.value.holder || '',
        owner: translation.value.owner || '',
        initial_owner: translation.value.initial_owner || '',
        dates: translation.value.dates || '',
        location: translation.value.location || '',
        dimensions: translation.value.dimensions || '',
        place_of_production: translation.value.place_of_production || '',
        method_for_datation: translation.value.method_for_datation || '',
        method_for_provenance: translation.value.method_for_provenance || '',
        obtention: translation.value.obtention || '',
        bibliography: translation.value.bibliography || '',
        author_id: translation.value.author_id || '',
        text_copy_editor_id: translation.value.text_copy_editor_id || '',
        translator_id: translation.value.translator_id || '',
        translation_copy_editor_id: translation.value.translation_copy_editor_id || '',
        backward_compatibility: translation.value.backward_compatibility || '',
        extra:
          typeof translation.value.extra === 'string'
            ? translation.value.extra
            : JSON.stringify(translation.value.extra) || '',
      }
    }
  }

  // Fetch translation
  const fetchTranslation = async () => {
    const id = route.params.id as string
    if (id && id !== 'new') {
      try {
        await translationStore.fetchItemTranslation(id)
      } catch {
        errorStore.addMessage('error', 'Failed to load translation')
        router.push('/item-translations')
      }
    }
  }

  // Mode management
  const enterEditMode = () => {
    mode.value = 'edit'
    initializeForm()
  }

  const cancelAction = async () => {
    if (hasUnsavedChanges.value) {
      const result = await cancelChangesStore.trigger(
        'Discard Changes',
        'You have unsaved changes. Are you sure you want to discard them?'
      )
      if (result === 'leave') {
        if (mode.value === 'create') {
          router.push('/item-translations')
        } else {
          mode.value = 'view'
          initializeForm()
        }
      }
    } else {
      if (mode.value === 'create') {
        router.push('/item-translations')
      } else {
        mode.value = 'view'
      }
    }
  }

  // Save translation
  const saveTranslation = async () => {
    if (!isFormValid.value) {
      errorStore.addMessage('error', 'Please fill in all required fields correctly')
      return
    }

    try {
      loadingStore.show('Saving...')

      // Prepare data - convert empty strings to null for optional fields
      const prepareData = () => {
        const data: any = {
          item_id: editForm.value.item_id,
          language_id: editForm.value.language_id,
          context_id: editForm.value.context_id,
          name: editForm.value.name,
          description: editForm.value.description,
        }

        // Optional string fields
        const optionalFields = [
          'alternate_name',
          'type',
          'holder',
          'owner',
          'initial_owner',
          'dates',
          'location',
          'dimensions',
          'place_of_production',
          'method_for_datation',
          'method_for_provenance',
          'obtention',
          'bibliography',
          'author_id',
          'text_copy_editor_id',
          'translator_id',
          'translation_copy_editor_id',
          'backward_compatibility',
        ]

        optionalFields.forEach(field => {
          const value = editForm.value[field as keyof TranslationFormData]
          data[field] = value && value.trim() !== '' ? value : null
        })

        // Handle extra JSON field
        if (editForm.value.extra.trim() !== '') {
          data.extra = editForm.value.extra
        } else {
          data.extra = null
        }

        return data
      }

      if (mode.value === 'create') {
        const savedTranslation =
          await translationStore.createItemTranslation(prepareData() as StoreItemTranslationRequest)
        errorStore.addMessage('info', 'Translation created successfully')

        // Reset changes tracking before navigation to prevent the "unsaved changes" dialog
        cancelChangesStore.resetChanges()

        // Navigate to the created translation in view mode
        router.push({
          name: 'item-translation-detail',
          params: { id: savedTranslation.id },
        })
      } else {
        const id = route.params.id as string
        await translationStore.updateItemTranslation(
          id,
          prepareData() as UpdateItemTranslationRequest
        )
        errorStore.addMessage('info', 'Translation updated successfully')
        
        // Fetch updated data and enter view mode
        await fetchTranslation()
        mode.value = 'view'
      }
    } catch (err: unknown) {
      const error = err as { response?: { data?: { message?: string } } }
      const message = error?.response?.data?.message || 'Failed to save translation'
      errorStore.addMessage('error', message)
    } finally {
      loadingStore.hide()
    }
  }

  // Delete translation
  const deleteTranslation = async () => {
    if (!translation.value) return

    const result = await deleteStore.trigger(
      'Delete Translation',
      `Are you sure you want to delete this translation? This action cannot be undone.`
    )

    if (result === 'delete') {
      try {
        loadingStore.show('Deleting...')
        await translationStore.deleteItemTranslation(translation.value.id)
        errorStore.addMessage('info', 'Translation deleted successfully')
        router.push('/item-translations')
      } catch {
        errorStore.addMessage('error', 'Failed to delete translation')
      } finally {
        loadingStore.hide()
      }
    }
  }

  // Navigation guard
  onBeforeRouteLeave(
    async (
      _to: RouteLocationNormalized,
      _from: RouteLocationNormalized,
      next: NavigationGuardNext
    ) => {
      if (hasUnsavedChanges.value) {
        const result = await cancelChangesStore.trigger(
          'Discard Changes',
          'You have unsaved changes. Are you sure you want to leave this page?'
        )
        if (result === 'leave') {
          next()
        } else {
          next(false)
        }
      } else {
        next()
      }
    }
  )

  // Watch for route changes
  watch(
    () => route.params.id,
    async newId => {
      if (newId === 'new') {
        mode.value = 'create'
        initializeForm()
      } else if (newId) {
        mode.value = 'view'
        await fetchTranslation()
      }
    }
  )

  // Watch for query parameter changes (edit mode)
  watch(
    () => route.query.edit,
    newEdit => {
      if (newEdit === 'true' && mode.value === 'view') {
        enterEditMode()
      }
    }
  )

  // Lifecycle
  onMounted(async () => {
    loadingStore.show()
    try {
      // Load required data
      await Promise.all([
        contextStore.contexts?.length
          ? Promise.resolve()
          : contextStore.fetchContexts({ page: 1, perPage: 100 }),
        languageStore.languages?.length
          ? Promise.resolve()
          : languageStore.fetchLanguages({ page: 1, perPage: 100 }),
        itemStore.items?.length
          ? Promise.resolve()
          : itemStore.fetchItems({ page: 1, perPage: 100 }),
      ])

      // Determine mode and fetch data
      const id = route.params.id as string
      // Check if we're on the /new route (route name) or if id === 'new' (legacy)
      if (route.name === 'item-translation-new' || id === 'new') {
        mode.value = 'create'
        initializeForm()
      } else {
        mode.value = route.query.edit === 'true' ? 'edit' : 'view'
        await fetchTranslation()
        if (mode.value === 'edit') {
          initializeForm()
        }
      }
    } catch {
      errorStore.addMessage('error', 'Failed to load required data')
    } finally {
      loadingStore.hide()
    }
  })
</script>
