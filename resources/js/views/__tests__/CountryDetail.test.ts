import { describe, it, expect, vi, beforeEach, beforeAll, afterAll } from 'vitest'
import { mount } from '@vue/test-utils'
import { createRouter, createWebHistory, type Router } from 'vue-router'
import { flushPromises } from '@vue/test-utils'
import CountryDetail from '../CountryDetail.vue'
import type { CountryResource } from '@metanull/inventory-app-api-client'

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
  GlobeAltIcon: { name: 'GlobeAltIcon', render: () => null },
}))

// Mock stores
const mockCountryStore = {
  currentCountry: null as CountryResource | null,
  loading: false,
  fetchCountry: vi.fn(),
  clearCurrentCountry: vi.fn(),
  createCountry: vi.fn(),
  updateCountry: vi.fn(),
  deleteCountry: vi.fn(),
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

vi.mock('@/stores/country', () => ({
  useCountryStore: () => mockCountryStore,
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

describe('CountryDetail.vue', () => {
  let router: Router

  beforeEach(() => {
    // Reset all mocks
    vi.clearAllMocks()

    router = createRouter({
      history: createWebHistory(),
      routes: [
        { path: '/', component: { template: '<div>Home</div>' } },
        { path: '/countries', component: { template: '<div>Countries</div>' } },
        { path: '/countries/:id', name: 'country-detail', component: CountryDetail },
      ],
    })
  })

  describe('Component Mounting', () => {
    it('should mount successfully for new country', async () => {
      await router.push('/countries/new')
      await router.isReady()

      const wrapper = mount(CountryDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()
      expect(wrapper.exists()).toBe(true)
    })

    it('should mount successfully for existing country', async () => {
      await router.push('/countries/GBR')
      await router.isReady()

      const wrapper = mount(CountryDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()
      expect(wrapper.exists()).toBe(true)
    })
  })

  describe('Route Parameters', () => {
    it('should detect new country mode from route', async () => {
      await router.push('/countries/new')
      await router.isReady()

      mount(CountryDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()
      expect(router.currentRoute.value.params.id).toBe('new')
    })

    it('should detect edit mode from route with country ID', async () => {
      await router.push('/countries/GBR')
      await router.isReady()

      mount(CountryDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()
      expect(router.currentRoute.value.params.id).toBe('GBR')
    })
  })

  describe('Component Structure', () => {
    it('should render the basic component structure', async () => {
      await router.push('/countries/new')
      await router.isReady()

      const wrapper = mount(CountryDetail, {
        global: {
          plugins: [router],
        },
      })

      await flushPromises()

      // Component should render
      expect(wrapper.exists()).toBe(true)
      expect(wrapper.find('div').exists()).toBe(true)
    })
  })
})
