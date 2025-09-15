import { describe, it, expect, vi, beforeEach } from 'vitest'

// Mock auth store to be unauthenticated
vi.mock('@/stores/auth', () => ({
  useAuthStore: () => ({ isAuthenticated: false }),
}))

import router from '../../router'

describe('Router guard - named redirect with params', () => {
  beforeEach(() => {
    // Reset router to a known state
    // Note: vue-router keeps state across tests; ensure push calls run guards
  })

  it('redirects unauthenticated user to login with redirectName and redirectParams', async () => {
    await router.push('/')
    await router.push({ name: 'language-detail', params: { id: 'tot' } })

    const route = router.currentRoute.value
    expect(route.name).toBe('login')
    expect(route.query.redirectName).toBe('language-detail')
    expect(typeof route.query.redirectParams).toBe('string')
    const decoded = JSON.parse(decodeURIComponent(String(route.query.redirectParams)))
    expect(decoded).toEqual({ id: 'tot' })
  })
})
