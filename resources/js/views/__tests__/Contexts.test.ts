import { beforeEach, describe, expect, it, vi, beforeAll, afterAll } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createWebHistory } from 'vue-router'
import Contexts from '../Contexts.vue'
import { useContextStore } from '@/stores/context'
import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
import { useErrorDisplayStore } from '@/stores/errorDisplay'
import { createMockContext } from '@/__tests__/test-utils'
import type { ContextResource } from '@metanull/inventory-app-api-client'
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
interface ContextsComponentInstance {
  contexts: ContextResource[]
  filteredContexts: ContextResource[]
  searchQuery: string
  sortDirection: string
  sortKey: string
  filterMode: string
  openContextDetail: (id: string) => void
  handleSort: (field: string) => void
  fetchContexts: () => Promise<void>
}

// Mock store interfaces
interface MockContextStore {
  contexts: ContextResource[]
  currentContext: ContextResource | null
  loading: boolean
  error: string | null
  defaultContext: ContextResource | undefined
  defaultContexts: ContextResource[]
  fetchContexts: () => Promise<void>
  fetchContext: (id: string) => Promise<void>
  createContext: (data: unknown) => Promise<ContextResource>
  updateContext: (id: string, data: unknown) => Promise<ContextResource>
  deleteContext: (id: string) => Promise<void>
  setDefaultContext: (id: string) => Promise<void>
  getDefaultContext: () => ContextResource | null
  clearError: () => void
  clearCurrentContext: () => void
}

interface MockLoadingOverlayStore {
  show: (message?: string) => void
  hide: () => void
}

interface MockErrorDisplayStore {
  addMessage: (type: string, message: string) => void
}

// Mock the stores
vi.mock('@/stores/context')
vi.mock('@/stores/loadingOverlay')
vi.mock('@/stores/errorDisplay')
vi.mock('@/stores/deleteConfirmation')

