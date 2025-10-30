import { describe, it, expect, beforeEach, vi, afterEach } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useCollectionImageStore } from '../collectionImage'
import type { CollectionImageResource } from '@metanull/inventory-app-api-client'

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
const mockCollectionImageApi = {
  collectionImagesIndex: vi.fn(),
  collectionImageShow: vi.fn(),
  collectionImageStore: vi.fn(),
  collectionImageUpdate: vi.fn(),
  collectionImageDestroy: vi.fn(),
  collectionImageMoveUp: vi.fn(),
  collectionImageMoveDown: vi.fn(),
  collectionImageTightenOrdering: vi.fn(),
  collectionAttachImage: vi.fn(),
  collectionImageDetach: vi.fn(),
}

vi.mock('@metanull/inventory-app-api-client', () => ({
  CollectionImageApi: class {
    constructor() {
      return mockCollectionImageApi
    }
  },
  Configuration: vi.fn(),
}))

const mockCollectionImages: CollectionImageResource[] = [
  {
    id: '123e4567-e89b-12d3-a456-426614174000',
    collection_id: 'collection-001',
    path: '/storage/images/image1.jpg',
    original_name: 'image1.jpg',
    mime_type: 'image/jpeg',
    size: 102400,
    alt_text: 'First image',
    display_order: 1,
    created_at: '2024-01-01T00:00:00Z',
    updated_at: '2024-01-01T00:00:00Z',
  },
  {
    id: '123e4567-e89b-12d3-a456-426614174001',
    collection_id: 'collection-001',
    path: '/storage/images/image2.jpg',
    original_name: 'image2.jpg',
    mime_type: 'image/jpeg',
    size: 204800,
    alt_text: 'Second image',
    display_order: 2,
    created_at: '2024-01-02T00:00:00Z',
    updated_at: '2024-01-02T00:00:00Z',
  },
  {
    id: '123e4567-e89b-12d3-a456-426614174002',
    collection_id: 'collection-001',
    path: '/storage/images/image3.jpg',
    original_name: 'image3.jpg',
    mime_type: 'image/jpeg',
    size: 153600,
    alt_text: null,
    display_order: 3,
    created_at: '2024-01-03T00:00:00Z',
    updated_at: '2024-01-03T00:00:00Z',
  },
]

