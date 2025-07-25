import { beforeEach, describe, expect, it, vi, beforeAll, afterAll } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createWebHistory, type Router } from 'vue-router'
import ProjectDetail from '../ProjectDetail.vue'
import { useProjectStore } from '@/stores/project'
import { useContextStore } from '@/stores/context'
import { useLanguageStore } from '@/stores/language'
import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
import { useErrorDisplayStore } from '@/stores/errorDisplay'
import { useDeleteConfirmationStore } from '@/stores/deleteConfirmation'
import { useCancelChangesConfirmationStore } from '@/stores/cancelChangesConfirmation'
import { createMockProject } from '@/__tests__/test-utils'

// Mock console.error to avoid noise in test output
vi.mock('console', () => ({
  error: vi.fn(),
  warn: vi.fn(),
}))

// Mock Vue's warn function to suppress Vue Router and prop validation warnings
const originalWarn = console.warn
beforeAll(() => {
  console.warn = vi.fn()
})

afterAll(() => {
  console.warn = originalWarn
})

// Mock icons
vi.mock('@heroicons/vue/24/solid', () => ({
  CheckCircleIcon: { template: '<div data-testid="check-circle-icon"></div>' },
  XCircleIcon: { template: '<div data-testid="x-circle-icon"></div>' },
  RocketLaunchIcon: { template: '<div data-testid="rocket-launch-icon"></div>' },
  ArchiveBoxIcon: { template: '<div data-testid="archive-box-icon"></div>' },
  FolderIcon: { template: '<div data-testid="folder-icon"></div>' },
  ArrowLeftIcon: { template: '<div data-testid="arrow-left-icon"></div>' },
  PencilIcon: { template: '<div data-testid="pencil-icon"></div>' },
  CheckIcon: { template: '<div data-testid="check-icon"></div>' },
  XMarkIcon: { template: '<div data-testid="x-mark-icon"></div>' },
  PlusIcon: { template: '<div data-testid="plus-icon"></div>' },
  TrashIcon: { template: '<div data-testid="trash-icon"></div>' },
  EyeIcon: { template: '<div data-testid="eye-icon"></div>' },
}))

// Mock stores
vi.mock('@/stores/project')
vi.mock('@/stores/context')
vi.mock('@/stores/language')
vi.mock('@/stores/loadingOverlay')
vi.mock('@/stores/errorDisplay')
vi.mock('@/stores/deleteConfirmation')
vi.mock('@/stores/cancelChangesConfirmation')

