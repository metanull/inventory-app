import { describe, it, expect, beforeEach, vi } from 'vitest'
import { createMockCollection } from '@/__tests__/test-utils'
import type { CollectionResource } from '@metanull/inventory-app-api-client'

describe('Collections Logic', () => {
  describe('Search Functionality', () => {
    let collections: CollectionResource[]

    beforeEach(() => {
      collections = [
        createMockCollection({ id: '1', internal_name: 'ancient-artifacts' }),
        createMockCollection({ id: '2', internal_name: 'modern-art' }),
        createMockCollection({ id: '3', internal_name: 'historical-documents' }),
      ]
    })

    it('filters collections by internal_name', () => {
      const searchQuery = 'ancient'
      const filtered = collections.filter(collection =>
        collection.internal_name.toLowerCase().includes(searchQuery.toLowerCase())
      )

      expect(filtered).toHaveLength(1)
      expect(filtered[0].internal_name).toBe('ancient-artifacts')
    })

    it('returns empty array when no matches found', () => {
      const searchQuery = 'nonexistent'
      const filtered = collections.filter(collection =>
        collection.internal_name.toLowerCase().includes(searchQuery.toLowerCase())
      )

      expect(filtered).toHaveLength(0)
    })

    it('is case insensitive', () => {
      const searchQuery = 'MODERN'
      const filtered = collections.filter(collection =>
        collection.internal_name.toLowerCase().includes(searchQuery.toLowerCase())
      )

      expect(filtered).toHaveLength(1)
      expect(filtered[0].internal_name).toBe('modern-art')
    })

    it('handles partial matches', () => {
      const searchQuery = 'art'
      const filtered = collections.filter(collection =>
        collection.internal_name.toLowerCase().includes(searchQuery.toLowerCase())
      )

      expect(filtered).toHaveLength(2) // ancient-artifacts and modern-art
    })
  })

  describe('Sorting Functionality', () => {
    let collections: CollectionResource[]

    beforeEach(() => {
      collections = [
        createMockCollection({ id: '1', internal_name: 'z-collection' }),
        createMockCollection({ id: '2', internal_name: 'a-collection' }),
        createMockCollection({ id: '3', internal_name: 'm-collection' }),
      ]
    })

    it('sorts collections by internal_name ascending', () => {
      const sorted = [...collections].sort((a, b) => a.internal_name.localeCompare(b.internal_name))

      expect(sorted[0].internal_name).toBe('a-collection')
      expect(sorted[1].internal_name).toBe('m-collection')
      expect(sorted[2].internal_name).toBe('z-collection')
    })

    it('sorts collections by internal_name descending', () => {
      const sorted = [...collections].sort((a, b) => b.internal_name.localeCompare(a.internal_name))

      expect(sorted[0].internal_name).toBe('z-collection')
      expect(sorted[1].internal_name).toBe('m-collection')
      expect(sorted[2].internal_name).toBe('a-collection')
    })

    it('sorts collections by created_at ascending', () => {
      const collectionsWithDates = [
        createMockCollection({ id: '1', created_at: '2023-03-01T00:00:00Z' }),
        createMockCollection({ id: '2', created_at: '2023-01-01T00:00:00Z' }),
        createMockCollection({ id: '3', created_at: '2023-02-01T00:00:00Z' }),
      ]

      const sorted = [...collectionsWithDates].sort(
        (a, b) => new Date(a.created_at).getTime() - new Date(b.created_at).getTime()
      )

      expect(sorted[0].created_at).toBe('2023-01-01T00:00:00Z')
      expect(sorted[1].created_at).toBe('2023-02-01T00:00:00Z')
      expect(sorted[2].created_at).toBe('2023-03-01T00:00:00Z')
    })

    it('sorts collections by updated_at descending', () => {
      const collectionsWithDates = [
        createMockCollection({ id: '1', updated_at: '2023-01-01T00:00:00Z' }),
        createMockCollection({ id: '2', updated_at: '2023-03-01T00:00:00Z' }),
        createMockCollection({ id: '3', updated_at: '2023-02-01T00:00:00Z' }),
      ]

      const sorted = [...collectionsWithDates].sort(
        (a, b) => new Date(b.updated_at).getTime() - new Date(a.updated_at).getTime()
      )

      expect(sorted[0].updated_at).toBe('2023-03-01T00:00:00Z')
      expect(sorted[1].updated_at).toBe('2023-02-01T00:00:00Z')
      expect(sorted[2].updated_at).toBe('2023-01-01T00:00:00Z')
    })
  })

  describe('Validation Logic', () => {
    it('validates required internal_name field', () => {
      const isValid = (internalName: string) => {
        return Boolean(internalName && internalName.trim().length > 0)
      }

      expect(isValid('')).toBe(false)
      expect(isValid('   ')).toBe(false)
      expect(isValid('valid-name')).toBe(true)
    })

    it('validates internal_name format', () => {
      const isValidFormat = (internalName: string) => {
        // Basic validation: alphanumeric, hyphens, underscores only
        const regex = /^[a-zA-Z0-9-_]+$/
        return regex.test(internalName)
      }

      expect(isValidFormat('valid-name')).toBe(true)
      expect(isValidFormat('valid_name')).toBe(true)
      expect(isValidFormat('ValidName123')).toBe(true)
      expect(isValidFormat('invalid name')).toBe(false) // spaces not allowed
      expect(isValidFormat('invalid@name')).toBe(false) // special chars not allowed
    })

    it('validates backward_compatibility field format', () => {
      const isValidBackwardCompatibility = (value: string | null) => {
        if (value === null || value === '') return true
        // Should be alphanumeric with hyphens/underscores if provided
        const regex = /^[a-zA-Z0-9-_]+$/
        return regex.test(value)
      }

      expect(isValidBackwardCompatibility(null)).toBe(true)
      expect(isValidBackwardCompatibility('')).toBe(true)
      expect(isValidBackwardCompatibility('valid-compat')).toBe(true)
      expect(isValidBackwardCompatibility('invalid compat')).toBe(false)
    })
  })

  describe('URL Generation', () => {
    it('generates correct view URL for collection', () => {
      const generateViewUrl = (id: string) => `/collections/${id}`
      expect(generateViewUrl('test-id')).toBe('/collections/test-id')
    })

    it('generates correct edit URL for collection', () => {
      const generateEditUrl = (id: string) => `/collections/${id}?mode=edit`
      expect(generateEditUrl('test-id')).toBe('/collections/test-id?mode=edit')
    })

    it('generates correct new collection URL', () => {
      const generateNewUrl = () => '/collections/new'
      expect(generateNewUrl()).toBe('/collections/new')
    })
  })

  describe('Collection State Management', () => {
    it('tracks unsaved changes correctly', () => {
      const originalCollection = createMockCollection({ internal_name: 'original-name' })

      const hasUnsavedChanges = (
        original: CollectionResource,
        current: Partial<CollectionResource>
      ) => {
        return Object.keys(current).some(key => {
          const originalValue = original[key as keyof CollectionResource]
          const currentValue = current[key as keyof CollectionResource]
          return originalValue !== currentValue
        })
      }

      expect(hasUnsavedChanges(originalCollection, { internal_name: 'original-name' })).toBe(false)
      expect(hasUnsavedChanges(originalCollection, { internal_name: 'modified-name' })).toBe(true)
    })

    it('handles collection deletion confirmation', () => {
      const shouldConfirmDeletion = (collectionName: string) => {
        return window.confirm(`Are you sure you want to delete the collection "${collectionName}"?`)
      }

      // Mock window.confirm
      const originalConfirm = window.confirm
      window.confirm = vi.fn().mockReturnValue(true)

      expect(shouldConfirmDeletion('test-collection')).toBe(true)
      expect(window.confirm).toHaveBeenCalledWith(
        'Are you sure you want to delete the collection "test-collection"?'
      )

      // Restore original confirm
      window.confirm = originalConfirm
    })
  })
})
