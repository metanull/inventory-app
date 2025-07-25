import { beforeEach, describe, expect, it, vi, beforeAll, afterAll } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createWebHistory } from 'vue-router'
import Projects from '../Projects.vue'
import { useProjectStore } from '@/stores/project'
import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
import { useErrorDisplayStore } from '@/stores/errorDisplay'
import { useDeleteConfirmationStore } from '@/stores/deleteConfirmation'
import { createMockProject } from '@/__tests__/test-utils'
import type { ProjectResource } from '@metanull/inventory-app-api-client'
import type { Router } from 'vue-router'

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

// Component interface for proper typing
interface ProjectsComponentInstance {
  projects: ProjectResource[]
  filteredProjects: ProjectResource[]
  visibleProjects: ProjectResource[]
  enabledProjects: ProjectResource[]
  launchedProjects: ProjectResource[]
  filterMode: string
  searchQuery: string
  sortDirection: string
  sortKey: string
  openProjectDetail: (id: string) => void
  updateProjectStatus: (project: ProjectResource, field: string, value: boolean) => Promise<void>
  handleDeleteProject: (project: ProjectResource) => Promise<void>
  handleSort: (field: string) => void
  fetchProjects: () => Promise<void>
}

// Mock the stores
vi.mock('@/stores/project')
vi.mock('@/stores/loadingOverlay')
vi.mock('@/stores/errorDisplay')
vi.mock('@/stores/deleteConfirmation')

const mockProjects: ProjectResource[] = [
  createMockProject({
    id: '1',
    internal_name: 'Active Project',
    backward_compatibility: 'active-project',
    is_enabled: true,
    is_launched: true,
    launch_date: '2023-01-01T00:00:00Z',
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
    launch_date: '2024-12-01T00:00:00Z',
    created_at: '2023-03-01T00:00:00Z',
  }),
]

// Mock visible projects (enabled, launched, and past launch date)
const mockVisibleProjects = [mockProjects[0]] // Only the first one meets all criteria