describe('Contexts.vue Unit Tests', () => {
  let router: Router
  let mockContexts: ContextResource[]

  beforeEach(() => {
    setActivePinia(createPinia())

    // Create mock data
    mockContexts = [
      createMockContext({ id: '1', internal_name: 'Main Context', is_default: true }),
      createMockContext({ id: '2', internal_name: 'Test Context', is_default: false }),
    ]

    // Mock router
    router = createRouter({
      history: createWebHistory(),
      routes: [
        { path: '/contexts', component: { template: '<div>Contexts</div>' } },
        { path: '/contexts/:id', component: { template: '<div>Context Detail</div>' } },
      ],
    })

    // Reset all mocks
    vi.clearAllMocks()
  })

  it('renders correctly with context data', async () => {
    // Mock store values
    vi.mocked(useContextStore).mockReturnValue({
      contexts: mockContexts,
      currentContext: null,
      loading: false,
      error: null,
      defaultContext: mockContexts[0],
      defaultContexts: [mockContexts[0]],
      fetchContexts: vi.fn().mockResolvedValue(undefined),
      fetchContext: vi.fn().mockResolvedValue(undefined),
      createContext: vi.fn().mockResolvedValue(mockContexts[0]),
      updateContext: vi.fn().mockResolvedValue(mockContexts[0]),
      deleteContext: vi.fn().mockResolvedValue(undefined),
      setDefaultContext: vi.fn().mockResolvedValue(undefined),
      getDefaultContext: vi.fn().mockReturnValue(mockContexts[0]),
      clearError: vi.fn(),
      clearCurrentContext: vi.fn(),
    } as MockContextStore)

    vi.mocked(useLoadingOverlayStore).mockReturnValue({
      show: vi.fn(),
      hide: vi.fn(),
    } as MockLoadingOverlayStore)

    vi.mocked(useErrorDisplayStore).mockReturnValue({
      addMessage: vi.fn(),
    } as MockErrorDisplayStore)

    const wrapper = mount(Contexts, {
      global: {
        plugins: [router],
      },
    })

    await flushPromises()

    expect(wrapper.find('h1').text()).toBe('Contexts')
    expect(wrapper.text()).toContain('Main Context')
    expect(wrapper.text()).toContain('Test Context')
  })

  it('filters contexts by search query', async () => {
    vi.mocked(useContextStore).mockReturnValue({
      contexts: mockContexts,
      currentContext: null,
      loading: false,
      error: null,
      defaultContext: mockContexts[0],
      defaultContexts: [mockContexts[0]],
      fetchContexts: vi.fn().mockResolvedValue(undefined),
      fetchContext: vi.fn().mockResolvedValue(undefined),
      createContext: vi.fn().mockResolvedValue(mockContexts[0]),
      updateContext: vi.fn().mockResolvedValue(mockContexts[0]),
      deleteContext: vi.fn().mockResolvedValue(undefined),
      setDefaultContext: vi.fn().mockResolvedValue(undefined),
      getDefaultContext: vi.fn().mockReturnValue(mockContexts[0]),
      clearError: vi.fn(),
      clearCurrentContext: vi.fn(),
    } as MockContextStore)

    vi.mocked(useLoadingOverlayStore).mockReturnValue({
      show: vi.fn(),
      hide: vi.fn(),
    } as MockLoadingOverlayStore)

    vi.mocked(useErrorDisplayStore).mockReturnValue({
      addMessage: vi.fn(),
    } as MockErrorDisplayStore)

    const wrapper = mount(Contexts, {
      global: {
        plugins: [router],
      },
    })

    const vm = wrapper.vm as unknown as ContextsComponentInstance

    // Set search query
    vm.searchQuery = 'Main'
    await wrapper.vm.$nextTick()

    // Should filter to show only Main Context
    expect(vm.filteredContexts).toHaveLength(1)
    expect(vm.filteredContexts[0].internal_name).toBe('Main Context')
  })

  it('sorts contexts correctly', async () => {
    vi.mocked(useContextStore).mockReturnValue({
      contexts: mockContexts,
      currentContext: null,
      loading: false,
      error: null,
      defaultContext: mockContexts[0],
      defaultContexts: [mockContexts[0]],
      fetchContexts: vi.fn().mockResolvedValue(undefined),
      fetchContext: vi.fn().mockResolvedValue(undefined),
      createContext: vi.fn().mockResolvedValue(mockContexts[0]),
      updateContext: vi.fn().mockResolvedValue(mockContexts[0]),
      deleteContext: vi.fn().mockResolvedValue(undefined),
      setDefaultContext: vi.fn().mockResolvedValue(undefined),
      getDefaultContext: vi.fn().mockReturnValue(mockContexts[0]),
      clearError: vi.fn(),
      clearCurrentContext: vi.fn(),
    } as MockContextStore)

    const wrapper = mount(Contexts, {
      global: {
        plugins: [router],
      },
    })

    const vm = wrapper.vm as unknown as ContextsComponentInstance

    // Sort by name descending
    vm.handleSort('internal_name') // First call toggles from default 'asc' to 'desc'
    await wrapper.vm.$nextTick()

    expect(vm.sortDirection).toBe('desc')
    expect(vm.filteredContexts[0].internal_name).toBe('Test Context')
  })

  it('filters contexts by default status', async () => {
    vi.mocked(useContextStore).mockReturnValue({
      contexts: mockContexts,
      currentContext: null,
      loading: false,
      error: null,
      defaultContext: mockContexts[0],
      defaultContexts: [mockContexts[0]],
      fetchContexts: vi.fn().mockResolvedValue(undefined),
      fetchContext: vi.fn().mockResolvedValue(undefined),
      createContext: vi.fn().mockResolvedValue(mockContexts[0]),
      updateContext: vi.fn().mockResolvedValue(mockContexts[0]),
      deleteContext: vi.fn().mockResolvedValue(undefined),
      setDefaultContext: vi.fn().mockResolvedValue(undefined),
      getDefaultContext: vi.fn().mockReturnValue(mockContexts[0]),
      clearError: vi.fn(),
      clearCurrentContext: vi.fn(),
    } as MockContextStore)

    const wrapper = mount(Contexts, {
      global: {
        plugins: [router],
      },
    })

    const vm = wrapper.vm as unknown as ContextsComponentInstance

    // Set filter mode to default
    vm.filterMode = 'default'
    await wrapper.vm.$nextTick()

    // Should filter to show only default contexts
    expect(vm.filteredContexts).toHaveLength(1)
    expect(vm.filteredContexts[0].is_default).toBe(true)
  })

  it('navigates to context detail when row is clicked', async () => {
    vi.mocked(useContextStore).mockReturnValue({
      contexts: mockContexts,
      currentContext: null,
      loading: false,
      error: null,
      defaultContext: mockContexts[0],
      defaultContexts: [mockContexts[0]],
      fetchContexts: vi.fn().mockResolvedValue(undefined),
      fetchContext: vi.fn().mockResolvedValue(undefined),
      createContext: vi.fn().mockResolvedValue(mockContexts[0]),
      updateContext: vi.fn().mockResolvedValue(mockContexts[0]),
      deleteContext: vi.fn().mockResolvedValue(undefined),
      setDefaultContext: vi.fn().mockResolvedValue(undefined),
      getDefaultContext: vi.fn().mockReturnValue(mockContexts[0]),
      clearError: vi.fn(),
      clearCurrentContext: vi.fn(),
    } as MockContextStore)

    const wrapper = mount(Contexts, {
      global: {
        plugins: [router],
      },
    })

    const vm = wrapper.vm as unknown as ContextsComponentInstance
    const pushSpy = vi.spyOn(router, 'push')

    // Simulate clicking on a context row
    vm.openContextDetail('1')

    expect(pushSpy).toHaveBeenCalledWith('/contexts/1')
  })
})
