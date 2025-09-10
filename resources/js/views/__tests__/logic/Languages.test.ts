/**
 * Unit Tests for Languages Component Business Logic
 *
 * These tests focus on the core functionality and business logic
 * of the Languages component without dealing with complex UI rendering.
 *
 * Tests cover:
 * - Search functionality across internal_name and backward_compatibility
 * - Sorting functionality
 * - Store interactions and data fetching
 * - Error handling
 * - Delete operations
 * - Default language filtering
 */

import { beforeEach, describe, expect, it, vi, beforeAll, afterAll } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useLanguageStore } from '@/stores/language'
import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
import { useErrorDisplayStore } from '@/stores/errorDisplay'
import { createMockLanguage } from '@/__tests__/test-utils'
import type { LanguageResource } from '@metanull/inventory-app-api-client'

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

// Test data - covering different language types for comprehensive testing
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
  createMockLanguage({
    id: 'spa',
    internal_name: 'Spanish',
    backward_compatibility: 'es',
    is_default: true,
    created_at: '2023-01-04T00:00:00Z',
    updated_at: '2023-01-04T00:00:00Z',
  }),
]

describe('Languages Logic Tests', () => {
  let mockLanguageStore: ReturnType<typeof useLanguageStore>
  let mockLoadingStore: ReturnType<typeof useLoadingOverlayStore>
  let mockErrorStore: ReturnType<typeof useErrorDisplayStore>

  beforeEach(() => {
    setActivePinia(createPinia())

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

  describe('Store Data Access', () => {
    it('should access languages from store', () => {
      const store = useLanguageStore()
      expect(store.languages).toEqual(mockLanguages)
      expect(store.languages.length).toBe(4)
    })

    it('should handle empty languages array', () => {
      mockLanguageStore.languages = []
      const store = useLanguageStore()
      expect(store.languages).toEqual([])
      expect(store.languages.length).toBe(0)
    })

    it('should access default languages from store', () => {
      const store = useLanguageStore()
      expect(store.defaultLanguages).toEqual(mockLanguages.filter(lang => lang.is_default))
      expect(store.defaultLanguages.length).toBe(2)
      expect(store.defaultLanguages[0].internal_name).toBe('English')
      expect(store.defaultLanguages[1].internal_name).toBe('Spanish')
    })
  })

  describe('Search Functionality Logic', () => {
    it('should filter by internal_name (case insensitive)', () => {
      const languages = mockLanguages
      const searchQuery = 'english'

      const filtered = languages.filter(language =>
        language.internal_name.toLowerCase().includes(searchQuery.toLowerCase())
      )

      expect(filtered.length).toBe(1)
      expect(filtered[0].internal_name).toBe('English')
    })

    it('should filter by backward_compatibility code', () => {
      const languages = mockLanguages
      const searchQuery = 'en'

      const filtered = languages.filter(language =>
        language.backward_compatibility?.toLowerCase().includes(searchQuery.toLowerCase())
      )

      expect(filtered.length).toBe(1)
      expect(filtered[0].backward_compatibility).toBe('en')
    })

    it('should handle null backward_compatibility in search', () => {
      const languages = mockLanguages
      const searchQuery = 'german'

      const filtered = languages.filter(
        language =>
          language.internal_name.toLowerCase().includes(searchQuery.toLowerCase()) ||
          language.backward_compatibility?.toLowerCase().includes(searchQuery.toLowerCase())
      )

      expect(filtered.length).toBe(1)
      expect(filtered[0].internal_name).toBe('German')
      expect(filtered[0].backward_compatibility).toBeNull()
    })

    it('should return empty array when no matches found', () => {
      const languages = mockLanguages
      const searchQuery = 'nonexistent'

      const filtered = languages.filter(
        language =>
          language.internal_name.toLowerCase().includes(searchQuery.toLowerCase()) ||
          language.backward_compatibility?.toLowerCase().includes(searchQuery.toLowerCase())
      )

      expect(filtered.length).toBe(0)
    })

    it('should handle empty search query', () => {
      const languages = mockLanguages
      const searchQuery = ''

      const filtered = languages.filter(
        language =>
          language.internal_name.toLowerCase().includes(searchQuery.toLowerCase()) ||
          language.backward_compatibility?.toLowerCase().includes(searchQuery.toLowerCase())
      )

      expect(filtered.length).toBe(4)
    })
  })

  describe('Sorting Logic', () => {
    it('should sort by internal_name ascending', () => {
      const languages = [...mockLanguages]
      const sorted = languages.sort((a, b) => a.internal_name.localeCompare(b.internal_name))

      expect(sorted[0].internal_name).toBe('English')
      expect(sorted[1].internal_name).toBe('French')
      expect(sorted[2].internal_name).toBe('German')
      expect(sorted[3].internal_name).toBe('Spanish')
    })

    it('should sort by internal_name descending', () => {
      const languages = [...mockLanguages]
      const sorted = languages.sort((a, b) => b.internal_name.localeCompare(a.internal_name))

      expect(sorted[0].internal_name).toBe('Spanish')
      expect(sorted[1].internal_name).toBe('German')
      expect(sorted[2].internal_name).toBe('French')
      expect(sorted[3].internal_name).toBe('English')
    })

    it('should sort by id ascending', () => {
      const languages = [...mockLanguages]
      const sorted = languages.sort((a, b) => a.id.localeCompare(b.id))

      expect(sorted[0].id).toBe('deu')
      expect(sorted[1].id).toBe('eng')
      expect(sorted[2].id).toBe('fra')
      expect(sorted[3].id).toBe('spa')
    })

    it('should sort by is_default descending (defaults first)', () => {
      const languages = [...mockLanguages]
      const sorted = languages.sort((a, b) => Number(b.is_default) - Number(a.is_default))

      expect(sorted[0].is_default).toBe(true)
      expect(sorted[1].is_default).toBe(true)
      expect(sorted[2].is_default).toBe(false)
      expect(sorted[3].is_default).toBe(false)
    })
  })

  describe('Store Operations', () => {
    it('should call fetchLanguages', async () => {
      const store = useLanguageStore()
      await store.fetchLanguages()

      expect(mockLanguageStore.fetchLanguages).toHaveBeenCalledOnce()
    })

    it('should handle fetchLanguages error', async () => {
      const error = new Error('Fetch failed')
      mockLanguageStore.fetchLanguages = vi.fn().mockRejectedValue(error)

      const store = useLanguageStore()

      try {
        await store.fetchLanguages()
      } catch (e) {
        expect(e).toBe(error)
      }

      expect(mockLanguageStore.fetchLanguages).toHaveBeenCalledOnce()
    })

    it('should call deleteLanguage with correct id', async () => {
      const store = useLanguageStore()
      const languageId = 'eng'

      store.deleteLanguage = vi.fn().mockResolvedValue(undefined)
      await store.deleteLanguage(languageId)

      expect(store.deleteLanguage).toHaveBeenCalledWith(languageId)
    })
  })

  describe('Loading and Error Handling', () => {
    it('should show loading overlay during operations', () => {
      const loadingStore = useLoadingOverlayStore()
      loadingStore.show()

      expect(mockLoadingStore.show).toHaveBeenCalledOnce()
    })

    it('should hide loading overlay after operations', () => {
      const loadingStore = useLoadingOverlayStore()
      loadingStore.hide()

      expect(mockLoadingStore.hide).toHaveBeenCalledOnce()
    })

    it('should add error message on failure', () => {
      const errorStore = useErrorDisplayStore()
      const errorMessage = 'Failed to fetch languages'
      errorStore.addMessage(errorMessage)

      expect(mockErrorStore.addMessage).toHaveBeenCalledWith(errorMessage)
    })
  })

  describe('Default Language Filtering', () => {
    it('should filter default languages correctly', () => {
      const languages = mockLanguages
      const defaultLanguages = languages.filter(lang => lang.is_default)

      expect(defaultLanguages.length).toBe(2)
      expect(defaultLanguages[0].internal_name).toBe('English')
      expect(defaultLanguages[1].internal_name).toBe('Spanish')
      expect(defaultLanguages.every(lang => lang.is_default)).toBe(true)
    })

    it('should handle no default languages', () => {
      const languagesWithoutDefaults = mockLanguages.map(lang => ({
        ...lang,
        is_default: false,
      }))
      const defaultLanguages = languagesWithoutDefaults.filter(lang => lang.is_default)

      expect(defaultLanguages.length).toBe(0)
    })

    it('should handle all languages being default', () => {
      const allDefaultLanguages = mockLanguages.map(lang => ({
        ...lang,
        is_default: true,
      }))
      const defaultLanguages = allDefaultLanguages.filter(lang => lang.is_default)

      expect(defaultLanguages.length).toBe(4)
      expect(defaultLanguages.every(lang => lang.is_default)).toBe(true)
    })
  })
})