describe('CollectionImage Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  afterEach(() => {
    vi.clearAllMocks()
  })

  it('should initialize with empty state', () => {
    const store = useCollectionImageStore()

    expect(store.collectionImages).toEqual([])
    expect(store.currentCollectionImage).toBeNull()
    expect(store.loading).toBe(false)
    expect(store.error).toBeNull()
  })

  describe('fetchCollectionImages', () => {
    it('should fetch collection images for a specific collection successfully', async () => {
      const store = useCollectionImageStore()

      mockCollectionImageApi.collectionImagesIndex.mockResolvedValue({
        data: mockCollectionImages,
      })

      await store.fetchCollectionImages('collection-001')

      expect(mockCollectionImageApi.collectionImagesIndex).toHaveBeenCalledWith(
        'collection-001',
        undefined
      )
      expect(store.collectionImages).toEqual(mockCollectionImages)
      expect(store.loading).toBe(false)
      expect(store.error).toBeNull()
    })

    it('should fetch collection images with includes parameter', async () => {
      const store = useCollectionImageStore()

      mockCollectionImageApi.collectionImagesIndex.mockResolvedValue({
        data: [mockCollectionImages[0]],
      })

      await store.fetchCollectionImages('collection-001', ['collection'])

      expect(mockCollectionImageApi.collectionImagesIndex).toHaveBeenCalledWith(
        'collection-001',
        'collection'
      )
    })

    it('should handle fetch error', async () => {
      const store = useCollectionImageStore()
      const error = new Error('Network error')

      mockCollectionImageApi.collectionImagesIndex.mockRejectedValue(error)

      await expect(store.fetchCollectionImages('collection-001')).rejects.toThrow('Network error')

      expect(store.loading).toBe(false)
      expect(store.error).toBe('Failed to fetch collection images')
    })
  })

  describe('fetchCollectionImage', () => {
    it('should fetch single collection image successfully', async () => {
      const store = useCollectionImageStore()
      const collectionImage = mockCollectionImages[0]

      mockCollectionImageApi.collectionImageShow.mockResolvedValue({
        data: { data: collectionImage },
      })

      const result = await store.fetchCollectionImage('123e4567-e89b-12d3-a456-426614174000')

      expect(mockCollectionImageApi.collectionImageShow).toHaveBeenCalledWith(
        '123e4567-e89b-12d3-a456-426614174000',
        undefined
      )
      expect(store.currentCollectionImage).toEqual(collectionImage)
      expect(result).toEqual(collectionImage)
    })

    it('should handle fetch single collection image error', async () => {
      const store = useCollectionImageStore()
      const error = new Error('Not found')

      mockCollectionImageApi.collectionImageShow.mockRejectedValue(error)

      await expect(
        store.fetchCollectionImage('123e4567-e89b-12d3-a456-426614174000')
      ).rejects.toThrow('Not found')

      expect(store.error).toBe('Failed to fetch collection image')
    })
  })

  describe('attachImageToCollection', () => {
    it('should attach available image to collection successfully', async () => {
      const store = useCollectionImageStore()
      const newCollectionImage = {
        ...mockCollectionImages[0],
        id: '123e4567-e89b-12d3-a456-426614174003',
      }

      mockCollectionImageApi.collectionAttachImage.mockResolvedValue({
        data: { data: newCollectionImage },
      })

      // Mock refetch
      mockCollectionImageApi.collectionImagesIndex.mockResolvedValue({
        data: [...mockCollectionImages, newCollectionImage],
      })

      const result = await store.attachImageToCollection('collection-001', 'image-004')

      expect(mockCollectionImageApi.collectionAttachImage).toHaveBeenCalledWith('collection-001', {
        available_image_id: 'image-004',
      })
      // Should refetch the list
      expect(mockCollectionImageApi.collectionImagesIndex).toHaveBeenCalledWith(
        'collection-001',
        undefined
      )
      expect(result).toBe(true)
    })

    it('should handle attach error', async () => {
      const store = useCollectionImageStore()
      const error = new Error('Image already attached')

      mockCollectionImageApi.collectionAttachImage.mockRejectedValue(error)

      await expect(store.attachImageToCollection('collection-001', 'image-004')).rejects.toThrow(
        'Image already attached'
      )

      expect(store.error).toBe('Failed to attach image to collection')
    })
  })

  describe('updateCollectionImage', () => {
    it('should update collection image successfully', async () => {
      const store = useCollectionImageStore()

      // Pre-populate via fetch
      mockCollectionImageApi.collectionImagesIndex.mockResolvedValue({
        data: mockCollectionImages,
      })
      await store.fetchCollectionImages('collection-001')

      const updatedCollectionImage = {
        ...mockCollectionImages[0],
        alt_text: 'Updated alt text',
      }

      // Mock both update and the subsequent fetchCollectionImage (which calls collectionImageShow)
      mockCollectionImageApi.collectionImageUpdate.mockResolvedValue({
        data: { data: updatedCollectionImage },
      })

      mockCollectionImageApi.collectionImageShow.mockResolvedValue({
        data: { data: updatedCollectionImage },
      })

      const result = await store.updateCollectionImage('123e4567-e89b-12d3-a456-426614174000', {
        alt_text: 'Updated alt text',
      })

      expect(mockCollectionImageApi.collectionImageUpdate).toHaveBeenCalledWith(
        '123e4567-e89b-12d3-a456-426614174000',
        { alt_text: 'Updated alt text' }
      )
      expect(result).toEqual(updatedCollectionImage)
      // Should update in local array
      expect(store.collectionImages[0].alt_text).toBe('Updated alt text')
    })

    it('should handle update error', async () => {
      const store = useCollectionImageStore()
      const error = new Error('Update failed')

      mockCollectionImageApi.collectionImageUpdate.mockRejectedValue(error)

      await expect(
        store.updateCollectionImage('123e4567-e89b-12d3-a456-426614174000', {
          alt_text: 'Updated alt text',
        })
      ).rejects.toThrow('Update failed')

      expect(store.error).toBe('Failed to update collection image')
    })
  })

  describe('deleteCollectionImage', () => {
    it('should delete collection image successfully', async () => {
      const store = useCollectionImageStore()

      // Pre-populate via fetch
      mockCollectionImageApi.collectionImagesIndex.mockResolvedValue({
        data: mockCollectionImages,
      })
      await store.fetchCollectionImages('collection-001')

      mockCollectionImageApi.collectionImageDestroy.mockResolvedValue({})

      await store.deleteCollectionImage('123e4567-e89b-12d3-a456-426614174000')

      expect(mockCollectionImageApi.collectionImageDestroy).toHaveBeenCalledWith(
        '123e4567-e89b-12d3-a456-426614174000'
      )
      expect(store.collectionImages).toHaveLength(2)
      expect(store.collectionImages[0].id).toBe('123e4567-e89b-12d3-a456-426614174001')
    })

    it('should handle delete error', async () => {
      const store = useCollectionImageStore()
      const error = new Error('Delete failed')

      mockCollectionImageApi.collectionImageDestroy.mockRejectedValue(error)

      await expect(
        store.deleteCollectionImage('123e4567-e89b-12d3-a456-426614174000')
      ).rejects.toThrow('Delete failed')

      expect(store.error).toBe('Failed to delete collection image')
    })
  })

  describe('detachImageFromCollection', () => {
    it('should detach collection image to available images successfully', async () => {
      const store = useCollectionImageStore()

      // Pre-populate via fetch
      mockCollectionImageApi.collectionImagesIndex.mockResolvedValue({
        data: mockCollectionImages,
      })
      await store.fetchCollectionImages('collection-001')

      mockCollectionImageApi.collectionImageDetach.mockResolvedValue({
        data: {
          success: true,
          message: 'Image detached successfully',
          available_image_id: 'image-001',
        },
      })

      const result = await store.detachImageFromCollection('123e4567-e89b-12d3-a456-426614174000')

      expect(mockCollectionImageApi.collectionImageDetach).toHaveBeenCalledWith(
        '123e4567-e89b-12d3-a456-426614174000'
      )
      expect(result.available_image_id).toBe('image-001')
      // Should remove from local array
      expect(store.collectionImages).toHaveLength(2)
      expect(store.collectionImages[0].id).toBe('123e4567-e89b-12d3-a456-426614174001')
    })

    it('should handle detach error', async () => {
      const store = useCollectionImageStore()
      const error = new Error('Detach failed')

      mockCollectionImageApi.collectionImageDetach.mockRejectedValue(error)

      await expect(
        store.detachImageFromCollection('123e4567-e89b-12d3-a456-426614174000')
      ).rejects.toThrow('Detach failed')

      expect(store.error).toBe('Failed to detach image from collection')
    })
  })

  describe('moveImageUp', () => {
    it('should move collection image up successfully', async () => {
      const store = useCollectionImageStore()

      // Pre-populate via fetch
      mockCollectionImageApi.collectionImagesIndex.mockResolvedValue({
        data: mockCollectionImages,
      })
      await store.fetchCollectionImages('collection-001')

      const movedImage = { ...mockCollectionImages[1], display_order: 1 }
      mockCollectionImageApi.collectionImageMoveUp.mockResolvedValue({
        data: { data: movedImage },
      })

      // Mock refetch
      mockCollectionImageApi.collectionImagesIndex.mockResolvedValue({
        data: [
          movedImage,
          { ...mockCollectionImages[0], display_order: 2 },
          mockCollectionImages[2],
        ],
      })

      await store.moveImageUp('123e4567-e89b-12d3-a456-426614174001')

      expect(mockCollectionImageApi.collectionImageMoveUp).toHaveBeenCalledWith(
        '123e4567-e89b-12d3-a456-426614174001'
      )
      // Should refetch the list
      expect(mockCollectionImageApi.collectionImagesIndex).toHaveBeenCalled()
    })

    it('should handle moveUp error', async () => {
      const store = useCollectionImageStore()
      const error = new Error('Move failed')

      mockCollectionImageApi.collectionImageMoveUp.mockRejectedValue(error)

      await expect(store.moveImageUp('123e4567-e89b-12d3-a456-426614174001')).rejects.toThrow(
        'Move failed'
      )

      expect(store.error).toBe('Failed to move image up')
    })
  })

  describe('moveImageDown', () => {
    it('should move collection image down successfully', async () => {
      const store = useCollectionImageStore()

      // Pre-populate via fetch
      mockCollectionImageApi.collectionImagesIndex.mockResolvedValue({
        data: mockCollectionImages,
      })
      await store.fetchCollectionImages('collection-001')

      const movedImage = { ...mockCollectionImages[0], display_order: 2 }
      mockCollectionImageApi.collectionImageMoveDown.mockResolvedValue({
        data: { data: movedImage },
      })

      // Mock refetch
      mockCollectionImageApi.collectionImagesIndex.mockResolvedValue({
        data: [
          { ...mockCollectionImages[1], display_order: 1 },
          movedImage,
          mockCollectionImages[2],
        ],
      })

      await store.moveImageDown('123e4567-e89b-12d3-a456-426614174000')

      expect(mockCollectionImageApi.collectionImageMoveDown).toHaveBeenCalledWith(
        '123e4567-e89b-12d3-a456-426614174000'
      )
      // Should refetch the list
      expect(mockCollectionImageApi.collectionImagesIndex).toHaveBeenCalled()
    })

    it('should handle moveDown error', async () => {
      const store = useCollectionImageStore()
      const error = new Error('Move failed')

      mockCollectionImageApi.collectionImageMoveDown.mockRejectedValue(error)

      await expect(store.moveImageDown('123e4567-e89b-12d3-a456-426614174000')).rejects.toThrow(
        'Move failed'
      )

      expect(store.error).toBe('Failed to move image down')
    })
  })

  describe('tightenOrdering', () => {
    it('should tighten ordering successfully', async () => {
      const store = useCollectionImageStore()

      mockCollectionImageApi.collectionImageTightenOrdering.mockResolvedValue({
        data: {
          success: true,
          message: 'Image ordering tightened successfully',
        },
      })

      await store.tightenOrdering('123e4567-e89b-12d3-a456-426614174000')

      expect(mockCollectionImageApi.collectionImageTightenOrdering).toHaveBeenCalledWith(
        '123e4567-e89b-12d3-a456-426614174000'
      )
    })

    it('should handle tightenOrdering error', async () => {
      const store = useCollectionImageStore()
      const error = new Error('Tighten failed')

      mockCollectionImageApi.collectionImageTightenOrdering.mockRejectedValue(error)

      await expect(store.tightenOrdering('123e4567-e89b-12d3-a456-426614174000')).rejects.toThrow(
        'Tighten failed'
      )

      expect(store.error).toBe('Failed to tighten image ordering')
    })
  })

  describe('reset', () => {
    it('should reset store to initial state', async () => {
      const store = useCollectionImageStore()

      // Set some state via actions
      mockCollectionImageApi.collectionImagesIndex.mockResolvedValue({
        data: mockCollectionImages,
      })
      await store.fetchCollectionImages('collection-001')

      mockCollectionImageApi.collectionImageShow.mockResolvedValue({
        data: { data: mockCollectionImages[0] },
      })
      await store.fetchCollectionImage('123e4567-e89b-12d3-a456-426614174000')

      // Reset
      store.reset()

      // Verify initial state
      expect(store.collectionImages).toEqual([])
      expect(store.currentCollectionImage).toBeNull()
      expect(store.loading).toBe(false)
      expect(store.error).toBeNull()
    })
  })
})
