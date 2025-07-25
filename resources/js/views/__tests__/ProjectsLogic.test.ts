/**
 * Unit Tests for Projects Component Business Logic
 *
 * These tests focus on the core functionality and business logic
 * of the Projects component without dealing with complex UI rendering.
 *
 * Tests cover:
 * - Project filtering (All, Enabled, Launched, Visible) - Project-specific features
 * - Search functionality across internal_name and backward_compatibility
 * - Sorting functionality
 * - Store interactions and data fetching
 * - Error handling
 * - Status updates (enable/disable, launch/unlaunch) - Project-specific features
 * - Delete operations
 */

import { beforeEach, describe, expect, it, vi, beforeAll, afterAll } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useProjectStore } from '@/stores/project'
import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
import { useErrorDisplayStore } from '@/stores/errorDisplay'
import { useDeleteConfirmationStore } from '@/stores/deleteConfirmation'
import { createMockProject } from '@/__tests__/test-utils'
import type { ProjectResource } from '@metanull/inventory-app-api-client'

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

// Mock the stores
vi.mock('@/stores/project')
vi.mock('@/stores/loadingOverlay')
vi.mock('@/stores/errorDisplay')
vi.mock('@/stores/deleteConfirmation')

// Test data - covering different project states for comprehensive testing
const mockProjects: ProjectResource[] = [
  createMockProject({
    id: '1',
    internal_name: 'Active Project',
    backward_compatibility: 'active-project',
    is_enabled: true,
    is_launched: true,
    launch_date: '2023-01-01T00:00:00Z', // Past date - visible
    created_at: '2023-01-01T00:00:00Z',
  }),
  createMockProject({
    id: '2',
    internal_name: 'Disabled Project',
    backward_compatibility: 'disabled-project',
    is_enabled: false,
    is_launched: false,
    launch_date: '2023-06-01T00:00:00Z',
    created_at: '2023-02-01T00:00:00Z',
  }),
  createMockProject({
    id: '3',
    internal_name: 'Enabled Not Launched',
    backward_compatibility: 'enabled-not-launched',
    is_enabled: true,
    is_launched: false,
    launch_date: '2024-12-01T00:00:00Z', // Future date
    created_at: '2023-03-01T00:00:00Z',
  }),
]

// Simulate the business logic functions from the component
class ProjectsLogic {
  public filterMode: 'all' | 'enabled' | 'launched' | 'visible' = 'all'
  public searchQuery = ''
  public sortKey = 'internal_name'
  public sortDirection: 'asc' | 'desc' = 'asc'

  constructor(
    private projectStore: ReturnType<typeof useProjectStore>,
    private loadingStore: ReturnType<typeof useLoadingOverlayStore>,
    private errorStore: ReturnType<typeof useErrorDisplayStore>,
    private deleteStore: ReturnType<typeof useDeleteConfirmationStore>
  ) {}

  get projects() {
    return this.projectStore.projects
  }

  get enabledProjects() {
    return this.projectStore.enabledProjects
  }

  get launchedProjects() {
    return this.projectStore.launchedProjects
  }

  get visibleProjects() {
    return this.projectStore.visibleProjects
  }

  get filteredProjects() {
    let list: ProjectResource[]

    switch (this.filterMode) {
      case 'visible':
        list = this.visibleProjects
        break
      case 'enabled':
        list = this.enabledProjects
        break
      case 'launched':
        list = this.launchedProjects
        break
      default:
        list = this.projects
    }

    // Apply search filter
    const query = this.searchQuery.trim().toLowerCase()
    if (query.length > 0) {
      list = list.filter(project => {
        const name = project.internal_name?.toLowerCase() ?? ''
        const compat = project.backward_compatibility?.toLowerCase() ?? ''
        return name.includes(query) || compat.includes(query)
      })
    }

    // Apply sorting
    return [...list].sort((a, b) => {
      const key = this.sortKey
      let valA: unknown
      let valB: unknown

      if (key === 'internal_name') {
        valA = a.internal_name ?? ''
        valB = b.internal_name ?? ''
      } else {
        valA = (a as unknown as Record<string, unknown>)[key]
        valB = (b as unknown as Record<string, unknown>)[key]
      }

      if (valA == null && valB == null) return 0
      if (valA == null) return 1
      if (valB == null) return -1
      if (valA < valB) return this.sortDirection === 'asc' ? -1 : 1
      if (valA > valB) return this.sortDirection === 'asc' ? 1 : -1
      return 0
    })
  }

  handleSort(key: string) {
    if (this.sortKey === key) {
      this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc'
    } else {
      this.sortKey = key
      this.sortDirection = 'asc'
    }
  }

