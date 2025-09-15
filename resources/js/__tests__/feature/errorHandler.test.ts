import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { ErrorHandler, setRouter, setAuthStoreAccessor } from '../../utils/errorHandler'
import type { Router } from 'vue-router'

const mockPush = vi.fn()

// Minimal typed mock for Vue Router
const mockRouter = {
  push: mockPush,
  currentRoute: {
    value: { fullPath: '/projects/123', name: 'project-detail', params: { id: '123' } },
  },
} as const

const tokenRef: { value: string | null } = { value: 'abc' }

describe('ErrorHandler.handleAuthenticationError', () => {
  beforeEach(async () => {
    mockPush.mockReset()
    tokenRef.value = 'abc'

    // Inject router and auth accessor
    setRouter(mockRouter as unknown as Router)
    setAuthStoreAccessor(() => ({ token: tokenRef }))

    // Ensure localStorage is available and reset
    localStorage.setItem('auth_token', 'abc')
  })

  afterEach(() => {
    localStorage.clear()
  })

  it('clears token and redirects to named login with redirectName/redirectParams', async () => {
    ErrorHandler.handleAuthenticationError()

    // Allow microtasks to complete for async IIFE
    await Promise.resolve()

    expect(localStorage.getItem('auth_token')).toBeNull()
    expect(tokenRef.value).toBeNull()
    expect(mockPush).toHaveBeenCalledWith({
      name: 'login',
      query: {
        redirectName: 'project-detail',
        redirectParams: encodeURIComponent(JSON.stringify({ id: '123' })),
      },
    })
  })

  it('does not redirect if already on login route', async () => {
    mockPush.mockReset()
    // Set current route to login
    const r = mockRouter as unknown as Router & {
      currentRoute: {
        value: {
          name?: string
          fullPath?: string
          matched?: unknown[]
          params?: Record<string, string>
          query?: Record<string, string>
          hash?: string
          redirectedFrom?: unknown
          meta?: Record<string, unknown>
        }
      }
    }
    r.currentRoute.value = {
      path: '/login',
      name: 'login',
      fullPath: '/login',
      matched: [],
      params: {},
      query: {},
      hash: '',
      redirectedFrom: undefined,
      meta: {},
    }

    ErrorHandler.handleAuthenticationError()
    await Promise.resolve()

    expect(mockPush).not.toHaveBeenCalled()
  })
})
