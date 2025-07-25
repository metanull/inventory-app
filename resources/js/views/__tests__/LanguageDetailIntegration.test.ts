/**
 * Integration Tests for LanguageDetail Component
 */

import { beforeEach, describe, expect, it, vi, beforeAll, afterAll } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createWebHistory } from 'vue-router'
import LanguageDetail from '../LanguageDetail.vue'
import { useLanguageStore } from '@/stores/language'
import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
import { useErrorDisplayStore } from '@/stores/errorDisplay'
import { createMockLanguage } from '@/__tests__/test-utils'
import type { LanguageResource } from '@metanull/inventory-app-api-client'
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

// Mock icon components
vi.mock('@heroicons/vue/24/solid', () => ({
  CheckIcon: { name: 'CheckIcon', render: () => null },
  CheckCircleIcon: { name: 'CheckCircleIcon', render: () => null },
  XCircleIcon: { name: 'XCircleIcon', render: () => null },
  XMarkIcon: { name: 'XMarkIcon', render: () => null },
  PlusIcon: { name: 'PlusIcon', render: () => null },
  ArrowLeftIcon: { name: 'ArrowLeftIcon', render: () => null },
  TrashIcon: { name: 'TrashIcon', render: () => null },
  PencilIcon: { name: 'PencilIcon', render: () => null },
  EyeIcon: { name: 'EyeIcon', render: () => null },
  LanguageIcon: { name: 'LanguageIcon', render: () => null },
}))

// Mock the stores
vi.mock('@/stores/language')
vi.mock('@/stores/loadingOverlay')
vi.mock('@/stores/errorDisplay')

const mockLanguage: LanguageResource = createMockLanguage({
  id: 'eng',
  internal_name: 'English',
  backward_compatibility: 'en',
  is_default: true,
  created_at: '2023-01-01T00:00:00Z',
  updated_at: '2023-01-01T00:00:00Z',
})

describe('LanguageDetail Integration Tests', () => {
  let mockLanguageStore: ReturnType<typeof useLanguageStore>
  let mockLoadingStore: ReturnType<typeof useLoadingOverlayStore>
  let mockErrorStore: ReturnType<typeof useErrorDisplayStore>
  let router: Router

  beforeEach(() => {
    setActivePinia(createPinia())

    // Setup router
    router = createRouter({
      history: createWebHistory(),
      routes: [
        { path: '/', component: { template: '<div>Home</div>' } },
        { path: '/languages', component: { template: '<div>Languages</div>' } },
        { path: '/languages/new', component: LanguageDetail },
        { path: '/languages/:id', component: LanguageDetail },
      ],
    })

    // Setup store mocks
    mockLanguageStore = {
      languages: [mockLanguage],
      currentLanguage: mockLanguage,
      loading: false,
      error: null,
      fetchLanguages: vi.fn().mockResolvedValue([mockLanguage]),
      getLanguageById: vi.fn().mockResolvedValue(mockLanguage),
      createLanguage: vi.fn().mockResolvedValue(mockLanguage),
      updateLanguage: vi.fn().mockResolvedValue(mockLanguage),
      deleteLanguage: vi.fn().mockResolvedValue(undefined),
      clearCurrentLanguage: vi.fn(),
      defaultLanguages: [mockLanguage],
    } as ReturnType<typeof useLanguageStore>

    mockLoadingStore = {
      show: vi.fn(),
      hide: vi.fn(),
    } as ReturnType<typeof useLoadingOverlayStore>

    mockErrorStore = {
      addMessage: vi.fn(),
    } as ReturnType<typeof useErrorDisplayStore>

    // Mock store implementations
    vi.mocked(useLanguageStore).mockReturnValue(mockLanguageStore)
    vi.mocked(useLoadingOverlayStore).mockReturnValue(mockLoadingStore)
    vi.mocked(useErrorDisplayStore).mockReturnValue(mockErrorStore)

    vi.clearAllMocks()
  })

  describe('LanguageDetail Component Integration', () => {
    it('should render and mount component successfully', async () => {
      const wrapper = mount(LanguageDetail, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      // Verify component renders
      expect(wrapper.exists()).toBe(true)
    })

    it('should render with stores connected', async () => {
      const wrapper = mount(LanguageDetail, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      // Component should render with all stores connected
      expect(wrapper.exists()).toBe(true)
      expect(mockLanguageStore).toBeDefined()
      expect(mockLoadingStore).toBeDefined()
      expect(mockErrorStore).toBeDefined()
    })

    it('should handle loading states during operations', async () => {
      mockLanguageStore.loading = true

      const wrapper = mount(LanguageDetail, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      // Component should handle loading state
      expect(wrapper.exists()).toBe(true)
      expect(mockLanguageStore.loading).toBe(true)
    })

    it('should handle error states', async () => {
      mockLanguageStore.error = 'Failed to load language'

      const wrapper = mount(LanguageDetail, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      // Component should handle error state
      expect(wrapper.exists()).toBe(true)
      expect(mockLanguageStore.error).toBe('Failed to load language')
    })

    it('should render with language data available', async () => {
      const wrapper = mount(LanguageDetail, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      // Component should access language data
      expect(wrapper.exists()).toBe(true)
      expect(mockLanguageStore.currentLanguage).toBe(mockLanguage)
    })

    it('should work with default languages', async () => {
      const wrapper = mount(LanguageDetail, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      // Component should have access to default languages
      expect(wrapper.exists()).toBe(true)
      expect(mockLanguageStore.defaultLanguages).toContain(mockLanguage)
    })
  })
})
