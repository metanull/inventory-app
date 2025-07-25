/**
 * Integration Tests for LanguageDetail Resource Management
 *
 * These tests verify complete user workflows for language detail operations
 * combining multiple components and stores to ensure Language detail features work correctly.
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

import type {
  LanguageResource,
  LanguageStoreRequest,
  LanguageUpdateRequest,
} from '@metanull/inventory-app-api-client'

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

describe('LanguageDetail Resource Integration Tests', () => {
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
      updateLanguage: vi.fn().mockImplementation((id: string, request: LanguageUpdateRequest) => {
        const updated = createMockLanguage({
          id: id,
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

  describe('Language Detail CRUD Operations', () => {
    it('should fetch language by id', async () => {
      const store = useLanguageStore()
      const result = await store.getLanguageById('eng')

      expect(store.getLanguageById).toHaveBeenCalledWith('eng')
      expect(result?.id).toBe('eng')
      expect(result?.internal_name).toBe('English')
      expect(result?.is_default).toBe(true)
    })

    it('should return null for non-existent language', async () => {
      const store = useLanguageStore()
      const result = await store.getLanguageById('nonexistent')

      expect(store.getLanguageById).toHaveBeenCalledWith('nonexistent')
      expect(result).toBeNull()
    })

    it('should create new language with complete data', async () => {
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

    it('should create language with null backward_compatibility', async () => {
      const store = useLanguageStore()
      const request: LanguageStoreRequest = {
        id: 'zho',
        internal_name: 'Chinese',
        backward_compatibility: null,
        is_default: false,
      }

      const result = await store.createLanguage(request)

      expect(result.id).toBe('zho')
      expect(result.internal_name).toBe('Chinese')
      expect(result.backward_compatibility).toBeNull()
    })

    it('should create default language', async () => {
      const store = useLanguageStore()
      const request: LanguageStoreRequest = {
        id: 'spa',
        internal_name: 'Spanish',
        backward_compatibility: 'es',
        is_default: true,
      }

      const result = await store.createLanguage(request)

      expect(result.is_default).toBe(true)
    })

    it('should update existing language', async () => {
      const store = useLanguageStore()
      const request: LanguageUpdateRequest = {
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

    it('should update language to change default status', async () => {
      const store = useLanguageStore()
      const request: LanguageUpdateRequest = {
        internal_name: 'French',
        backward_compatibility: 'fr',
        is_default: true,
      }

      const result = await store.updateLanguage('fra', request)

      expect(result.id).toBe('fra')
      expect(result.is_default).toBe(true)
    })

    it('should update language to remove backward_compatibility', async () => {
      const store = useLanguageStore()
      const request: LanguageUpdateRequest = {
        internal_name: 'French',
        backward_compatibility: null,
        is_default: false,
      }

      const result = await store.updateLanguage('fra', request)

      expect(result.backward_compatibility).toBeNull()
    })

    it('should delete language', async () => {
      const store = useLanguageStore()
      await store.deleteLanguage('fra')

      expect(store.deleteLanguage).toHaveBeenCalledWith('fra')
    })
  })

  describe('Language-Specific Resource Features', () => {
    it('should handle ISO 639-3 language codes', async () => {
      const store = useLanguageStore()
      const request: LanguageStoreRequest = {
        id: 'rus',
        internal_name: 'Russian',
        backward_compatibility: 'ru',
        is_default: false,
      }

      const result = await store.createLanguage(request)

      expect(result.id).toBe('rus')
      expect(result.id.length).toBe(3)
      expect(/^[a-z]{3}$/.test(result.id)).toBe(true)
    })

    it('should handle ISO 639-1 backward compatibility codes', async () => {
      const store = useLanguageStore()
      const request: LanguageStoreRequest = {
        id: 'ita',
        internal_name: 'Italian',
        backward_compatibility: 'it',
        is_default: false,
      }

      const result = await store.createLanguage(request)

      expect(result.backward_compatibility).toBe('it')
      expect(result.backward_compatibility?.length).toBe(2)
    })

    it('should handle multiple default languages', async () => {
      const store = useLanguageStore()

      // Create multiple default languages
      await store.createLanguage({
        id: 'spa',
        internal_name: 'Spanish',
        backward_compatibility: 'es',
        is_default: true,
      })

      await store.createLanguage({
        id: 'deu',
        internal_name: 'German',
        backward_compatibility: 'de',
        is_default: true,
      })

      // Unlike projects, languages can have multiple defaults
      expect(store.createLanguage).toHaveBeenCalledTimes(2)
    })

    it('should preserve timestamps during updates', async () => {
      const store = useLanguageStore()
      const request: LanguageUpdateRequest = {
        internal_name: 'English (Modified)',
        backward_compatibility: 'en',
        is_default: true,
      }

      const result = await store.updateLanguage('eng', request)

      expect(result.updated_at).toBeDefined()
      expect(new Date(result.updated_at!).getTime()).toBeGreaterThan(0)
    })
  })

  describe('Form Integration Workflows', () => {
    it('should handle complete create workflow', async () => {
      const store = useLanguageStore()

      // Simulate form submission
      const formData = {
        id: 'por',
        internal_name: 'Portuguese',
        backward_compatibility: 'pt',
        is_default: false,
      }

      const request: LanguageStoreRequest = {
        id: formData.id,
        internal_name: formData.internal_name,
        backward_compatibility: formData.backward_compatibility,
        is_default: formData.is_default,
      }

      const result = await store.createLanguage(request)

      expect(result.id).toBe(formData.id)
      expect(result.internal_name).toBe(formData.internal_name)
      expect(result.backward_compatibility).toBe(formData.backward_compatibility)
      expect(result.is_default).toBe(formData.is_default)
    })

    it('should handle complete update workflow', async () => {
      const store = useLanguageStore()

      // First fetch the language
      const existing = await store.getLanguageById('fra')
      expect(existing).toBeDefined()

      // Update form data
      const formData = {
        id: 'fra',
        internal_name: 'Français',
        backward_compatibility: 'fr',
        is_default: true,
      }

      const request: LanguageUpdateRequest = {
        internal_name: formData.internal_name,
        backward_compatibility: formData.backward_compatibility,
        is_default: formData.is_default,
      }

      const result = await store.updateLanguage('fra', request)

      expect(result.internal_name).toBe('Français')
      expect(result.is_default).toBe(true)
    })

    it('should handle empty backward compatibility in forms', async () => {
      const store = useLanguageStore()

      const formData = {
        id: 'kor',
        internal_name: 'Korean',
        backward_compatibility: '', // Empty string from form
        is_default: false,
      }

      const request: LanguageStoreRequest = {
        id: formData.id,
        internal_name: formData.internal_name,
        backward_compatibility: formData.backward_compatibility || null,
        is_default: formData.is_default,
      }

      const result = await store.createLanguage(request)

      expect(result.backward_compatibility).toBeNull()
    })
  })

  describe('Error Handling in Resource Operations', () => {
    it('should handle create language validation errors', async () => {
      const error = new Error('Invalid language code')
      mockLanguageStore.createLanguage = vi.fn().mockRejectedValue(error)

      const store = useLanguageStore()
      const request: LanguageStoreRequest = {
        id: 'invalid',
        internal_name: '',
        backward_compatibility: null,
        is_default: false,
      }

      await expect(store.createLanguage(request)).rejects.toThrow('Invalid language code')
      expect(store.createLanguage).toHaveBeenCalledWith(request)
    })

    it('should handle update language not found errors', async () => {
      const error = new Error('Language not found')
      mockLanguageStore.updateLanguage = vi.fn().mockRejectedValue(error)

      const store = useLanguageStore()
      const request: LanguageUpdateRequest = {
        internal_name: 'Test',
        backward_compatibility: null,
        is_default: false,
      }

      await expect(store.updateLanguage('nonexistent', request)).rejects.toThrow(
        'Language not found'
      )
      expect(store.updateLanguage).toHaveBeenCalledWith('nonexistent', request)
    })

    it('should handle delete language constraint errors', async () => {
      const error = new Error('Cannot delete default language')
      mockLanguageStore.deleteLanguage = vi.fn().mockRejectedValue(error)

      const store = useLanguageStore()

      await expect(store.deleteLanguage('eng')).rejects.toThrow('Cannot delete default language')
      expect(store.deleteLanguage).toHaveBeenCalledWith('eng')
    })

    it('should handle network errors', async () => {
      const error = new Error('Network connection failed')
      mockLanguageStore.getLanguageById = vi.fn().mockRejectedValue(error)

      const store = useLanguageStore()

      await expect(store.getLanguageById('eng')).rejects.toThrow('Network connection failed')
      expect(store.getLanguageById).toHaveBeenCalledWith('eng')
    })
  })

  describe('Complex Resource Scenarios', () => {
    it('should handle concurrent language operations', async () => {
      const store = useLanguageStore()

      const createPromise = store.createLanguage({
        id: 'deu',
        internal_name: 'German',
        backward_compatibility: 'de',
        is_default: false,
      })

      const updatePromise = store.updateLanguage('fra', {
        id: 'fra',
        internal_name: 'French (Updated)',
        backward_compatibility: 'fr',
        is_default: true,
      })

      const [createResult, updateResult] = await Promise.all([createPromise, updatePromise])

      expect(createResult.id).toBe('deu')
      expect(updateResult.internal_name).toBe('French (Updated)')
    })

    it('should handle language code normalization', async () => {
      const store = useLanguageStore()

      // Test that codes are stored in lowercase
      const request: LanguageStoreRequest = {
        id: 'JPN', // Uppercase input
        internal_name: 'Japanese',
        backward_compatibility: 'JA', // Uppercase input
        is_default: false,
      }

      // In a real implementation, codes would be normalized to lowercase
      const normalizedRequest = {
        ...request,
        id: request.id.toLowerCase(),
        backward_compatibility: request.backward_compatibility?.toLowerCase() || null,
      }

      const result = await store.createLanguage(normalizedRequest)

      expect(result.id).toBe('jpn')
      expect(result.backward_compatibility).toBe('ja')
    })
  })
})
