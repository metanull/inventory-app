import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { type TokenAcquireRequest } from '@metanull/inventory-app-api-client'
import { useApiClient } from '@/composables/useApiClient'

export interface TwoFactorChallenge {
  requires_two_factor: boolean
  available_methods: string[]
  primary_method: string
  message: string
}

export interface TwoFactorStatus {
  two_factor_enabled: boolean
  available_methods: string[]
  primary_method: string | null
  requires_two_factor: boolean
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

    try {
      const apiClient = createApiClient()
      const tokenRequest: TokenAcquireRequest = {
        email,
        password,
        device_name: 'Inventory Management UI',
        wipe_tokens: true,
      }

      const response = await apiClient.tokenAcquire(tokenRequest)

      // Check if response indicates 2FA required (status 202)
      if (response.status === 202) {
        twoFactorChallenge.value = response.data as unknown as TwoFactorChallenge
        pendingCredentials.value = { email, password, device_name: 'Inventory Management UI' }
        return // Don't complete login, wait for 2FA
      }

      // Handle successful login response
      handleLoginSuccess(response.data)
    } catch (err: unknown) {
      // Check if error response indicates 2FA required
      const errorResponse = (err as { response?: { status?: number; data?: unknown } })?.response
      if (errorResponse?.status === 202) {
        twoFactorChallenge.value = errorResponse.data as unknown as TwoFactorChallenge
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

  const verifyTwoFactor = async (code: string, method?: string) => {
    if (!pendingCredentials.value) {
      throw new Error('No pending authentication')
    }

    loading.value = true
    error.value = null

    try {
      const response = await globalThis.fetch('/api/mobile/verify-two-factor', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
        body: JSON.stringify({
          email: pendingCredentials.value.email,
          password: pendingCredentials.value.password,
          device_name: pendingCredentials.value.device_name,
          code,
          method,
        }),
      })

      if (!response.ok) {
        const errorData = await response.json()
        throw new Error(errorData.message || '2FA verification failed')
      }

      const data = await response.json()
      handleLoginSuccess(data)

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

  const requestEmailCode = async () => {
    if (!pendingCredentials.value) {
      throw new Error('No pending authentication')
    }

    loading.value = true
    error.value = null

    try {
      const response = await globalThis.fetch('/api/mobile/request-email-code', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
        body: JSON.stringify({
          email: pendingCredentials.value.email,
          password: pendingCredentials.value.password,
        }),
      })

      if (!response.ok) {
        const errorData = await response.json()
        throw new Error(errorData.message || 'Failed to send email code')
      }

      const data = await response.json()
      return data
    } catch (err: unknown) {
      const errorMessage = (err as { message?: string })?.message || 'Failed to send email code'
      error.value = errorMessage
      throw err
    } finally {
      loading.value = false
    }
  }

  const getTwoFactorStatus = async (email: string, password: string): Promise<TwoFactorStatus> => {
    try {
      const response = await globalThis.fetch('/api/mobile/two-factor-status', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
        body: JSON.stringify({ email, password }),
      })

      if (!response.ok) {
        const errorData = await response.json()
        throw new Error(errorData.message || 'Failed to get 2FA status')
      }

      return await response.json()
    } catch (err: unknown) {
      const errorMessage = (err as { message?: string })?.message || 'Failed to get 2FA status'
      error.value = errorMessage
      throw err
    }
  }

  const handleLoginSuccess = (responseData: unknown) => {
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
      loading.value = false
    }
  }

  const clearError = () => {
    error.value = null
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
    requestEmailCode,
    getTwoFactorStatus,
    cancel2FA,
  }
})