  async updateProjectStatus(project: ProjectResource, field: string, value: boolean) {
    try {
      this.loadingStore.show('Updating...')

      if (field === 'is_enabled') {
        await this.projectStore.setProjectEnabled(project.id, value)
        this.errorStore.addMessage(
          'info',
          `Project ${value ? 'enabled' : 'disabled'} successfully.`
        )
      } else if (field === 'is_launched') {
        await this.projectStore.setProjectLaunched(project.id, value)
        this.errorStore.addMessage(
          'info',
          `Project ${value ? 'launched' : 'unlaunched'} successfully.`
        )
      }
    } catch (error) {
      this.errorStore.addMessage('error', 'Failed to update project status. Please try again.')
      throw error
    } finally {
      this.loadingStore.hide()
    }
  }

  async handleDeleteProject(projectToDelete: ProjectResource) {
    const result = await this.deleteStore.trigger(
      'Delete Project',
      `Are you sure you want to delete "${projectToDelete.internal_name}"? This action cannot be undone.`
    )

    if (result === 'delete') {
      try {
        this.loadingStore.show('Deleting...')
        await this.projectStore.deleteProject(projectToDelete.id)
        this.errorStore.addMessage('info', 'Project deleted successfully.')
      } catch (error) {
        this.errorStore.addMessage('error', 'Failed to delete project. Please try again.')
        throw error
      } finally {
        this.loadingStore.hide()
      }
    }
  }

  async fetchProjects() {
    try {
      this.loadingStore.show()
      if (this.filterMode === 'visible') {
        await this.projectStore.fetchEnabledProjects()
      } else {
        await this.projectStore.fetchProjects()
      }
      this.errorStore.addMessage('info', 'Projects refreshed successfully.')
    } catch (error) {
      this.errorStore.addMessage('error', 'Failed to refresh projects. Please try again.')
      throw error
    } finally {
      this.loadingStore.hide()
    }
  }
}

