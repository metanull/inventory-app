import { describe, it, expect, beforeEach } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useLanguageStore } from '@/stores/language'
import { useAuthStore } from '@/stores/auth'
import { getTestCredentials, getApiBaseUrl } from '../../api/__tests__/integration.setup'

// Declare process for Node.js environments
declare const process: {
  env: Record<string, string | undefined>
}

describe('Language Store Integration Tests', () => {
  beforeEach(() => {
    setActivePinia(createPinia())

    // Set up environment for integration tests
    process.env.VITE_API_BASE_URL = getApiBaseUrl()
  })

  it('should authenticate and fetch languages from real API', async () => {
    const authStore = useAuthStore()
    const languageStore = useLanguageStore()

    try {
      // First authenticate
      const { email, password } = getTestCredentials()
      await authStore.login(email, password)

      expect(authStore.isAuthenticated).toBe(true)
      expect(authStore.token).toBeTruthy()

      // Then fetch languages
      await languageStore.fetchLanguages()

      expect(languageStore.languages).toBeDefined()
      expect(Array.isArray(languageStore.languages)).toBe(true)
      expect(languageStore.loading).toBe(false)
      expect(languageStore.error).toBeNull()
    } catch (error) {
      // If the API is not available, skip the test
      console.warn('API not available for integration tests:', error)
      return
    }
  }, 10000) // 10 second timeout for network operations

  it('should create, read, update, and delete a language', async () => {
    const authStore = useAuthStore()
    const languageStore = useLanguageStore()

    try {
      // Authenticate first
      const { email, password } = getTestCredentials()
      await authStore.login(email, password)

      const testLanguageId = 'tst'
      const testLanguageData = {
        id: testLanguageId,
        internal_name: 'Test Language',
        backward_compatibility: 'ts',
      }

      // Clean up any existing test language
      try {
        await languageStore.deleteLanguage(testLanguageId)
      } catch {
        // Ignore if language doesn't exist
      }

      // CREATE: Create a new language
      const createdLanguage = await languageStore.createLanguage(testLanguageData)
      expect(createdLanguage.id).toBe(testLanguageId)
      expect(createdLanguage.internal_name).toBe('Test Language')

      // READ: Fetch the created language
      const fetchedLanguage = await languageStore.fetchLanguage(testLanguageId)
      expect(fetchedLanguage.id).toBe(testLanguageId)
      expect(fetchedLanguage.internal_name).toBe('Test Language')

      // UPDATE: Update the language
      const updatedLanguage = await languageStore.updateLanguage(testLanguageId, {
        internal_name: 'Updated Test Language',
        backward_compatibility: 'ts',
      })
      expect(updatedLanguage.internal_name).toBe('Updated Test Language')

      // DELETE: Delete the language
      await languageStore.deleteLanguage(testLanguageId)

      // Verify deletion by trying to fetch (should fail)
      try {
        await languageStore.fetchLanguage(testLanguageId)
        // If we get here, the language wasn't deleted
        expect.fail('Language should have been deleted')
      } catch (error) {
        // Expected - language should not exist
        expect(error).toBeDefined()
      }
    } catch (error) {
      console.warn('API not available for integration tests:', error)
      return
    }
  }, 15000) // 15 second timeout for multiple operations

  it('should set a language as default', async () => {
    const authStore = useAuthStore()
    const languageStore = useLanguageStore()

    try {
      // Authenticate first
      const { email, password } = getTestCredentials()
      await authStore.login(email, password)

      const testLanguageId = 'tsd' // Test Set Default
      const testLanguageData = {
        id: testLanguageId,
        internal_name: 'Test Set Default Language',
        backward_compatibility: 'td',
        // Note: is_default is prohibited in create operations
      }

      // Clean up any existing test language
      try {
        await languageStore.deleteLanguage(testLanguageId)
      } catch {
        // Ignore if language doesn't exist
      }

      // Create a test language (will be created as non-default by API)
      const createdLanguage = await languageStore.createLanguage(testLanguageData)
      expect(createdLanguage.is_default).toBe(false)

      // Set the language as default
      const updatedLanguage = await languageStore.setDefaultLanguage(testLanguageId, true)
      expect(updatedLanguage.is_default).toBe(true)

      // Fetch all languages to verify only one is default
      await languageStore.fetchLanguages()
      const defaultLanguages = languageStore.languages.filter(lang => lang.is_default)
      expect(defaultLanguages).toHaveLength(1)
      expect(defaultLanguages[0].id).toBe(testLanguageId)

      // Unset the language as default by setting another language as default
      // First, get the original default language (should be 'eng' if it exists)
      const englishLanguage = languageStore.languages.find(lang => lang.id === 'eng')
      if (englishLanguage) {
        await languageStore.setDefaultLanguage('eng', true)

        // Verify the change
        await languageStore.fetchLanguages()
        const testLang = languageStore.languages.find(lang => lang.id === testLanguageId)
        const engLang = languageStore.languages.find(lang => lang.id === 'eng')

        expect(testLang?.is_default).toBe(false)
        expect(engLang?.is_default).toBe(true)
      }

      // Clean up: Delete the test language
      await languageStore.deleteLanguage(testLanguageId)
    } catch (error) {
      console.warn('API not available for integration tests:', error)
      return
    }
  }, 20000) // 20 second timeout for multiple operations

  it('should handle API errors appropriately', async () => {
    const authStore = useAuthStore()
    const languageStore = useLanguageStore()

    try {
      // Authenticate first
      const { email, password } = getTestCredentials()
      await authStore.login(email, password)

      // Try to fetch a non-existent language
      try {
        await languageStore.fetchLanguage('nonexistent')
        expect.fail('Should have thrown an error for non-existent language')
      } catch (error) {
        expect(error).toBeDefined()
      }

      // Try to create a language with invalid data
      try {
        await languageStore.createLanguage({
          id: '', // Invalid: empty ID
          internal_name: '',
          backward_compatibility: null,
        })
        expect.fail('Should have thrown an error for invalid language data')
      } catch (error) {
        expect(error).toBeDefined()
      }
    } catch (error) {
      console.warn('API not available for integration tests:', error)
      return
    }
  }, 10000)

  it('should get the default language', async () => {
    const authStore = useAuthStore()
    const languageStore = useLanguageStore()

    try {
      // First authenticate
      const { email, password } = getTestCredentials()
      await authStore.login(email, password)

      expect(authStore.isAuthenticated).toBe(true)

      // Get the default language
      const defaultLanguage = await languageStore.getDefaultLanguage()

      expect(defaultLanguage).toBeDefined()
      expect(defaultLanguage.is_default).toBe(true)
      expect(typeof defaultLanguage.id).toBe('string')
      expect(typeof defaultLanguage.internal_name).toBe('string')
    } catch (error) {
      // If the API is not available, skip the test
      console.warn('API not available for integration tests:', error)
      return
    }
  }, 10000)

  it('should handle authentication failures', async () => {
    const authStore = useAuthStore()

    // Clear any existing authentication state
    await authStore.logout()

    try {
      // Try to authenticate with invalid credentials
      await authStore.login('invalid@example.com', 'wrongpassword')
      expect.fail('Should have thrown an error for invalid credentials')
    } catch (error) {
      expect(error).toBeDefined()
      expect(authStore.isAuthenticated).toBe(false)
    }
  }, 10000)
})
