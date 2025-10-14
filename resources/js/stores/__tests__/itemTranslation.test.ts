import { describe, it, expect, beforeEach, vi, afterEach } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useItemTranslationStore } from '../itemTranslation'
import type { ItemTranslationResource } from '@metanull/inventory-app-api-client'

// Mock the ErrorHandler
vi.mock('@/utils/errorHandler', () => ({
  ErrorHandler: {
    handleError: vi.fn(),
  },
}))

// Mock the auth store
vi.mock('../auth', () => ({
  useAuthStore: vi.fn(() => ({
    token: 'mock-token',
  })),
}))

// Mock the API client
const mockItemTranslationApi = {
  itemTranslationIndex: vi.fn(),
  itemTranslationShow: vi.fn(),
  itemTranslationStore: vi.fn(),
  itemTranslationUpdate: vi.fn(),
  itemTranslationDestroy: vi.fn(),
}

vi.mock('@metanull/inventory-app-api-client', () => ({
  ItemTranslationApi: vi.fn().mockImplementation(() => mockItemTranslationApi),
  Configuration: vi.fn(),
}))

const mockItemTranslations: ItemTranslationResource[] = [
  {
    id: '123e4567-e89b-12d3-a456-426614174000',
    item_id: 'item-001',
    language_id: 'en',
    context_id: 'context-001',
    name: 'Test Item',
    alternate_name: 'Alternative Test Item',
    description: 'This is a test item translation',
    type: 'painting',
    holder: 'Museum A',
    owner: 'Owner A',
    initial_owner: 'Initial Owner A',
    dates: '1920-1930',
    location: 'Gallery 1',
    dimensions: '100x80cm',
    place_of_production: 'Paris',
    method_for_datation: 'Carbon dating',
    method_for_provenance: 'Documentary evidence',
    obtention: 'Purchased at auction',
    bibliography: 'Reference 1, Reference 2',
    author_id: 'author-001',
    text_copy_editor_id: 'editor-001',
    translator_id: null,
    translation_copy_editor_id: null,
    backward_compatibility: 'legacy-001',
    extra: { custom: 'data' },
    created_at: '2024-01-01T00:00:00Z',
    updated_at: '2024-01-01T00:00:00Z',
  },
  {
    id: '123e4567-e89b-12d3-a456-426614174001',
    item_id: 'item-001',
    language_id: 'fr',
    context_id: 'context-001',
    name: 'Article de Test',
    alternate_name: null,
    description: 'Ceci est une traduction de test',
    type: 'painting',
    holder: null,
    owner: null,
    initial_owner: null,
    dates: null,
    location: null,
    dimensions: null,
    place_of_production: null,
    method_for_datation: null,
    method_for_provenance: null,
    obtention: null,
    bibliography: null,
    author_id: null,
    text_copy_editor_id: null,
    translator_id: 'translator-001',
    translation_copy_editor_id: 'editor-002',
    backward_compatibility: null,
    extra: null,
    created_at: '2024-01-02T00:00:00Z',
    updated_at: '2024-01-02T00:00:00Z',
  },
]

