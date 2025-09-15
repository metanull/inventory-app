import { describe, it, expect, vi, beforeEach } from 'vitest'
import { setRouter, setAuthStoreAccessor, ErrorHandler } from '../../utils/errorHandler'
import type { Router } from 'vue-router'

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

const tokenRef = { value: 'abc' as string | null }

describe('Auth Flow - logout', () => {
  beforeEach(() => {
    mockPush.mockReset()
    tokenRef.value = 'abc'
    localStorage.setItem('auth_token', 'abc')
    setAuthStoreAccessor(() => ({ token: tokenRef }))
  })

  it('logout clears token and returns to login', async () => {
    const router = makeRouter('/')
    setRouter(router)

    // Simulate backend wiping tokens -> behave like 401 handler
    ErrorHandler.handleAuthenticationError()
    await Promise.resolve()

    expect(localStorage.getItem('auth_token')).toBeNull()
    expect(tokenRef.value).toBeNull()
    expect(mockPush).toHaveBeenCalledWith({ name: 'login', query: { redirectName: 'home' } })
  })
})
