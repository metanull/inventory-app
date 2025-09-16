import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import {
  type ProjectResource,
  type ProjectStoreRequest,
  type ProjectSetEnabledRequest,
  type ProjectSetLaunchedRequest,
} from '@metanull/inventory-app-api-client'
import { ErrorHandler } from '@/utils/errorHandler'
import { useApiClient } from '@/composables/useApiClient'
import {
  type IndexQueryOptions,
  type ShowQueryOptions,
  buildIncludes,
  buildPagination,
  mergeParams,
  type PaginationMeta,
  extractPaginationMeta,
} from '@/utils/apiQueryParams'

export const useProjectStore = defineStore('project', () => {
  const projects = ref<ProjectResource[]>([])
  const visibleProjects = ref<ProjectResource[]>([])
  const currentProject = ref<ProjectResource | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const page = ref(1)
  const perPage = ref(20)
  const total = ref<number | null>(null)

  // Create API client instance with session-aware configuration
  const createApiClient = () => {
    return useApiClient().createProjectApi()
  }

  const enabledProjects = computed(() => projects.value.filter(project => project.is_enabled))
  const launchedProjects = computed(() => projects.value.filter(project => project.is_launched))
  const getProjectById = computed(
    () => (id: string) => projects.value.find(project => project.id === id)
  )

  // Fetch all projects (supports includes + pagination)
  const fetchProjects = async ({
    include = [],
    page: p = 1,
    perPage: pp = 20,
  }: IndexQueryOptions = {}) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      const params = mergeParams(buildIncludes(include), buildPagination(p, pp))
      const response = await apiClient.projectIndex({ params })
      const data = response.data?.data || []
      const meta: PaginationMeta | undefined = extractPaginationMeta(response.data)
      projects.value = data
      if (meta) {
        total.value = typeof meta.total === 'number' ? meta.total : total.value
        page.value = typeof meta.current_page === 'number' ? meta.current_page : p
        perPage.value = typeof meta.per_page === 'number' ? meta.per_page : pp
      } else {
        page.value = p
        perPage.value = pp
      }
    } catch (err: unknown) {
      ErrorHandler.handleError(err, 'Failed to fetch projects')
      error.value = 'Failed to fetch projects'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Fetch enabled projects only (visible projects) - supports pagination
  const fetchEnabledProjects = async ({
    include = [],
    page: p = 1,
    perPage: pp = 20,
  }: IndexQueryOptions = {}) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      const params = mergeParams(buildIncludes(include), buildPagination(p, pp))
      const response = await apiClient.projectEnabled({ params })
      const data = response.data?.data || []
      const meta: PaginationMeta | undefined = extractPaginationMeta(response.data)
      visibleProjects.value = data
      if (meta) {
        total.value = typeof meta.total === 'number' ? meta.total : total.value
        page.value = typeof meta.current_page === 'number' ? meta.current_page : p
        perPage.value = typeof meta.per_page === 'number' ? meta.per_page : pp
      } else {
        page.value = p
        perPage.value = pp
      }
    } catch (err: unknown) {
      ErrorHandler.handleError(err, 'Failed to fetch enabled projects')
      error.value = 'Failed to fetch enabled projects'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Fetch a single project by ID
  const fetchProject = async (id: string, { include = [] }: ShowQueryOptions = {}) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      const params = mergeParams(buildIncludes(include))
      const hasParams = Object.keys(params).length > 0
      const response = hasParams
        ? await apiClient.projectShow(id, { params })
        : await apiClient.projectShow(id)
      currentProject.value = response.data.data
      return response.data.data
    } catch (err: unknown) {
      ErrorHandler.handleError(err, `Failed to fetch project ${id}`)
      error.value = 'Failed to fetch project'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Create a new project
  const createProject = async (projectData: ProjectStoreRequest) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      const response = await apiClient.projectStore(projectData)
      const newProject = response.data.data

      // Add to local state
      projects.value.push(newProject)
      currentProject.value = newProject

      return newProject
    } catch (err: unknown) {
      ErrorHandler.handleError(err, 'Failed to create project')
      error.value = 'Failed to create project'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Update an existing project
  const updateProject = async (id: string, projectData: ProjectStoreRequest) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      const response = await apiClient.projectUpdate(id, projectData)
      const updatedProject = response.data.data

      // Update local state
      const index = projects.value.findIndex(p => p.id === id)
      if (index !== -1) {
        projects.value[index] = updatedProject
      }

      if (currentProject.value?.id === id) {
        currentProject.value = updatedProject
      }

      return updatedProject
    } catch (err: unknown) {
      ErrorHandler.handleError(err, 'Failed to update project')
      error.value = 'Failed to update project'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Delete a project
  const deleteProject = async (id: string) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      await apiClient.projectDestroy(id)

      // Remove from local state
      projects.value = projects.value.filter(p => p.id !== id)

      if (currentProject.value?.id === id) {
        currentProject.value = null
      }
    } catch (err: unknown) {
      ErrorHandler.handleError(err, 'Failed to delete project')
      error.value = 'Failed to delete project'
      throw err
    } finally {
      loading.value = false
    }
  }

  // Set project enabled/disabled status
  const setProjectEnabled = async (id: string, isEnabled: boolean) => {
    error.value = null

    try {
      const apiClient = createApiClient()
      const requestData: ProjectSetEnabledRequest = { is_enabled: isEnabled }
      const response = await apiClient.projectSetEnabled(id, requestData)
      const updatedProject = response.data.data

      // Update local state
      const index = projects.value.findIndex(p => p.id === id)
      if (index !== -1) {
        projects.value[index] = updatedProject
      }

      if (currentProject.value?.id === id) {
        currentProject.value = updatedProject
      }

      return updatedProject
    } catch (err: unknown) {
      ErrorHandler.handleError(err, 'Failed to set project enabled status')
      error.value = 'Failed to set project enabled status'
      throw err
    } finally {
      // Note: We don't set loading to false here since we didn't set it to true
      // Action loading is handled by the component that calls this method
    }
  }

  // Set project launched/not launched status
  const setProjectLaunched = async (id: string, isLaunched: boolean) => {
    error.value = null

    try {
      const apiClient = createApiClient()
      const requestData: ProjectSetLaunchedRequest = {
        is_launched: isLaunched,
      }
      const response = await apiClient.projectSetLaunched(id, requestData)
      const updatedProject = response.data.data

      // Update local state
      const index = projects.value.findIndex(p => p.id === id)
      if (index !== -1) {
        projects.value[index] = updatedProject
      }

      if (currentProject.value?.id === id) {
        currentProject.value = updatedProject
      }

      return updatedProject
    } catch (err: unknown) {
      ErrorHandler.handleError(err, 'Failed to set project launched status')
      error.value = 'Failed to set project launched status'
      throw err
    } finally {
      // Note: We don't set loading to false here since we didn't set it to true
      // Action loading is handled by the component that calls this method
    }
  }

  // Clear current project
  const clearCurrentProject = () => {
    currentProject.value = null
  }

  // Clear all projects
  const clearProjects = () => {
    projects.value = []
    visibleProjects.value = []
    currentProject.value = null
  }

  // Clear error
  const clearError = () => {
    error.value = null
  }

  // Helper methods
  const enableProject = async (id: string) => {
    return await setProjectEnabled(id, true)
  }

  const disableProject = async (id: string) => {
    return await setProjectEnabled(id, false)
  }

  const launchProject = async (id: string) => {
    return await setProjectLaunched(id, true)
  }

  const unlaunchProject = async (id: string) => {
    return await setProjectLaunched(id, false)
  }

  return {
    // State
    projects,
    visibleProjects,
    currentProject,
    loading,
    error,
    page,
    perPage,
    total,

    // Computed
    enabledProjects,
    launchedProjects,
    getProjectById,

    // Actions
    fetchProjects,
    fetchEnabledProjects,
    fetchProject,
    createProject,
    updateProject,
    deleteProject,
    setProjectEnabled,
    setProjectLaunched,
    enableProject,
    disableProject,
    launchProject,
    unlaunchProject,
    clearCurrentProject,
    clearProjects,
    clearError,
  }
})
