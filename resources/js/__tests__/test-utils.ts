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

// Helper function to generate UUIDs for testing
const generateUuid = (): string => {
  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
    const r = (Math.random() * 16) | 0
    const v = c === 'x' ? r : (r & 0x3) | 0x8
    return v.toString(16)
  })
}

// Mock data factories with proper ID formats
export const createMockContext = (overrides: Partial<ContextResource> = {}): ContextResource => ({
  id: '123e4567-e89b-12d3-a456-426614174000',
  internal_name: 'Test Context',
  backward_compatibility: 'test',
  is_default: false,
  created_at: '2023-01-01T00:00:00Z',
  updated_at: '2023-01-01T00:00:00Z',
  ...overrides,
})

export const createMockProject = (overrides: Partial<ProjectResource> = {}): ProjectResource => ({
  id: '123e4567-e89b-12d3-a456-426614174001',
  internal_name: 'Test Project',
  display_name: 'Test Project Display',
  backward_compatibility: null,
  is_enabled: true,
  is_launched: false,
  launch_date: null,
  created_at: '2023-01-01T00:00:00Z',
  updated_at: '2023-01-01T00:00:00Z',
  ...overrides,
})

export const createMockLanguage = (
  overrides: Partial<LanguageResource> = {}
): LanguageResource => ({
  id: 'eng',
  internal_name: 'English',
  display_name: 'English',
  backward_compatibility: 'en',
  is_default: false,
  created_at: '2023-01-01T00:00:00Z',
  updated_at: '2023-01-01T00:00:00Z',
  ...overrides,
})

export const createMockCountry = (overrides: Partial<CountryResource> = {}): CountryResource => ({
  id: 'usa',
  internal_name: 'United States',
  display_name: 'United States of America',
  backward_compatibility: 'us',
  created_at: '2023-01-01T00:00:00Z',
  updated_at: '2023-01-01T00:00:00Z',
  ...overrides,
})

export const createMockItem = (overrides: Partial<ItemResource> = {}): ItemResource => ({
  id: '123e4567-e89b-12d3-a456-426614174002',
  internal_name: 'Test Item',
  display_name: 'Test Item Display',
  backward_compatibility: null,
  partner_id: '123e4567-e89b-12d3-a456-426614174003',
  created_at: '2023-01-01T00:00:00Z',
  updated_at: '2023-01-01T00:00:00Z',
  ...overrides,
})

export const createMockPartner = (overrides: Partial<PartnerResource> = {}): PartnerResource => ({
  id: '123e4567-e89b-12d3-a456-426614174003',
  internal_name: 'Test Partner',
  display_name: 'Test Partner Display',
  backward_compatibility: null,
  country_id: 'usa',
  created_at: '2023-01-01T00:00:00Z',
  updated_at: '2023-01-01T00:00:00Z',
  ...overrides,
})

export const createMockTag = (overrides: Partial<TagResource> = {}): TagResource => ({
  id: '123e4567-e89b-12d3-a456-426614174004',
  internal_name: 'Test Tag',
  display_name: 'Test Tag Display',
  backward_compatibility: null,
  created_at: '2023-01-01T00:00:00Z',
  updated_at: '2023-01-01T00:00:00Z',
  ...overrides,
})

export const createMockPicture = (overrides: Partial<PictureResource> = {}): PictureResource => ({
  id: '123e4567-e89b-12d3-a456-426614174005',
  internal_name: 'Test Picture',
  display_name: 'Test Picture Display',
  backward_compatibility: null,
  file_path: '/test/path/image.jpg',
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
