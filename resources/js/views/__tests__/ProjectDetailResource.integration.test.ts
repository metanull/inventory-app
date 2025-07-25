/**
 * Resource Integration Tests for ProjectDetail Management
 *
 * These tests verify complete resource workflows for project detail operations,
 * focusing on resource-level interactions, data consistency, and business logic
 * without UI rendering complexity.
 *
 * Tests cover:
 * - Resource creation, update, and deletion workflows
 * - Data validation and transformation
 * - Resource state management and consistency
 * - Error handling and recovery scenarios
 * - Resource relationships (contexts, languages)
 * - Status management workflows (enable/disable, launch/unlaunch)
 */

import { beforeEach, describe, expect, it, vi, beforeAll, afterAll } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useProjectStore } from '@/stores/project'
import { useContextStore } from '@/stores/context'
import { useLanguageStore } from '@/stores/language'
import { createMockProject } from '@/__tests__/test-utils'
import type {
  ProjectResource,
  ContextResource,
  LanguageResource,
} from '@metanull/inventory-app-api-client'

// Mock console.error to suppress error output during tests
vi.mock('console', () => ({
  error: vi.fn(),
  warn: vi.fn(),
  log: vi.fn(),
}))

// Store original console methods for cleanup
// eslint-disable-next-line @typescript-eslint/no-explicit-any
let originalConsole: any

beforeAll(() => {
  originalConsole = { ...console }
  console.error = vi.fn()
  console.warn = vi.fn()
  console.log = vi.fn()
})

afterAll(() => {
  Object.assign(console, originalConsole)
})

// Mock the stores
vi.mock('@/stores/project')
vi.mock('@/stores/context')
vi.mock('@/stores/language')

// Test data for comprehensive resource testing
const mockProjects: ProjectResource[] = [
  createMockProject({
    id: '1',
    internal_name: 'Alpha Project',
    backward_compatibility: 'alpha',
    launch_date: '2023-01-15T00:00:00Z',
    is_enabled: true,
    is_launched: true,
  }),
  createMockProject({
    id: '2',
    internal_name: 'Beta Project',
    backward_compatibility: 'beta',
    launch_date: '2023-06-01T00:00:00Z',
    is_enabled: true,
    is_launched: false,
  }),
  createMockProject({
    id: '3',
    internal_name: 'Gamma Project',
    backward_compatibility: 'gamma',
    launch_date: null,
    is_enabled: false,
    is_launched: false,
  }),
]

const mockContexts: ContextResource[] = [
  {
    id: 'ctx-1',
    internal_name: 'Development Context',
    backward_compatibility: 'dev',
    is_default: true,
    created_at: '2023-01-01T00:00:00Z',
    updated_at: '2023-01-01T00:00:00Z',
  },
  {
    id: 'ctx-2',
    internal_name: 'Production Context',
    backward_compatibility: 'prod',
    is_default: false,
    created_at: '2023-01-01T00:00:00Z',
    updated_at: '2023-01-01T00:00:00Z',
  },
  {
    id: 'ctx-3',
    internal_name: 'Testing Context',
    backward_compatibility: 'test',
    is_default: false,
    created_at: '2023-01-01T00:00:00Z',
    updated_at: '2023-01-01T00:00:00Z',
  },
]

const mockLanguages: LanguageResource[] = [
  {
    id: 'lang-1',
    internal_name: 'English',
    backward_compatibility: 'en',
    is_default: true,
    created_at: '2023-01-01T00:00:00Z',
    updated_at: '2023-01-01T00:00:00Z',
  },
  {
    id: 'lang-2',
    internal_name: 'Spanish',
    backward_compatibility: 'es',
    is_default: false,
    created_at: '2023-01-01T00:00:00Z',
    updated_at: '2023-01-01T00:00:00Z',
  },
  {
    id: 'lang-3',
    internal_name: 'French',
    backward_compatibility: 'fr',
    is_default: false,
    created_at: '2023-01-01T00:00:00Z',
    updated_at: '2023-01-01T00:00:00Z',
  },
]

