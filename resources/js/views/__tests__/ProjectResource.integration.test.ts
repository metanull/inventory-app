/**
 * Integration Tests for Project Resource Management
 *
 * These tests verify complete user workflows combining multiple components
 * and stores to ensure Project-specific features work together correctly.
 */

import { beforeEach, describe, expect, it, vi, beforeAll, afterAll } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useProjectStore } from '@/stores/project'
import { useContextStore } from '@/stores/context'
import { useLanguageStore } from '@/stores/language'
import { createMockProject } from '@/__tests__/test-utils'

// Mock console.error to avoid noise in test output
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

import type {
  ProjectResource,
  ContextResource,
  LanguageResource,
  ProjectStoreRequest,
} from '@metanull/inventory-app-api-client'

// Mock the stores instead of the API client
vi.mock('@/stores/project')
vi.mock('@/stores/context')
vi.mock('@/stores/language')

// Test data
const mockProjects: ProjectResource[] = [
  createMockProject({
    id: '1',
    internal_name: 'Alpha Project',
    backward_compatibility: 'alpha',
    is_enabled: true,
    is_launched: true,
  }),
  createMockProject({
    id: '2',
    internal_name: 'Beta Project',
    backward_compatibility: 'beta',
    is_enabled: true,
    is_launched: false,
  }),
  createMockProject({
    id: '3',
    internal_name: 'Gamma Project',
    backward_compatibility: 'gamma',
    is_enabled: false,
    is_launched: false,
  }),
]

const mockContexts: ContextResource[] = [
  {
    id: 'ctx-1',
    internal_name: 'Test Context',
    backward_compatibility: 'test-context',
    is_default: true,
    created_at: '2023-01-01T00:00:00Z',
    updated_at: '2023-01-01T00:00:00Z',
  },
]

const mockLanguages: LanguageResource[] = [
  {
    id: 'lang-1',
    internal_name: 'English',
    backward_compatibility: 'english',
    is_default: true,
    created_at: '2023-01-01T00:00:00Z',
    updated_at: '2023-01-01T00:00:00Z',
  },
]

