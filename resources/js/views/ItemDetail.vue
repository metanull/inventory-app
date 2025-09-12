<template>
  <DetailView
    :store-loading="itemStore.loading"
    :resource="mode === 'create' ? null : item"
    :mode="mode"
    :save-disabled="!hasUnsavedChanges"
    :has-unsaved-changes="hasUnsavedChanges"
    :back-link="backLink"
    :create-title="'New Item'"
    :create-subtitle="'(Creating)'"
    information-title="Item Information"
    :information-description="informationDescription"
    :fetch-data="fetchItem"
    @edit="enterEditMode"
    @save="saveItem"
    @cancel="cancelAction"
    @delete="deleteItem"
  >
    <template #resource-icon>
      <ItemIcon :class="`h-6 w-6 ${colorClasses.icon}`" />
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
            <DisplayText v-else>{{ item?.internal_name }}</DisplayText>
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
              <option value="">Select type...</option>
              <option value="object">Object</option>
              <option value="monument">Monument</option>
            </select>
            <div v-else class="flex items-center">
              <span
                :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                  colorClasses.badgeBackground,
                  colorClasses.badge,
                ]"
              >
                {{ item?.type === 'object' ? 'Object' : 'Monument' }}
              </span>
            </div>
          </DescriptionDetail>
        </DescriptionRow>
        <DescriptionRow variant="gray">
          <DescriptionTerm>Partner</DescriptionTerm>
          <DescriptionDetail>
            <GenericDropdown
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.partner_id"
              :options="partnerOptions"
              :show-no-default-option="true"
              no-default-label="No partner selected"
              no-default-value=""
            />
            <DisplayText v-else>{{ item?.partner?.internal_name || '—' }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>
        <DescriptionRow variant="white">
          <DescriptionTerm>Project</DescriptionTerm>
          <DescriptionDetail>
            <GenericDropdown
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.project_id"
              :options="projectOptions"
              :show-no-default-option="true"
              no-default-label="No project selected"
              no-default-value=""
            />
            <DisplayText v-else>{{ item?.project?.internal_name || '—' }}</DisplayText>
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
            <DisplayText v-else>{{ item?.country?.internal_name || '—' }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>
        <DescriptionRow
          v-if="item?.backward_compatibility || mode === 'edit' || mode === 'create'"
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
            <DisplayText v-else>{{ item?.backward_compatibility }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>
        <DescriptionRow v-if="item?.created_at" variant="gray">
          <DescriptionTerm>Created</DescriptionTerm>
          <DescriptionDetail>
            <DateDisplay :date="item.created_at" format="medium" variant="small-dark" />
          </DescriptionDetail>
        </DescriptionRow>
        <DescriptionRow v-if="item?.updated_at" variant="white">
          <DescriptionTerm>Last Updated</DescriptionTerm>
          <DescriptionDetail>
            <DateDisplay :date="item.updated_at" format="medium" variant="small-dark" />
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
  import { ArchiveBoxIcon as ItemIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline'
  import { useItemStore } from '@/stores/item'
  import { usePartnerStore } from '@/stores/partner'
  import { useProjectStore } from '@/stores/project'
  import { useCountryStore } from '@/stores/country'
  import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
  import { useErrorDisplayStore } from '@/stores/errorDisplay'
  import { useCancelChangesConfirmationStore } from '@/stores/cancelChangesConfirmation'
  import { useDeleteConfirmationStore } from '@/stores/deleteConfirmation'
  import { useColors, type ColorName } from '@/composables/useColors'
  import type {
    ItemStoreRequest,
    ItemStoreRequestTypeEnum,
  } from '@metanull/inventory-app-api-client'

  // Types
  type Mode = 'view' | 'edit' | 'create'

  interface ItemFormData {
    id?: string
    internal_name: string
    backward_compatibility: string
    type: string
    partner_id: string
    project_id: string
    country_id: string
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
  const itemStore = useItemStore()
  const partnerStore = usePartnerStore()
  const projectStore = useProjectStore()
  const countryStore = useCountryStore()
  const loadingStore = useLoadingOverlayStore()
  const errorStore = useErrorDisplayStore()
  const deleteStore = useDeleteConfirmationStore()
  const cancelChangesStore = useCancelChangesConfirmationStore()

  // Reactive state - Single source of truth for mode
  const mode = ref<Mode>('view')

  // Computed properties
  const item = computed(() => itemStore.currentItem)

  const editForm = ref<ItemFormData>({
    id: '',
    internal_name: '',
    backward_compatibility: '',
    type: '',
    partner_id: '',
    project_id: '',
    country_id: '',
  })

  // Information description based on mode
  const informationDescription = computed(() => {
    switch (mode.value) {
      case 'create':
        return 'Create a new item in your inventory system.'
      case 'edit':
        return 'Edit detailed information about this item.'
      default:
        return 'Detailed information about this item.'
    }
  })

  // Back link configuration
  const backLink = computed(() => ({
    title: 'Back to Items',
    route: '/items',
    icon: ArrowLeftIcon,
    color: props.color,
  }))

  // Dropdown options
  const partnerOptions = computed(() =>
    (partnerStore.partners || []).map(partner => ({
      id: partner.id,
      internal_name: partner.internal_name,
      is_default: false,
    }))
  )

  const projectOptions = computed(() =>
    (projectStore.projects || []).map(project => ({
      id: project.id,
      internal_name: project.internal_name,
      is_default: false,
    }))
  )

  const countryOptions = computed(() =>
    (countryStore.countries || []).map(country => ({
      id: country.id,
      internal_name: country.internal_name,
      is_default: false,
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
        editForm.value.backward_compatibility !== defaultValues.backward_compatibility ||
        editForm.value.type !== defaultValues.type ||
        editForm.value.partner_id !== defaultValues.partner_id ||
        editForm.value.project_id !== defaultValues.project_id ||
        editForm.value.country_id !== defaultValues.country_id
      )
    }

    // For edit mode, compare with original values
    if (!item.value) return false

    const originalValues = getFormValuesFromItem()
    return (
      editForm.value.internal_name !== originalValues.internal_name ||
      editForm.value.backward_compatibility !== originalValues.backward_compatibility ||
      editForm.value.type !== originalValues.type ||
      editForm.value.partner_id !== originalValues.partner_id ||
      editForm.value.project_id !== originalValues.project_id ||
      editForm.value.country_id !== originalValues.country_id
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

  // Initialize edit form from item data
  const getDefaultFormValues = (): ItemFormData => ({
    id: '',
    internal_name: '',
    backward_compatibility: '',
    type: '',
    partner_id: '',
    project_id: '',
    country_id: '',
  })

  const getFormValuesFromItem = (): ItemFormData => {
    if (!item.value) return getDefaultFormValues()

    return {
      id: item.value.id,
      internal_name: item.value.internal_name,
      backward_compatibility: item.value.backward_compatibility || '',
      type: item.value.type,
      partner_id: item.value.partner?.id || '',
      project_id: item.value.project?.id || '',
      country_id: item.value.country?.id || '',
    }
  }

  // Fetch item function
  const fetchItem = async () => {
    const itemId = route.params.id as string
    if (!itemId || mode.value === 'create') return

    try {
      loadingStore.show()
      await itemStore.fetchItem(itemId)
    } catch {
      errorStore.addMessage(
        'error',
        'Failed to load item. The item may not exist or you may not have permission to view it.'
      )
      router.push({ name: 'items' })
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
    if (!item.value) return
    mode.value = 'edit'
    editForm.value = getFormValuesFromItem()
  }

  const enterViewMode = () => {
    mode.value = 'view'
    // Clear form data when returning to view mode
    editForm.value = getDefaultFormValues()
  }

  // Action handlers
  const saveItem = async () => {
    try {
      loadingStore.show(mode.value === 'create' ? 'Creating...' : 'Saving...')

      const itemData: ItemStoreRequest = {
        internal_name: editForm.value.internal_name,
        backward_compatibility: editForm.value.backward_compatibility || null,
        type: editForm.value.type as ItemStoreRequestTypeEnum,
        partner_id: editForm.value.partner_id || null,
        project_id: editForm.value.project_id || null,
        country_id: editForm.value.country_id || null,
      }

      if (mode.value === 'create') {
        const savedItem = await itemStore.createItem(itemData)
        errorStore.addMessage('info', 'Item created successfully.')

        // Reset changes to prevent navigation guard triggering
        cancelChangesStore.resetChanges()

        // Load the new item and enter view mode
        await itemStore.fetchItem(savedItem.id)
        enterViewMode()
      } else if (mode.value === 'edit' && item.value) {
        // Update existing item
        await itemStore.updateItem(item.value.id, itemData)
        errorStore.addMessage('info', 'Item updated successfully.')

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
        `Failed to ${mode.value === 'create' ? 'create' : 'update'} item. Please try again.`
      )
    } finally {
      loadingStore.hide()
    }
  }

  const cancelAction = async () => {
    if (hasUnsavedChanges.value) {
      const result = await cancelChangesStore.trigger(
        mode.value === 'create' ? 'New Item has unsaved changes' : 'Item has unsaved changes',
        mode.value === 'create'
          ? 'There are unsaved changes to this new item. If you navigate away, the changes will be lost. Are you sure you want to navigate away? This action cannot be undone.'
          : 'There are unsaved changes to this item. If you navigate away, the changes will be lost. Are you sure you want to navigate away? This action cannot be undone.'
      )

      if (result === 'stay') {
        if (mode.value === 'create') {
          // For create mode, navigate back to items list
          router.push({ name: 'items' })
        } else {
          // For edit mode, return to view mode and remove query parameter
          enterViewMode()
          if (route.query.edit) {
            const query = { ...route.query }
            delete query.edit
            await router.replace({ query })
          }
        }
      }
    } else {
      // No unsaved changes, proceed directly
      if (mode.value === 'create') {
        router.push({ name: 'items' })
      } else {
        enterViewMode()
        if (route.query.edit) {
          const query = { ...route.query }
          delete query.edit
          await router.replace({ query })
        }
      }
    }
  }

  const deleteItem = async () => {
    if (!item.value) return

    const result = await deleteStore.trigger(
      'Delete Item',
      `Are you sure you want to delete "${item.value.internal_name}"? This action cannot be undone.`
    )

    if (result === 'delete') {
      try {
        loadingStore.show('Deleting...')
        await itemStore.deleteItem(item.value.id)
        errorStore.addMessage('info', 'Item deleted successfully.')
        router.push({ name: 'items' })
      } catch {
        errorStore.addMessage('error', 'Failed to delete item. Please try again.')
      } finally {
        loadingStore.hide()
      }
    }
  }

  // Initialize component
  const initializeComponent = async () => {
    const itemId = route.params.id as string
    const isCreateRoute = route.name === 'item-new' || route.path === '/items/new'

    try {
      if (isCreateRoute) {
        // Clear current item to avoid showing stale data from previously viewed items
        itemStore.clearCurrentItem()

        // For create mode, fetch dropdown options
        await Promise.all([
          partnerStore.fetchPartners(),
          projectStore.fetchProjects(),
          countryStore.fetchCountries(),
        ])
        enterCreateMode()
      } else if (itemId) {
        // For view/edit mode, fetch item data and dropdown options
        await Promise.all([
          fetchItem(),
          partnerStore.fetchPartners(),
          projectStore.fetchProjects(),
          countryStore.fetchCountries(),
        ])

        // Check if we should start in edit mode from query parameter
        if (route.query.edit === 'true' && item.value) {
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
      if (hasUnsavedChanges.value) {
        const result = await cancelChangesStore.trigger(
          'Item has unsaved changes',
          'There are unsaved changes to this item. If you navigate away, the changes will be lost. Are you sure you want to navigate away? This action cannot be undone.'
        )

        if (result === 'stay') {
          next(false) // Block navigation
        } else {
          next() // Allow navigation
        }
      } else {
        next() // Allow navigation if no unsaved changes
      }
    }
  )

  // Initialize on mount
  onMounted(initializeComponent)

  // Watch for route changes to handle navigation between different items or create/edit modes
  watch(() => route.params.id, initializeComponent)
  watch(() => route.query.edit, initializeComponent)
</script>