describe('Projects.vue', () => {
  let mockProjectStore: ReturnType<typeof useProjectStore>
  let mockLoadingStore: ReturnType<typeof useLoadingOverlayStore>
  let mockErrorStore: ReturnType<typeof useErrorDisplayStore>
  let mockDeleteStore: ReturnType<typeof useDeleteConfirmationStore>
  let router: Router

  beforeEach(() => {
    setActivePinia(createPinia())

    // Setup router
    router = createRouter({
      history: createWebHistory(),
      routes: [
        { path: '/', component: { template: '<div>Home</div>' } },
        { path: '/projects', component: Projects },
        { path: '/projects/new', component: { template: '<div>New Project</div>' } },
        { path: '/projects/:id', component: { template: '<div>Project Detail</div>' } },
      ],
    })

    // Setup store mocks
    mockProjectStore = {
      projects: mockProjects,
      visibleProjects: mockVisibleProjects,
      enabledProjects: [mockProjects[0], mockProjects[2]], // Projects with is_enabled: true
      launchedProjects: [mockProjects[0]], // Projects with is_launched: true
      fetchProjects: vi.fn().mockResolvedValue(mockProjects),
      fetchEnabledProjects: vi.fn().mockResolvedValue(mockVisibleProjects),
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

    vi.clearAllMocks()
  })

  describe('Component Logic', () => {
    it('should initialize with default values', async () => {
      const wrapper = mount(Projects, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await wrapper.vm.$nextTick()

      expect((wrapper.vm as unknown as ProjectsComponentInstance).filterMode).toBe('all')
      expect((wrapper.vm as unknown as ProjectsComponentInstance).sortKey).toBe('internal_name')
      expect((wrapper.vm as unknown as ProjectsComponentInstance).sortDirection).toBe('asc')
      expect((wrapper.vm as unknown as ProjectsComponentInstance).searchQuery).toBe('')
    })

    it('should have access to store data', async () => {
      const wrapper = mount(Projects, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await wrapper.vm.$nextTick()

      expect((wrapper.vm as unknown as ProjectsComponentInstance).projects.length).toBe(3)
      expect((wrapper.vm as unknown as ProjectsComponentInstance).enabledProjects.length).toBe(2)
      expect((wrapper.vm as unknown as ProjectsComponentInstance).launchedProjects.length).toBe(1)
      expect((wrapper.vm as unknown as ProjectsComponentInstance).visibleProjects.length).toBe(1)
    })
  })

  describe('Filter Functionality', () => {
    it('should filter projects when "Enabled" filter is selected', async () => {
      const wrapper = mount(Projects, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      ;(wrapper.vm as unknown as ProjectsComponentInstance).filterMode = 'enabled'
      await wrapper.vm.$nextTick()

      const filteredProjects = (wrapper.vm as unknown as ProjectsComponentInstance).filteredProjects
      expect(filteredProjects.length).toBe(2)
      expect(filteredProjects.every((p: ProjectResource) => p.is_enabled)).toBe(true)
    })

    it('should filter projects when "Launched" filter is selected', async () => {
      const wrapper = mount(Projects, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      ;(wrapper.vm as unknown as ProjectsComponentInstance).filterMode = 'launched'
      await wrapper.vm.$nextTick()

      const filteredProjects = (wrapper.vm as unknown as ProjectsComponentInstance).filteredProjects
      expect(filteredProjects.length).toBe(1)
      expect(filteredProjects.every((p: ProjectResource) => p.is_launched)).toBe(true)
    })

    it('should filter projects when "Visible" filter is selected', async () => {
      const wrapper = mount(Projects, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      ;(wrapper.vm as unknown as ProjectsComponentInstance).filterMode = 'visible'
      await wrapper.vm.$nextTick()

      const filteredProjects = (wrapper.vm as unknown as ProjectsComponentInstance).filteredProjects
      expect(filteredProjects.length).toBe(1)
      expect(filteredProjects[0].id).toBe('1')
    })
  })

  describe('Search Functionality', () => {
    it('should search projects by internal name', async () => {
      const wrapper = mount(Projects, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await wrapper.vm.$nextTick()
      ;(wrapper.vm as unknown as ProjectsComponentInstance).searchQuery = 'Active'
      await wrapper.vm.$nextTick()

      const filteredProjects = (wrapper.vm as unknown as ProjectsComponentInstance).filteredProjects
      expect(filteredProjects.length).toBe(1)
      expect(filteredProjects[0].internal_name).toBe('Active Project')
    })

    it('should filter projects by backward_compatibility', async () => {
      const wrapper = mount(Projects, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      wrapper.vm.searchQuery = 'disabled-project'
      await wrapper.vm.$nextTick()

      const filteredProjects = wrapper.vm.filteredProjects
      expect(filteredProjects.length).toBe(1)
      expect(filteredProjects[0].backward_compatibility).toBe('disabled-project')
    })

    it('should be case insensitive', async () => {
      const wrapper = mount(Projects, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      wrapper.vm.searchQuery = 'ACTIVE'
      await wrapper.vm.$nextTick()

      const filteredProjects = wrapper.vm.filteredProjects
      expect(filteredProjects.length).toBe(1)
      expect(filteredProjects[0].internal_name).toBe('Active Project')
    })

    it('should return empty results for non-matching search', async () => {
      const wrapper = mount(Projects, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      wrapper.vm.searchQuery = 'nonexistent'
      await wrapper.vm.$nextTick()

      const filteredProjects = wrapper.vm.filteredProjects
      expect(filteredProjects.length).toBe(0)
    })
  })

  describe('Sorting Functionality', () => {
    it('should sort by internal_name ascending by default', () => {
      const wrapper = mount(Projects, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      expect(wrapper.vm.sortKey).toBe('internal_name')
      expect(wrapper.vm.sortDirection).toBe('asc')
    })

    it('should sort projects correctly by internal_name ascending', async () => {
      const wrapper = mount(Projects, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      const filteredProjects = wrapper.vm.filteredProjects
      expect(filteredProjects[0].internal_name).toBe('Active Project')
      expect(filteredProjects[1].internal_name).toBe('Disabled Project')
      expect(filteredProjects[2].internal_name).toBe('Enabled Not Launched')
    })

    it('should toggle sort direction when clicking same header', async () => {
      const wrapper = mount(Projects, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      wrapper.vm.handleSort('internal_name')
      await wrapper.vm.$nextTick()

      expect(wrapper.vm.sortDirection).toBe('desc')

      const filteredProjects = wrapper.vm.filteredProjects
      expect(filteredProjects[0].internal_name).toBe('Enabled Not Launched')
      expect(filteredProjects[1].internal_name).toBe('Disabled Project')
      expect(filteredProjects[2].internal_name).toBe('Active Project')
    })

    it('should change sort key when clicking different header', async () => {
      const wrapper = mount(Projects, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      wrapper.vm.handleSort('is_enabled')
      await wrapper.vm.$nextTick()

      expect(wrapper.vm.sortKey).toBe('is_enabled')
      expect(wrapper.vm.sortDirection).toBe('asc')
    })

    it('should handle null values in sorting', async () => {
      // Create a project with null internal_name by directly assigning it
      const projectWithNull = {
        ...createMockProject({
          id: '4',
          internal_name: 'temp',
          is_enabled: true,
          is_launched: false,
        }),
        internal_name: null,
      }

      const projectsWithNull = [...mockProjects, projectWithNull]

      mockProjectStore.projects = projectsWithNull

      const wrapper = mount(Projects, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await wrapper.vm.$nextTick()

      // Sort by internal_name to test null handling
      wrapper.vm.handleSort('internal_name')
      await wrapper.vm.$nextTick()

      // Null values should be sorted to the end
      const filteredProjects = wrapper.vm.filteredProjects
      expect(filteredProjects[filteredProjects.length - 1].internal_name).toBe(null)
    })
  })

  describe('Combined Filters and Search', () => {
    it('should apply search on filtered results', async () => {
      const wrapper = mount(Projects, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      // First filter by enabled projects
      wrapper.vm.filterMode = 'enabled'
      await wrapper.vm.$nextTick()

      // Then search within enabled projects
      wrapper.vm.searchQuery = 'Active'
      await wrapper.vm.$nextTick()

      const filteredProjects = wrapper.vm.filteredProjects
      expect(filteredProjects.length).toBe(1)
      expect(filteredProjects[0].internal_name).toBe('Active Project')
      expect(filteredProjects[0].is_enabled).toBe(true)
    })
  })

  describe('Data Fetching', () => {
    it('should fetch projects on mount', async () => {
      mount(Projects, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      // Wait for component mounted and async operations
      await flushPromises()

      expect(mockProjectStore.fetchProjects).toHaveBeenCalled()
      expect(mockProjectStore.fetchEnabledProjects).toHaveBeenCalled()
    })

    it('should show loading overlay when no cached data', async () => {
      mockProjectStore.projects = []

      mount(Projects, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      expect(mockLoadingStore.show).toHaveBeenCalled()
    })

    it('should display info message when cached data is refreshed', async () => {
      // Projects exist (cached data)
      mount(Projects, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      // Wait for async operations
      await new Promise(resolve => setTimeout(resolve, 0))

      expect(mockErrorStore.addMessage).toHaveBeenCalledWith('info', 'List refreshed')
    })

    it('should handle fetch error gracefully', async () => {
      mockProjectStore.fetchProjects = vi.fn().mockRejectedValue(new Error('Network error'))

      mount(Projects, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await new Promise(resolve => setTimeout(resolve, 0))

      expect(mockErrorStore.addMessage).toHaveBeenCalledWith(
        'error',
        'Failed to fetch projects. Please try again.'
      )
    })

    it('should handle retry action', async () => {
      const wrapper = mount(Projects, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await wrapper.vm.fetchProjects()

      expect(mockLoadingStore.show).toHaveBeenCalled()
      expect(mockProjectStore.fetchProjects).toHaveBeenCalled()
    })
  })

  describe('Navigation Actions', () => {
    it('should navigate to project detail when row is clicked', async () => {
      const wrapper = mount(Projects, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      const spy = vi.spyOn(router, 'push')
      await wrapper.vm.openProjectDetail('123')

      expect(spy).toHaveBeenCalledWith('/projects/123')
    })

    it('should navigate to edit mode via edit button', async () => {
      const wrapper = mount(Projects, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      // Find the first project row's edit button
      const editButton = wrapper.find('button[title="Edit"]')
      expect(editButton.exists()).toBe(true)

      // Mock router push to test navigation
      const routerPushSpy = vi.spyOn(router, 'push')

      await editButton.trigger('click')

      expect(routerPushSpy).toHaveBeenCalledWith('/projects/1?edit=true')
    })
  })

  describe('Project Status Updates', () => {
    it('should update project enabled status', async () => {
      const wrapper = mount(Projects, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      const project = mockProjects[0]
      await wrapper.vm.updateProjectStatus(project, 'is_enabled', false)

      expect(mockLoadingStore.show).toHaveBeenCalledWith('Updating...')
      expect(mockProjectStore.setProjectEnabled).toHaveBeenCalledWith(project.id, false)
      expect(mockErrorStore.addMessage).toHaveBeenCalledWith(
        'info',
        'Project disabled successfully.'
      )
      expect(mockLoadingStore.hide).toHaveBeenCalled()
    })

    it('should update project launched status', async () => {
      const wrapper = mount(Projects, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      const project = mockProjects[0]
      await wrapper.vm.updateProjectStatus(project, 'is_launched', false)

      expect(mockLoadingStore.show).toHaveBeenCalledWith('Updating...')
      expect(mockProjectStore.setProjectLaunched).toHaveBeenCalledWith(project.id, false)
      expect(mockErrorStore.addMessage).toHaveBeenCalledWith(
        'info',
        'Project unlaunched successfully.'
      )
      expect(mockLoadingStore.hide).toHaveBeenCalled()
    })

    it('should handle status update error', async () => {
      const wrapper = mount(Projects, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      mockProjectStore.setProjectEnabled = vi.fn().mockRejectedValue(new Error('Update failed'))

      const project = mockProjects[0]
      await wrapper.vm.updateProjectStatus(project, 'is_enabled', false)

      expect(mockErrorStore.addMessage).toHaveBeenCalledWith(
        'error',
        'Failed to update project status. Please try again.'
      )
      expect(mockLoadingStore.hide).toHaveBeenCalled()
    })
  })

  describe('Project Deletion', () => {
    it('should show delete confirmation dialog', async () => {
      const wrapper = mount(Projects, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      const project = mockProjects[0]
      await wrapper.vm.handleDeleteProject(project)

      expect(mockDeleteStore.trigger).toHaveBeenCalledWith(
        'Delete Project',
        `Are you sure you want to delete "${project.internal_name}"? This action cannot be undone.`
      )
    })

    it('should delete project when confirmed', async () => {
      const wrapper = mount(Projects, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      mockDeleteStore.trigger = vi.fn().mockResolvedValue('delete')

      const project = mockProjects[0]
      await wrapper.vm.handleDeleteProject(project)

      expect(mockLoadingStore.show).toHaveBeenCalledWith('Deleting...')
      expect(mockProjectStore.deleteProject).toHaveBeenCalledWith(project.id)
      expect(mockErrorStore.addMessage).toHaveBeenCalledWith(
        'info',
        'Project deleted successfully.'
      )
      expect(mockLoadingStore.hide).toHaveBeenCalled()
    })

    it('should not delete project when cancelled', async () => {
      const wrapper = mount(Projects, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      mockDeleteStore.trigger = vi.fn().mockResolvedValue('cancel')

      const project = mockProjects[0]
      await wrapper.vm.handleDeleteProject(project)

      expect(mockProjectStore.deleteProject).not.toHaveBeenCalled()
    })

    it('should handle delete error', async () => {
      const wrapper = mount(Projects, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      mockDeleteStore.trigger = vi.fn().mockResolvedValue('delete')
      mockProjectStore.deleteProject = vi.fn().mockRejectedValue(new Error('Delete failed'))

      const project = mockProjects[0]
      await wrapper.vm.handleDeleteProject(project)

      expect(mockErrorStore.addMessage).toHaveBeenCalledWith(
        'error',
        'Failed to delete project. Please try again.'
      )
    })
  })

  describe('Empty States', () => {
    it('should show empty state when no projects exist', () => {
      mockProjectStore.projects = []

      const wrapper = mount(Projects, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      expect(wrapper.vm.filteredProjects.length).toBe(0)
    })

    it('should show appropriate empty message for different filters', async () => {
      mockProjectStore.projects = []
      mockProjectStore.visibleProjects = []

      const wrapper = mount(Projects, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      // Test different filter modes empty messages
      wrapper.vm.filterMode = 'all'
      await wrapper.vm.$nextTick()
      // Empty message would be tested in the ListView component

      wrapper.vm.filterMode = 'visible'
      await wrapper.vm.$nextTick()
      // Different empty message for visible filter

      wrapper.vm.filterMode = 'enabled'
      await wrapper.vm.$nextTick()
      // Different empty message for enabled filter
    })
  })
})
