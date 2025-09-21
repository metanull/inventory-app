import { describe, it, expect, vi, beforeEach } from 'vitest'
import type { AxiosInstance, AxiosRequestConfig } from 'axios'
import { createPinia, setActivePinia } from 'pinia'
import axios from 'axios'
import { createSessionAwareAxios, getSessionAwareAxios } from '@/utils/sessionAwareAxios'
import { DEFAULT_PER_PAGE } from '@/utils/apiQueryParams'

// Mock the auth store
vi.mock('@/stores/auth', () => ({
  useAuthStore: vi.fn(() => ({
    token: 'test-token',
  })),
}))

// Mock the error handler
vi.mock('@/utils/errorHandler', () => ({
  ErrorHandler: {
    handleError: vi.fn(),
  },
}))

// Mock axios
vi.mock('axios', () => ({
  default: {
    create: vi.fn(() => ({
      interceptors: {
        request: {
          use: vi.fn(),
        },
        response: {
          use: vi.fn(),
        },
      },
    })),
  },
}))

describe('sessionAwareAxios', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('should create axios instance with correct configuration', () => {
    const instance = createSessionAwareAxios()

    expect(axios.create).toHaveBeenCalledWith({
      // No baseURL set - let API client configuration handle it
      timeout: 30000,
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
    })

    expect(instance).toBeDefined()
  })

  it('should configure request and response interceptors', () => {
    const mockAxiosInstance = {
      interceptors: {
        request: {
          use: vi.fn(),
        },
        response: {
          use: vi.fn(),
        },
      },
    }

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    vi.mocked(axios.create).mockReturnValue(mockAxiosInstance as any)

    createSessionAwareAxios()

    // Verify that interceptors were configured
    expect(mockAxiosInstance.interceptors.request.use).toHaveBeenCalledWith(
      expect.any(Function),
      expect.any(Function)
    )

    expect(mockAxiosInstance.interceptors.response.use).toHaveBeenCalledWith(
      expect.any(Function),
      expect.any(Function)
    )
  })

  it('should return singleton instance', () => {
    const instance1 = getSessionAwareAxios()
    const instance2 = getSessionAwareAxios()

    expect(instance1).toBe(instance2)
  })

  it('should configure interceptors correctly', () => {
    const mockUse = vi.fn()
    const mockAxiosInstance = {
      interceptors: {
        request: { use: mockUse },
        response: { use: mockUse },
      },
    }

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    vi.mocked(axios.create).mockReturnValue(mockAxiosInstance as any)

    createSessionAwareAxios()

    // Should have called interceptor setup twice (request + response)
    expect(mockUse).toHaveBeenCalledTimes(2)

    // Check that functions were passed to interceptors
    const [requestSuccess, requestError] = mockUse.mock.calls[0]
    const [responseSuccess, responseError] = mockUse.mock.calls[1]

    expect(typeof requestSuccess).toBe('function')
    expect(typeof requestError).toBe('function')
    expect(typeof responseSuccess).toBe('function')
    expect(typeof responseError).toBe('function')
  })

  it('injects default per_page when missing on GET list calls', async () => {
    const requestHandlers: Array<(cfg: AxiosRequestConfig) => AxiosRequestConfig> = []
    const mockAxiosInstance = {
      interceptors: {
        request: {
          use: vi.fn((success: (cfg: AxiosRequestConfig) => AxiosRequestConfig) =>
            requestHandlers.push(success)
          ),
        },
        response: { use: vi.fn() },
      },
    }
    vi.mocked(axios.create).mockReturnValue(mockAxiosInstance as unknown as AxiosInstance)

    createSessionAwareAxios()

    // Simulate a GET request config without per_page but with store metadata
    const cfg: AxiosRequestConfig & { __storeMethod?: { needsPagination?: boolean } } = {
      method: 'get',
      url: '/api/items',
      params: { page: 3 },
      __storeMethod: { needsPagination: true },
    }
    const processed = requestHandlers[0](cfg)
    expect(processed.params.per_page).toBe(DEFAULT_PER_PAGE)

    // Should not override when per_page is explicitly set, even with metadata
    const cfgExplicit: AxiosRequestConfig & { __storeMethod?: { needsPagination?: boolean } } = {
      method: 'get',
      url: '/api/items',
      params: { page: 1, per_page: 50 },
      __storeMethod: { needsPagination: true },
    }
    const processedExplicit = requestHandlers[0](cfgExplicit)
    expect(processedExplicit.params.per_page).toBe(50)

    // Should not inject per_page when metadata indicates no pagination needed
    const cfgNoPagination: AxiosRequestConfig & { __storeMethod?: { supportsInclude?: boolean } } =
      {
        method: 'get',
        url: '/api/items/123',
        params: { include: 'details' },
        __storeMethod: { supportsInclude: true },
      }
    const processedNoPagination = requestHandlers[0](cfgNoPagination)
    expect(processedNoPagination.params.per_page).toBeUndefined()

    // Should not inject per_page when no metadata is present
    const cfgNoMeta: AxiosRequestConfig = {
      method: 'get',
      url: '/api/items',
      params: { page: 1 },
    }
    const processedNoMeta = requestHandlers[0](cfgNoMeta)
    expect(processedNoMeta.params.per_page).toBeUndefined()
  })
})
