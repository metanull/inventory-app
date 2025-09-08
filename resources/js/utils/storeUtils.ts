import { useContextStore } from '@/stores/context'
import { useCountryStore } from '@/stores/country'
import { useLanguageStore } from '@/stores/language'
import { useProjectStore } from '@/stores/project'
import { useErrorDisplayStore } from '@/stores/errorDisplay'
import { useLoadingOverlayStore } from '@/stores/loadingOverlay'

/**
 * Clear all Pinia stores and reload default data
 * This function:
 * 1. Clears all store data (except auth)
 * 2. Reloads default data for resource stores
 * 3. Reloads the current page
 */
export const clearCacheAndReload = async (): Promise<void> => {
  try {
    // Get store instances
    const contextStore = useContextStore()
    const countryStore = useCountryStore()
    const languageStore = useLanguageStore()
    const projectStore = useProjectStore()
    const errorDisplayStore = useErrorDisplayStore()
    const loadingOverlayStore = useLoadingOverlayStore()

    // Show loading overlay
    loadingOverlayStore.show('Clearing cache...')

    // Clear all store data (preserve auth)
    // Clear resource stores
    contextStore.contexts = []
    contextStore.clearCurrentContext()
    contextStore.clearError()

    // Clear countries store
    countryStore.clearCurrentCountry()
    countryStore.clearError()

    // Clear languages store
    languageStore.clearCurrentLanguage()
    languageStore.clearError()

    // Clear projects store
    projectStore.clearCurrentProject()
    if (projectStore.clearProjects) {
      projectStore.clearProjects()
    }
    projectStore.clearError()

    // Clear UI stores
    errorDisplayStore.clearAll()

    // Reload default data for main resource stores
    const reloadPromises = []

    // Load contexts (includes default contexts)
    reloadPromises.push(contextStore.fetchContexts())

    // Load languages (includes default language)
    reloadPromises.push(languageStore.fetchLanguages())

    // Wait for all default data to load
    await Promise.all(reloadPromises)

    // Hide loading overlay
    loadingOverlayStore.hide()

    // Show success message
    errorDisplayStore.addMessage('info', 'Cache cleared successfully!')

    // Reload the current page after a short delay
    setTimeout(() => {
      window.location.reload()
    }, 500)
  } catch (error) {
    // Hide loading overlay on error
    const loadingOverlayStore = useLoadingOverlayStore()
    const errorDisplayStore = useErrorDisplayStore()

    loadingOverlayStore.hide()
    errorDisplayStore.addMessage('error', 'Failed to clear cache. Please try again.')

    console.error('Failed to clear cache:', error)
  }
}
