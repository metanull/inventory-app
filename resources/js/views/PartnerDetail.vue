<template>
  <DetailView
    :store-loading="partnerStore.loading"
    :resource="mode === 'create' ? null : partner"
    :mode="mode"
    :save-disabled="!hasUnsavedChanges"
    :has-unsaved-changes="hasUnsavedChanges"
    :back-link="backLink"
    :create-title="'New Partner'"
    :create-subtitle="'(Creating)'"
    information-title="Partner Information"
    :information-description="informationDescription"
    :fetch-data="fetchPartner"
    @edit="enterEditMode"
    @save="savePartner"
    @cancel="cancelAction"
    @delete="deletePartner"
  >
    <template #resource-icon>
      <UserGroupIcon :class="['h-6 w-6', colorClasses.icon]" />
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
            <DisplayText v-else>{{ partner?.internal_name }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>
        <DescriptionRow variant="white">
          <DescriptionTerm>Type</DescriptionTerm>
          <DescriptionDetail>
            <select
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.type"
              :class="[
                'block w-full px-3 py-2 rounded-md shadow-sm sm:text-sm',
                colorClasses.border,
                colorClasses.focus,
              ]"
            >
              <option value="museum">Museum</option>
              <option value="institution">Institution</option>
              <option value="individual">Individual</option>
            </select>
            <span
              v-else
              :class="[
                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                colorClasses!.badge,
              ]"
            >
              {{
                partner?.type ? partner.type.charAt(0).toUpperCase() + partner.type.slice(1) : ''
              }}
            </span>
          </DescriptionDetail>
        </DescriptionRow>
        <DescriptionRow variant="gray">
          <DescriptionTerm>Country</DescriptionTerm>
          <DescriptionDetail>
            <GenericDropdown
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.country_id"
              :options="countryOptions"
              :show-no-default-option="true"
              no-default-label="No country selected"
              no-default-value=""
            />
            <DisplayText v-else>{{ partner?.country?.internal_name || '—' }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>
        <DescriptionRow
          v-if="partner?.backward_compatibility || mode === 'edit' || mode === 'create'"
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
            <DisplayText v-else>{{ partner?.backward_compatibility }}</DisplayText>
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
  import { UserGroupIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline'
  import { usePartnerStore } from '@/stores/partner'
  import { useCountryStore } from '@/stores/country'
  import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
  import { useErrorDisplayStore } from '@/stores/errorDisplay'
  import { useCancelChangesConfirmationStore } from '@/stores/cancelChangesConfirmation'
  import { useDeleteConfirmationStore } from '@/stores/deleteConfirmation'
  import type { PartnerStoreRequestTypeEnum } from '@metanull/inventory-app-api-client'
  import { useColors, type ColorName } from '@/composables/useColors'

  // Types
  type Mode = 'view' | 'edit' | 'create'

  interface PartnerFormData {
    id?: string
    internal_name: string
    type: PartnerStoreRequestTypeEnum
    country_id: string
    backward_compatibility: string
  }

  interface Props {
    color?: ColorName
  }

  const props = withDefaults(defineProps<Props>(), {
    color: 'yellow',
  })

  // Color classes from centralized system
  const colorClasses = useColors(computed(() => props.color))

  // Composables
  const route = useRoute()
  const router = useRouter()
  const partnerStore = usePartnerStore()
  const countryStore = useCountryStore()
  const loadingStore = useLoadingOverlayStore()
  const errorStore = useErrorDisplayStore()
  const deleteStore = useDeleteConfirmationStore()
  const cancelChangesStore = useCancelChangesConfirmationStore()

  // Reactive state - Single source of truth for mode
  const mode = ref<Mode>('view')

  // Computed properties
  const partner = computed(() => partnerStore.currentPartner)
  const countries = computed(() => countryStore.countries)

  const editForm = ref<PartnerFormData>({
    id: '',
    internal_name: '',
    type: 'museum' as PartnerStoreRequestTypeEnum,
    country_id: '',
    backward_compatibility: '',
  })

  // Form options
  const countryOptions = computed(() =>
    countries.value.map(country => ({
      id: country.id,
      internal_name: country.internal_name,
      is_default: false,
    }))
  )

  // Information description based on mode
  const informationDescription = computed(() => {
    switch (mode.value) {
      case 'create':
        return 'Create a new partner in your inventory system.'
      case 'edit':
        return 'Edit detailed information about this partner.'
      default:
        return 'Detailed information about this partner.'
    }
  })

  // Back link configuration
  const backLink = computed(() => ({
    title: 'Back to Partners',
    route: '/partners',
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
        editForm.value.type !== defaultValues.type ||
        editForm.value.country_id !== defaultValues.country_id ||
        editForm.value.backward_compatibility !== defaultValues.backward_compatibility
      )
    }

    // For edit mode, compare with original values
    if (!partner.value) return false

    const originalValues = getFormValuesFromPartner()
    return (
      editForm.value.internal_name !== originalValues.internal_name ||
      editForm.value.type !== originalValues.type ||
      editForm.value.country_id !== originalValues.country_id ||
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

  // Initialize edit form from partner data
  const getDefaultFormValues = (): PartnerFormData => ({
    id: '',
    internal_name: '',
    type: 'museum' as PartnerStoreRequestTypeEnum,
    country_id: '',
    backward_compatibility: '',
  })

  const getFormValuesFromPartner = (): PartnerFormData => {
    if (!partner.value) return getDefaultFormValues()

    return {
      id: partner.value.id,
      internal_name: partner.value.internal_name,
      type: partner.value.type as PartnerStoreRequestTypeEnum,
      country_id: partner.value.country?.id || '',
      backward_compatibility: partner.value.backward_compatibility || '',
    }
  }

  // Fetch partner function
  const fetchPartner = async () => {
    const partnerId = route.params.id as string
    if (!partnerId || mode.value === 'create') return

    try {
      loadingStore.show()
      await partnerStore.fetchPartner(partnerId)
    } catch {
      errorStore.addMessage(
        'error',
        'Failed to load partner. The partner may not exist or you may not have permission to view it.'
      )
      router.push({ name: 'partners' })
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
    if (!partner.value) return
    mode.value = 'edit'
    editForm.value = getFormValuesFromPartner()
  }

  const enterViewMode = () => {
    mode.value = 'view'
    // Clear form data when returning to view mode
    editForm.value = getDefaultFormValues()
  }

  // Action handlers
  const savePartner = async () => {
    try {
      loadingStore.show(mode.value === 'create' ? 'Creating...' : 'Saving...')

      const partnerData = {
        internal_name: editForm.value.internal_name,
        type: editForm.value.type,
        country_id: editForm.value.country_id || null,
        backward_compatibility: editForm.value.backward_compatibility || null,
      }

      if (mode.value === 'create') {
        const savedPartner = await partnerStore.createPartner(partnerData)
        errorStore.addMessage('info', 'Partner created successfully.')

        // Load the new partner and enter view mode
        await partnerStore.fetchPartner(savedPartner.id)
        enterViewMode()
      } else if (mode.value === 'edit' && partner.value) {
        // Update existing partner
        await partnerStore.updatePartner(partner.value.id, partnerData)
        errorStore.addMessage('info', 'Partner updated successfully.')

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
        `Failed to ${mode.value === 'create' ? 'create' : 'update'} partner. Please try again.`
      )
    } finally {
      loadingStore.hide()
    }
  }

  const cancelAction = async () => {
    if (hasUnsavedChanges.value) {
      const result = await cancelChangesStore.trigger(
        mode.value === 'create' ? 'New Partner has unsaved changes' : 'Partner has unsaved changes',
        mode.value === 'create'
          ? 'There are unsaved changes to this new partner. If you navigate away, the changes will be lost. Are you sure you want to navigate away? This action cannot be undone.'
          : `There are unsaved changes to "${partner.value?.internal_name}". If you navigate away, the changes will be lost. Are you sure you want to navigate away? This action cannot be undone.`
      )

      if (result === 'stay') {
        return // Cancel navigation
      } else {
        cancelChangesStore.resetChanges() // Reset changes before leaving
      }
    }

    if (mode.value === 'create') {
      router.push({ name: 'partners' })
    } else {
      enterViewMode()
    }
  }

  const deletePartner = async () => {
    if (!partner.value || !partner.value.id) return

    const result = await deleteStore.trigger(
      'Delete Partner',
      `Are you sure you want to delete "${partner.value.internal_name}"? This action cannot be undone.`
    )

    if (result === 'delete') {
      try {
        loadingStore.show('Deleting...')
        await partnerStore.deletePartner(partner.value.id)
        errorStore.addMessage('info', 'Partner deleted successfully.')
        router.push({ name: 'partners' })
      } catch {
        errorStore.addMessage(
          'error',
          'Failed to delete partner. The partner may be in use or you may not have permission to delete it.'
        )
      } finally {
        loadingStore.hide()
      }
    }
  }

  // Initialize component
  const initializeComponent = async () => {
    const partnerId = route.params.id as string
    const isCreateRoute = route.name === 'partner-new' || route.path === '/partners/new'

    try {
      // Load countries for form dropdowns
      if (countries.value.length === 0) {
        await countryStore.fetchCountries()
      }

      if (isCreateRoute) {
        // Clear current partner to avoid showing stale data from previously viewed partners
        partnerStore.clearCurrentPartner()
        enterCreateMode()
      } else if (partnerId) {
        // For view/edit mode, fetch partner data
        await fetchPartner()

        // Check if we should start in edit mode from query parameter
        if (route.query.edit === 'true' && partner.value) {
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
            ? 'New Partner has unsaved changes'
            : 'Partner has unsaved changes',
          mode.value === 'create'
            ? 'There are unsaved changes to this new partner. If you navigate away, the changes will be lost. Are you sure you want to navigate away? This action cannot be undone.'
            : `There are unsaved changes to "${partner.value?.internal_name}". If you navigate away, the changes will be lost. Are you sure you want to navigate away? This action cannot be undone.`
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
