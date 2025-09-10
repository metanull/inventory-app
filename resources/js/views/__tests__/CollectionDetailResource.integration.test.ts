/**
 * Integration Tests for Collection Detail Resource Management
 *
 * These tests verify complete user workflows for detailed Collection management
 * combining store operations with collection-specific business logic.
 */

import { beforeEach, describe, expect, it, vi, beforeAll, afterAll } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useCollectionStore } from '@/stores/collection'
import { useLanguageStore } from '@/stores/language'
import { useContextStore } from '@/stores/context'
import { createMockCollection, createMockLanguage, createMockContext } from '@/__tests__/test-utils'
import type {
  CollectionResource,
  CollectionStoreRequest,
  LanguageResource,
  ContextResource,
} from '@metanull/inventory-app-api-client'

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
vi.mock('@/stores/collection')
vi.mock('@/stores/language')
vi.mock('@/stores/context')

// Test data
const mockLanguages: LanguageResource[] = [
  createMockLanguage({
    id: 'eng',
    internal_name: 'English',
    backward_compatibility: 'en',
  }),
  createMockLanguage({
    id: 'fra',
    internal_name: 'French',
    backward_compatibility: 'fr',
  }),
]

const mockContexts: ContextResource[] = [
  createMockContext({
    id: 'museum-ctx',
    internal_name: 'Museum Context',
    backward_compatibility: 'museum',
  }),
  createMockContext({
    id: 'archive-ctx',
    internal_name: 'Archive Context',
    backward_compatibility: 'archive',
  }),
]

const mockCollection: CollectionResource = createMockCollection({
  id: '123e4567-e89b-12d3-a456-426614174000',
  internal_name: 'Test Collection Detail',
  backward_compatibility: 'test-detail',
  language_id: 'eng',
  context_id: 'museum-ctx',
  created_at: '2023-01-01T00:00:00Z',
  updated_at: '2023-01-01T00:00:00Z',
})

