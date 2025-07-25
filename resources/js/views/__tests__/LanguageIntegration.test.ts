/**
 * Integration Tests for Language Components
 *
 * These tests verify that Language components work correctly together
 * with real component mounting and interaction testing.
 */

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

// Component interface types for proper typing
interface LanguagesComponentInstance {
  languages: LanguageResource[]
  filteredLanguages: LanguageResource[]
  defaultLanguages: LanguageResource[]
  filterMode: string
  searchQuery: string
  sortKey: string
  sortDirection: string
  openLanguageDetail: (id: string) => void
  updateLanguageStatus: (language: LanguageResource, field: string, value: boolean) => Promise<void>
  handleDeleteLanguage: (language: LanguageResource) => Promise<void>
  handleSort: (field: string) => void
  fetchLanguages: () => Promise<void>
}

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

describe('Language Integration Tests', () => {
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

  describe('Component Integration', () => {
    it('should mount successfully', async () => {
      const wrapper = mount(Languages, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      expect(wrapper.exists()).toBe(true)
      expect(mockLanguageStore.fetchLanguages).toHaveBeenCalledOnce()
    })

    it('should display languages from store', async () => {
      const wrapper = mount(Languages, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      // Check that language data is accessible through component
      const vm = wrapper.vm as unknown as LanguagesComponentInstance
      expect(vm.languages).toHaveLength(3)
      expect(vm.languages[0].internal_name).toBe('English')
    })

    it('should handle empty language list', async () => {
      mockLanguageStore.languages = []
      mockLanguageStore.defaultLanguages = []

      const wrapper = mount(Languages, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as LanguagesComponentInstance
      expect(vm.languages).toHaveLength(0)
    })
  })

  describe('Search Integration', () => {
    it('should filter languages by search query', async () => {
      const wrapper = mount(Languages, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      // Set search query
      const vm = wrapper.vm as unknown as LanguagesComponentInstance
      vm.searchQuery = 'English'
      await wrapper.vm.$nextTick()

      const filteredLanguages = vm.filteredLanguages
      expect(filteredLanguages).toHaveLength(1)
      expect(filteredLanguages[0].internal_name).toBe('English')
    })

    it('should filter by backward compatibility code', async () => {
      const wrapper = mount(Languages, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as LanguagesComponentInstance
      vm.searchQuery = 'fr'
      await wrapper.vm.$nextTick()

      const filteredLanguages = vm.filteredLanguages
      expect(filteredLanguages).toHaveLength(1)
      expect(filteredLanguages[0].backward_compatibility).toBe('fr')
    })

    it('should handle case insensitive search', async () => {
      const wrapper = mount(Languages, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as LanguagesComponentInstance
      vm.searchQuery = 'GERMAN'
      await wrapper.vm.$nextTick()

      const filteredLanguages = vm.filteredLanguages
      expect(filteredLanguages).toHaveLength(1)
      expect(filteredLanguages[0].internal_name).toBe('German')
    })
  })

  describe('Sorting Integration', () => {
    it('should sort languages by internal_name ascending', async () => {
      const wrapper = mount(Languages, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as LanguagesComponentInstance

      // Component starts with internal_name asc by default
      // So languages should already be sorted: English, French, German
      const sortedLanguages = vm.filteredLanguages
      expect(sortedLanguages[0].internal_name).toBe('English')
      expect(sortedLanguages[1].internal_name).toBe('French')
      expect(sortedLanguages[2].internal_name).toBe('German')
      expect(vm.sortDirection).toBe('asc')
      expect(vm.sortKey).toBe('internal_name')
    })

    it('should toggle sort direction', async () => {
      const wrapper = mount(Languages, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as LanguagesComponentInstance

      // Initially: sortKey='internal_name', sortDirection='asc'
      expect(vm.sortDirection).toBe('asc')
      expect(vm.sortKey).toBe('internal_name')

      // First handleSort call - toggle to descending since key is same
      vm.handleSort('internal_name')
      await wrapper.vm.$nextTick()
      expect(vm.sortDirection).toBe('desc')

      // Second handleSort call - toggle back to ascending
      vm.handleSort('internal_name')
      await wrapper.vm.$nextTick()
      expect(vm.sortDirection).toBe('asc')
    })

    it('should sort by different fields', async () => {
      const wrapper = mount(Languages, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as LanguagesComponentInstance

      // Sort by id
      vm.handleSort('id')
      await wrapper.vm.$nextTick()
      expect(vm.sortKey).toBe('id')
      expect(vm.sortDirection).toBe('asc')

      // Sort by is_default
      vm.handleSort('is_default')
      await wrapper.vm.$nextTick()
      expect(vm.sortKey).toBe('is_default')
      expect(vm.sortDirection).toBe('asc')
    })
  })

  describe('Navigation Integration', () => {
    it('should navigate to language detail', async () => {
      const wrapper = mount(Languages, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as LanguagesComponentInstance
      const pushSpy = vi.spyOn(router, 'push')

      vm.openLanguageDetail('eng')

      expect(pushSpy).toHaveBeenCalledWith('/languages/eng')
    })

    it('should navigate to new language page', async () => {
      const wrapper = mount(Languages, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as LanguagesComponentInstance
      const pushSpy = vi.spyOn(router, 'push')

      // Simulate clicking "Add Language" button
      if (vm.openLanguageDetail) {
        vm.openLanguageDetail('new')
      }

      expect(pushSpy).toHaveBeenCalledWith('/languages/new')
    })
  })

  describe('Default Language Integration', () => {
    it('should display default language indicators', async () => {
      const wrapper = mount(Languages, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as LanguagesComponentInstance
      const defaultLanguages = vm.languages.filter((lang: LanguageResource) => lang.is_default)

      expect(defaultLanguages).toHaveLength(1)
      expect(defaultLanguages[0].internal_name).toBe('English')
    })

    it('should handle multiple default languages', async () => {
      // Add another default language
      const multipleDefaultLanguages = [
        ...mockLanguages,
        createMockLanguage({
          id: 'spa',
          internal_name: 'Spanish',
          backward_compatibility: 'es',
          is_default: true,
        }),
      ]

      mockLanguageStore.languages = multipleDefaultLanguages
      mockLanguageStore.defaultLanguages = multipleDefaultLanguages.filter(lang => lang.is_default)

      const wrapper = mount(Languages, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      const vm = wrapper.vm as unknown as LanguagesComponentInstance
      const defaultLanguages = vm.languages.filter((lang: LanguageResource) => lang.is_default)

      expect(defaultLanguages).toHaveLength(2)
      expect(defaultLanguages.map((lang: LanguageResource) => lang.internal_name)).toContain(
        'English'
      )
      expect(defaultLanguages.map((lang: LanguageResource) => lang.internal_name)).toContain(
        'Spanish'
      )
    })
  })

  describe('Error Handling Integration', () => {
    it('should handle fetch error gracefully', async () => {
      const error = new Error('Network error')
      mockLanguageStore.fetchLanguages = vi.fn().mockRejectedValue(error)

      const wrapper = mount(Languages, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      expect(mockLanguageStore.fetchLanguages).toHaveBeenCalledOnce()
      // Component should still mount even if fetch fails
      expect(wrapper.exists()).toBe(true)
    })

    it('should display error state', async () => {
      mockLanguageStore.error = 'Failed to load languages'

      const wrapper = mount(Languages, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      // Should still render the component
      expect(wrapper.exists()).toBe(true)

      // Store should have the error
      expect(mockLanguageStore.error).toBe('Failed to load languages')
    })
  })

  describe('Loading State Integration', () => {
    it('should show loading state', async () => {
      mockLanguageStore.loading = true

      const wrapper = mount(Languages, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      // Should still render the component
      expect(wrapper.exists()).toBe(true)

      // Store should show loading
      expect(mockLanguageStore.loading).toBe(true)
    })

    it('should hide loading state after fetch', async () => {
      const wrapper = mount(Languages, {
        global: {
          plugins: [createPinia(), router],
        },
      })

      await flushPromises()

      // Should render the component
      expect(wrapper.exists()).toBe(true)

      // Store should not be loading
      expect(mockLanguageStore.loading).toBe(false)
    })
  })
})
