import { computed } from 'vue'
import { Configuration } from '@metanull/inventory-app-api-client'
import { getSessionAwareAxios } from '@/utils/sessionAwareAxios'

// Import all API classes from the generated client
import {
  AvailableImageApi,
  CollectionApi,
  CollectionImageApi,
  ContextApi,
  CountryApi,
  ImageUploadApi,
  InfoApi,
  ItemApi,
  ItemImageApi,
  ItemTranslationApi,
  LanguageApi,
  LocationApi,
  LocationTranslationApi,
  MarkdownApi,
  MobileAppAuthenticationApi,
  PartnerApi,
  ProjectApi,
  ProvinceApi,
  ProvinceTranslationApi,
  TagApi,
  ThemeApi,
  ThemeTranslationApi,
  UserPermissionsApi,
} from '@metanull/inventory-app-api-client'

/**
 * Composable for creating session-aware API clients
 *
 * Features:
 * - Centralized API client creation with consistent configuration
 * - Automatic session-aware axios injection for all API clients
 * - Dynamic token management from auth store
 * - Full TypeScript support for all generated API classes
 * - Zero maintenance overhead for future client regeneration
 */
export const useApiClient = () => {
  // Get the session-aware axios instance
  const sessionAxios = getSessionAwareAxios()

  // Reactive configuration that updates when auth state changes
  const configuration = computed(() => {
    // Support both Vite (import.meta.env) and Node (process.env) for baseURL
    let baseURL: string
    if (
      typeof import.meta !== 'undefined' &&
      import.meta.env &&
      import.meta.env.VITE_API_BASE_URL
    ) {
      baseURL = import.meta.env.VITE_API_BASE_URL
    } else if (typeof process !== 'undefined' && process.env && process.env.VITE_API_BASE_URL) {
      baseURL = process.env.VITE_API_BASE_URL
    } else {
      baseURL = 'http://127.0.0.1:8000/api'
    }

    // Do not set accessToken here – session-aware axios will inject the
    // Authorization header dynamically from the auth store for every request.
    return new Configuration({ basePath: baseURL })
  })

  // Factory methods for all API clients
  // Each method creates a new instance with session-aware axios injected

  const createAvailableImageApi = () =>
    new AvailableImageApi(configuration.value, configuration.value.basePath, sessionAxios)

  const createCollectionApi = () =>
    new CollectionApi(configuration.value, configuration.value.basePath, sessionAxios)

  const createCollectionImageApi = () =>
    new CollectionImageApi(configuration.value, configuration.value.basePath, sessionAxios)

  const createContextApi = () =>
    new ContextApi(configuration.value, configuration.value.basePath, sessionAxios)

  const createCountryApi = () =>
    new CountryApi(configuration.value, configuration.value.basePath, sessionAxios)

  const createImageUploadApi = () =>
    new ImageUploadApi(configuration.value, configuration.value.basePath, sessionAxios)

  const createInfoApi = () =>
    new InfoApi(configuration.value, configuration.value.basePath, sessionAxios)

  const createItemApi = () =>
    new ItemApi(configuration.value, configuration.value.basePath, sessionAxios)

  const createItemImageApi = () =>
    new ItemImageApi(configuration.value, configuration.value.basePath, sessionAxios)

  const createItemTranslationApi = () =>
    new ItemTranslationApi(configuration.value, configuration.value.basePath, sessionAxios)

  const createLanguageApi = () =>
    new LanguageApi(configuration.value, configuration.value.basePath, sessionAxios)

  const createLocationApi = () =>
    new LocationApi(configuration.value, configuration.value.basePath, sessionAxios)

  const createLocationTranslationApi = () =>
    new LocationTranslationApi(configuration.value, configuration.value.basePath, sessionAxios)

  const createMarkdownApi = () =>
    new MarkdownApi(configuration.value, configuration.value.basePath, sessionAxios)

  const createMobileAppAuthenticationApi = () =>
    new MobileAppAuthenticationApi(configuration.value, configuration.value.basePath, sessionAxios)

  const createPartnerApi = () =>
    new PartnerApi(configuration.value, configuration.value.basePath, sessionAxios)

  const createProjectApi = () =>
    new ProjectApi(configuration.value, configuration.value.basePath, sessionAxios)

  const createProvinceApi = () =>
    new ProvinceApi(configuration.value, configuration.value.basePath, sessionAxios)

  const createProvinceTranslationApi = () =>
    new ProvinceTranslationApi(configuration.value, configuration.value.basePath, sessionAxios)

  const createTagApi = () =>
    new TagApi(configuration.value, configuration.value.basePath, sessionAxios)

  const createThemeApi = () =>
    new ThemeApi(configuration.value, configuration.value.basePath, sessionAxios)

  const createThemeTranslationApi = () =>
    new ThemeTranslationApi(configuration.value, configuration.value.basePath, sessionAxios)

  const createUserPermissionsApi = () =>
    new UserPermissionsApi(configuration.value, configuration.value.basePath, sessionAxios)

  // Return all factory methods
  return {
    // Core configuration (for advanced use cases)
    get configuration() {
      return configuration.value
    },
    sessionAxios,

    // API factory methods
    createAvailableImageApi,
    createCollectionApi,
    createCollectionImageApi,
    createContextApi,
    createCountryApi,
    createImageUploadApi,
    createInfoApi,
    createItemApi,
    createItemImageApi,
    createItemTranslationApi,
    createLanguageApi,
    createLocationApi,
    createLocationTranslationApi,
    createMarkdownApi,
    createMobileAppAuthenticationApi,
    createPartnerApi,
    createProjectApi,
    createProvinceApi,
    createProvinceTranslationApi,
    createTagApi,
    createThemeApi,
    createThemeTranslationApi,
    createUserPermissionsApi,
  }
}

// Declare process for Node.js environments
declare const process: {
  env: Record<string, string | undefined>
}
