import { beforeEach, describe, expect, it, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useProjectStore } from '../project'
import type { ProjectResource, ProjectStoreRequest } from '@metanull/inventory-app-api-client'

// Mock the API client
const mockProjectApi = {
  projectIndex: vi.fn(),
  projectShow: vi.fn(),
  projectStore: vi.fn(),
  projectUpdate: vi.fn(),
  projectDestroy: vi.fn(),
  projectSetEnabled: vi.fn(),
  projectSetLaunched: vi.fn(),
  projectEnabled: vi.fn(),
}

vi.mock('@metanull/inventory-app-api-client', () => ({
  ProjectApi: vi.fn().mockImplementation(() => mockProjectApi),
  Configuration: vi.fn(),
}))

// Mock the auth store
vi.mock('@/stores/auth', () => ({
  useAuthStore: vi.fn(() => ({
    token: 'mock-token',
    isAuthenticated: true,
  })),
}))

// Mock the error handler
vi.mock('@/utils/errorHandler', () => ({
  ErrorHandler: {
    handleError: vi.fn(),
  },
}))

const mockProjectData: ProjectResource = {
  id: '123e4567-e89b-12d3-a456-426614174000',
  internal_name: 'Test Project',
  backward_compatibility: 'test-project',
  launch_date: '2024-01-01T00:00:00Z',
  is_launched: false,
  is_enabled: true,
  created_at: '2024-01-01T00:00:00Z',
  updated_at: '2024-01-01T00:00:00Z',
}

const mockProjectsResponse = {
  data: {
    data: [
      mockProjectData,
      {
        id: '123e4567-e89b-12d3-a456-426614174001',
        internal_name: 'Another Project',
        backward_compatibility: 'another-project',
        launch_date: null,
        is_launched: true,
        is_enabled: false,
        created_at: '2024-01-02T00:00:00Z',
        updated_at: '2024-01-02T00:00:00Z',
      },
    ],
  },
}