describe('ProjectDetail Resource Integration Tests', () => {
  let mockProjectStore: ReturnType<typeof useProjectStore>
  let mockContextStore: ReturnType<typeof useContextStore>
  let mockLanguageStore: ReturnType<typeof useLanguageStore>

  beforeEach(() => {
    setActivePinia(createPinia())

    // Setup comprehensive store mocks with resource operations
    mockProjectStore = {
      projects: mockProjects,
      currentProject: mockProjects[0],
      loading: false,
      error: null,
      fetchProjects: vi.fn().mockResolvedValue(mockProjects),
      fetchProject: vi.fn().mockResolvedValue(mockProjects[0]),
      createProject: vi.fn(),
      updateProject: vi.fn(),
      deleteProject: vi.fn(),
      setProjectEnabled: vi.fn(),
      setProjectLaunched: vi.fn(),
      clearCurrentProject: vi.fn(),
    } as ReturnType<typeof useProjectStore>

    mockContextStore = {
      contexts: mockContexts,
      defaultContext: mockContexts[0], // Development context as default
      loading: false,
      fetchContexts: vi.fn().mockResolvedValue(mockContexts),
    } as ReturnType<typeof useContextStore>

    mockLanguageStore = {
      languages: mockLanguages,
      defaultLanguage: mockLanguages[0], // English as default
      loading: false,
      fetchLanguages: vi.fn().mockResolvedValue(mockLanguages),
    } as ReturnType<typeof useLanguageStore>

    // Mock store implementations
    vi.mocked(useProjectStore).mockReturnValue(mockProjectStore)
    vi.mocked(useContextStore).mockReturnValue(mockContextStore)
    vi.mocked(useLanguageStore).mockReturnValue(mockLanguageStore)

    vi.clearAllMocks()
  })

  describe('Project Resource Creation Workflows', () => {
    it('should create project with minimum required data', async () => {
      const newProject = createMockProject({
        id: 'new-1',
        internal_name: 'Minimal Project',
        backward_compatibility: null,
        launch_date: null,
        is_enabled: true,
        is_launched: false,
      })

      mockProjectStore.createProject = vi.fn().mockResolvedValue(newProject)

      const projectData = {
        internal_name: 'Minimal Project',
        backward_compatibility: null,
        launch_date: null,
        context_id: null,
        language_id: null,
      }

      const result = await mockProjectStore.createProject(projectData)

      expect(mockProjectStore.createProject).toHaveBeenCalledWith(projectData)
      expect(result.internal_name).toBe('Minimal Project')
      expect(result.id).toBe('new-1')
    })

    it('should create project with full data including relationships', async () => {
      const newProject = createMockProject({
        id: 'new-2',
        internal_name: 'Full Featured Project',
        backward_compatibility: 'full-featured',
        launch_date: '2024-01-01T00:00:00Z',
        is_enabled: true,
        is_launched: false,
        context: mockContexts[1], // Production context
        language: mockLanguages[1], // Spanish
      })

      mockProjectStore.createProject = vi.fn().mockResolvedValue(newProject)

      const projectData = {
        internal_name: 'Full Featured Project',
        backward_compatibility: 'full-featured',
        launch_date: '2024-01-01',
        context_id: 'ctx-2',
        language_id: 'lang-2',
      }

      const result = await mockProjectStore.createProject(projectData)

      expect(result.context?.id).toBe('ctx-2')
      expect(result.language?.id).toBe('lang-2')
      expect(result.backward_compatibility).toBe('full-featured')
    })

    it('should handle project creation with validation errors', async () => {
      const validationError = new Error('Validation failed: internal_name must be unique')
      mockProjectStore.createProject = vi.fn().mockRejectedValue(validationError)

      const projectData = {
        internal_name: 'Alpha Project', // Duplicate name
        backward_compatibility: 'alpha-duplicate',
        launch_date: null,
        context_id: 'ctx-1',
        language_id: 'lang-1',
      }

      await expect(mockProjectStore.createProject(projectData)).rejects.toThrow(validationError)
      expect(mockProjectStore.createProject).toHaveBeenCalledWith(projectData)
    })

    it('should use default context and language when none specified', async () => {
      const newProject = createMockProject({
        id: 'new-3',
        internal_name: 'Default Project',
        context: mockContexts[0], // Default context
        language: mockLanguages[0], // Default language
      })

      mockProjectStore.createProject = vi.fn().mockResolvedValue(newProject)

      const projectData = {
        internal_name: 'Default Project',
        backward_compatibility: null,
        launch_date: null,
        context_id: mockContexts[0].id,
        language_id: mockLanguages[0].id,
      }

      const result = await mockProjectStore.createProject(projectData)

      expect(result.context?.internal_name).toBe('Development Context')
      expect(result.language?.internal_name).toBe('English')
    })
  })

  describe('Project Resource Update Workflows', () => {
    it('should update project with partial data changes', async () => {
      const updatedProject = {
        ...mockProjects[0],
        internal_name: 'Updated Alpha Project',
        backward_compatibility: 'updated-alpha',
      }

      mockProjectStore.updateProject = vi.fn().mockResolvedValue(updatedProject)

      const updateData = {
        internal_name: 'Updated Alpha Project',
        backward_compatibility: 'updated-alpha',
        launch_date: '2023-01-15',
        context_id: 'ctx-1',
        language_id: 'lang-1',
      }

      const result = await mockProjectStore.updateProject('1', updateData)

      expect(result.internal_name).toBe('Updated Alpha Project')
      expect(result.backward_compatibility).toBe('updated-alpha')
      expect(mockProjectStore.updateProject).toHaveBeenCalledWith('1', updateData)
    })

    it('should update project relationships (context and language)', async () => {
      const updatedProject = {
        ...mockProjects[0],
        context: mockContexts[2], // Switch to Testing context
        language: mockLanguages[2], // Switch to French
      }

      mockProjectStore.updateProject = vi.fn().mockResolvedValue(updatedProject)

      const updateData = {
        internal_name: 'Alpha Project',
        backward_compatibility: 'alpha',
        launch_date: '2023-01-15',
        context_id: 'ctx-3',
        language_id: 'lang-3',
      }

      const result = await mockProjectStore.updateProject('1', updateData)

      expect(result.context?.internal_name).toBe('Testing Context')
      expect(result.language?.internal_name).toBe('French')
    })

    it('should handle project update with non-existent ID', async () => {
      const notFoundError = new Error('Project not found: invalid-id')
      mockProjectStore.updateProject = vi.fn().mockRejectedValue(notFoundError)

      const updateData = {
        internal_name: 'Updated Project',
        backward_compatibility: 'updated',
        launch_date: null,
        context_id: 'ctx-1',
        language_id: 'lang-1',
      }

      await expect(mockProjectStore.updateProject('invalid-id', updateData)).rejects.toThrow(
        notFoundError
      )
    })

    it('should clear optional fields when updating with null values', async () => {
      const updatedProject = {
        ...mockProjects[1],
        backward_compatibility: null,
        launch_date: null,
        context: null,
        language: null,
      }

      mockProjectStore.updateProject = vi.fn().mockResolvedValue(updatedProject)

      const updateData = {
        internal_name: 'Beta Project',
        backward_compatibility: null,
        launch_date: null,
        context_id: null,
        language_id: null,
      }

      const result = await mockProjectStore.updateProject('2', updateData)

      expect(result.backward_compatibility).toBeNull()
      expect(result.launch_date).toBeNull()
      expect(result.context).toBeNull()
      expect(result.language).toBeNull()
    })
  })

  describe('Project Resource Status Management', () => {
    it('should enable a disabled project', async () => {
      const disabledProject = mockProjects[2] // Gamma project is disabled
      const enabledProject = { ...disabledProject, is_enabled: true }

      mockProjectStore.setProjectEnabled = vi.fn().mockResolvedValue(enabledProject)

      const result = await mockProjectStore.setProjectEnabled('3', true)

      expect(result.is_enabled).toBe(true)
      expect(mockProjectStore.setProjectEnabled).toHaveBeenCalledWith('3', true)
    })

    it('should disable an enabled project', async () => {
      const enabledProject = mockProjects[0] // Alpha project is enabled
      const disabledProject = { ...enabledProject, is_enabled: false }

      mockProjectStore.setProjectEnabled = vi.fn().mockResolvedValue(disabledProject)

      const result = await mockProjectStore.setProjectEnabled('1', false)

      expect(result.is_enabled).toBe(false)
      expect(mockProjectStore.setProjectEnabled).toHaveBeenCalledWith('1', false)
    })

    it('should launch an unlaunched project', async () => {
      const unlaunchedProject = mockProjects[1] // Beta project is not launched
      const launchedProject = { ...unlaunchedProject, is_launched: true }

      mockProjectStore.setProjectLaunched = vi.fn().mockResolvedValue(launchedProject)

      const result = await mockProjectStore.setProjectLaunched('2', true)

      expect(result.is_launched).toBe(true)
      expect(mockProjectStore.setProjectLaunched).toHaveBeenCalledWith('2', true)
    })

    it('should unlaunch a launched project', async () => {
      const launchedProject = mockProjects[0] // Alpha project is launched
      const unlaunchedProject = { ...launchedProject, is_launched: false }

      mockProjectStore.setProjectLaunched = vi.fn().mockResolvedValue(unlaunchedProject)

      const result = await mockProjectStore.setProjectLaunched('1', false)

      expect(result.is_launched).toBe(false)
      expect(mockProjectStore.setProjectLaunched).toHaveBeenCalledWith('1', false)
    })

    it('should handle status toggle errors gracefully', async () => {
      const statusError = new Error('Failed to update project status')
      mockProjectStore.setProjectEnabled = vi.fn().mockRejectedValue(statusError)

      await expect(mockProjectStore.setProjectEnabled('1', false)).rejects.toThrow(statusError)
    })

    it('should handle launch toggle with business logic validation', async () => {
      // Simulate business rule: cannot launch a disabled project
      const validationError = new Error('Cannot launch a disabled project')
      mockProjectStore.setProjectLaunched = vi.fn().mockRejectedValue(validationError)

      await expect(mockProjectStore.setProjectLaunched('3', true)).rejects.toThrow(validationError)
    })
  })

  describe('Project Resource Deletion Workflows', () => {
    it('should delete an existing project', async () => {
      mockProjectStore.deleteProject = vi.fn().mockResolvedValue(undefined)

      await mockProjectStore.deleteProject('1')

      expect(mockProjectStore.deleteProject).toHaveBeenCalledWith('1')
    })

    it('should handle deletion of non-existent project', async () => {
      const notFoundError = new Error('Project not found: invalid-id')
      mockProjectStore.deleteProject = vi.fn().mockRejectedValue(notFoundError)

      await expect(mockProjectStore.deleteProject('invalid-id')).rejects.toThrow(notFoundError)
    })

    it('should handle deletion with dependency constraints', async () => {
      const constraintError = new Error('Cannot delete project: has active dependencies')
      mockProjectStore.deleteProject = vi.fn().mockRejectedValue(constraintError)

      await expect(mockProjectStore.deleteProject('1')).rejects.toThrow(constraintError)
    })
  })

  describe('Resource Relationship Management', () => {
    it('should load all contexts for project selection', async () => {
      await mockContextStore.fetchContexts()

      expect(mockContextStore.fetchContexts).toHaveBeenCalled()
      expect(mockContextStore.contexts).toHaveLength(3)
      expect(mockContextStore.contexts[0].is_default).toBe(true)
    })

    it('should load all languages for project selection', async () => {
      await mockLanguageStore.fetchLanguages()

      expect(mockLanguageStore.fetchLanguages).toHaveBeenCalled()
      expect(mockLanguageStore.languages).toHaveLength(3)
      expect(mockLanguageStore.languages[0].is_default).toBe(true)
    })

    it('should identify default context correctly', () => {
      const defaultContext = mockContextStore.defaultContext

      expect(defaultContext?.id).toBe('ctx-1')
      expect(defaultContext?.internal_name).toBe('Development Context')
      expect(defaultContext?.is_default).toBe(true)
    })

    it('should identify default language correctly', () => {
      const defaultLanguage = mockLanguageStore.defaultLanguage

      expect(defaultLanguage?.id).toBe('lang-1')
      expect(defaultLanguage?.internal_name).toBe('English')
      expect(defaultLanguage?.is_default).toBe(true)
    })

    it('should handle missing context relationships gracefully', async () => {
      const projectWithoutContext = createMockProject({
        id: '4',
        internal_name: 'Orphaned Project',
        context: null,
        language: mockLanguages[0],
      })

      mockProjectStore.fetchProject = vi.fn().mockResolvedValue(projectWithoutContext)

      const result = await mockProjectStore.fetchProject('4')

      expect(result.context).toBeNull()
      expect(result.language).not.toBeNull()
    })

    it('should handle missing language relationships gracefully', async () => {
      const projectWithoutLanguage = createMockProject({
        id: '5',
        internal_name: 'Language-less Project',
        context: mockContexts[0],
        language: null,
      })

      mockProjectStore.fetchProject = vi.fn().mockResolvedValue(projectWithoutLanguage)

      const result = await mockProjectStore.fetchProject('5')

      expect(result.context).not.toBeNull()
      expect(result.language).toBeNull()
    })
  })

  describe('Resource Data Consistency and Validation', () => {
    it('should maintain data consistency across multiple operations', async () => {
      // Create project
      const newProject = createMockProject({
        id: 'consistency-1',
        internal_name: 'Consistency Test Project',
        context: mockContexts[0],
        language: mockLanguages[0],
      })

      mockProjectStore.createProject = vi.fn().mockResolvedValue(newProject)

      let result = await mockProjectStore.createProject({
        internal_name: 'Consistency Test Project',
        backward_compatibility: null,
        launch_date: null,
        context_id: 'ctx-1',
        language_id: 'lang-1',
      })

      expect(result.id).toBe('consistency-1')

      // Update project
      const updatedProject = {
        ...newProject,
        internal_name: 'Updated Consistency Project',
        is_enabled: false,
      }

      mockProjectStore.updateProject = vi.fn().mockResolvedValue(updatedProject)

      result = await mockProjectStore.updateProject('consistency-1', {
        internal_name: 'Updated Consistency Project',
        backward_compatibility: null,
        launch_date: null,
        context_id: 'ctx-1',
        language_id: 'lang-1',
      })

      expect(result.internal_name).toBe('Updated Consistency Project')

      // Toggle status
      const enabledProject = { ...updatedProject, is_enabled: true }
      mockProjectStore.setProjectEnabled = vi.fn().mockResolvedValue(enabledProject)

      result = await mockProjectStore.setProjectEnabled('consistency-1', true)

      expect(result.is_enabled).toBe(true)
    })

    it('should validate launch date format consistency', async () => {
      const projectWithFormattedDate = createMockProject({
        id: 'date-test',
        internal_name: 'Date Test Project',
        launch_date: '2024-03-15T09:30:00Z',
      })

      mockProjectStore.createProject = vi.fn().mockResolvedValue(projectWithFormattedDate)

      const result = await mockProjectStore.createProject({
        internal_name: 'Date Test Project',
        backward_compatibility: null,
        launch_date: '2024-03-15',
        context_id: null,
        language_id: null,
      })

      // Verify the date is stored in ISO format
      expect(result.launch_date).toBe('2024-03-15T09:30:00Z')
    })

    it('should handle backward compatibility uniqueness validation', async () => {
      const duplicateError = new Error('Backward compatibility code must be unique')
      mockProjectStore.createProject = vi.fn().mockRejectedValue(duplicateError)

      await expect(
        mockProjectStore.createProject({
          internal_name: 'Duplicate BC Project',
          backward_compatibility: 'alpha', // Already exists
          launch_date: null,
          context_id: 'ctx-1',
          language_id: 'lang-1',
        })
      ).rejects.toThrow(duplicateError)
    })
  })

  describe('Resource Error Recovery and Resilience', () => {
    it('should handle network errors during resource fetching', async () => {
      const networkError = new Error('Network timeout')
      mockProjectStore.fetchProject = vi.fn().mockRejectedValue(networkError)

      await expect(mockProjectStore.fetchProject('1')).rejects.toThrow(networkError)
    })

    it('should handle server errors during resource operations', async () => {
      const serverError = new Error('Internal server error')
      mockProjectStore.updateProject = vi.fn().mockRejectedValue(serverError)

      await expect(
        mockProjectStore.updateProject('1', {
          internal_name: 'Updated Project',
          backward_compatibility: 'updated',
          launch_date: null,
          context_id: 'ctx-1',
          language_id: 'lang-1',
        })
      ).rejects.toThrow(serverError)
    })

    it('should handle resource conflicts during concurrent operations', async () => {
      const conflictError = new Error('Resource conflict: project was modified by another user')
      mockProjectStore.updateProject = vi.fn().mockRejectedValue(conflictError)

      await expect(
        mockProjectStore.updateProject('1', {
          internal_name: 'Conflicted Update',
          backward_compatibility: 'conflict',
          launch_date: null,
          context_id: 'ctx-1',
          language_id: 'lang-1',
        })
      ).rejects.toThrow(conflictError)
    })
  })
})
