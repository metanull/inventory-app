import axios, { type AxiosInstance, type AxiosResponse, type AxiosError } from 'axios'
import { useAuthStore } from '@/stores/auth'
import { useVersionCheckStore } from '@/stores/versionCheck'
import { ErrorHandler } from '@/utils/errorHandler'
import { DEFAULT_PER_PAGE } from '@/utils/apiQueryParams'

/**
 * Session-aware axios instance for handling authentication and session expiration
 *
 * Features:
 * - Automatic token attachment from auth store
 * - Graceful 401 error handling with session renewal attempts
 * - Integration with existing ErrorHandler infrastructure
 * - Prevention of duplicate renewal requests
 * - UX-friendly error handling for session expiration
 */

// Track ongoing token renewal to prevent duplicate requests
let isRenewing = false
let failedQueue: Array<{
  resolve: (value?: unknown) => void
  reject: (reason?: unknown) => void
}> = []

/**
 * Process the queue of failed requests after token renewal
 */
const processQueue = (error: AxiosError | null, token: string | null = null) => {
  failedQueue.forEach(({ resolve, reject }) => {
    if (error) {
      reject(error)
    } else {
      resolve(token)
    }
  })

  failedQueue = []
}

/**
 * Create a session-aware axios instance with interceptors
 */
export const createSessionAwareAxios = (): AxiosInstance => {
  const instance = axios.create({
    // Don't set baseURL here - let the API client configuration handle it
    // This prevents conflicts with the generated client's basePath logic
    timeout: 30000,
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
    },
  })

  // Request interceptor: Attach current auth token and check version
  instance.interceptors.request.use(
    config => {
      const authStore = useAuthStore()
      const token = authStore.token

      if (token) {
        if (!config.headers) {
          // Initialize headers if missing (can be undefined in tests or custom calls)
          ;(config as { headers: Record<string, unknown> }).headers = {}
        }
        ;(config.headers as Record<string, unknown>).Authorization = `Bearer ${token}`
      }

      // Ensure default per_page for list endpoints unless explicitly provided
      // Supports both { params: { ... } } calls and direct URL queries
      const params = (config.params ?? {}) as Record<string, unknown>
      const hasPerPage = Object.prototype.hasOwnProperty.call(params, 'per_page')
      if (!hasPerPage) {
        // Only inject for GET requests targeting index-like endpoints
        const method = (config.method || 'get').toLowerCase()
        const url = (config.url || '').toLowerCase()
        const isList =
          (method === 'get' &&
            /\/(index|list|items|partners|countries|languages|contexts|collections|projects)?$/i.test(
              url
            )) ||
          method === 'get'
        if (isList) {
          ;(config.params as Record<string, unknown>) = { ...params, per_page: DEFAULT_PER_PAGE }
        }
      }

      // Activity-based version checking: piggyback on API requests
      // Check version on every request with debouncing (cooldown handled by store)
      const versionStore = useVersionCheckStore()
      if (versionStore.canCheck) {
        // Non-blocking check - don't await
        versionStore.checkVersion().catch(err => {
          console.warn('[SessionAxios] Version check failed:', err)
        })
      }

      return config
    },
    error => {
      return Promise.reject(error)
    }
  )

  // Response interceptor: Handle 401 errors and session renewal
  instance.interceptors.response.use(
    (response: AxiosResponse) => {
      return response
    },
    async (error: AxiosError) => {
      const originalRequest = error.config

      // Check if this is a 401 error and we haven't already tried to renew
      if (error.response?.status === 401 && originalRequest && !originalRequest._retry) {
        // If we're already renewing, queue this request
        if (isRenewing) {
          return new Promise((resolve, reject) => {
            failedQueue.push({ resolve, reject })
          })
            .then(() => {
              // Retry the original request with updated token
              const authStore = useAuthStore()
              if (authStore.token) {
                if (!originalRequest.headers) {
                  ;(originalRequest as { headers: Record<string, unknown> }).headers = {}
                }
                ;(originalRequest.headers as Record<string, unknown>).Authorization =
                  `Bearer ${authStore.token}`
              }
              return instance(originalRequest)
            })
            .catch(err => {
              return Promise.reject(err)
            })
        }

        // Mark as retry attempt and start renewal process
        originalRequest._retry = true
        isRenewing = true

        try {
          const authStore = useAuthStore()

          // Attempt to refresh the token
          const refreshed = await attemptTokenRefresh()

          if (refreshed && authStore.token) {
            // Update the original request with new token
            if (!originalRequest.headers) {
              ;(originalRequest as { headers: Record<string, unknown> }).headers = {}
            }
            ;(originalRequest.headers as Record<string, unknown>).Authorization =
              `Bearer ${authStore.token}`

            // Process queued requests
            processQueue(null, authStore.token)

            // Retry the original request
            return instance(originalRequest)
          } else {
            // Refresh failed, handle as authentication error
            throw new Error('Token refresh failed')
          }
        } catch (refreshError) {
          // Process queue with error
          processQueue(error, null)

          // Handle authentication error through existing infrastructure
          console.error('Token refresh error:', refreshError)
          ErrorHandler.handleError(error, 'Session expired - automatic renewal failed')

          return Promise.reject(error)
        } finally {
          isRenewing = false
        }
      }

      // For non-401 errors or if retry already attempted, handle normally
      ErrorHandler.handleError(error, 'API request failed')
      return Promise.reject(error)
    }
  )

  return instance
}

/**
 * Attempt to refresh the authentication token
 *
 * Note: Current auth implementation uses Sanctum tokens which don't have
 * refresh capability. This method serves as a placeholder for future
 * refresh token implementation or validates current token status.
 */
const attemptTokenRefresh = async (): Promise<boolean> => {
  // Sanctum Personal Access Tokens do not support refresh; immediately signal failure
  // This ensures a clean 401 flow to the ErrorHandler without retry loops
  return false
}

// Singleton instance for consistent use across the application
let sessionAwareAxiosInstance: AxiosInstance | null = null

/**
 * Get the singleton session-aware axios instance
 */
export const getSessionAwareAxios = (): AxiosInstance => {
  if (!sessionAwareAxiosInstance) {
    sessionAwareAxiosInstance = createSessionAwareAxios()
  }
  return sessionAwareAxiosInstance
}

// Type augmentation for axios config to track retry attempts
declare module 'axios' {
  interface AxiosRequestConfig {
    _retry?: boolean
  }
}
