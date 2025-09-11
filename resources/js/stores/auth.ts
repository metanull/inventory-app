import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { type TokenAcquireRequest } from '@metanull/inventory-app-api-client'
import { useApiClient } from '@/composables/useApiClient'

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem('auth_token'))
  const loading = ref(false)
  const error = ref<string | null>(null)

  const isAuthenticated = computed(() => !!token.value)

  // Create API client instance with session-aware configuration
  const createApiClient = () => {
    return useApiClient().createMobileAppAuthenticationApi()
  }

  const login = async (email: string, password: string) => {
    loading.value = true
    error.value = null

    try {
      const apiClient = createApiClient()
      const tokenRequest: TokenAcquireRequest = {
        email,
        password,
        device_name: 'Inventory Management UI',
        wipe_tokens: true,
      }

      const response = await apiClient.tokenAcquire(tokenRequest)

      // Parse the response format: "tokenCount;actualToken"
      const responseData = response.data
      let authToken: string

      if (typeof responseData === 'string' && responseData.includes(';')) {
        const [tokenCount, extractedToken] = responseData.split(';')
        //const errorDisplayStore = useErrorDisplayStore()
        //errorDisplayStore.addMessage(
        //  'info',
        //  `Authentication successful. Active tokens: ${tokenCount}`
        //)
        if (!tokenCount || isNaN(Number(tokenCount))) {
          throw new Error('Invalid token count received from server')
        }
        if (!extractedToken) {
          throw new Error('Failed to extract authentication token')
        }
        authToken = extractedToken
      } else {
        // Fallback for different response formats
        authToken = responseData as string
      }

      token.value = authToken
      localStorage.setItem('auth_token', authToken)
    } catch (err: unknown) {
      const errorMessage =
        (err as { response?: { data?: { message?: string } } })?.response?.data?.message ||
        'Login failed'
      error.value = errorMessage
      throw err
    } finally {
      loading.value = false
    }
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
    login,
    logout,
    clearError,
  }
})
