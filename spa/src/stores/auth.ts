import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import {
  type AcquireTokenMobileAppAuthenticationRequest,
  type TokenAcquire202Response,
  type TokenAcquire202ResponseAvailableMethods,
  type TokenAcquire202ResponseMessage,
  type MarkdownPreview422ResponseSuccess,
  type VerifyTwoFactorMobileAppAuthenticationRequest,
  type VerifyTwoFactorMobileAppAuthenticationRequestMethodEnum,
  type TwoFactorStatusMobileAppAuthenticationRequest,
} from '@metanull/inventory-app-api-client'
import { useApiClient } from '@/composables/useApiClient'
import { usePermissionsStore } from './permissions'
import { clearAuthCookies } from '@/utils/cookies'

export interface TwoFactorChallenge {
  requires_two_factor: MarkdownPreview422ResponseSuccess
  available_methods: TokenAcquire202ResponseAvailableMethods
  primary_method: string | null
  message: TokenAcquire202ResponseMessage
}

export interface TwoFactorStatus {
  two_factor_enabled: MarkdownPreview422ResponseSuccess
  available_methods: TokenAcquire202ResponseAvailableMethods
  primary_method: string | null
  requires_two_factor: MarkdownPreview422ResponseSuccess
}

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem('auth_token'))
  const loading = ref(false)
  const error = ref<string | null>(null)

  // 2FA state
  const twoFactorChallenge = ref<TwoFactorChallenge | null>(null)
  const pendingCredentials = ref<{ email: string; password: string; device_name: string } | null>(
    null
  )

  const isAuthenticated = computed(() => !!token.value)
  const requires2FA = computed(() => !!twoFactorChallenge.value?.requires_two_factor)

  // Create API client instance with session-aware configuration
  const createApiClient = () => {
    return useApiClient().createMobileAppAuthenticationApi()
  }

  const login = async (email: string, password: string) => {
    loading.value = true
    error.value = null
    twoFactorChallenge.value = null

    // Clear any stale CSRF/XSRF cookies that might cause 419 errors
    // This is critical for mobile browsers with saved credentials
    clearAuthCookies()

    try {
      const apiClient = createApiClient()
      const tokenRequest: AcquireTokenMobileAppAuthenticationRequest = {
        email,
        password,
        device_name: 'Inventory Management UI',
        wipe_tokens: true,
      }

      const response = await apiClient.tokenAcquire(tokenRequest)

      // Check if response indicates 2FA required (status 202)
      if (response.status === 202) {
        // The 202 response contains the 2FA challenge info
        const challengeData = response.data as unknown as TokenAcquire202Response
        twoFactorChallenge.value = {
          requires_two_factor: challengeData.requires_two_factor,
          available_methods: challengeData.available_methods,
          primary_method: challengeData.primary_method,
          message: challengeData.message,
        }
        pendingCredentials.value = { email, password, device_name: 'Inventory Management UI' }
        return // Don't complete login, wait for 2FA
      }

      // Handle successful login response
      await handleLoginSuccess(response.data)
    } catch (err: unknown) {
      // Check if error response indicates 2FA required
      const errorResponse = (err as { response?: { status?: number; data?: unknown } })?.response
      if (errorResponse?.status === 202) {
        const challengeData = errorResponse.data as unknown as TokenAcquire202Response
        twoFactorChallenge.value = {
          requires_two_factor: challengeData.requires_two_factor,
          available_methods: challengeData.available_methods,
          primary_method: challengeData.primary_method,
          message: challengeData.message,
        }
        pendingCredentials.value = { email, password, device_name: 'Inventory Management UI' }
        return
      }

      const errorMessage =
        (errorResponse?.data as { message?: string })?.message ||
        (err as { message?: string })?.message ||
        'Login failed'
      error.value = errorMessage
      throw err
    } finally {
      loading.value = false
    }
  }

  const verifyTwoFactor = async (code: string, method?: 'totp' | 'email') => {
    if (!pendingCredentials.value) {
      throw new Error('No pending authentication')
    }

    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      const verifyRequest: VerifyTwoFactorMobileAppAuthenticationRequest = {
        email: pendingCredentials.value.email,
        password: pendingCredentials.value.password,
        device_name: pendingCredentials.value.device_name,
        code,
        method: method as VerifyTwoFactorMobileAppAuthenticationRequestMethodEnum,
      }

      const response = await apiClient.tokenVerifyTwoFactor(verifyRequest)

      await handleLoginSuccess(response.data)

      // Clear 2FA state
      twoFactorChallenge.value = null
      pendingCredentials.value = null
    } catch (err: unknown) {
      const errorMessage = (err as { message?: string })?.message || '2FA verification failed'
      error.value = errorMessage
      throw err
    } finally {
      loading.value = false
    }
  }

  const getTwoFactorStatus = async (email: string, password: string): Promise<TwoFactorStatus> => {
    try {
      const apiClient = createApiClient()
      const statusRequest: TwoFactorStatusMobileAppAuthenticationRequest = {
        email,
        password,
      }

      const response = await apiClient.tokenTwoFactorStatus(statusRequest)
      return response.data.data as TwoFactorStatus
    } catch (err: unknown) {
      const errorMessage = (err as { message?: string })?.message || 'Failed to get 2FA status'
      error.value = errorMessage
      throw err
    }
  }

  const handleLoginSuccess = async (responseData: unknown) => {
    let authToken: string

    const data = responseData as { token?: string } | string
    if (typeof data === 'object' && data.token) {
      // New format with token and user info
      authToken = data.token
    } else if (typeof data === 'string' && data.includes(';')) {
      // Legacy format: "tokenCount;actualToken"
      const [tokenCount, extractedToken] = data.split(';')
      if (!tokenCount || isNaN(Number(tokenCount))) {
        throw new Error('Invalid token count received from server')
      }
      if (!extractedToken) {
        throw new Error('Failed to extract authentication token')
      }
      authToken = extractedToken
    } else {
      // Fallback for different response formats
      authToken = data as string
    }

    token.value = authToken
    localStorage.setItem('auth_token', authToken)

    // Fetch user permissions after successful authentication
    const permissionsStore = usePermissionsStore()
    await permissionsStore.fetchPermissions()
  }

  const cancel2FA = () => {
    twoFactorChallenge.value = null
    pendingCredentials.value = null
    error.value = null
  }

  const logout = async () => {
    loading.value = true

    try {
      const apiClient = createApiClient()
      await apiClient.tokenWipe()
    } catch (err) {
      console.error('Logout error:', err)
    } finally {
      token.value = null
      localStorage.removeItem('auth_token')

      // Clear user permissions on logout
      const permissionsStore = usePermissionsStore()
      permissionsStore.clearPermissions()

      // Clear all pinia stores by resetting the entire pinia instance state
      // This ensures no stale data remains after logout
      try {
        const pinia = (
          this as unknown as { _p?: { _s?: Map<string, { $id: string; $reset?: () => void }> } }
        )._p
        if (pinia && pinia._s) {
          pinia._s.forEach((store: { $id: string; $reset?: () => void }) => {
            if (store.$id !== 'auth' && typeof store.$reset === 'function') {
              store.$reset()
            }
          })
        }
      } catch (e) {
        // Ignore errors in test environment where pinia instance may not be available
        console.warn('Could not reset pinia stores:', e)
      }

      loading.value = false
    }
  }

  const clearError = () => {
    error.value = null
  }

  /**
   * Validate the current session is still valid
   * Called after app rebuild or when session state needs verification
   *
   * @throws {Error} If session is invalid
   */
  const validateSession = async () => {
    if (!token.value) {
      throw new Error('No token found')
    }

    try {
      // Try to load permissions as a simple auth check
      const permissionsStore = usePermissionsStore()
      await permissionsStore.fetchPermissions()
    } catch (err) {
      // If permissions fetch fails, session is invalid
      console.error('[Auth] Session validation failed:', err)
      throw new Error('Session is invalid')
    }
  }

  return {
    token,
    loading,
    error,
    isAuthenticated,
    requires2FA,
    twoFactorChallenge,
    login,
    logout,
    clearError,
    verifyTwoFactor,
    getTwoFactorStatus,
    cancel2FA,
    validateSession,
  }
})