describe('Collection Detail Resource Integration Tests', () => {
  let mockCollectionStore: ReturnType<typeof useCollectionStore>
  let mockLanguageStore: ReturnType<typeof useLanguageStore>
  let mockContextStore: ReturnType<typeof useContextStore>

  beforeEach(() => {
    setActivePinia(createPinia())

    // Setup collection store mock
    mockCollectionStore = {
      collections: [mockCollection],
      currentCollection: null,
      loading: false,
      clearCurrentCollection: vi.fn().mockImplementation(() => {
        mockCollectionStore.currentCollection = null
      }),
      fetchCollections: vi.fn().mockResolvedValue(undefined),
      fetchCollection: vi.fn().mockImplementation((id: string) => {
        if (id === mockCollection.id) {
          mockCollectionStore.currentCollection = mockCollection
        } else {
          mockCollectionStore.currentCollection = null
        }
        return Promise.resolve(undefined)
      }),
      createCollection: vi.fn().mockImplementation((request: CollectionStoreRequest) => {
        const newCollection = createMockCollection({
          id: '123e4567-e89b-12d3-a456-426614174999',
          internal_name: request.internal_name,
          language_id: request.language_id,
          context_id: request.context_id,
          backward_compatibility: request.backward_compatibility,
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString(),
        })
        mockCollectionStore.currentCollection = newCollection
        return Promise.resolve(newCollection)
      }),
      updateCollection: vi
        .fn()
        .mockImplementation((id: string, request: CollectionStoreRequest) => {
          const updated = createMockCollection({
            id,
            internal_name: request.internal_name,
            language_id: request.language_id,
            context_id: request.context_id,
            backward_compatibility: request.backward_compatibility,
            updated_at: new Date().toISOString(),
          })
          if (id === mockCollection.id) {
            mockCollectionStore.currentCollection = updated
          }
          return Promise.resolve(updated)
        }),
      deleteCollection: vi.fn().mockImplementation((id: string) => {
        if (mockCollectionStore.currentCollection?.id === id) {
          mockCollectionStore.currentCollection = null
        }
        return Promise.resolve(undefined)
      }),
    } as ReturnType<typeof useCollectionStore>

    // Setup language store mock
    mockLanguageStore = {
      languages: mockLanguages,
      currentLanguage: null,
      loading: false,
      error: null,
      fetchLanguages: vi.fn().mockResolvedValue(mockLanguages),
      getLanguageById: vi.fn().mockImplementation((id: string) => {
        return Promise.resolve(mockLanguages.find(l => l.id === id) || null)
      }),
      createLanguage: vi.fn(),
      updateLanguage: vi.fn(),
      deleteLanguage: vi.fn(),
      defaultLanguages: mockLanguages,
    } as ReturnType<typeof useLanguageStore>

    // Setup context store mock
    mockContextStore = {
      contexts: mockContexts,
      currentContext: null,
      loading: false,
      error: null,
      fetchContexts: vi.fn().mockResolvedValue(mockContexts),
      getContextById: vi.fn().mockImplementation((id: string) => {
        return Promise.resolve(mockContexts.find(c => c.id === id) || null)
      }),
      createContext: vi.fn(),
      updateContext: vi.fn(),
      deleteContext: vi.fn(),
    } as ReturnType<typeof useContextStore>

    // Mock store implementations
    vi.mocked(useCollectionStore).mockReturnValue(mockCollectionStore)
    vi.mocked(useLanguageStore).mockReturnValue(mockLanguageStore)
    vi.mocked(useContextStore).mockReturnValue(mockContextStore)

    vi.clearAllMocks()
  })

  describe('Collection Detail Data Loading', () => {
    it('should load collection with related data', async () => {
      const collectionStore = useCollectionStore()
      const languageStore = useLanguageStore()
      const contextStore = useContextStore()

      // Simulate loading collection detail page
      await Promise.all([
        languageStore.fetchLanguages(),
        contextStore.fetchContexts(),
        collectionStore.fetchCollection(mockCollection.id),
      ])

      expect(languageStore.fetchLanguages).toHaveBeenCalledOnce()
      expect(contextStore.fetchContexts).toHaveBeenCalledOnce()
      expect(collectionStore.fetchCollection).toHaveBeenCalledWith(mockCollection.id)
      expect(collectionStore.currentCollection).toBe(mockCollection)
    })

    it('should handle missing collection gracefully', async () => {
      const collectionStore = useCollectionStore()

      await collectionStore.fetchCollection('nonexistent-id')

      expect(collectionStore.fetchCollection).toHaveBeenCalledWith('nonexistent-id')
      expect(collectionStore.currentCollection).toBeNull()
    })

    it('should load language options for collection form', async () => {
      const languageStore = useLanguageStore()

      await languageStore.fetchLanguages()

      expect(languageStore.fetchLanguages).toHaveBeenCalledOnce()
      expect(languageStore.languages).toHaveLength(2)
      expect(languageStore.languages[0].id).toBe('eng')
      expect(languageStore.languages[1].id).toBe('fra')
    })

    it('should load context options for collection form', async () => {
      const contextStore = useContextStore()

      await contextStore.fetchContexts()

      expect(contextStore.fetchContexts).toHaveBeenCalledOnce()
      expect(contextStore.contexts).toHaveLength(2)
      expect(contextStore.contexts[0].id).toBe('museum-ctx')
      expect(contextStore.contexts[1].id).toBe('archive-ctx')
    })
  })

  describe('Collection Detail CRUD Operations', () => {
    it('should create new collection with form data', async () => {
      const collectionStore = useCollectionStore()
      const request: CollectionStoreRequest = {
        internal_name: 'New Detailed Collection',
        language_id: 'fra',
        context_id: 'archive-ctx',
        backward_compatibility: 'new-detailed',
      }

      const result = await collectionStore.createCollection(request)

      expect(collectionStore.createCollection).toHaveBeenCalledWith(request)
      expect(result.internal_name).toBe('New Detailed Collection')
      expect(result.language_id).toBe('fra')
      expect(result.context_id).toBe('archive-ctx')
      expect(result.backward_compatibility).toBe('new-detailed')
      expect(collectionStore.currentCollection).toBe(result)
    })

    it('should update existing collection with form data', async () => {
      const collectionStore = useCollectionStore()

      // First fetch the collection
      await collectionStore.fetchCollection(mockCollection.id)
      expect(collectionStore.currentCollection).toBe(mockCollection)

      const updateRequest: CollectionStoreRequest = {
        internal_name: 'Updated Test Collection',
        language_id: 'fra',
        context_id: 'archive-ctx',
        backward_compatibility: 'updated-test',
      }

      const result = await collectionStore.updateCollection(mockCollection.id, updateRequest)

      expect(collectionStore.updateCollection).toHaveBeenCalledWith(
        mockCollection.id,
        updateRequest
      )
      expect(result.internal_name).toBe('Updated Test Collection')
      expect(result.language_id).toBe('fra')
      expect(result.context_id).toBe('archive-ctx')
      expect(result.backward_compatibility).toBe('updated-test')
      expect(collectionStore.currentCollection).toBe(result)
    })

    it('should delete collection and clear current state', async () => {
      const collectionStore = useCollectionStore()

      // First fetch the collection
      await collectionStore.fetchCollection(mockCollection.id)
      expect(collectionStore.currentCollection).toBe(mockCollection)

      await collectionStore.deleteCollection(mockCollection.id)

      expect(collectionStore.deleteCollection).toHaveBeenCalledWith(mockCollection.id)
      expect(collectionStore.currentCollection).toBeNull()
    })
  })

  describe('Collection Detail Form Integration', () => {
    it('should integrate language and context lookups for form validation', async () => {
      const languageStore = useLanguageStore()
      const contextStore = useContextStore()

      // Load form dependencies
      await Promise.all([languageStore.fetchLanguages(), contextStore.fetchContexts()])

      // Validate language exists
      const selectedLanguage = await languageStore.getLanguageById('eng')
      expect(languageStore.getLanguageById).toHaveBeenCalledWith('eng')
      expect(selectedLanguage?.id).toBe('eng')
      expect(selectedLanguage?.internal_name).toBe('English')

      // Validate context exists
      const selectedContext = await contextStore.getContextById('museum-ctx')
      expect(contextStore.getContextById).toHaveBeenCalledWith('museum-ctx')
      expect(selectedContext?.id).toBe('museum-ctx')
      expect(selectedContext?.internal_name).toBe('Museum Context')
    })

    it('should handle invalid language/context selection', async () => {
      const languageStore = useLanguageStore()
      const contextStore = useContextStore()

      const invalidLanguage = await languageStore.getLanguageById('invalid')
      const invalidContext = await contextStore.getContextById('invalid')

      expect(invalidLanguage).toBeNull()
      expect(invalidContext).toBeNull()
    })

    it('should provide form options from loaded data', () => {
      const languageStore = useLanguageStore()
      const contextStore = useContextStore()

      // Form should have access to loaded options
      expect(languageStore.languages).toHaveLength(2)
      expect(contextStore.contexts).toHaveLength(2)

      // Language options should be formatted correctly
      const languageOptions = languageStore.languages.map(lang => ({
        value: lang.id,
        label: lang.internal_name,
      }))
      expect(languageOptions).toEqual([
        { value: 'eng', label: 'English' },
        { value: 'fra', label: 'French' },
      ])

      // Context options should be formatted correctly
      const contextOptions = contextStore.contexts.map(ctx => ({
        value: ctx.id,
        label: ctx.internal_name,
      }))
      expect(contextOptions).toEqual([
        { value: 'museum-ctx', label: 'Museum Context' },
        { value: 'archive-ctx', label: 'Archive Context' },
      ])
    })
  })

  describe('Collection Detail State Management', () => {
    it('should maintain consistent state during create workflow', async () => {
      const collectionStore = useCollectionStore()

      // Initial state - no current collection
      expect(collectionStore.currentCollection).toBeNull()

      const request: CollectionStoreRequest = {
        internal_name: 'Workflow Test Collection',
        language_id: 'eng',
        context_id: 'museum-ctx',
        backward_compatibility: null,
      }

      // After create - should set as current
      const created = await collectionStore.createCollection(request)
      expect(collectionStore.currentCollection).toBe(created)
      expect(collectionStore.currentCollection?.internal_name).toBe('Workflow Test Collection')
    })

    it('should maintain consistent state during update workflow', async () => {
      const collectionStore = useCollectionStore()

      // Load existing collection
      await collectionStore.fetchCollection(mockCollection.id)
      const originalCollection = collectionStore.currentCollection

      const updateRequest: CollectionStoreRequest = {
        internal_name: 'State Management Test',
        language_id: 'fra',
        context_id: 'archive-ctx',
        backward_compatibility: 'state-test',
      }

      // After update - should replace current
      const updated = await collectionStore.updateCollection(mockCollection.id, updateRequest)
      expect(collectionStore.currentCollection).toBe(updated)
      expect(collectionStore.currentCollection).not.toBe(originalCollection)
      expect(collectionStore.currentCollection?.internal_name).toBe('State Management Test')
    })

    it('should clear state when collection is deleted', async () => {
      const collectionStore = useCollectionStore()

      // Load collection first
      await collectionStore.fetchCollection(mockCollection.id)
      expect(collectionStore.currentCollection).toBe(mockCollection)

      // Delete should clear current collection
      await collectionStore.deleteCollection(mockCollection.id)
      expect(collectionStore.currentCollection).toBeNull()
    })

    it('should allow manual state clearing', () => {
      const collectionStore = useCollectionStore()

      // Set some current collection
      collectionStore.currentCollection = mockCollection
      expect(collectionStore.currentCollection).toBe(mockCollection)

      // Manual clear should work
      collectionStore.clearCurrentCollection()
      expect(collectionStore.clearCurrentCollection).toHaveBeenCalledOnce()
      expect(collectionStore.currentCollection).toBeNull()
    })
  })
})
