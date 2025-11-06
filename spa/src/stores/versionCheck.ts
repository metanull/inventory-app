import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

/**
 * Version information structure from backend
 */
export interface VersionInfo {
  app_version: string
  build_number: string
  unique_build_id: string
  api_client_version?: string
  commit_sha?: string
  build_timestamp?: string
}

/**
 * Store for managing version checks and detecting backend updates
 *
 * Features:
 * - Detects backend version changes via version.json
 * - Detects maintenance mode via down.lock file
 * - Singleton pattern for atomic checks (no parallel requests)
 * - Activity-based checking with debounce cooldown
 * - Automatic reload on version mismatch
 */
export const useVersionCheckStore = defineStore('versionCheck', () => {
  // Current version loaded at app start
  const currentVersion = ref<string | null>(null)

  // Latest version from server
  const latestVersion = ref<string | null>(null)

  // Maintenance mode detection
  const isInMaintenanceMode = ref<boolean>(false)

  // Version mismatch detected
  const isUpdateAvailable = ref<boolean>(false)

  // Check state
  const isChecking = ref<boolean>(false)
  const lastCheckTime = ref<number>(0)

  // Cooldown period in milliseconds (15 seconds)
  const COOLDOWN_PERIOD = 15000

  // Base URL for version endpoint
  const getBaseUrl = (): string => {
    if (
      typeof import.meta !== 'undefined' &&
      import.meta.env &&
      import.meta.env.VITE_API_BASE_URL
    ) {
      // Remove /api suffix to get base URL
      return import.meta.env.VITE_API_BASE_URL.replace(/\/api\/?$/, '')
    }
    return 'http://127.0.0.1:8000'
  }

  /**
   * Check if enough time has passed since last check (cooldown)
   */
  const canCheck = computed(() => {
    const now = Date.now()
    return now - lastCheckTime.value >= COOLDOWN_PERIOD
  })

  /**
   * Load initial version from server
   * Should be called once at app startup
   */
  const loadInitialVersion = async (): Promise<void> => {
    try {
      const baseUrl = getBaseUrl()
      const response = await fetch(`${baseUrl}/version.json?t=${Date.now()}`, {
        cache: 'no-cache',
      })

      if (response.ok) {
        const data = (await response.json()) as VersionInfo
        currentVersion.value = data.unique_build_id
        latestVersion.value = data.unique_build_id
        console.log('[VersionCheck] Initial version loaded:', currentVersion.value)
      } else {
        console.warn('[VersionCheck] Failed to load initial version:', response.status)
      }
    } catch (error) {
      console.warn('[VersionCheck] Error loading initial version:', error)
    }
  }

  /**
   * Check for version updates and maintenance mode
   * Uses singleton pattern to prevent parallel checks
   */
  const checkVersion = async (): Promise<void> => {
    // Skip if already checking or in cooldown period
    if (isChecking.value || !canCheck.value) {
      return
    }

    // Skip if no current version set (app not initialized)
    if (!currentVersion.value) {
      return
    }

    isChecking.value = true
    lastCheckTime.value = Date.now()

    try {
      const baseUrl = getBaseUrl()

      // Check for maintenance mode (down.lock)
      try {
        const downLockResponse = await fetch(`${baseUrl}/down.lock?t=${Date.now()}`, {
          cache: 'no-cache',
        })
        isInMaintenanceMode.value = downLockResponse.ok
      } catch {
        // down.lock file not found (not in maintenance mode)
        isInMaintenanceMode.value = false
      }

      // Check version
      const versionResponse = await fetch(`${baseUrl}/version.json?t=${Date.now()}`, {
        cache: 'no-cache',
      })

      if (versionResponse.ok) {
        const data = (await versionResponse.json()) as VersionInfo
        latestVersion.value = data.unique_build_id

        // Detect version mismatch
        if (currentVersion.value !== latestVersion.value) {
          console.log(
            '[VersionCheck] Version mismatch detected:',
            currentVersion.value,
            '→',
            latestVersion.value
          )
          isUpdateAvailable.value = true
        }
      }
    } catch (error) {
      console.warn('[VersionCheck] Check failed:', error)
    } finally {
      isChecking.value = false
    }
  }

  /**
   * Force reload the application
   * Clears all caches and performs a hard reload
   */
  const reloadApplication = (): void => {
    console.log('[VersionCheck] Reloading application...')

    // Clear all caches
    localStorage.clear()
    sessionStorage.clear()

    // Perform hard reload
    window.location.reload()
  }

  /**
   * Reset version check state
   * Used for testing
   */
  const reset = (): void => {
    currentVersion.value = null
    latestVersion.value = null
    isInMaintenanceMode.value = false
    isUpdateAvailable.value = false
    isChecking.value = false
    lastCheckTime.value = 0
  }

  return {
    // State
    currentVersion,
    latestVersion,
    isInMaintenanceMode,
    isUpdateAvailable,
    isChecking,
    canCheck,

    // Actions
    loadInitialVersion,
    checkVersion,
    reloadApplication,
    reset,
  }
})
