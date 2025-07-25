<template>
  <!-- Unified Project Detail View -->
  <DetailView
    :store-loading="projectStore.loading"
    :resource="mode === 'create' ? null : project"
    :mode="mode"
    :save-disabled="!hasUnsavedChanges"
    :has-unsaved-changes="hasUnsavedChanges"
    :back-link="backLink"
    :status-cards="statusCardsConfig"
    :create-title="'New Project'"
    :create-subtitle="'(Creating)'"
    information-title="Project Information"
    :information-description="informationDescription"
    :fetch-data="fetchProject"
    @edit="enterEditMode"
    @save="saveProject"
    @cancel="cancelAction"
    @delete="deleteProject"
    @status-toggle="handleStatusToggle"
  >
    <template #resource-icon>
      <ProjectIcon class="h-6 w-6 text-orange-600" />
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
            <DisplayText v-else>{{ project?.internal_name }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>
        <DescriptionRow
          v-if="project?.backward_compatibility || mode === 'edit' || mode === 'create'"
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
            <DisplayText v-else>{{ project?.backward_compatibility }}</DisplayText>
          </DescriptionDetail>
        </DescriptionRow>
        <DescriptionRow variant="gray">
          <DescriptionTerm>Launch Date</DescriptionTerm>
          <DescriptionDetail>
            <FormInput
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.launch_date"
              type="date"
            />
            <template v-else>
              <DateDisplay
                v-if="project?.launch_date"
                :date="project.launch_date"
                format="medium"
                variant="small-dark"
              />
              <DisplayText v-else variant="gray">Not scheduled</DisplayText>
            </template>
          </DescriptionDetail>
        </DescriptionRow>
        <DescriptionRow variant="white">
          <DescriptionTerm>Default Context</DescriptionTerm>
          <DescriptionDetail>
            <GenericDropdown
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.context_id"
              :options="contexts"
              :show-no-default-option="true"
              no-default-label="No default context"
              no-default-value=""
            />
            <template v-else>
              <DisplayText v-if="project?.context">{{ project.context.internal_name }}</DisplayText>
              <DisplayText v-else variant="gray">No default context set</DisplayText>
            </template>
          </DescriptionDetail>
        </DescriptionRow>
        <DescriptionRow variant="gray">
          <DescriptionTerm>Default Language</DescriptionTerm>
          <DescriptionDetail>
            <GenericDropdown
              v-if="mode === 'edit' || mode === 'create'"
              v-model="editForm.language_id"
              :options="languages"
              :show-no-default-option="true"
              no-default-label="No default language"
              no-default-value=""
            />
            <template v-else>
              <DisplayText v-if="project?.language">{{
                project.language.internal_name
              }}</DisplayText>
              <DisplayText v-else variant="gray">No default language set</DisplayText>
            </template>
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
  import { useProjectStore } from '@/stores/project'
  import { useContextStore } from '@/stores/context'
  import { useLanguageStore } from '@/stores/language'
  import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
  import { useErrorDisplayStore } from '@/stores/errorDisplay'
  import { useDeleteConfirmationStore } from '@/stores/deleteConfirmation'
  import { useCancelChangesConfirmationStore } from '@/stores/cancelChangesConfirmation'
  import DateDisplay from '@/components/format/Date.vue'
  import DisplayText from '@/components/format/DisplayText.vue'
  import DetailView from '@/components/layout/detail/DetailView.vue'
  import GenericDropdown from '@/components/format/GenericDropdown.vue'
  import FormInput from '@/components/format/FormInput.vue'
  import DescriptionList from '@/components/format/description/DescriptionList.vue'
  import DescriptionRow from '@/components/format/description/DescriptionRow.vue'
  import DescriptionTerm from '@/components/format/description/DescriptionTerm.vue'
  import DescriptionDetail from '@/components/format/description/DescriptionDetail.vue'
  import {
    CheckCircleIcon,
    XCircleIcon,
    RocketLaunchIcon as RocketIcon,
    ArchiveBoxIcon as PackageIcon,
    FolderIcon as ProjectIcon,
    ArrowLeftIcon,
  } from '@heroicons/vue/24/solid'

  // Types
  type Mode = 'view' | 'edit' | 'create'

  interface ProjectFormData {
    internal_name: string
    backward_compatibility: string
    launch_date: string
    context_id: string
    language_id: string
  }

  // Composables
  const route = useRoute()
  const router = useRouter()
  const projectStore = useProjectStore()
  const contextStore = useContextStore()
  const languageStore = useLanguageStore()
  const loadingStore = useLoadingOverlayStore()
  const errorStore = useErrorDisplayStore()
  const deleteStore = useDeleteConfirmationStore()
  const cancelChangesStore = useCancelChangesConfirmationStore()

  // Reactive state - Single source of truth for mode
  const mode = ref<Mode>('view')

  // Computed properties
  const project = computed(() => projectStore.currentProject)

  // Dropdown options
  const contexts = computed(() => contextStore.contexts)
  const languages = computed(() => languageStore.languages)
  const defaultContext = computed(() => contextStore.defaultContext)
  const defaultLanguage = computed(() => languageStore.defaultLanguage)

  // Information description based on mode
  const informationDescription = computed(() => {
    switch (mode.value) {
      case 'create':
        return 'Create a new project in your inventory system.'
      case 'edit':
        return 'Edit detailed information about this project.'
      default:
        return 'Detailed information about this project.'
    }
  })

  // Back link configuration
  const backLink = computed(() => ({
    title: 'Back to Projects',
    route: '/projects',
    icon: ArrowLeftIcon,
    color: 'orange',
  }))

  // Edit form data
  const editForm = ref<ProjectFormData>({
    internal_name: '',
    backward_compatibility: '',
    launch_date: '',
    context_id: '',
    language_id: '',
  })

  // Get default form values
  const getDefaultFormValues = (): ProjectFormData => ({
    internal_name: '',
    backward_compatibility: '',
    launch_date: '',
    context_id: defaultContext.value?.id || '',
    language_id: defaultLanguage.value?.id || '',
  })

  // Get form values from project
  const getFormValuesFromProject = (): ProjectFormData => {
    if (!project.value) return getDefaultFormValues()

    // Format launch_date for HTML date input (YYYY-MM-DD)
    let formattedLaunchDate = ''
    if (project.value.launch_date) {
      formattedLaunchDate = project.value.launch_date.split('T')[0]
    }

    return {
      internal_name: project.value.internal_name,
      backward_compatibility: project.value.backward_compatibility || '',
      launch_date: formattedLaunchDate,
      context_id: project.value.context?.id || '',
      language_id: project.value.language?.id || '',
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
        editForm.value.backward_compatibility !== defaultValues.backward_compatibility ||
        editForm.value.launch_date !== defaultValues.launch_date ||
        editForm.value.context_id !== defaultValues.context_id ||
        editForm.value.language_id !== defaultValues.language_id
      )
    }

    // For edit mode, compare with original values
    if (!project.value) return false

    const originalValues = getFormValuesFromProject()
    return (
      editForm.value.internal_name !== originalValues.internal_name ||
      editForm.value.backward_compatibility !== originalValues.backward_compatibility ||
      editForm.value.launch_date !== originalValues.launch_date ||
      editForm.value.context_id !== originalValues.context_id ||
      editForm.value.language_id !== originalValues.language_id
    )
  })

  // Status cards configuration
  const statusCardsConfig = computed(() => {
    if (!project.value) return []

    return [
      {
        title: 'Status',
        description: 'Project status and availability',
        mainColor: 'green',
        statusText: project.value.is_enabled ? 'Enabled' : 'Disabled',
        toggleTitle: 'Project Status',
        isActive: project.value.is_enabled,
        loading: false,
        disabled: false,
        activeIconBackgroundClass: 'bg-green-100',
        inactiveIconBackgroundClass: 'bg-red-100',
        activeIconClass: 'text-green-600',
        inactiveIconClass: 'text-red-600',
        activeIconComponent: CheckCircleIcon,
        inactiveIconComponent: XCircleIcon,
      },
      {
        title: 'Launch Status',
        description: 'Project launch status',
        mainColor: 'blue',
        statusText: project.value.is_launched ? 'Launched' : 'Not Launched',
        toggleTitle: 'Launch Status',
        isActive: project.value.is_launched,
        loading: false,
        disabled: !project.value.is_enabled,
        activeIconBackgroundClass: 'bg-blue-100',
        inactiveIconBackgroundClass: 'bg-gray-100',
        activeIconClass: 'text-blue-600',
        inactiveIconClass: 'text-gray-600',
        activeIconComponent: RocketIcon,
        inactiveIconComponent: PackageIcon,
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
    if (!project.value) return
    mode.value = 'edit'
    editForm.value = getFormValuesFromProject()
  }

  const enterViewMode = () => {
    mode.value = 'view'
    // Clear form data when returning to view mode
    editForm.value = getDefaultFormValues()
  }

  // Action handlers
  const cancelAction = async () => {
    if (mode.value === 'create') {
      // Navigate back to projects list
      router.push('/projects')
      return
    }

    if (mode.value === 'edit') {
      // "Navigate" back to project detail in view mode
      enterViewMode()

      // Remove edit query parameter if present
      if (route.query.edit) {
        const query = { ...route.query }
        delete query.edit
        router.replace({ query })
      }
    }
  }

  const saveProject = async () => {
    try {
      loadingStore.show('Saving...')
      const projectData = {
        internal_name: editForm.value.internal_name,
        backward_compatibility: editForm.value.backward_compatibility || null,
        launch_date: editForm.value.launch_date || null,
        context_id: editForm.value.context_id || null,
        language_id: editForm.value.language_id || null,
      }

      if (mode.value === 'create') {
        // Create new project
        const newProject = await projectStore.createProject(projectData)
        errorStore.addMessage('info', 'Project created successfully.')

        // Load the new project and enter view mode
        await projectStore.fetchProject(newProject.id)
        enterViewMode()
      } else if (mode.value === 'edit' && project.value) {
        // Update existing project
        await projectStore.updateProject(project.value.id, projectData)
        errorStore.addMessage('info', 'Project updated successfully.')

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
        'Failed to save project. Please check your input and try again.'
      )
    } finally {
      loadingStore.hide()
    }
  }

  const deleteProject = async () => {
    if (project.value?.id) {
      const result = await deleteStore.trigger(
        'Delete Project',
        `Are you sure you want to delete "${project.value.internal_name}"? This action cannot be undone.`
      )

      if (result === 'delete') {
        try {
          loadingStore.show('Deleting...')
          await projectStore.deleteProject(project.value.id)
          errorStore.addMessage('info', 'Project deleted successfully.')
          router.push('/projects')
        } catch {
          errorStore.addMessage('error', 'Failed to delete project. Please try again.')
        } finally {
          loadingStore.hide()
        }
      }
    }
  }

  // Status toggle handlers
  const toggleEnabled = async () => {
    if (!project.value) return

    try {
      loadingStore.show('Updating...')
      const newStatus = !project.value.is_enabled
      await projectStore.setProjectEnabled(project.value.id, newStatus)
      errorStore.addMessage('info', `Project ${newStatus ? 'enabled' : 'disabled'} successfully.`)
    } catch {
      errorStore.addMessage('error', 'Failed to update project status. Please try again.')
    } finally {
      loadingStore.hide()
    }
  }

  const toggleLaunched = async () => {
    if (!project.value) return

    try {
      loadingStore.show('Updating...')
      const newStatus = !project.value.is_launched
      await projectStore.setProjectLaunched(project.value.id, newStatus)
      errorStore.addMessage(
        'info',
        `Project ${newStatus ? 'launched' : 'unlaunched'} successfully.`
      )
    } catch {
      errorStore.addMessage('error', 'Failed to update project launch status. Please try again.')
    } finally {
      loadingStore.hide()
    }
  }

  const handleStatusToggle = async (index: number) => {
    if (index === 0) {
      await toggleEnabled()
    } else if (index === 1) {
      await toggleLaunched()
    }
  }

  // Fetch project function
  const fetchProject = async () => {
    const projectId = route.params.id as string
    if (!projectId || mode.value === 'create') return

    try {
      loadingStore.show()
      await projectStore.fetchProject(projectId)
    } catch {
      errorStore.addMessage('error', 'Failed to load project. Please try again.')
    } finally {
      loadingStore.hide()
    }
  }

  // Initialize component
  const initializeComponent = async () => {
    const projectId = route.params.id as string
    const isCreateRoute = route.name === 'project-new' || route.path === '/projects/new'

    try {
      if (isCreateRoute) {
        // Clear current project to avoid showing stale data from previously viewed projects
        projectStore.clearCurrentProject()

        // For create mode, only fetch dropdown options
        await Promise.all([contextStore.fetchContexts(), languageStore.fetchLanguages()])
        enterCreateMode()
      } else if (projectId) {
        // For view/edit mode, fetch project data and dropdown options
        await Promise.all([
          fetchProject(),
          contextStore.fetchContexts(),
          languageStore.fetchLanguages(),
        ])

        // Check if we should start in edit mode from query parameter
        if (route.query.edit === 'true' && project.value) {
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
            ? 'New Project has unsaved changes'
            : 'Project has unsaved changes',
          mode.value === 'create'
            ? 'There are unsaved changes to this new project. If you navigate away, the changes will be lost. Are you sure you want to navigate away? This action cannot be undone.'
            : `There are unsaved changes to "${project.value?.internal_name}". If you navigate away, the changes will be lost. Are you sure you want to navigate away? This action cannot be undone.`
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
    project,
    editForm,
    hasUnsavedChanges,
    informationDescription,
    enterEditMode,
    enterViewMode,
    saveProject,
    cancelAction,
    deleteProject,
  })
</script>
