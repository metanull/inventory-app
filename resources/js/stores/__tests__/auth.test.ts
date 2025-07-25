import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'

// Mock the API client before importing the store
vi.mock('@metanull/inventory-app-api-client', () => ({
  MobileAppAuthenticationApi: vi.fn().mockImplementation(() => ({
    tokenAcquire: vi.fn(),
    tokenWipe: vi.fn(),
  })),
  Configuration: vi.fn(),
}))

import { useAuthStore } from '../auth'

// Mock localStorage
const localStorageMock = {
  getItem: vi.fn(),
  setItem: vi.fn(),
  removeItem: vi.fn(),
  clear: vi.fn(),
}
Object.defineProperty(window, 'localStorage', {
  value: localStorageMock,
})

describe('Auth Store', () => {
  let authStore: ReturnType<typeof useAuthStore>

  beforeEach(async () => {
    // Clear all mocks first
    vi.clearAllMocks()
    localStorageMock.getItem.mockReturnValue(null)

    // Setup Pinia
    setActivePinia(createPinia())

    // Import the store after setting up mocks
    const { useAuthStore } = await import('../auth')
    authStore = useAuthStore()
  })

  afterEach(() => {
    vi.resetAllMocks()
  })

  describe('Initial State', () => {
    it('should have correct initial state', () => {
      expect(authStore.token).toBeNull()
      expect(authStore.loading).toBe(false)
      expect(authStore.error).toBeNull()
      expect(authStore.isAuthenticated).toBe(false)
    })

    it('should be authenticated when token exists', () => {
      authStore.token = 'test-token'
      expect(authStore.isAuthenticated).toBe(true)
    })
  })

  describe('Login', () => {
    it('should login successfully', async () => {
      const mockToken = 'new-auth-token'
      const mockResponse = { data: `1;${mockToken}` }

      const { MobileAppAuthenticationApi } = (await vi.importMock(
        '@metanull/inventory-app-api-client'
      )) as {
        MobileAppAuthenticationApi: ReturnType<typeof vi.fn>
      }

      const mockApiInstance = {
        tokenAcquire: vi.fn().mockResolvedValueOnce(mockResponse),
        tokenWipe: vi.fn(),
      }

      MobileAppAuthenticationApi.mockReturnValue(mockApiInstance)

      await authStore.login('test@example.com', 'password')

      expect(mockApiInstance.tokenAcquire).toHaveBeenCalledWith({
        email: 'test@example.com',
        password: 'password',
        device_name: 'Inventory Management UI',
        wipe_tokens: true,
      })
      expect(authStore.token).toBe(mockToken)
      expect(authStore.loading).toBe(false)
      expect(authStore.error).toBeNull()
      expect(localStorageMock.setItem).toHaveBeenCalledWith('auth_token', mockToken)
    })

    it('should handle login error', async () => {
      const mockError = {
        response: {
          data: {
            message: 'Invalid credentials',
          },
        },
      }

      const { MobileAppAuthenticationApi } = (await vi.importMock(
        '@metanull/inventory-app-api-client'
      )) as {
        MobileAppAuthenticationApi: ReturnType<typeof vi.fn>
      }

      const mockApiInstance = {
        tokenAcquire: vi.fn().mockRejectedValueOnce(mockError),
        tokenWipe: vi.fn(),
      }

      MobileAppAuthenticationApi.mockReturnValue(mockApiInstance)

      await expect(authStore.login('test@example.com', 'wrong-password')).rejects.toThrow()

      expect(authStore.token).toBeNull()
      expect(authStore.loading).toBe(false)
      expect(authStore.error).toBe('Invalid credentials')
      expect(localStorageMock.setItem).not.toHaveBeenCalled()
    })
  })

  describe('Logout', () => {
    beforeEach(() => {
      authStore.token = 'existing-token'
    })

    it('should logout successfully', async () => {
      const { MobileAppAuthenticationApi } = (await vi.importMock(
        '@metanull/inventory-app-api-client'
      )) as {
        MobileAppAuthenticationApi: ReturnType<typeof vi.fn>
      }

      const mockApiInstance = {
        tokenAcquire: vi.fn(),
        tokenWipe: vi.fn().mockResolvedValueOnce(undefined),
      }

      MobileAppAuthenticationApi.mockReturnValue(mockApiInstance)

      await authStore.logout()

      expect(mockApiInstance.tokenWipe).toHaveBeenCalled()
      expect(authStore.token).toBeNull()
      expect(authStore.error).toBeNull()
      expect(localStorageMock.removeItem).toHaveBeenCalledWith('auth_token')
    })
  })

  describe('Error Management', () => {
    it('should clear error', () => {
      authStore.error = 'Some error'
      authStore.clearError()
      expect(authStore.error).toBeNull()
    })
  })
})
