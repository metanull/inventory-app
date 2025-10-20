/**
 * Cookie management utilities
 * 
 * This module provides functions to manage browser cookies, specifically for
 * clearing CSRF/XSRF tokens that may interfere with API authentication.
 */

/**
 * Delete a specific cookie by name
 * 
 * @param name - The name of the cookie to delete
 * @param path - The path of the cookie (default: '/')
 * @param domain - The domain of the cookie (optional)
 */
export function deleteCookie(name: string, path: string = '/', domain?: string): void {
  let cookieString = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=${path}`
  
  if (domain) {
    cookieString += `; domain=${domain}`
  }
  
  document.cookie = cookieString
}

/**
 * Clear XSRF/CSRF tokens from cookies
 * 
 * This prevents stale CSRF tokens (potentially saved by browser autofill)
 * from interfering with API token-based authentication.
 * 
 * This is particularly important for mobile/PWA scenarios where:
 * - Users save credentials in their browser
 * - Old XSRF cookies might be stored
 * - API uses Bearer token authentication (not CSRF-protected sessions)
 * 
 * Laravel uses 'XSRF-TOKEN' as the default CSRF cookie name.
 */
export function clearCsrfCookies(): void {
  // Laravel's default XSRF cookie name
  deleteCookie('XSRF-TOKEN', '/')
  
  // Clear for current domain and parent domain (if applicable)
  const hostname = window.location.hostname
  const parts = hostname.split('.')
  
  // If subdomain, also try parent domain
  if (parts.length > 2) {
    const parentDomain = parts.slice(-2).join('.')
    deleteCookie('XSRF-TOKEN', '/', `.${parentDomain}`)
  }
  
  // Also delete for full hostname
  deleteCookie('XSRF-TOKEN', '/', hostname)
}

/**
 * Clear Laravel session cookies
 * 
 * Removes session cookies that might interfere with token-based auth.
 * 
 * Laravel's session cookie name is based on APP_NAME:
 * Str::slug(env('APP_NAME', 'laravel'), '_').'_session'
 * 
 * For APP_NAME='Inventory-App', the cookie name is 'inventory_app_session'
 */
export function clearSessionCookies(): void {
  // Laravel's session cookie based on APP_NAME configuration
  // APP_NAME='Inventory-App' -> 'inventory_app_session'
  const sessionCookieName = 'inventory_app_session'
  
  deleteCookie(sessionCookieName, '/')
  
  // Clear for current domain
  const hostname = window.location.hostname
  deleteCookie(sessionCookieName, '/', hostname)
  
  // If subdomain, also try parent domain
  const parts = hostname.split('.')
  if (parts.length > 2) {
    const parentDomain = parts.slice(-2).join('.')
    deleteCookie(sessionCookieName, '/', `.${parentDomain}`)
  }
}

/**
 * Clear all authentication-related cookies
 * 
 * Use this on login to ensure clean state for token-based authentication
 */
export function clearAuthCookies(): void {
  clearCsrfCookies()
  clearSessionCookies()
}
