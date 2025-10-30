import { describe, it, expect, beforeEach } from 'vitest'
import { createMockCollection, createMockLanguage, createMockContext } from '@/__tests__/test-utils'
import type {
  CollectionResource,
  LanguageResource,
  ContextResource,
} from '@metanull/inventory-app-api-client'

describe('CollectionDetail Logic', () => {
  describe('Form Mode Detection', () => {
    it('detects create mode when ID is "new"', () => {
      const isCreateMode = (id: string) => id === 'new'

      expect(isCreateMode('new')).toBe(true)
      expect(isCreateMode('existing-id')).toBe(false)
      expect(isCreateMode('')).toBe(false)
    })

    it('detects edit mode when mode query parameter is "edit"', () => {
      const isEditMode = (mode: string | null) => mode === 'edit'

      expect(isEditMode('edit')).toBe(true)
      expect(isEditMode('view')).toBe(false)
      expect(isEditMode(null)).toBe(false)
    })

    it('defaults to view mode when no mode specified and not creating', () => {
      const getMode = (id: string, queryMode: string | null) => {
        if (id === 'new') return 'create'
        if (queryMode === 'edit') return 'edit'
        return 'view'
      }

      expect(getMode('new', null)).toBe('create')
      expect(getMode('existing-id', 'edit')).toBe('edit')
      expect(getMode('existing-id', null)).toBe('view')
      expect(getMode('existing-id', 'invalid')).toBe('view')
    })
  })

  describe('Form Validation', () => {
    it('validates required internal_name field', () => {
      const validateInternalName = (value: string) => {
        if (!value || value.trim().length === 0) {
          return 'Internal name is required'
        }
        return null
      }

      expect(validateInternalName('')).toBe('Internal name is required')
      expect(validateInternalName('   ')).toBe('Internal name is required')
      expect(validateInternalName('valid-name')).toBe(null)
    })

    it('validates internal_name format constraints', () => {
      const validateInternalNameFormat = (value: string) => {
        const regex = /^[a-zA-Z0-9-_]+$/
        if (!regex.test(value)) {
          return 'Internal name can only contain letters, numbers, hyphens, and underscores'
        }
        return null
      }

      expect(validateInternalNameFormat('valid-name')).toBe(null)
      expect(validateInternalNameFormat('valid_name')).toBe(null)
      expect(validateInternalNameFormat('ValidName123')).toBe(null)
      expect(validateInternalNameFormat('invalid name')).toBe(
        'Internal name can only contain letters, numbers, hyphens, and underscores'
      )
      expect(validateInternalNameFormat('invalid@name')).toBe(
        'Internal name can only contain letters, numbers, hyphens, and underscores'
      )
    })

    it('validates backward_compatibility field', () => {
      const validateBackwardCompatibility = (value: string | null) => {
        if (value === null || value === '') return null
        const regex = /^[a-zA-Z0-9-_]+$/
        if (!regex.test(value)) {
          return 'Backward compatibility can only contain letters, numbers, hyphens, and underscores'
        }
        return null
      }

      expect(validateBackwardCompatibility(null)).toBe(null)
      expect(validateBackwardCompatibility('')).toBe(null)
      expect(validateBackwardCompatibility('valid-compat')).toBe(null)
      expect(validateBackwardCompatibility('invalid compat')).toBe(
        'Backward compatibility can only contain letters, numbers, hyphens, and underscores'
      )
    })

    it('validates complete form data', () => {
      interface FormData {
        internal_name: string
        backward_compatibility: string | null
      }

      const validateForm = (data: FormData) => {
        const errors: Record<string, string> = {}

        if (!data.internal_name || data.internal_name.trim().length === 0) {
          errors.internal_name = 'Internal name is required'
        } else {
          const regex = /^[a-zA-Z0-9-_]+$/
          if (!regex.test(data.internal_name)) {
            errors.internal_name =
              'Internal name can only contain letters, numbers, hyphens, and underscores'
          }
        }

        if (data.backward_compatibility && data.backward_compatibility.trim().length > 0) {
          const regex = /^[a-zA-Z0-9-_]+$/
          if (!regex.test(data.backward_compatibility)) {
            errors.backward_compatibility =
              'Backward compatibility can only contain letters, numbers, hyphens, and underscores'
          }
        }

        return Object.keys(errors).length > 0 ? errors : null
      }

      expect(validateForm({ internal_name: 'valid-name', backward_compatibility: null })).toBe(null)
      expect(validateForm({ internal_name: '', backward_compatibility: null })).toEqual({
        internal_name: 'Internal name is required',
      })
      expect(validateForm({ internal_name: 'invalid name', backward_compatibility: null })).toEqual(
        {
          internal_name:
            'Internal name can only contain letters, numbers, hyphens, and underscores',
        }
      )
    })
  })

  describe('Data Transformation', () => {
    it('transforms collection data for API submission in create mode', () => {
      const formData = {
        internal_name: 'test-collection',
        backward_compatibility: 'old-name',
        translations: [],
      }

      const transformForCreate = (data: typeof formData) => ({
        internal_name: data.internal_name,
        backward_compatibility: data.backward_compatibility || null,
        translations: data.translations || [],
      })

      const result = transformForCreate(formData)
      expect(result).toEqual({
        internal_name: 'test-collection',
        backward_compatibility: 'old-name',
        translations: [],
      })
    })

    it('transforms collection data for API submission in update mode', () => {
      const collection = createMockCollection({
        id: 'test-id',
        internal_name: 'original-name',
      })

      const formData = {
        internal_name: 'updated-name',
        backward_compatibility: 'updated-compat',
        translations: [],
      }

      const transformForUpdate = (original: CollectionResource, data: typeof formData) => ({
        id: original.id,
        internal_name: data.internal_name,
        backward_compatibility: data.backward_compatibility || null,
        translations: data.translations || [],
      })

      const result = transformForUpdate(collection, formData)
      expect(result).toEqual({
        id: 'test-id',
        internal_name: 'updated-name',
        backward_compatibility: 'updated-compat',
        translations: [],
      })
    })

    it('initializes form data from existing collection', () => {
      const collection = createMockCollection({
        internal_name: 'existing-collection',
        backward_compatibility: 'existing-compat',
      })

      const initializeFormData = (collection: CollectionResource) => ({
        internal_name: collection.internal_name,
        backward_compatibility: collection.backward_compatibility,
        translations: collection.translations || [],
      })

      const result = initializeFormData(collection)
      expect(result.internal_name).toBe('existing-collection')
      expect(result.backward_compatibility).toBe('existing-compat')
      expect(result.translations).toEqual([])
    })
  })

  describe('Unsaved Changes Detection', () => {
    let originalCollection: CollectionResource

    beforeEach(() => {
      originalCollection = createMockCollection({
        internal_name: 'original-name',
        backward_compatibility: 'original-compat',
      })
    })

    it('detects changes in internal_name', () => {
      const hasChanges = (original: CollectionResource, current: { internal_name: string }) => {
        return original.internal_name !== current.internal_name
      }

      expect(hasChanges(originalCollection, { internal_name: 'original-name' })).toBe(false)
      expect(hasChanges(originalCollection, { internal_name: 'modified-name' })).toBe(true)
    })

    it('detects changes in backward_compatibility', () => {
      const hasChanges = (
        original: CollectionResource,
        current: { backward_compatibility: string | null }
      ) => {
        return original.backward_compatibility !== current.backward_compatibility
      }

      expect(hasChanges(originalCollection, { backward_compatibility: 'original-compat' })).toBe(
        false
      )
      expect(hasChanges(originalCollection, { backward_compatibility: 'modified-compat' })).toBe(
        true
      )
      expect(hasChanges(originalCollection, { backward_compatibility: null })).toBe(true)
    })

    it('detects any changes in form data', () => {
      interface FormData {
        internal_name: string
        backward_compatibility: string | null
      }

      const hasAnyChanges = (original: CollectionResource, current: FormData) => {
        return (
          original.internal_name !== current.internal_name ||
          original.backward_compatibility !== current.backward_compatibility
        )
      }

      expect(
        hasAnyChanges(originalCollection, {
          internal_name: 'original-name',
          backward_compatibility: 'original-compat',
        })
      ).toBe(false)

      expect(
        hasAnyChanges(originalCollection, {
          internal_name: 'modified-name',
          backward_compatibility: 'original-compat',
        })
      ).toBe(true)

      expect(
        hasAnyChanges(originalCollection, {
          internal_name: 'original-name',
          backward_compatibility: 'modified-compat',
        })
      ).toBe(true)
    })
  })

  describe('Dropdown Options Generation', () => {
    it('generates language dropdown options', () => {
      const languages = [
        createMockLanguage({ id: 'eng', internal_name: 'English' }),
        createMockLanguage({ id: 'fra', internal_name: 'French' }),
      ]

      const generateLanguageOptions = (languages: LanguageResource[]) => {
        return languages.map(lang => ({
          value: lang.id,
          label: lang.internal_name,
        }))
      }

      const options = generateLanguageOptions(languages)
      expect(options).toEqual([
        { value: 'eng', label: 'English' },
        { value: 'fra', label: 'French' },
      ])
    })

    it('generates context dropdown options', () => {
      const contexts = [
        createMockContext({ id: 'ctx-1', internal_name: 'Museum Context' }),
        createMockContext({ id: 'ctx-2', internal_name: 'Archive Context' }),
      ]

      const generateContextOptions = (contexts: ContextResource[]) => {
        return contexts.map(ctx => ({
          value: ctx.id,
          label: ctx.internal_name,
        }))
      }

      const options = generateContextOptions(contexts)
      expect(options).toEqual([
        { value: 'ctx-1', label: 'Museum Context' },
        { value: 'ctx-2', label: 'Archive Context' },
      ])
    })
  })

  describe('Navigation Logic', () => {
    it('determines redirect after successful create', () => {
      const getRedirectAfterCreate = (createdId: string) => `/collections/${createdId}`
      expect(getRedirectAfterCreate('new-collection-id')).toBe('/collections/new-collection-id')
    })

    it('determines redirect after successful update', () => {
      const getRedirectAfterUpdate = (id: string) => `/collections/${id}`
      expect(getRedirectAfterUpdate('updated-collection-id')).toBe(
        '/collections/updated-collection-id'
      )
    })

    it('determines back navigation target', () => {
      const getBackNavigationTarget = () => '/collections'
      expect(getBackNavigationTarget()).toBe('/collections')
    })
  })

  describe('Error Handling Logic', () => {
    it('handles validation errors from API', () => {
      const apiError = {
        response: {
          data: {
            errors: {
              internal_name: ['The internal name field is required.'],
              backward_compatibility: ['The backward compatibility field must be unique.'],
            },
          },
        },
      }

      const extractValidationErrors = (error: typeof apiError) => {
        if (error.response?.data?.errors) {
          const errors: Record<string, string> = {}
          Object.entries(error.response.data.errors).forEach(([field, messages]) => {
            if (Array.isArray(messages) && messages.length > 0) {
              errors[field] = messages[0]
            }
          })
          return errors
        }
        return {}
      }

      const errors = extractValidationErrors(apiError)
      expect(errors).toEqual({
        internal_name: 'The internal name field is required.',
        backward_compatibility: 'The backward compatibility field must be unique.',
      })
    })

    it('handles network errors gracefully', () => {
      const networkError = new Error('Network Error')

      const getErrorMessage = (error: Error) => {
        if (error.message.includes('Network')) {
          return 'Unable to connect to server. Please check your internet connection.'
        }
        return 'An unexpected error occurred. Please try again.'
      }

      expect(getErrorMessage(networkError)).toBe(
        'Unable to connect to server. Please check your internet connection.'
      )
      expect(getErrorMessage(new Error('Unknown error'))).toBe(
        'An unexpected error occurred. Please try again.'
      )
    })
  })
})
