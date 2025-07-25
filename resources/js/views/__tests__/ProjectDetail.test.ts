import { describe, it, expect, vi, beforeEach, beforeAll, afterAll } from 'vitest'
import { mount } from '@vue/test-utils'
import { createRouter, createWebHistory, type Router } from 'vue-router'
import { flushPromises } from '@vue/test-utils'
import ProjectDetail from '../ProjectDetail.vue'
import type { ProjectResource } from '@metanull/inventory-app-api-client'

// Mock console.error to avoid noise in test output
vi.mock('console', () => ({
  error: vi.fn(),
  warn: vi.fn(),
  log: vi.fn(),
}))

// Store original console methods for cleanup
let originalConsole: Record<string, unknown>

beforeAll(() => {
  originalConsole = { ...console }
  console.error = vi.fn()
  console.warn = vi.fn()
  console.log = vi.fn()
})

afterAll(() => {
  Object.assign(console, originalConsole)
})

// Mock icon modules with comprehensive exports
vi.mock('@heroicons/vue/24/solid', () => ({
  CheckIcon: { name: 'CheckIcon', render: () => null },
  XMarkIcon: { name: 'XMarkIcon', render: () => null },
  PlusIcon: { name: 'PlusIcon', render: () => null },
  ArrowLeftIcon: { name: 'ArrowLeftIcon', render: () => null },
  TrashIcon: { name: 'TrashIcon', render: () => null },
  PencilIcon: { name: 'PencilIcon', render: () => null },
  EyeIcon: { name: 'EyeIcon', render: () => null },
  CheckCircleIcon: { name: 'CheckCircleIcon', render: () => null },
  XCircleIcon: { name: 'XCircleIcon', render: () => null },
  RocketLaunchIcon: { name: 'RocketLaunchIcon', render: () => null },
  ArchiveBoxIcon: { name: 'ArchiveBoxIcon', render: () => null },
  FolderIcon: { name: 'FolderIcon', render: () => null },
}))

vi.mock('@heroicons/vue/24/outline', () => ({
  CheckIcon: { name: 'CheckIcon', render: () => null },
  XMarkIcon: { name: 'XMarkIcon', render: () => null },
  PlusIcon: { name: 'PlusIcon', render: () => null },
  ArrowLeftIcon: { name: 'ArrowLeftIcon', render: () => null },
  TrashIcon: { name: 'TrashIcon', render: () => null },
  PencilIcon: { name: 'PencilIcon', render: () => null },
  EyeIcon: { name: 'EyeIcon', render: () => null },
}))

// Mock stores
const mockProjectStore = {
  currentProject: null as ProjectResource | null,
  loading: false,
  fetchProject: vi.fn(),
  clearCurrentProject: vi.fn(),
  createProject: vi.fn(),
  updateProject: vi.fn(),
  deleteProject: vi.fn(),
  setProjectEnabled: vi.fn(),
  setProjectLaunched: vi.fn(),
}

const mockContextStore = {
  contexts: [],
  loading: false,
  fetchContexts: vi.fn(),
  defaultContext: null,
}

const mockLanguageStore = {
  languages: [],
  loading: false,
  fetchLanguages: vi.fn(),
  defaultLanguage: null,
}

const mockLoadingOverlayStore = {
  show: vi.fn(),
  hide: vi.fn(),
}

const mockErrorDisplayStore = {
  addMessage: vi.fn(),
}

const mockDeleteConfirmationStore = {
  trigger: vi.fn(),
}

const mockCancelChangesConfirmationStore = {
  trigger: vi.fn(),
}

vi.mock('@/stores/project', () => ({
  useProjectStore: () => mockProjectStore,
}))

vi.mock('@/stores/context', () => ({
  useContextStore: () => mockContextStore,
}))

vi.mock('@/stores/language', () => ({
  useLanguageStore: () => mockLanguageStore,
}))

vi.mock('@/stores/loadingOverlay', () => ({
  useLoadingOverlayStore: () => mockLoadingOverlayStore,
}))

vi.mock('@/stores/errorDisplay', () => ({
  useErrorDisplayStore: () => mockErrorDisplayStore,
}))

vi.mock('@/stores/deleteConfirmation', () => ({
  useDeleteConfirmationStore: () => mockDeleteConfirmationStore,
}))

vi.mock('@/stores/cancelChangesConfirmation', () => ({
  useCancelChangesConfirmationStore: () => mockCancelChangesConfirmationStore,
}))

