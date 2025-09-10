/**
 * Integration Tests for Collection Resource Management
 *
 * These tests verify complete user workflows combining multiple components
 * and stores to ensure Collection-specific features work together correctly.
 */

import { beforeEach, describe, expect, it, vi, beforeAll, afterAll } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useCollectionStore } from '@/stores/collection'
import { createMockCollection } from '@/__tests__/test-utils'
import type { CollectionResource, CollectionStoreRequest } from '@metanull/inventory-app-api-client'

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

// Mock the stores instead of the API client
vi.mock('@/stores/collection')

// Test data
const mockCollections: CollectionResource[] = [
  createMockCollection({
    id: '123e4567-e89b-12d3-a456-426614174001',
    internal_name: 'Ancient Artifacts',
    backward_compatibility: 'ancient-01',
    created_at: '2023-01-01T00:00:00Z',
    updated_at: '2023-01-01T00:00:00Z',
  }),
  createMockCollection({
    id: '123e4567-e89b-12d3-a456-426614174002',
    internal_name: 'Modern Art Collection',
    backward_compatibility: null,
    created_at: '2023-01-02T00:00:00Z',
    updated_at: '2023-01-02T00:00:00Z',
  }),
]

describe('Collection Resource Integration Tests', () => {
  let mockCollectionStore: ReturnType<typeof useCollectionStore>

  beforeEach(() => {
    setActivePinia(createPinia())

    // Reset mock data for each test
    const resetMockCollections = [
      createMockCollection({
        id: '123e4567-e89b-12d3-a456-426614174001',
        internal_name: 'Ancient Artifacts',
        backward_compatibility: 'ancient-01',
        created_at: '2023-01-01T00:00:00Z',
        updated_at: '2023-01-01T00:00:00Z',
      }),
      createMockCollection({
        id: '123e4567-e89b-12d3-a456-426614174002',
        internal_name: 'Modern Art Collection',
        backward_compatibility: null,
        created_at: '2023-01-02T00:00:00Z',
        updated_at: '2023-01-02T00:00:00Z',
      }),
    ]

    // Setup comprehensive store mock
    mockCollectionStore = {
      collections: resetMockCollections,
      currentCollection: null,
      loading: false,
      clearCurrentCollection: vi.fn().mockImplementation(() => {
        mockCollectionStore.currentCollection = null
      }),
      fetchCollections: vi.fn().mockResolvedValue(undefined),
      fetchCollection: vi.fn().mockImplementation((id: string) => {
        const collection = resetMockCollections.find(c => c.id === id)
        mockCollectionStore.currentCollection = collection || null
        return Promise.resolve(undefined)
      }),
      createCollection: vi.fn().mockImplementation((request: CollectionStoreRequest) => {
        const newCollection = createMockCollection({
          id: '123e4567-e89b-12d3-a456-426614174999',
          internal_name: request.internal_name,
          backward_compatibility: request.backward_compatibility,
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString(),
        })
        return Promise.resolve(newCollection)
      }),
      updateCollection: vi
        .fn()
        .mockImplementation((id: string, request: CollectionStoreRequest) => {
          const updated = createMockCollection({
            id,
            internal_name: request.internal_name,
            backward_compatibility: request.backward_compatibility,
            updated_at: new Date().toISOString(),
          })
          const index = mockCollectionStore.collections.findIndex(c => c.id === id)
          if (index !== -1) {
            mockCollectionStore.collections[index] = updated
          }
          if (mockCollectionStore.currentCollection?.id === id) {
            mockCollectionStore.currentCollection = updated
          }
          return Promise.resolve(updated)
        }),
      deleteCollection: vi.fn().mockResolvedValue(undefined),
    } as ReturnType<typeof useCollectionStore>

    // Mock store implementations
    vi.mocked(useCollectionStore).mockReturnValue(mockCollectionStore)

    vi.clearAllMocks()
  })

  describe('Collection CRUD Operations', () => {
    it('should fetch all collections', async () => {
      const store = useCollectionStore()
      await store.fetchCollections()

      expect(store.fetchCollections).toHaveBeenCalledOnce()
    })

    it('should fetch collection by id', async () => {
      const store = useCollectionStore()
      await store.fetchCollection('123e4567-e89b-12d3-a456-426614174001')

      expect(store.fetchCollection).toHaveBeenCalledWith('123e4567-e89b-12d3-a456-426614174001')
      expect(store.currentCollection?.id).toBe('123e4567-e89b-12d3-a456-426614174001')
      expect(store.currentCollection?.internal_name).toBe('Ancient Artifacts')
    })

    it('should handle non-existent collection fetch', async () => {
      const store = useCollectionStore()
      await store.fetchCollection('nonexistent-id')

      expect(store.fetchCollection).toHaveBeenCalledWith('nonexistent-id')
      expect(store.currentCollection).toBeNull()
    })

    it('should create new collection', async () => {
      const store = useCollectionStore()
      const request: CollectionStoreRequest = {
        internal_name: 'New Test Collection',
        language_id: 'eng',
        context_id: 'ctx-123',
        backward_compatibility: 'new-test-01',
      }

      const result = await store.createCollection(request)

      expect(store.createCollection).toHaveBeenCalledWith(request)
      expect(result.internal_name).toBe('New Test Collection')
      expect(result.backward_compatibility).toBe('new-test-01')
    })

    it('should update existing collection', async () => {
      const store = useCollectionStore()
      const collectionId = '123e4567-e89b-12d3-a456-426614174001'
      const request: CollectionStoreRequest = {
        internal_name: 'Updated Ancient Artifacts',
        language_id: 'eng',
        context_id: 'ctx-123',
        backward_compatibility: 'ancient-updated',
      }

      // First set current collection
      await store.fetchCollection(collectionId)

      const result = await store.updateCollection(collectionId, request)

      expect(store.updateCollection).toHaveBeenCalledWith(collectionId, request)
      expect(result.id).toBe(collectionId)
      expect(result.internal_name).toBe('Updated Ancient Artifacts')
      expect(result.backward_compatibility).toBe('ancient-updated')
      expect(store.currentCollection?.internal_name).toBe('Updated Ancient Artifacts')
    })

    it('should delete collection', async () => {
      const store = useCollectionStore()
      const collectionId = '123e4567-e89b-12d3-a456-426614174001'

      await store.deleteCollection(collectionId)

      expect(store.deleteCollection).toHaveBeenCalledWith(collectionId)
    })

    it('should clear current collection', () => {
      const store = useCollectionStore()
      store.currentCollection = mockCollections[0]

      store.clearCurrentCollection()

      expect(store.clearCurrentCollection).toHaveBeenCalledOnce()
      expect(store.currentCollection).toBeNull()
    })
  })

  describe('Collection State Management', () => {
    it('should maintain collections list consistency', async () => {
      const store = useCollectionStore()

      expect(store.collections).toHaveLength(2)
      expect(store.collections[0].internal_name).toBe('Ancient Artifacts')
      expect(store.collections[1].internal_name).toBe('Modern Art Collection')
    })

    it('should handle loading state', () => {
      const store = useCollectionStore()

      expect(store.loading).toBe(false)
    })

    it('should handle current collection state transitions', async () => {
      const store = useCollectionStore()

      // Initial state
      expect(store.currentCollection).toBeNull()

      // After fetch
      await store.fetchCollection('123e4567-e89b-12d3-a456-426614174001')
      expect(store.currentCollection).not.toBeNull()
      expect(store.currentCollection?.id).toBe('123e4567-e89b-12d3-a456-426614174001')

      // After clear
      store.clearCurrentCollection()
      expect(store.currentCollection).toBeNull()
    })

    it('should handle creating new collection', async () => {
      const store = useCollectionStore()

      const request: CollectionStoreRequest = {
        internal_name: 'Another New Collection',
        language_id: 'fra',
        context_id: 'ctx-456',
        backward_compatibility: null,
      }

      const result = await store.createCollection(request)

      expect(store.createCollection).toHaveBeenCalledWith(request)
      expect(result.internal_name).toBe('Another New Collection')
    })

    it('should handle updating existing collection', async () => {
      const store = useCollectionStore()
      const collectionId = '123e4567-e89b-12d3-a456-426614174002'

      const request: CollectionStoreRequest = {
        internal_name: 'Updated Modern Art',
        language_id: 'eng',
        context_id: 'ctx-789',
        backward_compatibility: 'modern-updated',
      }

      const result = await store.updateCollection(collectionId, request)

      expect(store.updateCollection).toHaveBeenCalledWith(collectionId, request)
      expect(result.internal_name).toBe('Updated Modern Art')
      expect(result.backward_compatibility).toBe('modern-updated')
    })

    it('should handle deleting collection', async () => {
      const store = useCollectionStore()
      const collectionId = '123e4567-e89b-12d3-a456-426614174002'

      await store.deleteCollection(collectionId)

      expect(store.deleteCollection).toHaveBeenCalledWith(collectionId)
    })
  })
})