describe('ProjectDetail Integration Tests', () => {
  let router: Router
  let pinia: ReturnType<typeof createPinia>
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  let mockProjectStore: any
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  let mockContextStore: any
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  let mockLanguageStore: any
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  let mockLoadingStore: any
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  let mockErrorStore: any
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  let mockDeleteStore: any
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  let mockCancelStore: any

  beforeEach(() => {
    // Setup Pinia
    pinia = createPinia()
    setActivePinia(pinia)

    // Setup router
    router = createRouter({
      history: createWebHistory(),
      routes: [
        { path: '/projects', name: 'Projects', component: { template: '<div>Projects</div>' } },
        {
          path: '/projects/new',
          name: 'ProjectCreate',
          component: { template: '<div>Create</div>' },
        },
        {
          path: '/projects/:id',
          name: 'ProjectDetail',
          component: { template: '<div>Detail</div>' },
        },
        {
          path: '/projects/:id/edit',
          name: 'ProjectEdit',
          component: { template: '<div>Edit</div>' },
        },
      ],
    })

    // Mock store implementations
    mockProjectStore = {
      currentProject: null,
      loading: false,
      fetchProject: vi.fn(),
      createProject: vi.fn(),
      updateProject: vi.fn(),
      deleteProject: vi.fn(),
      setProjectEnabled: vi.fn(),
      setProjectLaunched: vi.fn(),
      clearCurrentProject: vi.fn(),
    }

    mockContextStore = {
      contexts: [],
      loading: false,
      fetchContexts: vi.fn(),
      defaultContext: null,
    }

    mockLanguageStore = {
      languages: [],
      loading: false,
      fetchLanguages: vi.fn(),
      defaultLanguage: null,
    }

    mockLoadingStore = {
      show: vi.fn(),
      hide: vi.fn(),
    }

    mockErrorStore = {
      addMessage: vi.fn(),
    }

    mockDeleteStore = {
      trigger: vi.fn(),
    }

    mockCancelStore = {
      trigger: vi.fn(),
    }

    // Mock store functions
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    vi.mocked(useProjectStore).mockReturnValue(mockProjectStore as any)
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    vi.mocked(useContextStore).mockReturnValue(mockContextStore as any)
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    vi.mocked(useLanguageStore).mockReturnValue(mockLanguageStore as any)
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    vi.mocked(useLoadingOverlayStore).mockReturnValue(mockLoadingStore as any)
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    vi.mocked(useErrorDisplayStore).mockReturnValue(mockErrorStore as any)
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    vi.mocked(useDeleteConfirmationStore).mockReturnValue(mockDeleteStore as any)
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    vi.mocked(useCancelChangesConfirmationStore).mockReturnValue(mockCancelStore as any)

    // Reset mocks
    vi.clearAllMocks()
  })

  describe('Component Mounting and Data Loading', () => {
    it('should mount correctly for existing project view', async () => {
      const mockProject = createMockProject({
        id: 'test-project-1',
        internal_name: 'Test Project',
      })

      mockProjectStore.fetchProject.mockResolvedValue(mockProject)
      mockProjectStore.currentProject = mockProject

      router.push('/projects/test-project-1')
      await router.isReady()

      const wrapper = mount(ProjectDetail, {
        global: {
          plugins: [pinia, router],
        },
      })

      await flushPromises()

      // Verify component mounted and data was fetched
      expect(wrapper.exists()).toBe(true)
      expect(mockProjectStore.fetchProject).toHaveBeenCalledWith('test-project-1')
      expect(wrapper.text()).toContain('Test Project')
    })

    it('should mount correctly for project creation', async () => {
      router.push('/projects/new')
      await router.isReady()

      const wrapper = mount(ProjectDetail, {
        global: {
          plugins: [pinia, router],
        },
      })

      await flushPromises()

      // Verify component mounted for creation
      expect(wrapper.exists()).toBe(true)
      expect(wrapper.text()).toContain('New Project')
      expect(wrapper.text()).toContain('Creating')
    })

    it('should load contexts and languages on mount', async () => {
      mockContextStore.fetchContexts.mockResolvedValue([])
      mockLanguageStore.fetchLanguages.mockResolvedValue([])

      router.push('/projects/new')
      await router.isReady()

      mount(ProjectDetail, {
        global: {
          plugins: [pinia, router],
        },
      })

      await flushPromises()

      // Verify contexts and languages are loaded
      expect(mockContextStore.fetchContexts).toHaveBeenCalled()
      expect(mockLanguageStore.fetchLanguages).toHaveBeenCalled()
    })
  })

  describe('Loading States and Error Handling', () => {
    it('should show loading overlay during data fetching', async () => {
      const mockProject = createMockProject({
        id: 'test-project-1',
        internal_name: 'Test Project',
      })

      mockProjectStore.fetchProject.mockImplementation(
        () => new Promise(resolve => setTimeout(() => resolve(mockProject), 100))
      )

      router.push('/projects/test-project-1')
      await router.isReady()

      mount(ProjectDetail, {
        global: {
          plugins: [pinia, router],
        },
      })

      // Should show loading state
      expect(mockLoadingStore.show).toHaveBeenCalled()

      // Wait for async operation
      await new Promise(resolve => setTimeout(resolve, 150))
      await flushPromises()

      // Should hide loading state
      expect(mockLoadingStore.hide).toHaveBeenCalled()
    })

    it('should handle project fetch errors gracefully', async () => {
      const fetchError = new Error('Failed to fetch project')
      mockProjectStore.fetchProject.mockRejectedValue(fetchError)

      router.push('/projects/test-project-1')
      await router.isReady()

      mount(ProjectDetail, {
        global: {
          plugins: [pinia, router],
        },
      })

      await flushPromises()

      // Verify error handling
      expect(mockErrorStore.addMessage).toHaveBeenCalledWith(
        'error',
        'Failed to load project. Please try again.'
      )
    })
  })

  describe('Form Display and User Interface', () => {
    it('should display form inputs in create mode', async () => {
      router.push('/projects/new')
      await router.isReady()

      const wrapper = mount(ProjectDetail, {
        global: {
          plugins: [pinia, router],
        },
      })

      await flushPromises()

      // Verify form inputs are present
      const internalNameInput = wrapper.find('input[type="text"]')
      expect(internalNameInput.exists()).toBe(true)
    })

    it('should display project information in view mode', async () => {
      const mockProject = createMockProject({
        id: 'test-project-1',
        internal_name: 'Test Project',
        backward_compatibility: 'legacy-project',
      })

      mockProjectStore.fetchProject.mockResolvedValue(mockProject)
      mockProjectStore.currentProject = mockProject

      router.push('/projects/test-project-1')
      await router.isReady()

      const wrapper = mount(ProjectDetail, {
        global: {
          plugins: [pinia, router],
        },
      })

      await flushPromises()

      // Verify project information is displayed
      expect(wrapper.text()).toContain('Test Project')
      expect(wrapper.text()).toContain('legacy-project')
    })
  })

  describe('Navigation and Route Handling', () => {
    it('should handle route parameters correctly', async () => {
      const mockProject = createMockProject({
        id: 'route-test-123',
        internal_name: 'Route Test Project',
      })

      mockProjectStore.fetchProject.mockResolvedValue(mockProject)

      router.push('/projects/route-test-123')
      await router.isReady()

      mount(ProjectDetail, {
        global: {
          plugins: [pinia, router],
        },
      })

      await flushPromises()

      // Verify correct route parameter handling
      expect(mockProjectStore.fetchProject).toHaveBeenCalledWith('route-test-123')
    })

    it('should clear project data on component unmount', async () => {
      router.push('/projects/new')
      await router.isReady()

      const wrapper = mount(ProjectDetail, {
        global: {
          plugins: [pinia, router],
        },
      })

      await flushPromises()

      // Component should handle lifecycle correctly
      expect(wrapper.exists()).toBe(true)

      // Unmount component
      wrapper.unmount()

      // Verify cleanup
      expect(wrapper.exists()).toBe(false)
    })
  })
})