describe('ProjectDetail Component', () => {
  let router: Router

  beforeEach(() => {
    // Reset all mocks
    vi.clearAllMocks()

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
  })

  describe('Component Mounting', () => {
    it('should mount correctly for project creation', async () => {
      router.push('/projects/new')
      await router.isReady()

      const wrapper = mount(ProjectDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()
      expect(wrapper.exists()).toBe(true)
    })

    it('should mount correctly for existing project view', async () => {
      const mockProject = {
        id: '1',
        internal_name: 'Test Project',
        display_name: 'Test Project Display',
        backward_compatibility: null,
        is_enabled: true,
        is_launched: false,
        launch_date: null,
        created_at: '2023-01-01T00:00:00Z',
        updated_at: '2023-01-01T00:00:00Z',
      }

      mockProjectStore.fetchProject.mockResolvedValue(mockProject)
      mockProjectStore.currentProject = mockProject

      router.push('/projects/1')
      await router.isReady()

      const wrapper = mount(ProjectDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()
      expect(wrapper.exists()).toBe(true)
      expect(mockProjectStore.fetchProject).toHaveBeenCalledWith('1')
    })

    it('should mount correctly for project edit', async () => {
      const mockProject = {
        id: '1',
        internal_name: 'Test Project',
        display_name: 'Test Project Display',
        backward_compatibility: null,
        is_enabled: true,
        is_launched: false,
        launch_date: null,
        created_at: '2023-01-01T00:00:00Z',
        updated_at: '2023-01-01T00:00:00Z',
      }

      mockProjectStore.fetchProject.mockResolvedValue(mockProject)
      mockProjectStore.currentProject = mockProject

      router.push('/projects/1/edit')
      await router.isReady()

      const wrapper = mount(ProjectDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()
      expect(wrapper.exists()).toBe(true)
      expect(mockProjectStore.fetchProject).toHaveBeenCalledWith('1')
    })
  })

  describe('Data Loading', () => {
    it('should load contexts and languages on mount', async () => {
      mockContextStore.fetchContexts.mockResolvedValue([])
      mockLanguageStore.fetchLanguages.mockResolvedValue([])

      router.push('/projects/new')
      await router.isReady()

      mount(ProjectDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      expect(mockContextStore.fetchContexts).toHaveBeenCalled()
      expect(mockLanguageStore.fetchLanguages).toHaveBeenCalled()
    })

    it('should handle project fetch errors gracefully', async () => {
      const fetchError = new Error('Failed to fetch project')
      mockProjectStore.fetchProject.mockRejectedValue(fetchError)

      router.push('/projects/1')
      await router.isReady()

      mount(ProjectDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      expect(mockErrorDisplayStore.addMessage).toHaveBeenCalledWith(
        'error',
        'Failed to load project. Please try again.'
      )
    })
  })

  describe('Route Parameter Handling', () => {
    it('should handle string route parameters correctly', async () => {
      const mockProject = {
        id: '1',
        internal_name: 'Test Project',
        display_name: 'Test Project Display',
        backward_compatibility: null,
        is_enabled: true,
        is_launched: false,
        launch_date: null,
        created_at: '2023-01-01T00:00:00Z',
        updated_at: '2023-01-01T00:00:00Z',
      }

      mockProjectStore.fetchProject.mockResolvedValue(mockProject)

      router.push('/projects/123')
      await router.isReady()

      mount(ProjectDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      // Should call fetchProject with string "123"
      expect(mockProjectStore.fetchProject).toHaveBeenCalledWith('123')
    })

    it('should handle invalid route parameters', async () => {
      router.push('/projects/invalid')
      await router.isReady()

      mount(ProjectDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      // Should attempt to fetch even with invalid ID
      expect(mockProjectStore.fetchProject).toHaveBeenCalledWith('invalid')
    })
  })

  describe('Loading States', () => {
    it('should show loading overlay during data fetching', async () => {
      mockProjectStore.fetchProject.mockImplementation(
        () =>
          new Promise(resolve =>
            setTimeout(
              () =>
                resolve({
                  id: '1',
                  internal_name: 'Test Project',
                  display_name: 'Test Project Display',
                  is_enabled: true,
                  is_launched: false,
                }),
              100
            )
          )
      )

      router.push('/projects/1')
      await router.isReady()

      mount(ProjectDetail, {
        global: {
          plugins: [router],
        },
      })

      // Should set loading state
      expect(mockLoadingOverlayStore.show).toHaveBeenCalled()

      // Wait for the async mock to complete (100ms timeout)
      await new Promise(resolve => setTimeout(resolve, 150))
      await flushPromises()

      // Should clear loading state after completion
      expect(mockLoadingOverlayStore.hide).toHaveBeenCalled()
    })
  })
})
