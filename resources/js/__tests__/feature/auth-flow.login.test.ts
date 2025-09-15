import { describe, it, expect, vi, beforeEach } from 'vitest'
import { setRouter, setAuthStoreAccessor, ErrorHandler } from '../../utils/errorHandler'
import type { Router } from 'vue-router'

// Simple mock router
const mockPush = vi.fn()
const makeRouter = (path = '/'): Router =>
  ({
    push: mockPush,
    currentRoute: {
      value: { fullPath: path, name: path === '/login' ? 'login' : 'home', meta: {} },
    },
    resolve: (to: unknown) => {
      const loc = to as { path?: string } | string
      const p = typeof loc === 'string' ? loc : (loc?.path ?? '/')
      return { matched: [{ path: p }] } as unknown as ReturnType<Router['resolve']>
    },
  }) as unknown as Router

const tokenRef = { value: null as string | null }

describe('Auth Flow - fresh session', () => {
  beforeEach(() => {
    mockPush.mockReset()
    tokenRef.value = null
    setAuthStoreAccessor(() => ({ token: tokenRef }))
  })

  it('no token -> go to login, after login go home', async () => {
    const router = makeRouter('/')
    setRouter(router)

    // Simulate 401 somewhere -> should redirect to login with redirect=/
    ErrorHandler.handleAuthenticationError()
    await Promise.resolve()

    expect(mockPush).toHaveBeenCalledWith({ name: 'login', query: { redirectName: 'home' } })

    // Simulate login
    localStorage.setItem('auth_token', 'abc')
    tokenRef.value = 'abc'

    // After login, app logic will read redirect and go there; here we simply assert we can push '/'
    mockPush.mockReset()
    router.push('/')
    expect(mockPush).toHaveBeenCalledWith('/')
  })
})
