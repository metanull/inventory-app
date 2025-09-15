import { describe, it, expect, vi, beforeEach } from 'vitest'
import { setRouter, setAuthStoreAccessor, ErrorHandler } from '../../utils/errorHandler'
import type { Router } from 'vue-router'

const mockPush = vi.fn()
const makeRouter = (path = '/languages'): Router =>
  ({
    push: mockPush,
    currentRoute: {
      value: {
        fullPath: path,
        name: path.startsWith('/languages/') ? 'language-detail' : 'languages',
        params: path.startsWith('/languages/') ? { id: path.split('/').pop() } : {},
        meta: { requiresAuth: true },
      },
    },
    resolve: (to: unknown) => {
      const loc = to as { path?: string } | string
      const p = typeof loc === 'string' ? loc : (loc?.path ?? '/')
      return { matched: [{ path: p }] } as unknown as ReturnType<Router['resolve']>
    },
  }) as unknown as Router

const tokenRef = { value: 'abc' as string | null }

describe('Auth Flow - token expiration during navigation', () => {
  beforeEach(() => {
    mockPush.mockReset()
    tokenRef.value = 'abc'
    localStorage.setItem('auth_token', 'abc')
    setAuthStoreAccessor(() => ({ token: tokenRef }))
  })

  it('on 401: single redirect to login with intended route as redirect', async () => {
    const router = makeRouter('/languages/tot')
    setRouter(router)

    // Simulate multiple 401 errors occurring nearly simultaneously
    ErrorHandler.handleAuthenticationError()
    ErrorHandler.handleAuthenticationError()
    ErrorHandler.handleAuthenticationError()

    await Promise.resolve()

    // Expect only one navigation triggered
    expect(mockPush).toHaveBeenCalledTimes(1)
    expect(mockPush).toHaveBeenCalledWith({
      name: 'login',
      query: {
        redirectName: 'language-detail',
        redirectParams: encodeURIComponent(JSON.stringify({ id: 'tot' })),
      },
    })
  })
})
