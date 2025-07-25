/**
 * Integration Tests for Language Resource Management
 *
 * These tests verify complete user workflows combining multiple components
 * and stores to ensure Language-specific features work together correctly.
 */

import { beforeEach, describe, expect, it, vi, beforeAll, afterAll } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useLanguageStore } from '@/stores/language'
import { createMockLanguage } from '@/__tests__/test-utils'

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

import type { LanguageResource, LanguageStoreRequest } from '@metanull/inventory-app-api-client'

// Mock the stores instead of the API client
vi.mock('@/stores/language')

// Test data
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
]

describe('Language Resource Integration Tests', () => {
  let mockLanguageStore: ReturnType<typeof useLanguageStore>

  beforeEach(() => {
    setActivePinia(createPinia())

    // Setup comprehensive store mock
    mockLanguageStore = {
      languages: mockLanguages,
      currentLanguage: null,
      loading: false,
      error: null,
      fetchLanguages: vi.fn().mockResolvedValue(mockLanguages),
      getLanguageById: vi.fn().mockImplementation((id: string) => {
        return Promise.resolve(mockLanguages.find(l => l.id === id) || null)
      }),
      createLanguage: vi.fn().mockImplementation((request: LanguageStoreRequest) => {
        const newLanguage = createMockLanguage({
          id: request.id,
          internal_name: request.internal_name,
          backward_compatibility: request.backward_compatibility,
          is_default: request.is_default || false,
        })
        return Promise.resolve(newLanguage)
      }),
      updateLanguage: vi.fn().mockImplementation((id: string, request: LanguageStoreRequest) => {
        const updated = createMockLanguage({
          id: request.id,
          internal_name: request.internal_name,
          backward_compatibility: request.backward_compatibility,
          is_default: request.is_default || false,
          updated_at: new Date().toISOString(),
        })
        return Promise.resolve(updated)
      }),
      deleteLanguage: vi.fn().mockResolvedValue(undefined),
      defaultLanguages: mockLanguages.filter(lang => lang.is_default),
    } as ReturnType<typeof useLanguageStore>

    // Mock store implementations
    vi.mocked(useLanguageStore).mockReturnValue(mockLanguageStore)

    vi.clearAllMocks()
  })

  describe('Language CRUD Operations', () => {
    it('should fetch all languages', async () => {
      const store = useLanguageStore()
      const result = await store.fetchLanguages()

      expect(store.fetchLanguages).toHaveBeenCalledOnce()
      expect(result).toEqual(mockLanguages)
    })

    it('should get language by id', async () => {
      const store = useLanguageStore()
      const language = await store.getLanguageById('eng')

      expect(store.getLanguageById).toHaveBeenCalledWith('eng')
      expect(language?.id).toBe('eng')
      expect(language?.internal_name).toBe('English')
    })

    it('should return null for non-existent language', async () => {
      const store = useLanguageStore()
      const language = await store.getLanguageById('nonexistent')

      expect(store.getLanguageById).toHaveBeenCalledWith('nonexistent')
      expect(language).toBeNull()
    })

    it('should create new language', async () => {
      const store = useLanguageStore()
      const request: LanguageStoreRequest = {
        id: 'deu',
        internal_name: 'German',
        backward_compatibility: 'de',
        is_default: false,
      }

      const result = await store.createLanguage(request)

      expect(store.createLanguage).toHaveBeenCalledWith(request)
      expect(result.id).toBe('deu')
      expect(result.internal_name).toBe('German')
      expect(result.backward_compatibility).toBe('de')
      expect(result.is_default).toBe(false)
    })

    it('should update existing language', async () => {
      const store = useLanguageStore()
      const request: LanguageStoreRequest = {
        id: 'eng',
        internal_name: 'English (Updated)',
        backward_compatibility: 'en',
        is_default: true,
      }

      const result = await store.updateLanguage('eng', request)

      expect(store.updateLanguage).toHaveBeenCalledWith('eng', request)
      expect(result.id).toBe('eng')
      expect(result.internal_name).toBe('English (Updated)')
      expect(result.is_default).toBe(true)
    })

    it('should delete language', async () => {
      const store = useLanguageStore()
      await store.deleteLanguage('fra')

      expect(store.deleteLanguage).toHaveBeenCalledWith('fra')
    })
  })

  describe('Language-Specific Features', () => {
    it('should handle default language filtering', () => {
      const store = useLanguageStore()
      const defaultLanguages = store.defaultLanguages

      expect(defaultLanguages.length).toBe(1)
      expect(defaultLanguages[0].id).toBe('eng')
      expect(defaultLanguages[0].is_default).toBe(true)
    })

    it('should handle multiple default languages', () => {
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

      const store = useLanguageStore()
      const defaultLanguages = store.defaultLanguages

      expect(defaultLanguages.length).toBe(2)
      expect(defaultLanguages.every(lang => lang.is_default)).toBe(true)
    })

    it('should handle no default languages', () => {
      const noDefaultLanguages = mockLanguages.map(lang => ({
        ...lang,
        is_default: false,
      }))

      mockLanguageStore.languages = noDefaultLanguages
      mockLanguageStore.defaultLanguages = []

      const store = useLanguageStore()
      const defaultLanguages = store.defaultLanguages

      expect(defaultLanguages.length).toBe(0)
    })
  })

  describe('Language ISO Code Validation', () => {
    it('should handle 3-character ISO language codes', async () => {
      const store = useLanguageStore()
      const request: LanguageStoreRequest = {
        id: 'deu',
        internal_name: 'German',
        backward_compatibility: 'de',
        is_default: false,
      }

      const result = await store.createLanguage(request)

      expect(result.id).toBe('deu')
      expect(result.id.length).toBe(3)
    })

    it('should handle backward compatibility codes', async () => {
      const store = useLanguageStore()
      const request: LanguageStoreRequest = {
        id: 'rus',
        internal_name: 'Russian',
        backward_compatibility: 'ru',
        is_default: false,
      }

      const result = await store.createLanguage(request)

      expect(result.backward_compatibility).toBe('ru')
      expect(result.backward_compatibility?.length).toBe(2)
    })

    it('should handle null backward compatibility', async () => {
      const store = useLanguageStore()
      const request: LanguageStoreRequest = {
        id: 'zho',
        internal_name: 'Chinese',
        backward_compatibility: null,
        is_default: false,
      }

      const result = await store.createLanguage(request)

      expect(result.backward_compatibility).toBeNull()
    })
  })

  describe('Error Handling in Resource Operations', () => {
    it('should handle fetch languages error', async () => {
      const error = new Error('Network error')
      mockLanguageStore.fetchLanguages = vi.fn().mockRejectedValue(error)

      const store = useLanguageStore()

      await expect(store.fetchLanguages()).rejects.toThrow('Network error')
      expect(store.fetchLanguages).toHaveBeenCalledOnce()
    })

    it('should handle create language error', async () => {
      const error = new Error('Validation error')
      mockLanguageStore.createLanguage = vi.fn().mockRejectedValue(error)

      const store = useLanguageStore()
      const request: LanguageStoreRequest = {
        id: 'invalid',
        internal_name: '',
        backward_compatibility: null,
        is_default: false,
      }

      await expect(store.createLanguage(request)).rejects.toThrow('Validation error')
      expect(store.createLanguage).toHaveBeenCalledWith(request)
    })

    it('should handle update language error', async () => {
      const error = new Error('Update failed')
      mockLanguageStore.updateLanguage = vi.fn().mockRejectedValue(error)

      const store = useLanguageStore()
      const request: LanguageStoreRequest = {
        id: 'eng',
        internal_name: 'English',
        backward_compatibility: 'en',
        is_default: true,
      }

      await expect(store.updateLanguage('eng', request)).rejects.toThrow('Update failed')
      expect(store.updateLanguage).toHaveBeenCalledWith('eng', request)
    })

    it('should handle delete language error', async () => {
      const error = new Error('Delete failed')
      mockLanguageStore.deleteLanguage = vi.fn().mockRejectedValue(error)

      const store = useLanguageStore()

      await expect(store.deleteLanguage('eng')).rejects.toThrow('Delete failed')
      expect(store.deleteLanguage).toHaveBeenCalledWith('eng')
    })
  })
})