describe('Project Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())

    // Reset all mocks
    vi.clearAllMocks()
  })

  describe('Initial State', () => {
    it('should have correct initial state', () => {
      const projectStore = useProjectStore()

      expect(projectStore.projects).toEqual([])
      expect(projectStore.currentProject).toBeNull()
      expect(projectStore.loading).toBe(false)
      expect(projectStore.error).toBeNull()
    })
  })

  describe('Getters', () => {
    it('should filter enabled projects', () => {
      const projectStore = useProjectStore()
      projectStore.projects = mockProjectsResponse.data.data

      const enabledProjects = projectStore.enabledProjects
      expect(enabledProjects).toHaveLength(1)
      expect(enabledProjects[0].is_enabled).toBe(true)
    })

    it('should filter launched projects', () => {
      const projectStore = useProjectStore()
      projectStore.projects = mockProjectsResponse.data.data

      const launchedProjects = projectStore.launchedProjects
      expect(launchedProjects).toHaveLength(1)
      expect(launchedProjects[0].is_launched).toBe(true)
    })

    it('should find project by id', () => {
      const projectStore = useProjectStore()
      projectStore.projects = mockProjectsResponse.data.data

      const project = projectStore.getProjectById('123e4567-e89b-12d3-a456-426614174000')
      expect(project).toEqual(mockProjectData)
    })

    it('should return undefined for non-existent project id', () => {
      const projectStore = useProjectStore()
      projectStore.projects = mockProjectsResponse.data.data

      const project = projectStore.getProjectById('non-existent-id')
      expect(project).toBeUndefined()
    })
  })

  describe('fetchProjects', () => {
    it('should fetch projects successfully', async () => {
      const projectStore = useProjectStore()
      mockProjectApi.projectIndex.mockResolvedValue(mockProjectsResponse)

      await projectStore.fetchProjects()

      expect(projectStore.loading).toBe(false)
      expect(projectStore.projects).toEqual(mockProjectsResponse.data.data)
      expect(projectStore.error).toBeNull()
      expect(mockProjectApi.projectIndex).toHaveBeenCalledTimes(1)
    })

    it('should handle fetch projects error', async () => {
      const projectStore = useProjectStore()
      const error = new Error('Network error')
      mockProjectApi.projectIndex.mockRejectedValue(error)

      await expect(projectStore.fetchProjects()).rejects.toThrow('Network error')

      expect(projectStore.loading).toBe(false)
      expect(projectStore.projects).toEqual([])
      expect(projectStore.error).toBe('Failed to fetch projects')
    })
  })

  describe('fetchProject', () => {
    it('should fetch single project successfully', async () => {
      const projectStore = useProjectStore()
      const projectId = '123e4567-e89b-12d3-a456-426614174000'
      mockProjectApi.projectShow.mockResolvedValue({
        data: { data: mockProjectData },
      })

      const result = await projectStore.fetchProject(projectId)

      expect(result).toEqual(mockProjectData)
      expect(projectStore.loading).toBe(false)
      expect(projectStore.currentProject).toEqual(mockProjectData)
      expect(projectStore.error).toBeNull()
      expect(mockProjectApi.projectShow).toHaveBeenCalledWith(projectId)
    })

    it('should handle fetch project error', async () => {
      const projectStore = useProjectStore()
      const projectId = '123e4567-e89b-12d3-a456-426614174000'
      const error = new Error('Not found')
      mockProjectApi.projectShow.mockRejectedValue(error)

      await expect(projectStore.fetchProject(projectId)).rejects.toThrow('Not found')

      expect(projectStore.loading).toBe(false)
      expect(projectStore.currentProject).toBeNull()
      expect(projectStore.error).toBe('Failed to fetch project')
    })
  })

  describe('createProject', () => {
    it('should create project successfully', async () => {
      const projectStore = useProjectStore()
      const newProject: ProjectStoreRequest = {
        internal_name: 'New Project',
        backward_compatibility: 'new-project',
        launch_date: '2024-12-31',
        is_enabled: true,
        is_launched: false,
        context_id: 'context-123',
        language_id: 'language-123',
      }

      const createdProject = {
        ...newProject,
        id: 'new-id',
        created_at: '2024-01-01T00:00:00Z',
        updated_at: '2024-01-01T00:00:00Z',
      }

      mockProjectApi.projectStore.mockResolvedValue({
        data: { data: createdProject },
      })

      const result = await projectStore.createProject(newProject)

      expect(result).toEqual(createdProject)
      expect(projectStore.loading).toBe(false)
      expect(projectStore.error).toBeNull()
      expect(mockProjectApi.projectStore).toHaveBeenCalledWith(newProject)
    })

    it('should handle create project error', async () => {
      const projectStore = useProjectStore()
      const newProject: ProjectStoreRequest = {
        internal_name: 'New Project',
        backward_compatibility: 'new-project',
        is_enabled: true,
        is_launched: false,
        context_id: 'context-123',
        language_id: 'language-123',
      }

      const error = new Error('Validation error')
      mockProjectApi.projectStore.mockRejectedValue(error)

      await expect(projectStore.createProject(newProject)).rejects.toThrow('Validation error')

      expect(projectStore.loading).toBe(false)
      expect(projectStore.error).toBe('Failed to create project')
    })
  })

  describe('updateProject', () => {
    it('should update project successfully', async () => {
      const projectStore = useProjectStore()
      const projectId = '123e4567-e89b-12d3-a456-426614174000'
      const updatedData: ProjectStoreRequest = {
        internal_name: 'Updated Project',
        backward_compatibility: 'updated-project',
        is_enabled: true,
        is_launched: false,
        context_id: 'context-123',
        language_id: 'language-123',
      }

      const updatedProject = { ...mockProjectData, ...updatedData }
      mockProjectApi.projectUpdate.mockResolvedValue({
        data: { data: updatedProject },
      })

      const result = await projectStore.updateProject(projectId, updatedData)

      expect(result).toEqual(updatedProject)
      expect(projectStore.loading).toBe(false)
      expect(projectStore.error).toBeNull()
      expect(mockProjectApi.projectUpdate).toHaveBeenCalledWith(projectId, updatedData)
    })

    it('should handle update project error', async () => {
      const projectStore = useProjectStore()
      const projectId = '123e4567-e89b-12d3-a456-426614174000'
      const updatedData: ProjectStoreRequest = {
        internal_name: 'Updated Project',
        backward_compatibility: 'updated-project',
        is_enabled: true,
        is_launched: false,
        context_id: 'context-123',
        language_id: 'language-123',
      }

      const error = new Error('Update failed')
      mockProjectApi.projectUpdate.mockRejectedValue(error)

      await expect(projectStore.updateProject(projectId, updatedData)).rejects.toThrow(
        'Update failed'
      )

      expect(projectStore.loading).toBe(false)
      expect(projectStore.error).toBe('Failed to update project')
    })
  })

  describe('deleteProject', () => {
    it('should delete project successfully', async () => {
      const projectStore = useProjectStore()
      const projectId = '123e4567-e89b-12d3-a456-426614174000'
      mockProjectApi.projectDestroy.mockResolvedValue({ data: {} })

      await projectStore.deleteProject(projectId)

      expect(projectStore.loading).toBe(false)
      expect(projectStore.error).toBeNull()
      expect(mockProjectApi.projectDestroy).toHaveBeenCalledWith(projectId)
    })

    it('should handle delete project error', async () => {
      const projectStore = useProjectStore()
      const projectId = '123e4567-e89b-12d3-a456-426614174000'
      const error = new Error('Delete failed')
      mockProjectApi.projectDestroy.mockRejectedValue(error)

      await expect(projectStore.deleteProject(projectId)).rejects.toThrow('Delete failed')

      expect(projectStore.loading).toBe(false)
      expect(projectStore.error).toBe('Failed to delete project')
    })
  })

  describe('enableProject', () => {
    it('should enable project successfully', async () => {
      const projectStore = useProjectStore()
      const projectId = '123e4567-e89b-12d3-a456-426614174000'
      const enabledProject = { ...mockProjectData, is_enabled: true }
      mockProjectApi.projectSetEnabled.mockResolvedValue({
        data: { data: enabledProject },
      })

      const result = await projectStore.enableProject(projectId)

      expect(result).toEqual(enabledProject)
      expect(projectStore.loading).toBe(false)
      expect(projectStore.error).toBeNull()
      expect(mockProjectApi.projectSetEnabled).toHaveBeenCalledWith(projectId, { is_enabled: true })
    })

    it('should handle enable project error', async () => {
      const projectStore = useProjectStore()
      const projectId = '123e4567-e89b-12d3-a456-426614174000'
      const error = new Error('Enable failed')
      mockProjectApi.projectSetEnabled.mockRejectedValue(error)

      await expect(projectStore.enableProject(projectId)).rejects.toThrow('Enable failed')

      expect(projectStore.loading).toBe(false)
      expect(projectStore.error).toBe('Failed to set project enabled status')
    })
  })

  describe('disableProject', () => {
    it('should disable project successfully', async () => {
      const projectStore = useProjectStore()
      const projectId = '123e4567-e89b-12d3-a456-426614174000'
      const disabledProject = { ...mockProjectData, is_enabled: false }
      mockProjectApi.projectSetEnabled.mockResolvedValue({
        data: { data: disabledProject },
      })

      const result = await projectStore.disableProject(projectId)

      expect(result).toEqual(disabledProject)
      expect(projectStore.loading).toBe(false)
      expect(projectStore.error).toBeNull()
      expect(mockProjectApi.projectSetEnabled).toHaveBeenCalledWith(projectId, {
        is_enabled: false,
      })
    })

    it('should handle disable project error', async () => {
      const projectStore = useProjectStore()
      const projectId = '123e4567-e89b-12d3-a456-426614174000'
      const error = new Error('Disable failed')
      mockProjectApi.projectSetEnabled.mockRejectedValue(error)

      await expect(projectStore.disableProject(projectId)).rejects.toThrow('Disable failed')

      expect(projectStore.loading).toBe(false)
      expect(projectStore.error).toBe('Failed to set project enabled status')
    })
  })

  describe('launchProject', () => {
    it('should launch project successfully', async () => {
      const projectStore = useProjectStore()
      const projectId = '123e4567-e89b-12d3-a456-426614174000'
      const launchedProject = { ...mockProjectData, is_launched: true }
      mockProjectApi.projectSetLaunched.mockResolvedValue({
        data: { data: launchedProject },
      })

      const result = await projectStore.launchProject(projectId)

      expect(result).toEqual(launchedProject)
      expect(projectStore.loading).toBe(false)
      expect(projectStore.error).toBeNull()
      expect(mockProjectApi.projectSetLaunched).toHaveBeenCalledWith(projectId, {
        is_launched: true,
      })
    })

    it('should handle launch project error', async () => {
      const projectStore = useProjectStore()
      const projectId = '123e4567-e89b-12d3-a456-426614174000'
      const error = new Error('Launch failed')
      mockProjectApi.projectSetLaunched.mockRejectedValue(error)

      await expect(projectStore.launchProject(projectId)).rejects.toThrow('Launch failed')

      expect(projectStore.loading).toBe(false)
      expect(projectStore.error).toBe('Failed to set project launched status')
    })
  })

  describe('unlaunchProject', () => {
    it('should unlaunch project successfully', async () => {
      const projectStore = useProjectStore()
      const projectId = '123e4567-e89b-12d3-a456-426614174000'
      const unlaunchedProject = { ...mockProjectData, is_launched: false }
      mockProjectApi.projectSetLaunched.mockResolvedValue({
        data: { data: unlaunchedProject },
      })

      const result = await projectStore.unlaunchProject(projectId)

      expect(result).toEqual(unlaunchedProject)
      expect(projectStore.loading).toBe(false)
      expect(projectStore.error).toBeNull()
      expect(mockProjectApi.projectSetLaunched).toHaveBeenCalledWith(projectId, {
        is_launched: false,
      })
    })

    it('should handle unlaunch project error', async () => {
      const projectStore = useProjectStore()
      const projectId = '123e4567-e89b-12d3-a456-426614174000'
      const error = new Error('Unlaunch failed')
      mockProjectApi.projectSetLaunched.mockRejectedValue(error)

      await expect(projectStore.unlaunchProject(projectId)).rejects.toThrow('Unlaunch failed')

      expect(projectStore.loading).toBe(false)
      expect(projectStore.error).toBe('Failed to set project launched status')
    })
  })

  describe('clearError', () => {
    it('should clear error', () => {
      const projectStore = useProjectStore()
      projectStore.error = 'Some error'
      projectStore.clearError()
      expect(projectStore.error).toBeNull()
    })
  })

  describe('clearCurrentProject', () => {
    it('should clear current project', () => {
      const projectStore = useProjectStore()
      projectStore.currentProject = mockProjectData
      projectStore.clearCurrentProject()
      expect(projectStore.currentProject).toBeNull()
    })
  })
})
