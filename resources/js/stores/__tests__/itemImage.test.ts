import { describe, it, expect, beforeEach, vi, afterEach } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useItemImageStore } from '../itemImage'
import type { ItemImageResource } from '@metanull/inventory-app-api-client'

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
const mockItemImageApi = {
  itemImagesIndex: vi.fn(),
  itemImageShow: vi.fn(),
  itemImageStore: vi.fn(),
  itemImageUpdate: vi.fn(),
  itemImageDestroy: vi.fn(),
  itemImageMoveUp: vi.fn(),
  itemImageMoveDown: vi.fn(),
  itemImageTightenOrdering: vi.fn(),
  itemAttachImage: vi.fn(),
  itemImageDetach: vi.fn(),
}

vi.mock('@metanull/inventory-app-api-client', () => ({
  ItemImageApi: vi.fn().mockImplementation(() => mockItemImageApi),
  Configuration: vi.fn(),
}))

const mockItemImages: ItemImageResource[] = [
  {
    id: '123e4567-e89b-12d3-a456-426614174000',
    item_id: 'item-001',
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
    item_id: 'item-001',
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
    item_id: 'item-001',
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

describe('ItemImage Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  afterEach(() => {
    vi.clearAllMocks()
  })

  it('should initialize with empty state', () => {
    const store = useItemImageStore()

    expect(store.itemImages).toEqual([])
    expect(store.currentItemImage).toBeNull()
    expect(store.loading).toBe(false)
    expect(store.error).toBeNull()
  })

  describe('fetchItemImages', () => {
    it('should fetch item images for a specific item successfully', async () => {
      const store = useItemImageStore()

      mockItemImageApi.itemImagesIndex.mockResolvedValue({
        data: mockItemImages,
      })

      await store.fetchItemImages('item-001')

      expect(mockItemImageApi.itemImagesIndex).toHaveBeenCalledWith('item-001', undefined)
      expect(store.itemImages).toEqual(mockItemImages)
      expect(store.loading).toBe(false)
      expect(store.error).toBeNull()
    })

    it('should fetch item images with includes parameter', async () => {
      const store = useItemImageStore()

      mockItemImageApi.itemImagesIndex.mockResolvedValue({
        data: [mockItemImages[0]],
      })

      await store.fetchItemImages('item-001', ['item'])

      expect(mockItemImageApi.itemImagesIndex).toHaveBeenCalledWith('item-001', 'item')
    })

    it('should handle fetch error', async () => {
      const store = useItemImageStore()
      const error = new Error('Network error')

      mockItemImageApi.itemImagesIndex.mockRejectedValue(error)

      await expect(store.fetchItemImages('item-001')).rejects.toThrow('Network error')

      expect(store.loading).toBe(false)
      expect(store.error).toBe('Failed to fetch item images')
    })
  })

  describe('fetchItemImage', () => {
    it('should fetch single item image successfully', async () => {
      const store = useItemImageStore()
      const itemImage = mockItemImages[0]

      mockItemImageApi.itemImageShow.mockResolvedValue({
        data: { data: itemImage },
      })

      const result = await store.fetchItemImage('123e4567-e89b-12d3-a456-426614174000')

      expect(mockItemImageApi.itemImageShow).toHaveBeenCalledWith(
        '123e4567-e89b-12d3-a456-426614174000',
        undefined
      )
      expect(store.currentItemImage).toEqual(itemImage)
      expect(result).toEqual(itemImage)
    })

    it('should handle fetch single item image error', async () => {
      const store = useItemImageStore()
      const error = new Error('Not found')

      mockItemImageApi.itemImageShow.mockRejectedValue(error)

      await expect(
        store.fetchItemImage('123e4567-e89b-12d3-a456-426614174000')
      ).rejects.toThrow('Not found')

      expect(store.error).toBe('Failed to fetch item image')
    })
  })

  describe('attachImageToItem', () => {
    it('should attach available image to item successfully', async () => {
      const store = useItemImageStore()
      const newItemImage = {
        ...mockItemImages[0],
        id: '123e4567-e89b-12d3-a456-426614174003',
      }

      mockItemImageApi.itemAttachImage.mockResolvedValue({
        data: { data: newItemImage },
      })

      // Mock refetch
      mockItemImageApi.itemImagesIndex.mockResolvedValue({
        data: [...mockItemImages, newItemImage],
      })

      const result = await store.attachImageToItem('item-001', 'image-004')

      expect(mockItemImageApi.itemAttachImage).toHaveBeenCalledWith('item-001', {
        available_image_id: 'image-004',
      })
      // Should refetch the list
      expect(mockItemImageApi.itemImagesIndex).toHaveBeenCalledWith('item-001', undefined)
      expect(result).toBe(true)
    })

    it('should handle attach error', async () => {
      const store = useItemImageStore()
      const error = new Error('Image already attached')

      mockItemImageApi.itemAttachImage.mockRejectedValue(error)

      await expect(store.attachImageToItem('item-001', 'image-004')).rejects.toThrow(
        'Image already attached'
      )

      expect(store.error).toBe('Failed to attach image to item')
    })
  })

  describe('updateItemImage', () => {
    it('should update item image successfully', async () => {
      const store = useItemImageStore()

      // Pre-populate via fetch
      mockItemImageApi.itemImagesIndex.mockResolvedValue({
        data: mockItemImages,
      })
      await store.fetchItemImages('item-001')

      const updatedItemImage = {
        ...mockItemImages[0],
        alt_text: 'Updated alt text',
      }

      // Mock both update and the subsequent fetchItemImage (which calls itemImageShow)
      mockItemImageApi.itemImageUpdate.mockResolvedValue({
        data: { data: updatedItemImage },
      })
      
      mockItemImageApi.itemImageShow.mockResolvedValue({
        data: { data: updatedItemImage },
      })

      const result = await store.updateItemImage('123e4567-e89b-12d3-a456-426614174000', {
        alt_text: 'Updated alt text',
      })

      expect(mockItemImageApi.itemImageUpdate).toHaveBeenCalledWith(
        '123e4567-e89b-12d3-a456-426614174000',
        { alt_text: 'Updated alt text' }
      )
      expect(result).toEqual(updatedItemImage)
      // Should update in local array
      expect(store.itemImages[0].alt_text).toBe('Updated alt text')
    })

    it('should handle update error', async () => {
      const store = useItemImageStore()
      const error = new Error('Update failed')

      mockItemImageApi.itemImageUpdate.mockRejectedValue(error)

      await expect(
        store.updateItemImage('123e4567-e89b-12d3-a456-426614174000', {
          alt_text: 'Updated alt text',
        })
      ).rejects.toThrow('Update failed')

      expect(store.error).toBe('Failed to update item image')
    })
  })

  describe('deleteItemImage', () => {
    it('should delete item image successfully', async () => {
      const store = useItemImageStore()

      // Pre-populate via fetch
      mockItemImageApi.itemImagesIndex.mockResolvedValue({
        data: mockItemImages,
      })
      await store.fetchItemImages('item-001')

      mockItemImageApi.itemImageDestroy.mockResolvedValue({})

      await store.deleteItemImage('123e4567-e89b-12d3-a456-426614174000')

      expect(mockItemImageApi.itemImageDestroy).toHaveBeenCalledWith(
        '123e4567-e89b-12d3-a456-426614174000'
      )
      expect(store.itemImages).toHaveLength(2)
      expect(store.itemImages[0].id).toBe('123e4567-e89b-12d3-a456-426614174001')
    })

    it('should handle delete error', async () => {
      const store = useItemImageStore()
      const error = new Error('Delete failed')

      mockItemImageApi.itemImageDestroy.mockRejectedValue(error)

      await expect(
        store.deleteItemImage('123e4567-e89b-12d3-a456-426614174000')
      ).rejects.toThrow('Delete failed')

      expect(store.error).toBe('Failed to delete item image')
    })
  })

  describe('detachImageFromItem', () => {
    it('should detach item image to available images successfully', async () => {
      const store = useItemImageStore()

      // Pre-populate via fetch
      mockItemImageApi.itemImagesIndex.mockResolvedValue({
        data: mockItemImages,
      })
      await store.fetchItemImages('item-001')

      mockItemImageApi.itemImageDetach.mockResolvedValue({
        data: {
          success: true,
          message: 'Image detached successfully',
          available_image_id: 'image-001',
        },
      })

      const result = await store.detachImageFromItem('123e4567-e89b-12d3-a456-426614174000')

      expect(mockItemImageApi.itemImageDetach).toHaveBeenCalledWith(
        '123e4567-e89b-12d3-a456-426614174000'
      )
      expect(result.available_image_id).toBe('image-001')
      // Should remove from local array
      expect(store.itemImages).toHaveLength(2)
      expect(store.itemImages[0].id).toBe('123e4567-e89b-12d3-a456-426614174001')
    })

    it('should handle detach error', async () => {
      const store = useItemImageStore()
      const error = new Error('Detach failed')

      mockItemImageApi.itemImageDetach.mockRejectedValue(error)

      await expect(
        store.detachImageFromItem('123e4567-e89b-12d3-a456-426614174000')
      ).rejects.toThrow('Detach failed')

      expect(store.error).toBe('Failed to detach image from item')
    })
  })

  describe('moveImageUp', () => {
    it('should move item image up successfully', async () => {
      const store = useItemImageStore()

      // Pre-populate via fetch
      mockItemImageApi.itemImagesIndex.mockResolvedValue({
        data: mockItemImages,
      })
      await store.fetchItemImages('item-001')

      const movedImage = { ...mockItemImages[1], display_order: 1 }
      mockItemImageApi.itemImageMoveUp.mockResolvedValue({
        data: { data: movedImage },
      })

      // Mock refetch
      mockItemImageApi.itemImagesIndex.mockResolvedValue({
        data: [movedImage, { ...mockItemImages[0], display_order: 2 }, mockItemImages[2]],
      })

      await store.moveImageUp('123e4567-e89b-12d3-a456-426614174001')

      expect(mockItemImageApi.itemImageMoveUp).toHaveBeenCalledWith(
        '123e4567-e89b-12d3-a456-426614174001'
      )
      // Should refetch the list
      expect(mockItemImageApi.itemImagesIndex).toHaveBeenCalled()
    })

    it('should handle moveUp error', async () => {
      const store = useItemImageStore()
      const error = new Error('Move failed')

      mockItemImageApi.itemImageMoveUp.mockRejectedValue(error)

      await expect(store.moveImageUp('123e4567-e89b-12d3-a456-426614174001')).rejects.toThrow(
        'Move failed'
      )

      expect(store.error).toBe('Failed to move image up')
    })
  })

  describe('moveImageDown', () => {
    it('should move item image down successfully', async () => {
      const store = useItemImageStore()

      // Pre-populate via fetch
      mockItemImageApi.itemImagesIndex.mockResolvedValue({
        data: mockItemImages,
      })
      await store.fetchItemImages('item-001')

      const movedImage = { ...mockItemImages[0], display_order: 2 }
      mockItemImageApi.itemImageMoveDown.mockResolvedValue({
        data: { data: movedImage },
      })

      // Mock refetch
      mockItemImageApi.itemImagesIndex.mockResolvedValue({
        data: [{ ...mockItemImages[1], display_order: 1 }, movedImage, mockItemImages[2]],
      })

      await store.moveImageDown('123e4567-e89b-12d3-a456-426614174000')

      expect(mockItemImageApi.itemImageMoveDown).toHaveBeenCalledWith(
        '123e4567-e89b-12d3-a456-426614174000'
      )
      // Should refetch the list
      expect(mockItemImageApi.itemImagesIndex).toHaveBeenCalled()
    })

    it('should handle moveDown error', async () => {
      const store = useItemImageStore()
      const error = new Error('Move failed')

      mockItemImageApi.itemImageMoveDown.mockRejectedValue(error)

      await expect(
        store.moveImageDown('123e4567-e89b-12d3-a456-426614174000')
      ).rejects.toThrow('Move failed')

      expect(store.error).toBe('Failed to move image down')
    })
  })

  describe('tightenOrdering', () => {
    it('should tighten ordering successfully', async () => {
      const store = useItemImageStore()

      mockItemImageApi.itemImageTightenOrdering.mockResolvedValue({
        data: {
          success: true,
          message: 'Image ordering tightened successfully',
        },
      })

      await store.tightenOrdering('123e4567-e89b-12d3-a456-426614174000')

      expect(mockItemImageApi.itemImageTightenOrdering).toHaveBeenCalledWith(
        '123e4567-e89b-12d3-a456-426614174000'
      )
    })

    it('should handle tightenOrdering error', async () => {
      const store = useItemImageStore()
      const error = new Error('Tighten failed')

      mockItemImageApi.itemImageTightenOrdering.mockRejectedValue(error)

      await expect(
        store.tightenOrdering('123e4567-e89b-12d3-a456-426614174000')
      ).rejects.toThrow('Tighten failed')

      expect(store.error).toBe('Failed to tighten image ordering')
    })
  })

  describe('reset', () => {
    it('should reset store to initial state', async () => {
      const store = useItemImageStore()

      // Set some state via actions
      mockItemImageApi.itemImagesIndex.mockResolvedValue({
        data: mockItemImages,
      })
      await store.fetchItemImages('item-001')

      mockItemImageApi.itemImageShow.mockResolvedValue({
        data: { data: mockItemImages[0] },
      })
      await store.fetchItemImage('123e4567-e89b-12d3-a456-426614174000')

      // Reset
      store.reset()

      // Verify initial state
      expect(store.itemImages).toEqual([])
      expect(store.currentItemImage).toBeNull()
      expect(store.loading).toBe(false)
      expect(store.error).toBeNull()
    })
  })
})