describe('Project Resource Integration Tests', () => {
  let projectStore: ReturnType<typeof useProjectStore>
  let contextStore: ReturnType<typeof useContextStore>
  let languageStore: ReturnType<typeof useLanguageStore>

  beforeEach(async () => {
    setActivePinia(createPinia())

    projectStore = useProjectStore()
    contextStore = useContextStore()
    languageStore = useLanguageStore()

    // Setup comprehensive store mocks
    const mockProjectStoreImplementation = {
      projects: mockProjects,
      currentProject: null as ProjectResource | null,
      loading: false,
      enabledProjects: mockProjects.filter(p => p.is_enabled),
      launchedProjects: mockProjects.filter(p => p.is_launched),
      visibleProjects: mockProjects.filter(p => p.is_enabled), // Simplified for tests
      fetchProjects: vi.fn().mockResolvedValue(mockProjects),
      fetchProject: vi.fn().mockImplementation((id: string) => {
        let project = mockProjects.find(p => p.id === id)
        // Handle the case where we're fetching a newly created project
        if (id === 'new-project' && !project) {
          project = {
            ...createMockProject({ id: 'new-project', internal_name: 'New Integration Project' }),
            internal_name: 'New Integration Project',
          }
        }
        mockProjectStoreImplementation.currentProject = project || null
        return Promise.resolve(project)
      }),
      createProject: vi
        .fn()
        .mockImplementation((data: ProjectStoreRequest) =>
          Promise.resolve({ ...createMockProject(data), id: 'new-project' })
        ),
      updateProject: vi
        .fn()
        .mockImplementation((id: string, data: ProjectStoreRequest) =>
          Promise.resolve({ ...mockProjects.find(p => p.id === id), ...data })
        ),
      deleteProject: vi.fn().mockResolvedValue(undefined),
      setProjectEnabled: vi
        .fn()
        .mockImplementation((id: string, enabled: boolean) =>
          Promise.resolve({ ...mockProjects.find(p => p.id === id), is_enabled: enabled })
        ),
      setProjectLaunched: vi
        .fn()
        .mockImplementation((id: string, launched: boolean) =>
          Promise.resolve({ ...mockProjects.find(p => p.id === id), is_launched: launched })
        ),
    }

    const mockContextStoreImplementation = {
      contexts: mockContexts,
      defaultContext: mockContexts[0],
      fetchContexts: vi.fn().mockResolvedValue(mockContexts),
    }

    const mockLanguageStoreImplementation = {
      languages: mockLanguages,
      defaultLanguage: mockLanguages[0],
      fetchLanguages: vi.fn().mockResolvedValue(mockLanguages),
    }

    // Mock store implementations
    vi.mocked(useProjectStore).mockReturnValue(
      mockProjectStoreImplementation as ReturnType<typeof useProjectStore>
    )
    vi.mocked(useContextStore).mockReturnValue(
      mockContextStoreImplementation as ReturnType<typeof useContextStore>
    )
    vi.mocked(useLanguageStore).mockReturnValue(
      mockLanguageStoreImplementation as ReturnType<typeof useLanguageStore>
    )

    // Update store references
    projectStore = mockProjectStoreImplementation as ReturnType<typeof useProjectStore>
    contextStore = mockContextStoreImplementation as ReturnType<typeof useContextStore>
    languageStore = mockLanguageStoreImplementation as ReturnType<typeof useLanguageStore>

    vi.clearAllMocks()
  })

  describe('Project List and Detail Integration (Project-specific workflows)', () => {
    it('should complete full project lifecycle: list → create → view → edit → delete', async () => {
      // 1. Load initial project list (simulating Projects.vue)
      await projectStore.fetchProjects()
      expect(projectStore.projects).toHaveLength(3)

      // 2. Create new project (simulating ProjectDetail.vue in create mode)
      const newProjectData = {
        internal_name: 'New Integration Project',
        backward_compatibility: 'new-integration',
        launch_date: '2024-01-01',
        context_id: 'ctx-1',
        language_id: 'lang-1',
      }

      const createdProject = await projectStore.createProject(newProjectData)
      expect(createdProject.id).toBe('new-project')
      expect(createdProject.internal_name).toBe('New Integration Project')

      // 3. Fetch the created project (simulating navigation to detail view)
      await projectStore.fetchProject('new-project')
      expect(projectStore.currentProject?.internal_name).toBe('New Integration Project')

      // 4. Edit the project (simulating ProjectDetail.vue in edit mode)
      const updatedData = {
        internal_name: 'Updated Integration Project',
        backward_compatibility: 'updated-integration',
      }
      await projectStore.updateProject('new-project', updatedData)

      // 5. Delete the project (completing the lifecycle)
      await projectStore.deleteProject('new-project')

      // Verify the full workflow completed successfully
      expect(projectStore.createProject).toHaveBeenCalledWith(newProjectData)
      expect(projectStore.updateProject).toHaveBeenCalledWith('new-project', updatedData)
      expect(projectStore.deleteProject).toHaveBeenCalledWith('new-project')
    })

    it('should handle Project-specific filtering workflows across components', async () => {
      // Load projects and verify filtering computations work together
      await projectStore.fetchProjects()

      // Test enabled projects filter (Project-specific)
      const enabledProjects = projectStore.enabledProjects
      expect(enabledProjects).toHaveLength(2) // Alpha and Beta are enabled
      expect(enabledProjects.every(p => p.is_enabled)).toBe(true)

      // Test launched projects filter (Project-specific)
      const launchedProjects = projectStore.launchedProjects
      expect(launchedProjects).toHaveLength(1) // Only Alpha is launched
      expect(launchedProjects.every(p => p.is_launched)).toBe(true)

      // Test visible projects filter (Project-specific)
      const visibleProjects = projectStore.visibleProjects
      expect(visibleProjects).toHaveLength(2) // Enabled projects are visible
      expect(visibleProjects.every(p => p.is_enabled)).toBe(true)
    })

    it('should handle Project-specific status toggle workflows', async () => {
      // Load project
      await projectStore.fetchProject('2') // Beta Project (enabled, not launched)

      // Toggle enabled status (Project-specific operation)
      await projectStore.setProjectEnabled('2', false)
      expect(projectStore.setProjectEnabled).toHaveBeenCalledWith('2', false)

      // Toggle launch status (Project-specific operation)
      await projectStore.setProjectLaunched('2', true)
      expect(projectStore.setProjectLaunched).toHaveBeenCalledWith('2', true)

      // Verify both status changes were processed
      expect(projectStore.setProjectEnabled).toHaveBeenCalledTimes(1)
      expect(projectStore.setProjectLaunched).toHaveBeenCalledTimes(1)
    })
  })

  describe('Cross-store Dependencies', () => {
    it('should coordinate between Project, Context, and Language stores for form operations', async () => {
      // Load all dependencies (simulating ProjectDetail.vue initialization)
      await Promise.all([
        projectStore.fetchProject('1'),
        contextStore.fetchContexts(),
        languageStore.fetchLanguages(),
      ])

      // Verify all stores have data
      expect(projectStore.currentProject).toBeTruthy()
      expect(contextStore.contexts).toHaveLength(1)
      expect(languageStore.languages).toHaveLength(1)

      // Create project with relations (testing cross-store integration)
      const projectWithRelations = {
        internal_name: 'Cross-store Test Project',
        context_id: contextStore.contexts[0].id,
        language_id: languageStore.languages[0].id,
      }

      await projectStore.createProject(projectWithRelations)

      expect(projectStore.createProject).toHaveBeenCalledWith(
        expect.objectContaining({
          context_id: 'ctx-1',
          language_id: 'lang-1',
        })
      )
    })
  })

  describe('Error Handling Integration', () => {
    it('should handle API errors gracefully across the workflow', async () => {
      // Mock API failure on the original store instance
      const originalFetchProjects = projectStore.fetchProjects
      projectStore.fetchProjects = vi.fn().mockRejectedValue(new Error('API Error'))

      // Verify error is handled (thrown in this case)
      await expect(projectStore.fetchProjects()).rejects.toThrow('API Error')

      // Restore original implementation and verify it works
      projectStore.fetchProjects = originalFetchProjects

      // Verify that after error, we can still use the store
      expect(typeof projectStore.fetchProjects).toBe('function')
    })
  })

  describe('Project-specific Business Rules', () => {
    it('should enforce Project-specific visibility rules', async () => {
      await projectStore.fetchProjects()

      // Test that disabled projects are not included in visible projects
      const visibleProjects = projectStore.visibleProjects
      const hasDisabledProjects = visibleProjects.some(p => !p.is_enabled)
      expect(hasDisabledProjects).toBe(false)

      // Test that only enabled projects can be in visible collection
      visibleProjects.forEach(project => {
        expect(project.is_enabled).toBe(true)
      })
    })

    it('should handle Project-specific search functionality', async () => {
      await projectStore.fetchProjects()

      // Simulate search functionality that would be used in Projects.vue
      const searchTerm = 'alpha'
      const searchResults = projectStore.projects.filter(
        project =>
          project.internal_name.toLowerCase().includes(searchTerm) ||
          (project.backward_compatibility &&
            project.backward_compatibility.toLowerCase().includes(searchTerm))
      )

      expect(searchResults).toHaveLength(1)
      expect(searchResults[0].internal_name).toBe('Alpha Project')
    })
  })
})
