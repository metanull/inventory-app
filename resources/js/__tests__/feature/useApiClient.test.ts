import { describe, it, expect, vi, beforeEach } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useApiClient } from '@/composables/useApiClient'
import { useAuthStore } from '@/stores/auth'

// Mock the API client module
vi.mock('@metanull/inventory-app-api-client', () => ({
  Configuration: vi.fn().mockImplementation(config => ({
    basePath: config?.basePath || 'http://127.0.0.1:8000/api',
    accessToken: config?.accessToken || null,
  })),
  LanguageApi: vi.fn(),
  CollectionApi: vi.fn(),
  ContextApi: vi.fn(),
  CountryApi: vi.fn(),
  DetailApi: vi.fn(),
  ExhibitionApi: vi.fn(),
  GalleryApi: vi.fn(),
  ImageUploadApi: vi.fn(),
  InfoApi: vi.fn(),
  ItemApi: vi.fn(),
  LocationApi: vi.fn(),
  MarkdownApi: vi.fn(),
  MobileAppAuthenticationApi: vi.fn(),
  PartnerApi: vi.fn(),
  PictureApi: vi.fn(),
  ProjectApi: vi.fn(),
  ProvinceApi: vi.fn(),
  TagApi: vi.fn(),
  ThemeApi: vi.fn(),
  DetailTranslationApi: vi.fn(),
  ExhibitionTranslationApi: vi.fn(),
  ItemTranslationApi: vi.fn(),
  LocationTranslationApi: vi.fn(),
  PartnerTranslationApi: vi.fn(),
  PictureTranslationApi: vi.fn(),
  ProvinceTranslationApi: vi.fn(),
  ThemeTranslationApi: vi.fn(),
  AvailableImageApi: vi.fn(),
}))

// Mock the session-aware axios
vi.mock('@/utils/sessionAwareAxios', () => ({
  getSessionAwareAxios: vi.fn(() => ({
    get: vi.fn(),
    post: vi.fn(),
    put: vi.fn(),
    delete: vi.fn(),
  })),
}))

describe('useApiClient composable', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  it('should create API client instances with session-aware axios', () => {
    const apiClient = useApiClient()

    // Test that all factory methods are available
    expect(typeof apiClient.createAvailableImageApi).toBe('function')
    expect(typeof apiClient.createCollectionApi).toBe('function')
    expect(typeof apiClient.createContextApi).toBe('function')
    expect(typeof apiClient.createCountryApi).toBe('function')
    expect(typeof apiClient.createImageUploadApi).toBe('function')
    expect(typeof apiClient.createInfoApi).toBe('function')
    expect(typeof apiClient.createItemApi).toBe('function')
    expect(typeof apiClient.createItemImageApi).toBe('function')
    expect(typeof apiClient.createItemTranslationApi).toBe('function')
    expect(typeof apiClient.createLanguageApi).toBe('function')
    expect(typeof apiClient.createLocationApi).toBe('function')
    expect(typeof apiClient.createLocationTranslationApi).toBe('function')
    expect(typeof apiClient.createMarkdownApi).toBe('function')
    expect(typeof apiClient.createMobileAppAuthenticationApi).toBe('function')
    expect(typeof apiClient.createPartnerApi).toBe('function')
    expect(typeof apiClient.createProjectApi).toBe('function')
    expect(typeof apiClient.createProvinceApi).toBe('function')
    expect(typeof apiClient.createProvinceTranslationApi).toBe('function')
    expect(typeof apiClient.createTagApi).toBe('function')
    expect(typeof apiClient.createThemeApi).toBe('function')
    expect(typeof apiClient.createThemeTranslationApi).toBe('function')

    // Test translation API methods (only existing translation APIs)
    expect(typeof apiClient.createItemTranslationApi).toBe('function')
    expect(typeof apiClient.createLocationTranslationApi).toBe('function')
    expect(typeof apiClient.createPartnerTranslationApi).toBe('function')
    expect(typeof apiClient.createProvinceTranslationApi).toBe('function')
    expect(typeof apiClient.createThemeTranslationApi).toBe('function')

    // Test other APIs
    expect(typeof apiClient.createAvailableImageApi).toBe('function')
  })

  it('should provide access to configuration and session axios', () => {
    const apiClient = useApiClient()

    expect(apiClient.configuration).toBeDefined()
    expect(apiClient.sessionAxios).toBeDefined()
  })

  it('should create API instances that can be called', () => {
    const apiClient = useApiClient()

    // Test that we can create instances without errors
    expect(() => apiClient.createLanguageApi()).not.toThrow()
    expect(() => apiClient.createCollectionApi()).not.toThrow()
  })

  it('should use configuration that reflects auth store state', () => {
    const authStore = useAuthStore()
    const apiClient = useApiClient()

    // Initially no token
    expect(authStore.token).toBeNull()

    // Configuration should be accessible
    expect(apiClient.configuration).toBeDefined()
    expect(apiClient.configuration.basePath).toBeDefined()
  })
})
