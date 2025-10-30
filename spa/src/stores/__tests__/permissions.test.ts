import { describe, it, expect, beforeEach, vi, afterEach } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { usePermissionsStore } from '../permissions'

// Mock the API client
const mockUserPermissionsApi = {
  userPermissions: vi.fn(),
}

vi.mock('@/composables/useApiClient', () => ({
  useApiClient: vi.fn(() => ({
    createUserPermissionsApi: vi.fn(() => mockUserPermissionsApi),
  })),
}))

describe('Permissions Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  afterEach(() => {
    vi.clearAllMocks()
  })

  it('should initialize with empty state', () => {
    const store = usePermissionsStore()

    expect(store.permissions).toEqual([])
    expect(store.loading).toBe(false)
    expect(store.error).toBeNull()
    expect(store.lastFetch).toBeNull()
  })

  describe('fetchPermissions', () => {
    it('should fetch and store permissions successfully', async () => {
      const mockPermissions = ['view data', 'create data', 'update data']
      mockUserPermissionsApi.userPermissions.mockResolvedValue({
        data: { permissions: mockPermissions },
      })

      const store = usePermissionsStore()
      await store.fetchPermissions()

      expect(mockUserPermissionsApi.userPermissions).toHaveBeenCalledTimes(1)
      expect(store.permissions).toEqual(mockPermissions)
      expect(store.loading).toBe(false)
      expect(store.error).toBeNull()
      expect(store.lastFetch).toBeInstanceOf(Date)
    })

    it('should handle empty permissions response', async () => {
      mockUserPermissionsApi.userPermissions.mockResolvedValue({
        data: { permissions: [] },
      })

      const store = usePermissionsStore()
      await store.fetchPermissions()

      expect(store.permissions).toEqual([])
      expect(store.loading).toBe(false)
      expect(store.error).toBeNull()
    })

    it('should handle API errors gracefully', async () => {
      const mockError = new Error('Network error')
      mockUserPermissionsApi.userPermissions.mockRejectedValue(mockError)

      const store = usePermissionsStore()
      await store.fetchPermissions()

      expect(store.permissions).toEqual([])
      expect(store.loading).toBe(false)
      expect(store.error).toBe('Network error')
    })

    it('should handle missing data property', async () => {
      mockUserPermissionsApi.userPermissions.mockResolvedValue({
        data: {},
      })

      const store = usePermissionsStore()
      await store.fetchPermissions()

      expect(store.permissions).toEqual([])
    })
  })

  describe('hasPermission', () => {
    it('should return true when permission exists', async () => {
      const mockPermissions = ['view data', 'create data']
      mockUserPermissionsApi.userPermissions.mockResolvedValue({
        data: { permissions: mockPermissions },
      })

      const store = usePermissionsStore()
      await store.fetchPermissions()

      expect(store.hasPermission('view data')).toBe(true)
      expect(store.hasPermission('create data')).toBe(true)
    })

    it('should return false when permission does not exist', async () => {
      const mockPermissions = ['view data']
      mockUserPermissionsApi.userPermissions.mockResolvedValue({
        data: { permissions: mockPermissions },
      })

      const store = usePermissionsStore()
      await store.fetchPermissions()

      expect(store.hasPermission('delete data')).toBe(false)
      expect(store.hasPermission('manage users')).toBe(false)
    })

    it('should return false when no permissions loaded', () => {
      const store = usePermissionsStore()

      expect(store.hasPermission('view data')).toBe(false)
    })
  })

  describe('hasAllPermissions', () => {
    it('should return true when user has all required permissions', async () => {
      const mockPermissions = ['view data', 'create data', 'update data']
      mockUserPermissionsApi.userPermissions.mockResolvedValue({
        data: { permissions: mockPermissions },
      })

      const store = usePermissionsStore()
      await store.fetchPermissions()

      expect(store.hasAllPermissions(['view data', 'create data'])).toBe(true)
      expect(store.hasAllPermissions(['view data'])).toBe(true)
    })

    it('should return false when user is missing any required permission', async () => {
      const mockPermissions = ['view data', 'create data']
      mockUserPermissionsApi.userPermissions.mockResolvedValue({
        data: { permissions: mockPermissions },
      })

      const store = usePermissionsStore()
      await store.fetchPermissions()

      expect(store.hasAllPermissions(['view data', 'delete data'])).toBe(false)
      expect(store.hasAllPermissions(['manage users', 'view data'])).toBe(false)
    })

    it('should return true for empty array', async () => {
      const mockPermissions = ['view data']
      mockUserPermissionsApi.userPermissions.mockResolvedValue({
        data: { permissions: mockPermissions },
      })

      const store = usePermissionsStore()
      await store.fetchPermissions()

      expect(store.hasAllPermissions([])).toBe(true)
    })
  })

  describe('hasAnyOfPermissions', () => {
    it('should return true when user has at least one required permission', async () => {
      const mockPermissions = ['view data', 'create data']
      mockUserPermissionsApi.userPermissions.mockResolvedValue({
        data: { permissions: mockPermissions },
      })

      const store = usePermissionsStore()
      await store.fetchPermissions()

      expect(store.hasAnyOfPermissions(['view data', 'manage users'])).toBe(true)
      expect(store.hasAnyOfPermissions(['delete data', 'create data'])).toBe(true)
    })

    it('should return false when user has none of the required permissions', async () => {
      const mockPermissions = ['view data']
      mockUserPermissionsApi.userPermissions.mockResolvedValue({
        data: { permissions: mockPermissions },
      })

      const store = usePermissionsStore()
      await store.fetchPermissions()

      expect(store.hasAnyOfPermissions(['delete data', 'manage users'])).toBe(false)
    })

    it('should return false for empty array', async () => {
      const mockPermissions = ['view data']
      mockUserPermissionsApi.userPermissions.mockResolvedValue({
        data: { permissions: mockPermissions },
      })

      const store = usePermissionsStore()
      await store.fetchPermissions()

      expect(store.hasAnyOfPermissions([])).toBe(false)
    })
  })

  describe('clearPermissions', () => {
    it('should clear all permissions and state', async () => {
      const mockPermissions = ['view data', 'create data']
      mockUserPermissionsApi.userPermissions.mockResolvedValue({
        data: { permissions: mockPermissions },
      })

      const store = usePermissionsStore()
      await store.fetchPermissions()

      expect(store.permissions).toEqual(mockPermissions)
      expect(store.lastFetch).toBeInstanceOf(Date)

      store.clearPermissions()

      expect(store.permissions).toEqual([])
      expect(store.lastFetch).toBeNull()
      expect(store.error).toBeNull()
    })
  })

  describe('refreshPermissions', () => {
    it('should refetch permissions', async () => {
      const mockPermissions = ['view data']
      mockUserPermissionsApi.userPermissions.mockResolvedValue({
        data: { permissions: mockPermissions },
      })

      const store = usePermissionsStore()
      await store.refreshPermissions()

      expect(mockUserPermissionsApi.userPermissions).toHaveBeenCalledTimes(1)
      expect(store.permissions).toEqual(mockPermissions)
    })
  })

  describe('hasAnyPermission computed', () => {
    it('should return true when permissions exist', async () => {
      const mockPermissions = ['view data']
      mockUserPermissionsApi.userPermissions.mockResolvedValue({
        data: { permissions: mockPermissions },
      })

      const store = usePermissionsStore()
      await store.fetchPermissions()

      expect(store.hasAnyPermission).toBe(true)
    })

    it('should return false when no permissions exist', () => {
      const store = usePermissionsStore()

      expect(store.hasAnyPermission).toBe(false)
    })
  })
})