describe('ItemTranslation Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  afterEach(() => {
    vi.clearAllMocks()
  })

  it('should initialize with empty state', () => {
    const store = useItemTranslationStore()

    expect(store.itemTranslations).toEqual([])
    expect(store.currentItemTranslation).toBeNull()
    expect(store.loading).toBe(false)
    expect(store.error).toBeNull()
    expect(store.page).toBe(1)
    expect(store.perPage).toBe(20)
    expect(store.total).toBeNull()
  })

  describe('fetchItemTranslations', () => {
    it('should fetch translations successfully', async () => {
      const store = useItemTranslationStore()

      mockItemTranslationApi.itemTranslationIndex.mockResolvedValue({
        data: mockItemTranslations,
      })

      await store.fetchItemTranslations()

      expect(mockItemTranslationApi.itemTranslationIndex).toHaveBeenCalledWith(
        1,
        20,
        undefined,
        undefined,
        undefined,
        undefined
      )
      expect(store.itemTranslations).toEqual(mockItemTranslations)
      expect(store.loading).toBe(false)
      expect(store.error).toBeNull()
    })

    it('should fetch translations with filters', async () => {
      const store = useItemTranslationStore()

      mockItemTranslationApi.itemTranslationIndex.mockResolvedValue({
        data: [mockItemTranslations[0]],
      })

      await store.fetchItemTranslations({
        page: 2,
        perPage: 10,
        filters: {
          item_id: 'item-001',
          language_id: 'en',
          context_id: 'context-001',
          default_context: true,
        },
      })

      expect(mockItemTranslationApi.itemTranslationIndex).toHaveBeenCalledWith(
        2,
        10,
        'item-001',
        'en',
        'context-001',
        true
      )
    })

    it('should handle fetch error', async () => {
      const store = useItemTranslationStore()
      const error = new Error('Network error')

      mockItemTranslationApi.itemTranslationIndex.mockRejectedValue(error)

      await expect(store.fetchItemTranslations()).rejects.toThrow('Network error')

      expect(store.loading).toBe(false)
      expect(store.error).toBe('Failed to fetch item translations')
    })
  })

  describe('fetchItemTranslation', () => {
    it('should fetch single translation successfully', async () => {
      const store = useItemTranslationStore()
      const translation = mockItemTranslations[0]

      mockItemTranslationApi.itemTranslationShow.mockResolvedValue({
        data: { data: translation },
      })

      const result = await store.fetchItemTranslation('123e4567-e89b-12d3-a456-426614174000')

      expect(mockItemTranslationApi.itemTranslationShow).toHaveBeenCalledWith(
        '123e4567-e89b-12d3-a456-426614174000'
      )
      expect(store.currentItemTranslation).toEqual(translation)
      expect(result).toEqual(translation)
    })

    it('should handle fetch single translation error', async () => {
      const store = useItemTranslationStore()
      const error = new Error('Not found')

      mockItemTranslationApi.itemTranslationShow.mockRejectedValue(error)

      await expect(
        store.fetchItemTranslation('123e4567-e89b-12d3-a456-426614174000')
      ).rejects.toThrow('Not found')

      expect(store.error).toBe('Failed to fetch item translation')
    })
  })

  describe('createItemTranslation', () => {
    it('should create translation successfully', async () => {
      const store = useItemTranslationStore()
      const createData = {
        item_id: 'item-002',
        language_id: 'en',
        context_id: 'context-001',
        name: 'New Item',
        description: 'New item description',
      }

      mockItemTranslationApi.itemTranslationStore.mockResolvedValue({
        data: 201,
      })

      // Mock fetchItemTranslations to simulate refetch
      mockItemTranslationApi.itemTranslationIndex.mockResolvedValue({
        data: [...mockItemTranslations],
      })

      const result = await store.createItemTranslation(createData)

      expect(mockItemTranslationApi.itemTranslationStore).toHaveBeenCalledWith(createData)
      // Should refetch the list
      expect(mockItemTranslationApi.itemTranslationIndex).toHaveBeenCalled()
      expect(result).toBe(true)
    })

    it('should handle create error', async () => {
      const store = useItemTranslationStore()
      const error = new Error('Validation error')

      mockItemTranslationApi.itemTranslationStore.mockRejectedValue(error)

      await expect(
        store.createItemTranslation({
          item_id: 'item-002',
          language_id: 'en',
          context_id: 'context-001',
          name: 'New Item',
          description: 'New item description',
        })
      ).rejects.toThrow('Validation error')

      expect(store.error).toBe('Failed to create item translation')
    })
  })

  describe('updateItemTranslation', () => {
    it('should update translation successfully', async () => {
      const store = useItemTranslationStore()
      
      // Pre-populate via fetch
      mockItemTranslationApi.itemTranslationIndex.mockResolvedValue({
        data: mockItemTranslations,
      })
      await store.fetchItemTranslations()

      const updatedTranslation = {
        ...mockItemTranslations[0],
        name: 'Updated Test Item',
      }

      mockItemTranslationApi.itemTranslationUpdate.mockResolvedValue({
        data: 200,
      })

      mockItemTranslationApi.itemTranslationShow.mockResolvedValue({
        data: { data: updatedTranslation },
      })

      const result = await store.updateItemTranslation('123e4567-e89b-12d3-a456-426614174000', {
        name: 'Updated Test Item',
      })

      expect(mockItemTranslationApi.itemTranslationUpdate).toHaveBeenCalledWith(
        '123e4567-e89b-12d3-a456-426614174000',
        { name: 'Updated Test Item' }
      )
      // Should refetch the single item
      expect(mockItemTranslationApi.itemTranslationShow).toHaveBeenCalled()
      expect(result).toEqual(updatedTranslation)
    })

    it('should handle update error', async () => {
      const store = useItemTranslationStore()
      const error = new Error('Update failed')

      mockItemTranslationApi.itemTranslationUpdate.mockRejectedValue(error)

      await expect(
        store.updateItemTranslation('123e4567-e89b-12d3-a456-426614174000', {
          name: 'Updated Test Item',
        })
      ).rejects.toThrow('Update failed')

      expect(store.error).toBe('Failed to update item translation')
    })
  })

  describe('deleteItemTranslation', () => {
    it('should delete translation successfully', async () => {
      const store = useItemTranslationStore()
      
      // Pre-populate via fetch
      mockItemTranslationApi.itemTranslationIndex.mockResolvedValue({
        data: mockItemTranslations,
      })
      await store.fetchItemTranslations()
      
      // Set current
      mockItemTranslationApi.itemTranslationShow.mockResolvedValue({
        data: { data: mockItemTranslations[0] },
      })
      await store.fetchItemTranslation('123e4567-e89b-12d3-a456-426614174000')

      mockItemTranslationApi.itemTranslationDestroy.mockResolvedValue({})

      await store.deleteItemTranslation('123e4567-e89b-12d3-a456-426614174000')

      expect(mockItemTranslationApi.itemTranslationDestroy).toHaveBeenCalledWith(
        '123e4567-e89b-12d3-a456-426614174000'
      )
      expect(store.itemTranslations).toHaveLength(1)
      expect(store.itemTranslations[0].id).toBe('123e4567-e89b-12d3-a456-426614174001')
      expect(store.currentItemTranslation).toBeNull()
    })

    it('should handle delete error', async () => {
      const store = useItemTranslationStore()
      const error = new Error('Delete failed')

      mockItemTranslationApi.itemTranslationDestroy.mockRejectedValue(error)

      await expect(
        store.deleteItemTranslation('123e4567-e89b-12d3-a456-426614174000')
      ).rejects.toThrow('Delete failed')

      expect(store.error).toBe('Failed to delete item translation')
    })
  })

  describe('reset', () => {
    it('should reset store to initial state', async () => {
      const store = useItemTranslationStore()

      // Set some state via actions
      mockItemTranslationApi.itemTranslationIndex.mockResolvedValue({
        data: mockItemTranslations,
      })
      await store.fetchItemTranslations({ page: 2, perPage: 50 })
      
      mockItemTranslationApi.itemTranslationShow.mockResolvedValue({
        data: { data: mockItemTranslations[0] },
      })
      await store.fetchItemTranslation('123e4567-e89b-12d3-a456-426614174000')

      // Reset
      store.reset()

      // Verify initial state
      expect(store.itemTranslations).toEqual([])
      expect(store.currentItemTranslation).toBeNull()
      expect(store.loading).toBe(false)
      expect(store.error).toBeNull()
      expect(store.page).toBe(1)
      expect(store.perPage).toBe(20)
      expect(store.total).toBeNull()
    })
  })
})
