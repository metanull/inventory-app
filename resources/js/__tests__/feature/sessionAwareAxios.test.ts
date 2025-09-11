import { describe, it, expect, vi, beforeEach } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import axios from 'axios'
import { createSessionAwareAxios, getSessionAwareAxios } from '@/utils/sessionAwareAxios'

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
      baseURL: window.location.origin,
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
})
