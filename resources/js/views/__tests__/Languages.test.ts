import { beforeEach, describe, expect, it, vi, beforeAll, afterAll } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import { createRouter, createWebHistory } from 'vue-router'
import Languages from '../Languages.vue'
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

// Component interface for proper typing
interface LanguagesComponentInstance {
  languages: LanguageResource[]
  filteredLanguages: LanguageResource[]
  searchQuery: string
  sortDirection: string
  sortKey: string
  openLanguageDetail: (id: string) => void
  handleSort: (field: string) => void
  fetchLanguages: () => Promise<void>
}

// Mock the stores
vi.mock('@/stores/language')
vi.mock('@/stores/loadingOverlay')
vi.mock('@/stores/errorDisplay')

const mockLanguages: LanguageResource[] = [
  createMockLanguage({
    id: 'eng',
    internal_name: 'English',
    backward_compatibility: 'en',
    is_default: true,
    created_at: '2023-01-01T00:00:00Z',
    updated_at: '2023-01-01T00:00:00Z',
  }),
  createMockLanguage({
    id: 'fra',
    internal_name: 'French',
    backward_compatibility: 'fr',
    is_default: false,
    created_at: '2023-01-02T00:00:00Z',
    updated_at: '2023-01-02T00:00:00Z',
  }),
  createMockLanguage({
    id: 'deu',
    internal_name: 'German',
    backward_compatibility: null,
    is_default: false,
    created_at: '2023-01-03T00:00:00Z',
    updated_at: '2023-01-03T00:00:00Z',
  }),
]

describe('Languages.vue', () => {
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
        { path: '/languages', component: Languages },
        { path: '/languages/new', component: { template: '<div>New Language</div>' } },
        { path: '/languages/:id', component: { template: '<div>Language Detail</div>' } },
      ],
    })

    // Setup store mocks
    mockLanguageStore = {
      languages: mockLanguages,
      currentLanguage: null,
      loading: false,
      error: null,
      fetchLanguages: vi.fn().mockResolvedValue(mockLanguages),
      getLanguageById: vi.fn(),
      createLanguage: vi.fn(),
      updateLanguage: vi.fn(),
      deleteLanguage: vi.fn(),
      defaultLanguages: mockLanguages.filter(lang => lang.is_default),
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

  describe('Component Logic', () => {
    it('should initialize with default values', async () => {
      const wrapper = mount(Languages, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await wrapper.vm.$nextTick()

      expect((wrapper.vm as unknown as LanguagesComponentInstance).sortKey).toBe('internal_name')
      expect((wrapper.vm as unknown as LanguagesComponentInstance).sortDirection).toBe('asc')
      expect((wrapper.vm as unknown as LanguagesComponentInstance).searchQuery).toBe('')
    })

    it('should have access to store data', async () => {
      const wrapper = mount(Languages, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await wrapper.vm.$nextTick()

      expect((wrapper.vm as unknown as LanguagesComponentInstance).languages.length).toBe(3)
    })
  })

  describe('Search Functionality', () => {
    it('should search languages by internal name', async () => {
      const wrapper = mount(Languages, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await wrapper.vm.$nextTick()
      ;(wrapper.vm as unknown as LanguagesComponentInstance).searchQuery = 'English'
      await wrapper.vm.$nextTick()

      const filteredLanguages = (wrapper.vm as unknown as LanguagesComponentInstance)
        .filteredLanguages
      expect(filteredLanguages.length).toBe(1)
      expect(filteredLanguages[0].internal_name).toContain('English')
    })

    it('should filter languages by backward_compatibility', async () => {
      const wrapper = mount(Languages, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await wrapper.vm.$nextTick()
      ;(wrapper.vm as unknown as LanguagesComponentInstance).searchQuery = 'fr'
      await wrapper.vm.$nextTick()

      const filteredLanguages = (wrapper.vm as unknown as LanguagesComponentInstance)
        .filteredLanguages
      expect(filteredLanguages.length).toBe(1)
      expect(filteredLanguages[0].backward_compatibility).toBe('fr')
    })
  })

  describe('Store Integration', () => {
    it('should call fetchLanguages on mount', async () => {
      mount(Languages, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      expect(mockLanguageStore.fetchLanguages).toHaveBeenCalledOnce()
    })
  })
})
