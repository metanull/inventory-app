import { useContextStore } from '@/stores/context'
import { useCountryStore } from '@/stores/country'
import { useLanguageStore } from '@/stores/language'
import { useProjectStore } from '@/stores/project'
import { useCollectionStore } from '@/stores/collection'
import { useItemStore } from '@/stores/item'
import { usePartnerStore } from '@/stores/partner'
import { useCollectionImageStore } from '@/stores/collectionImage'
import { useItemImageStore } from '@/stores/itemImage'
import { useItemTranslationStore } from '@/stores/itemTranslation'
import { useAvailableImageStore } from '@/stores/availableImage'
import { useImageUploadStore } from '@/stores/imageUpload'
import { useErrorDisplayStore } from '@/stores/errorDisplay'
import { useLoadingOverlayStore } from '@/stores/loadingOverlay'

/**
 * Clear all Pinia stores and reload default data
 * This function:
 * 1. Clears all store data (except auth)
 * 2. Reloads default data for resource stores
 * 3. Reloads the current page
 */
export const clearCacheAndReload = async (reload = true): Promise<void> => {
  try {
    // Get store instances
    const contextStore = useContextStore()
    const countryStore = useCountryStore()
    const languageStore = useLanguageStore()
    const projectStore = useProjectStore()
    const collectionStore = useCollectionStore()
    const itemStore = useItemStore()
    const partnerStore = usePartnerStore()
    const collectionImageStore = useCollectionImageStore()
    const itemImageStore = useItemImageStore()
    const itemTranslationStore = useItemTranslationStore()
    const availableImageStore = useAvailableImageStore()
    const imageUploadStore = useImageUploadStore()
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

    // Clear collection and item stores
    collectionStore.collections = []
    collectionStore.clearCurrentCollection()
    itemStore.items = []
    itemStore.clearCurrentItem()
    partnerStore.partners = []
    partnerStore.clearCurrentPartner()

    // Clear image-related stores (these have reset methods)
    collectionImageStore.reset()
    itemImageStore.reset()
    itemTranslationStore.reset()

    // Clear available image and upload stores (no reset method)
    availableImageStore.availableImages = []
    imageUploadStore.uploads = []

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

    // Show success message and handle overlay/reload behaviour
    if (reload) {
      // Keep the loading overlay visible so the user sees a continuous action
      // until the page actually reloads. The reload will reset the UI.
      errorDisplayStore.addMessage('info', 'Cache cleared successfully!')

      // Reload the current page after a short delay
      setTimeout(() => {
        window.location.reload()
      }, 50)
    } else {
      // If no reload is requested, hide the overlay and show a success message
      loadingOverlayStore.hide()
      errorDisplayStore.addMessage('info', 'Cache cleared successfully!')
    }
  } catch (error) {
    // Hide loading overlay on error
    const loadingOverlayStore = useLoadingOverlayStore()
    const errorDisplayStore = useErrorDisplayStore()

    loadingOverlayStore.hide()
    errorDisplayStore.addMessage('error', 'Failed to clear cache. Please try again.')

    console.error('Failed to clear cache:', error)
  }
}
