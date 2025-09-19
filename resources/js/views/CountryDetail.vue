<template>
  <!-- Unified Country Detail View -->
  <DetailView
    :store-loading="countryStore.loading"
    :resource="mode === 'create' ? null : country"
    :mode="mode"
    :save-disabled="!hasUnsavedChanges"
    :has-unsaved-changes="hasUnsavedChanges"
    :back-link="backLink"
    :create-title="'New Country'"
    :create-subtitle="'(Creating)'"
    information-title="Country Information"
    :information-description="informationDescription"
    :fetch-data="fetchCountry"
    @edit="enterEditMode"
    @save="saveCountry"
    @cancel="cancelAction"
    @delete="deleteCountry"
  >
    <template #resource-icon>
      <CountryIcon :class="`h-6 w-6 ${colorClasses.icon}`" />
    </template>
    <template #information>
      <DescriptionList>
        <DescriptionRow variant="gray">
          <DescriptionTerm>Country ID</DescriptionTerm>
          <DescriptionDetail>
            <FormInput
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.id"
              type="text"
              placeholder="ISO country code (e.g., GBR)"
              :disabled="mode === 'edit'"
            />
            <DisplayText v-else>{{ country?.id }}</DisplayText>
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
            <DisplayText v-else>{{ country?.internal_name }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>
        <DescriptionRow
          v-if="country?.backward_compatibility || mode === 'edit' || mode === 'create'"
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
            <DisplayText v-else>{{ country?.backward_compatibility }}</DisplayText>
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
    type NavigationGuardNext,
    type RouteLocationNormalized,
  } from 'vue-router'
  import type {
    CountryStoreRequest,
    CountryUpdateRequest,
  } from '@metanull/inventory-app-api-client'
  import { useCountryStore } from '@/stores/country'
  import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
  import { useCancelChangesConfirmationStore } from '@/stores/cancelChangesConfirmation'
  import { useDeleteConfirmationStore } from '@/stores/deleteConfirmation'
  import { useErrorDisplayStore } from '@/stores/errorDisplay'
  import DetailView from '@/components/layout/detail/DetailView.vue'
  import DescriptionList from '@/components/format/description/DescriptionList.vue'
  import DescriptionRow from '@/components/format/description/DescriptionRow.vue'
  import DescriptionTerm from '@/components/format/description/DescriptionTerm.vue'
  import DescriptionDetail from '@/components/format/description/DescriptionDetail.vue'
  import FormInput from '@/components/format/FormInput.vue'
  import DisplayText from '@/components/format/DisplayText.vue'
  import { GlobeAltIcon as CountryIcon } from '@heroicons/vue/24/solid'
  import { ArrowLeftIcon } from '@heroicons/vue/24/outline'
  import { useColors, type ColorName } from '@/composables/useColors'

  // Types
  type Mode = 'view' | 'edit' | 'create'

  interface CountryFormData {
    id: string
    internal_name: string
    backward_compatibility: string
  }

  interface Props {
    color?: ColorName
  }

  const props = withDefaults(defineProps<Props>(), {
    color: 'blue',
  })

  // Color classes from centralized system
  const colorClasses = useColors(computed(() => props.color))

  const route = useRoute()
  const router = useRouter()
  const countryStore = useCountryStore()
  const loadingStore = useLoadingOverlayStore()
  const cancelChangesStore = useCancelChangesConfirmationStore()
  const deleteConfirmationStore = useDeleteConfirmationStore()
  const errorStore = useErrorDisplayStore()

  // Route params
  const countryId = computed(() => {
    const id = route.params.id
    return Array.isArray(id) ? id[0] : id
  })

  // Mode determination
  const mode = ref<Mode>('view')

  // Determine mode from route
  if (countryId.value === 'new') {
    mode.value = 'create'
  }

  // Resource data
  const country = computed(() => countryStore.currentCountry)

  // Edit form state
  const editForm = ref<CountryFormData>({
    id: '',
    internal_name: '',
    backward_compatibility: '',
  })

  // Navigation
  const backLink = computed(() => ({
    title: 'Back to Countries',
    route: '/countries',
    icon: ArrowLeftIcon,
    color: props.color,
  }))

  // Information description
  const informationDescription = computed(() => {
    if (mode.value === 'create') {
      return 'Enter the country details below.'
    }
    return 'Country details and metadata.'
  })

  // Unsaved changes tracking
  const hasUnsavedChanges = computed(() => {
    if (mode.value === 'view') return false
    if (mode.value === 'create') {
      return editForm.value.internal_name.trim() !== ''
    }
    if (!country.value) return false
    return (
      editForm.value.internal_name !== country.value.internal_name ||
      editForm.value.backward_compatibility !== (country.value.backward_compatibility || '')
    )
  })

  // Methods
  const fetchCountry = async () => {
    const countryId = route.params.id as string

    if (!countryId || countryId === 'new') return

    try {
      loadingStore.show()
      await countryStore.fetchCountry(countryId)
    } catch {
      errorStore.addMessage('error', 'Failed to fetch country data. Please try again.')
    } finally {
      loadingStore.hide()
    }
  }

  // Mode management functions

  const enterCreateMode = () => {
    mode.value = 'create'
    editForm.value = {
      id: '',
      internal_name: '',
      backward_compatibility: '',
    }
  }

  const enterEditMode = () => {
    if (!country.value) return
    editForm.value = {
      id: country.value.id,
      internal_name: country.value.internal_name,
      backward_compatibility: country.value.backward_compatibility || '',
    }
    mode.value = 'edit'
  }

  const enterViewMode = () => {
    mode.value = 'view'
    // Clear form data when returning to view mode
    if (country.value) {
      editForm.value = {
        id: country.value.id,
        internal_name: country.value.internal_name,
        backward_compatibility: country.value.backward_compatibility || '',
      }
    }
  }

  const saveCountry = async () => {
    try {
      loadingStore.show('Saving...')
      if (mode.value === 'create') {
        const createData: CountryStoreRequest = {
          id: editForm.value.id,
          internal_name: editForm.value.internal_name,
          backward_compatibility: editForm.value.backward_compatibility || undefined,
        }
        const newCountry = await countryStore.createCountry(createData)
        if (newCountry) {
          errorStore.addMessage('info', 'Country created successfully.')
          await router.push({ name: 'country-detail', params: { id: newCountry.id } })
          mode.value = 'view'
        }
      } else if (mode.value === 'edit' && country.value) {
        const updateData: CountryUpdateRequest = {
          internal_name: editForm.value.internal_name,
          backward_compatibility: editForm.value.backward_compatibility || undefined,
        }
        const updatedCountry = await countryStore.updateCountry(country.value.id, updateData)
        if (updatedCountry) {
          errorStore.addMessage('info', 'Country updated successfully.')
          mode.value = 'view'

          // Remove edit query parameter if present
          if (route.query.edit) {
            const query = { ...route.query }
            delete query.edit
            await router.replace({ query })
          }
        }
      }
    } catch {
      errorStore.addMessage('error', 'Failed to save country. Please try again.')
    } finally {
      loadingStore.hide()
    }
  }

  const cancelAction = async () => {
    if (hasUnsavedChanges.value) {
      const result = await cancelChangesStore.trigger(
        mode.value === 'create' ? 'New Country has unsaved changes' : 'Country has unsaved changes',
        mode.value === 'create'
          ? 'There are unsaved changes to this new country. If you navigate away, the changes will be lost. Are you sure you want to navigate away? This action cannot be undone.'
          : `There are unsaved changes to "${country.value?.internal_name}". If you navigate away, the changes will be lost. Are you sure you want to navigate away? This action cannot be undone.`
      )

      if (result === 'stay') {
        return // Cancel navigation
      } else {
        cancelChangesStore.resetChanges() // Reset changes before leaving
      }
    }

    if (mode.value === 'create') {
      router.push({ name: 'countries' })
    } else {
      enterViewMode()
    }
  }

  const deleteCountry = async () => {
    if (!country.value) return
    const result = await deleteConfirmationStore.trigger(
      'Delete Country',
      `Are you sure you want to delete "${country.value.internal_name}"? This action cannot be undone.`
    )
    if (result === 'delete') {
      try {
        loadingStore.show('Deleting...')
        await countryStore.deleteCountry(country.value.id)
        errorStore.addMessage('info', 'Country deleted successfully.')
        await router.push({ name: 'countries' })
      } catch {
        errorStore.addMessage('error', 'Failed to delete country. Please try again.')
      } finally {
        loadingStore.hide()
      }
    }
  }

  // Initialize component based on route
  const initializeComponent = async () => {
    loadingStore.show()

    try {
      // Determine mode and initialize
      const isCreateMode = route.path.includes('/new') || route.params.id === 'new'
      const isEditMode = route.query.edit === 'true'

      if (isCreateMode) {
        enterCreateMode()
      } else {
        const countryId = route.params.id as string
        if (countryId) {
          await fetchCountry()
          if (isEditMode) {
            enterEditMode()
          } else {
            enterViewMode()
          }
        }
      }
    } catch {
      errorStore.addMessage('error', 'Failed to load country data. Please try again.')
    } finally {
      loadingStore.hide()
    }
  }

  // Initialize component on mount
  onMounted(initializeComponent)

  // Watch for route changes
  watch(
    () => route.params.id,
    async newId => {
      if (newId === 'new') {
        enterCreateMode()
      } else if (typeof newId === 'string') {
        await fetchCountry()
        enterViewMode()
      }
    }
  )

  // Watch for route query changes
  watch(
    () => route.query.edit,
    editQuery => {
      if (editQuery === 'true' && mode.value === 'view') {
        enterEditMode()
      } else if (editQuery !== 'true' && mode.value === 'edit') {
        enterViewMode()
      }
    }
  )

  // Update edit form when country data changes
  watch(
    country,
    newCountry => {
      if (newCountry && mode.value === 'view') {
        editForm.value = {
          id: newCountry.id,
          internal_name: newCountry.internal_name,
          backward_compatibility: newCountry.backward_compatibility || '',
        }
      }
    },
    { immediate: true }
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
            ? 'New Country has unsaved changes'
            : 'Country has unsaved changes',
          mode.value === 'create'
            ? 'There are unsaved changes to this new country. If you navigate away, the changes will be lost. Are you sure you want to navigate away? This action cannot be undone.'
            : `There are unsaved changes to "${country.value?.internal_name}". If you navigate away, the changes will be lost. Are you sure you want to navigate away? This action cannot be undone.`
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
