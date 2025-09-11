import axios, { type AxiosInstance, type AxiosResponse, type AxiosError } from 'axios'
import { useAuthStore } from '@/stores/auth'
import { ErrorHandler } from '@/utils/errorHandler'

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
    baseURL: window.location.origin,
    timeout: 30000,
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
    },
  })

  // Request interceptor: Attach current auth token
  instance.interceptors.request.use(
    config => {
      const authStore = useAuthStore()
      const token = authStore.token

      if (token) {
        config.headers.Authorization = `Bearer ${token}`
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
              if (originalRequest.headers && authStore.token) {
                originalRequest.headers.Authorization = `Bearer ${authStore.token}`
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
            if (originalRequest.headers) {
              originalRequest.headers.Authorization = `Bearer ${authStore.token}`
            }

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
  try {
    const authStore = useAuthStore()

    // For Sanctum tokens, we can't refresh them
    // Instead, we check if we still have a token
    // In a real scenario, you might want to make a lightweight API call
    // to verify the token is still valid

    if (!authStore.token) {
      return false
    }

    // TODO: Implement actual token refresh when using refresh tokens
    // For now, we assume the token is still valid if it exists
    // This will be caught by the 401 handling if it's actually expired

    return true
  } catch (error) {
    console.error('Token refresh failed:', error)
    return false
  }
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