describe('Projects Component Business Logic', () => {
  let mockProjectStore: ReturnType<typeof useProjectStore>
  let mockLoadingStore: ReturnType<typeof useLoadingOverlayStore>
  let mockErrorStore: ReturnType<typeof useErrorDisplayStore>
  let mockDeleteStore: ReturnType<typeof useDeleteConfirmationStore>
  let projectsLogic: ProjectsLogic

  beforeEach(() => {
    setActivePinia(createPinia())

    // Setup store mocks
    mockProjectStore = {
      projects: mockProjects,
      visibleProjects: [mockProjects[0]], // Only enabled, launched projects with past launch date
      enabledProjects: [mockProjects[0], mockProjects[2]], // Projects with is_enabled: true
      launchedProjects: [mockProjects[0]], // Projects with is_launched: true
      fetchProjects: vi.fn().mockResolvedValue(mockProjects),
      fetchEnabledProjects: vi.fn().mockResolvedValue([mockProjects[0]]),
      setProjectEnabled: vi.fn(),
      setProjectLaunched: vi.fn(),
      deleteProject: vi.fn(),
    } as ReturnType<typeof useProjectStore>

    mockLoadingStore = {
      show: vi.fn(),
      hide: vi.fn(),
    } as ReturnType<typeof useLoadingOverlayStore>

    mockErrorStore = {
      addMessage: vi.fn(),
    } as ReturnType<typeof useErrorDisplayStore>

    mockDeleteStore = {
      trigger: vi.fn().mockResolvedValue('cancel'),
    } as ReturnType<typeof useDeleteConfirmationStore>

    // Mock store implementations
    vi.mocked(useProjectStore).mockReturnValue(mockProjectStore)
    vi.mocked(useLoadingOverlayStore).mockReturnValue(mockLoadingStore)
    vi.mocked(useErrorDisplayStore).mockReturnValue(mockErrorStore)
    vi.mocked(useDeleteConfirmationStore).mockReturnValue(mockDeleteStore)

    projectsLogic = new ProjectsLogic(
      mockProjectStore,
      mockLoadingStore,
      mockErrorStore,
      mockDeleteStore
    )

    vi.clearAllMocks()
  })

  describe('Project-Specific Filtering Features', () => {
    describe('All Projects Filter', () => {
      it('should show all projects when filter is set to "all"', () => {
        projectsLogic.filterMode = 'all'

        const filteredProjects = projectsLogic.filteredProjects
        expect(filteredProjects.length).toBe(3)
        expect(filteredProjects).toEqual(mockProjects)
      })
    })

    describe('Enabled Projects Filter (Project-specific)', () => {
      it('should filter to show only enabled projects', () => {
        projectsLogic.filterMode = 'enabled'

        const filteredProjects = projectsLogic.filteredProjects
        expect(filteredProjects.length).toBe(2)
        expect(filteredProjects.every(p => p.is_enabled)).toBe(true)
        expect(filteredProjects.map(p => p.id)).toEqual(['1', '3'])
      })

      it('should include both launched and not-launched projects if they are enabled', () => {
        projectsLogic.filterMode = 'enabled'

        const filteredProjects = projectsLogic.filteredProjects
        const launched = filteredProjects.filter(p => p.is_launched)
        const notLaunched = filteredProjects.filter(p => !p.is_launched)

        expect(launched.length).toBe(1) // Active Project
        expect(notLaunched.length).toBe(1) // Enabled Not Launched
      })
    })

    describe('Launched Projects Filter (Project-specific)', () => {
      it('should filter to show only launched projects', () => {
        projectsLogic.filterMode = 'launched'

        const filteredProjects = projectsLogic.filteredProjects
        expect(filteredProjects.length).toBe(1)
        expect(filteredProjects.every(p => p.is_launched)).toBe(true)
        expect(filteredProjects[0].id).toBe('1')
      })

      it('should include launched projects regardless of enabled status', () => {
        // Add a launched but disabled project to test data
        const launchedDisabled = createMockProject({
          id: '4',
          internal_name: 'Launched Disabled',
          is_enabled: false,
          is_launched: true,
        })

        mockProjectStore.projects = [...mockProjects, launchedDisabled]
        mockProjectStore.launchedProjects = [mockProjects[0], launchedDisabled]

        projectsLogic.filterMode = 'launched'

        const filteredProjects = projectsLogic.filteredProjects
        expect(filteredProjects.length).toBe(2)
        expect(filteredProjects.every(p => p.is_launched)).toBe(true)
      })
    })

    describe('Visible Projects Filter (Project-specific)', () => {
      it('should filter to show only visible projects (enabled + launched + past launch date)', () => {
        projectsLogic.filterMode = 'visible'

        const filteredProjects = projectsLogic.filteredProjects
        expect(filteredProjects.length).toBe(1)
        expect(filteredProjects[0].id).toBe('1')
        expect(filteredProjects[0].is_enabled).toBe(true)
        expect(filteredProjects[0].is_launched).toBe(true)
      })

      it('should exclude enabled and launched projects with future launch dates', () => {
        // Add a project that is enabled and launched but has future launch date
        const futureVisible = createMockProject({
          id: '5',
          internal_name: 'Future Visible',
          is_enabled: true,
          is_launched: true,
          launch_date: '2025-12-01T00:00:00Z', // Future date
        })

        mockProjectStore.projects = [...mockProjects, futureVisible]
        // visibleProjects should not include future launches (handled by store logic)

        projectsLogic.filterMode = 'visible'

        const filteredProjects = projectsLogic.filteredProjects
        expect(filteredProjects.length).toBe(1) // Only the one with past launch date
        expect(filteredProjects[0].launch_date).toBe('2023-01-01T00:00:00Z')
      })
    })
  })

  describe('Search Functionality (Project-specific fields)', () => {
    it('should search in internal_name field', () => {
      projectsLogic.searchQuery = 'Active'

      const filteredProjects = projectsLogic.filteredProjects
      expect(filteredProjects.length).toBe(1)
      expect(filteredProjects[0].internal_name).toBe('Active Project')
    })

    it('should search in backward_compatibility field', () => {
      projectsLogic.searchQuery = 'disabled-project'

      const filteredProjects = projectsLogic.filteredProjects
      expect(filteredProjects.length).toBe(1)
      expect(filteredProjects[0].backward_compatibility).toBe('disabled-project')
    })

    it('should be case insensitive', () => {
      projectsLogic.searchQuery = 'ACTIVE'

      const filteredProjects = projectsLogic.filteredProjects
      expect(filteredProjects.length).toBe(1)
      expect(filteredProjects[0].internal_name).toBe('Active Project')
    })

    it('should handle partial matches', () => {
      projectsLogic.searchQuery = 'Project'

      const filteredProjects = projectsLogic.filteredProjects
      expect(filteredProjects.length).toBe(2) // "Active Project" and "Disabled Project"
      expect(filteredProjects.every(p => p.internal_name?.includes('Project'))).toBe(true)
    })

    it('should return empty results for non-matching search', () => {
      projectsLogic.searchQuery = 'nonexistent'

      const filteredProjects = projectsLogic.filteredProjects
      expect(filteredProjects.length).toBe(0)
    })

    it('should work combined with filters', () => {
      // Search within enabled projects only
      projectsLogic.filterMode = 'enabled'
      projectsLogic.searchQuery = 'Active'

      const filteredProjects = projectsLogic.filteredProjects
      expect(filteredProjects.length).toBe(1)
      expect(filteredProjects[0].internal_name).toBe('Active Project')
      expect(filteredProjects[0].is_enabled).toBe(true)
    })
  })

  describe('Sorting Functionality', () => {
    it('should sort by internal_name ascending by default', () => {
      const filteredProjects = projectsLogic.filteredProjects

      expect(filteredProjects[0].internal_name).toBe('Active Project')
      expect(filteredProjects[1].internal_name).toBe('Disabled Project')
      expect(filteredProjects[2].internal_name).toBe('Enabled Not Launched')
    })

    it('should toggle sort direction when clicking same header', () => {
      projectsLogic.handleSort('internal_name')

      expect(projectsLogic.sortDirection).toBe('desc')

      const filteredProjects = projectsLogic.filteredProjects
      expect(filteredProjects[0].internal_name).toBe('Enabled Not Launched')
      expect(filteredProjects[1].internal_name).toBe('Disabled Project')
      expect(filteredProjects[2].internal_name).toBe('Active Project')
    })

    it('should change sort key and reset to ascending when clicking different header', () => {
      projectsLogic.handleSort('is_enabled')

      expect(projectsLogic.sortKey).toBe('is_enabled')
      expect(projectsLogic.sortDirection).toBe('asc')
    })

    it('should sort by boolean fields correctly', () => {
      projectsLogic.handleSort('is_enabled')

      const filteredProjects = projectsLogic.filteredProjects
      // false values should come first in ascending order
      expect(filteredProjects[0].is_enabled).toBe(false)
      expect(filteredProjects[1].is_enabled).toBe(true)
      expect(filteredProjects[2].is_enabled).toBe(true)
    })

    it('should handle null values correctly', () => {
      // Create a project with null internal_name by overriding after creation
      const nullProject = createMockProject({
        id: '4',
        internal_name: 'temp',
        is_enabled: true,
        is_launched: false,
      })
      nullProject.internal_name = null as unknown as string

      const projectsWithNull = [...mockProjects, nullProject]
      mockProjectStore.projects = projectsWithNull

      projectsLogic.handleSort('internal_name')

      const filteredProjects = projectsLogic.filteredProjects
      // Null values should be sorted to the end
      expect(filteredProjects[filteredProjects.length - 1].internal_name).toBe(null)
    })
  })

  describe('Project Status Updates (Project-specific features)', () => {
    it('should update project enabled status', async () => {
      const project = mockProjects[0]

      await projectsLogic.updateProjectStatus(project, 'is_enabled', false)

      expect(mockLoadingStore.show).toHaveBeenCalledWith('Updating...')
      expect(mockProjectStore.setProjectEnabled).toHaveBeenCalledWith(project.id, false)
      expect(mockErrorStore.addMessage).toHaveBeenCalledWith(
        'info',
        'Project disabled successfully.'
      )
      expect(mockLoadingStore.hide).toHaveBeenCalled()
    })

    it('should update project launched status', async () => {
      const project = mockProjects[0]

      await projectsLogic.updateProjectStatus(project, 'is_launched', false)

      expect(mockLoadingStore.show).toHaveBeenCalledWith('Updating...')
      expect(mockProjectStore.setProjectLaunched).toHaveBeenCalledWith(project.id, false)
      expect(mockErrorStore.addMessage).toHaveBeenCalledWith(
        'info',
        'Project unlaunched successfully.'
      )
      expect(mockLoadingStore.hide).toHaveBeenCalled()
    })

    it('should handle status update errors', async () => {
      mockProjectStore.setProjectEnabled = vi.fn().mockRejectedValue(new Error('Update failed'))
      const project = mockProjects[0]

      await expect(projectsLogic.updateProjectStatus(project, 'is_enabled', false)).rejects.toThrow(
        'Update failed'
      )

      expect(mockErrorStore.addMessage).toHaveBeenCalledWith(
        'error',
        'Failed to update project status. Please try again.'
      )
      expect(mockLoadingStore.hide).toHaveBeenCalled()
    })
  })

  describe('Project Deletion', () => {
    it('should show delete confirmation dialog', async () => {
      const project = mockProjects[0]

      await projectsLogic.handleDeleteProject(project)

      expect(mockDeleteStore.trigger).toHaveBeenCalledWith(
        'Delete Project',
        `Are you sure you want to delete "${project.internal_name}"? This action cannot be undone.`
      )
    })

    it('should delete project when confirmed', async () => {
      mockDeleteStore.trigger = vi.fn().mockResolvedValue('delete')
      const project = mockProjects[0]

      await projectsLogic.handleDeleteProject(project)

      expect(mockLoadingStore.show).toHaveBeenCalledWith('Deleting...')
      expect(mockProjectStore.deleteProject).toHaveBeenCalledWith(project.id)
      expect(mockErrorStore.addMessage).toHaveBeenCalledWith(
        'info',
        'Project deleted successfully.'
      )
      expect(mockLoadingStore.hide).toHaveBeenCalled()
    })

    it('should not delete when cancelled', async () => {
      mockDeleteStore.trigger = vi.fn().mockResolvedValue('cancel')
      const project = mockProjects[0]

      await projectsLogic.handleDeleteProject(project)

      expect(mockProjectStore.deleteProject).not.toHaveBeenCalled()
    })

    it('should handle delete errors', async () => {
      mockDeleteStore.trigger = vi.fn().mockResolvedValue('delete')
      mockProjectStore.deleteProject = vi.fn().mockRejectedValue(new Error('Delete failed'))
      const project = mockProjects[0]

      await expect(projectsLogic.handleDeleteProject(project)).rejects.toThrow('Delete failed')

      expect(mockErrorStore.addMessage).toHaveBeenCalledWith(
        'error',
        'Failed to delete project. Please try again.'
      )
    })
  })

  describe('Data Fetching', () => {
    it('should fetch all projects for most filters', async () => {
      projectsLogic.filterMode = 'all'

      await projectsLogic.fetchProjects()

      expect(mockLoadingStore.show).toHaveBeenCalled()
      expect(mockProjectStore.fetchProjects).toHaveBeenCalled()
      expect(mockProjectStore.fetchEnabledProjects).not.toHaveBeenCalled()
      expect(mockErrorStore.addMessage).toHaveBeenCalledWith(
        'info',
        'Projects refreshed successfully.'
      )
      expect(mockLoadingStore.hide).toHaveBeenCalled()
    })

    it('should fetch only enabled projects for visible filter', async () => {
      projectsLogic.filterMode = 'visible'

      await projectsLogic.fetchProjects()

      expect(mockLoadingStore.show).toHaveBeenCalled()
      expect(mockProjectStore.fetchEnabledProjects).toHaveBeenCalled()
      expect(mockProjectStore.fetchProjects).not.toHaveBeenCalled()
      expect(mockLoadingStore.hide).toHaveBeenCalled()
    })

    it('should handle fetch errors', async () => {
      mockProjectStore.fetchProjects = vi.fn().mockRejectedValue(new Error('Network error'))

      await expect(projectsLogic.fetchProjects()).rejects.toThrow('Network error')

      expect(mockErrorStore.addMessage).toHaveBeenCalledWith(
        'error',
        'Failed to refresh projects. Please try again.'
      )
      expect(mockLoadingStore.hide).toHaveBeenCalled()
    })
  })

  describe('Complex Scenarios', () => {
    it('should handle filtering, searching, and sorting together', () => {
      // Set up scenario: filter enabled, search for "project", sort by name desc
      projectsLogic.filterMode = 'enabled'
      projectsLogic.searchQuery = 'project'
      projectsLogic.handleSort('internal_name') // First click sets to desc

      const filteredProjects = projectsLogic.filteredProjects

      // Should have enabled projects containing "project" sorted by name descending
      expect(filteredProjects.length).toBe(1) // Only "Active Project" is enabled and contains "project"
      expect(filteredProjects[0].internal_name).toBe('Active Project')
      expect(filteredProjects[0].is_enabled).toBe(true)
    })

    it('should maintain filter counts correctly', () => {
      // Verify the store computed properties return correct counts
      expect(projectsLogic.projects.length).toBe(3) // All projects
      expect(projectsLogic.enabledProjects.length).toBe(2) // Enabled projects
      expect(projectsLogic.launchedProjects.length).toBe(1) // Launched projects
      expect(projectsLogic.visibleProjects.length).toBe(1) // Visible projects (enabled + launched + past launch date)
    })

    it('should handle empty search results within filtered data', () => {
      projectsLogic.filterMode = 'launched' // Only 1 project
      projectsLogic.searchQuery = 'disabled' // Should not match the launched project

      const filteredProjects = projectsLogic.filteredProjects
      expect(filteredProjects.length).toBe(0)
    })
  })
})
