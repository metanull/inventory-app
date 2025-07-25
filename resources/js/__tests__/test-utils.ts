import { vi } from 'vitest'
import type {
  ItemResource,
  PartnerResource,
  ProjectResource,
  TagResource,
  PictureResource,
  CountryResource,
  LanguageResource,
  ContextResource,
  ApiResponse,
} from '@metanull/inventory-app-api-client'
import type { RouteRecordRaw } from 'vue-router'

// Mock data factories
export const createMockItem = (overrides: Partial<ItemResource> = {}): ItemResource => ({
  id: '1',
  internal_name: 'Test Item',
  backward_compatibility: null,
  type: 'object',
  created_at: '2023-01-01T00:00:00Z',
  updated_at: '2023-01-01T00:00:00Z',
  ...overrides,
})

export const createMockPartner = (overrides: Partial<PartnerResource> = {}): PartnerResource => ({
  id: '1',
  internal_name: 'Test Partner',
  backward_compatibility: null,
  type: 'museum',
  created_at: '2023-01-01T00:00:00Z',
  updated_at: '2023-01-01T00:00:00Z',
  ...overrides,
})

export const createMockProject = (overrides: Partial<ProjectResource> = {}): ProjectResource => ({
  id: '1',
  internal_name: 'Test Project',
  backward_compatibility: null,
  launch_date: '2023-06-01',
  is_launched: true,
  is_enabled: true,
  created_at: '2023-01-01T00:00:00Z',
  updated_at: '2023-01-01T00:00:00Z',
  ...overrides,
})

export const createMockTag = (overrides: Partial<TagResource> = {}): TagResource => ({
  id: '1',
  internal_name: 'Test Tag',
  backward_compatibility: null,
  description: 'Test tag description',
  created_at: '2023-01-01T00:00:00Z',
  updated_at: '2023-01-01T00:00:00Z',
  ...overrides,
})

export const createMockPicture = (overrides: Partial<PictureResource> = {}): PictureResource => ({
  id: '1',
  internal_name: 'Test Picture',
  backward_compatibility: null,
  path: '/images/test.jpg',
  copyright_text: 'Test Copyright',
  copyright_url: 'https://example.com',
  upload_name: 'test.jpg',
  upload_extension: 'jpg',
  upload_mime_type: 'image/jpeg',
  upload_size: 12345,
  created_at: '2023-01-01T00:00:00Z',
  updated_at: '2023-01-01T00:00:00Z',
  ...overrides,
})

export const createMockCountry = (overrides: Partial<CountryResource> = {}): CountryResource => ({
  id: '1',
  internal_name: 'Test Country',
  backward_compatibility: null,
  created_at: '2023-01-01T00:00:00Z',
  updated_at: '2023-01-01T00:00:00Z',
  ...overrides,
})

export const createMockLanguage = (
  overrides: Partial<LanguageResource> = {}
): LanguageResource => ({
  id: '1',
  internal_name: 'Test Language',
  backward_compatibility: null,
  is_default: false,
  created_at: '2023-01-01T00:00:00Z',
  updated_at: '2023-01-01T00:00:00Z',
  ...overrides,
})

export const createMockContext = (overrides: Partial<ContextResource> = {}): ContextResource => ({
  id: '1',
  internal_name: 'Test Context',
  backward_compatibility: null,
  is_default: false,
  created_at: '2023-01-01T00:00:00Z',
  updated_at: '2023-01-01T00:00:00Z',
  ...overrides,
})

// API Response helpers
export const createApiResponse = <T>(data: T): ApiResponse<T> => ({
  data,
})

export const createAxiosResponse = <T>(data: T, status = 200, statusText = 'OK') => ({
  data,
  status,
  statusText,
  headers: {},
  config: {} as Record<string, unknown>,
})

// Mock localStorage
export const createLocalStorageMock = () => ({
  getItem: vi.fn(),
  setItem: vi.fn(),
  removeItem: vi.fn(),
  clear: vi.fn(),
})

// Mock axios instance
export const createMockAxiosInstance = () => ({
  get: vi.fn(),
  post: vi.fn(),
  put: vi.fn(),
  delete: vi.fn(),
  interceptors: {
    request: {
      use: vi.fn(),
    },
    response: {
      use: vi.fn(),
    },
  },
})

// Common test setup utilities
export const setupApiClientMocks = () => {
  const mockAxiosInstance = createMockAxiosInstance()
  const localStorageMock = createLocalStorageMock()

  Object.defineProperty(window, 'localStorage', {
    value: localStorageMock,
  })

  return {
    mockAxiosInstance,
    localStorageMock,
  }
}

// Error response helpers
export const createApiError = (message: string, status = 400) => ({
  response: {
    data: {
      message,
    },
    status,
    statusText: status === 400 ? 'Bad Request' : 'Error',
  },
})

export const createNetworkError = (message = 'Network Error') => new Error(message)

// Form data helpers for testing
export const createMockFormData = (fields: Record<string, string | Blob> = {}) => {
  const formData = new FormData()
  Object.entries(fields).forEach(([key, value]) => {
    if (value instanceof Blob) {
      formData.append(key, value, 'test-file')
    } else {
      formData.append(key, value)
    }
  })
  return formData
}

// Mock file for upload testing
export const createMockFile = (_name = 'test.jpg', type = 'image/jpeg', content = 'test content') =>
  new Blob([content], { type })

// Router helpers for component testing
export const createTestRouter = async (routes: RouteRecordRaw[] = []) => {
  const { createRouter, createWebHistory } = await import('vue-router')
  return createRouter({
    history: createWebHistory(),
    routes: [
      { path: '/', component: { template: '<div>Home</div>' } },
      { path: '/login', component: { template: '<div>Login</div>' } },
      ...routes,
    ],
  })
}
