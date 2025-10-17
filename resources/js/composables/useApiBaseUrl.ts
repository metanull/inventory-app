/**
 * Composable for getting the API base URL
 *
 * This ensures consistency across the application by using the same
 * base URL configuration as the API client.
 *
 * IMPORTANT: This should ONLY be used for constructing URLs to API endpoints,
 * never for /web routes or other non-API URLs.
 */
export const useApiBaseUrl = (): string => {
  // Support both Vite (import.meta.env) and Node (process.env) for baseURL
  if (typeof import.meta !== 'undefined' && import.meta.env && import.meta.env.VITE_API_BASE_URL) {
    return import.meta.env.VITE_API_BASE_URL as string
    // eslint-disable-next-line no-undef
  } else if (typeof process !== 'undefined' && process.env && process.env.VITE_API_BASE_URL) {
    // eslint-disable-next-line no-undef
    return process.env.VITE_API_BASE_URL as string
  } else {
    return 'http://127.0.0.1:8000/api'
  }
}
