import { describe, it, expect, vi } from 'vitest'

// Mock auth store to be authenticated
vi.mock('@/stores/auth', () => ({
  useAuthStore: () => ({ isAuthenticated: true }),
}))

import router from '../../router'

describe('Router guard - authenticated user navigating to login', () => {
  it('redirects authenticated user away from login to home', async () => {
    await router.push('/login')
    const route = router.currentRoute.value
    expect(route.name).toBe('home')
    expect(route.path).toBe('/')
  })
})
