import { describe, it, expect, beforeEach, vi, afterEach } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useVersionCheckStore } from '../versionCheck'

// Mock fetch globally
global.fetch = vi.fn()

describe('VersionCheck Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
    vi.useFakeTimers()
  })

  afterEach(() => {
    vi.clearAllMocks()
    vi.useRealTimers()
  })

  it('should initialize with null state', () => {
    const store = useVersionCheckStore()

    expect(store.currentVersion).toBeNull()
    expect(store.latestVersion).toBeNull()
    expect(store.isInMaintenanceMode).toBe(false)
    expect(store.isUpdateAvailable).toBe(false)
    expect(store.isChecking).toBe(false)
    expect(store.canCheck).toBe(true)
  })

  describe('loadInitialVersion', () => {
    it('should load initial version successfully', async () => {
      const mockVersionData = {
        app_version: '1.0.0',
        build_number: '42',
        unique_build_id: '1.0.0.42',
      }

      ;(global.fetch as ReturnType<typeof vi.fn>).mockResolvedValueOnce({
        ok: true,
        json: async () => mockVersionData,
      })

      const store = useVersionCheckStore()
      await store.loadInitialVersion()

      expect(store.currentVersion).toBe('1.0.0.42')
      expect(store.latestVersion).toBe('1.0.0.42')
    })

    it('should handle fetch failure gracefully', async () => {
      ;(global.fetch as ReturnType<typeof vi.fn>).mockResolvedValueOnce({
        ok: false,
        status: 404,
      })

      const store = useVersionCheckStore()
      await store.loadInitialVersion()

      expect(store.currentVersion).toBeNull()
      expect(store.latestVersion).toBeNull()
    })

    it('should handle network error gracefully', async () => {
      ;(global.fetch as ReturnType<typeof vi.fn>).mockRejectedValueOnce(
        new Error('Network error')
      )

      const store = useVersionCheckStore()
      await store.loadInitialVersion()

      expect(store.currentVersion).toBeNull()
      expect(store.latestVersion).toBeNull()
    })
  })

  describe('checkVersion', () => {
    it('should skip check if already checking', async () => {
      const store = useVersionCheckStore()
      store.isChecking = true

      await store.checkVersion()

      expect(global.fetch).not.toHaveBeenCalled()
    })

    it('should skip check if in cooldown period', async () => {
      const store = useVersionCheckStore()
      store.currentVersion = '1.0.0.42'
      store.lastCheckTime = Date.now()

      await store.checkVersion()

      expect(global.fetch).not.toHaveBeenCalled()
    })

    it('should skip check if no current version set', async () => {
      const store = useVersionCheckStore()

      await store.checkVersion()

      expect(global.fetch).not.toHaveBeenCalled()
    })

    it('should detect version mismatch', async () => {
      const store = useVersionCheckStore()
      store.currentVersion = '1.0.0.42'

      ;(global.fetch as ReturnType<typeof vi.fn>)
        .mockResolvedValueOnce({
          ok: false,
        })
        .mockResolvedValueOnce({
          ok: true,
          json: async () => ({
            app_version: '1.0.1',
            build_number: '43',
            unique_build_id: '1.0.1.43',
          }),
        })

      // Advance time past cooldown
      vi.advanceTimersByTime(15001)

      await store.checkVersion()

      expect(store.isUpdateAvailable).toBe(true)
      expect(store.latestVersion).toBe('1.0.1.43')
    })

    it('should detect maintenance mode', async () => {
      const store = useVersionCheckStore()
      store.currentVersion = '1.0.0.42'

      ;(global.fetch as ReturnType<typeof vi.fn>)
        .mockResolvedValueOnce({
          ok: true,
        })
        .mockResolvedValueOnce({
          ok: true,
          json: async () => ({
            app_version: '1.0.0',
            build_number: '42',
            unique_build_id: '1.0.0.42',
          }),
        })

      // Advance time past cooldown
      vi.advanceTimersByTime(15001)

      await store.checkVersion()

      expect(store.isInMaintenanceMode).toBe(true)
    })

    it('should not detect maintenance mode when down.lock not found', async () => {
      const store = useVersionCheckStore()
      store.currentVersion = '1.0.0.42'

      ;(global.fetch as ReturnType<typeof vi.fn>)
        .mockResolvedValueOnce({
          ok: false,
        })
        .mockResolvedValueOnce({
          ok: true,
          json: async () => ({
            app_version: '1.0.0',
            build_number: '42',
            unique_build_id: '1.0.0.42',
          }),
        })

      // Advance time past cooldown
      vi.advanceTimersByTime(15001)

      await store.checkVersion()

      expect(store.isInMaintenanceMode).toBe(false)
    })

    it('should handle version check failure gracefully', async () => {
      const store = useVersionCheckStore()
      store.currentVersion = '1.0.0.42'

      ;(global.fetch as ReturnType<typeof vi.fn>).mockRejectedValue(new Error('Network error'))

      // Advance time past cooldown
      vi.advanceTimersByTime(15001)

      await store.checkVersion()

      expect(store.isChecking).toBe(false)
    })

    it('should respect cooldown period', async () => {
      const store = useVersionCheckStore()
      store.currentVersion = '1.0.0.42'

      ;(global.fetch as ReturnType<typeof vi.fn>)
        .mockResolvedValueOnce({
          ok: false,
        })
        .mockResolvedValueOnce({
          ok: true,
          json: async () => ({
            app_version: '1.0.0',
            build_number: '42',
            unique_build_id: '1.0.0.42',
          }),
        })

      // First check
      vi.advanceTimersByTime(15001)
      await store.checkVersion()

      expect(global.fetch).toHaveBeenCalledTimes(2)
      vi.clearAllMocks()

      // Immediate second check (within cooldown)
      await store.checkVersion()

      expect(global.fetch).not.toHaveBeenCalled()

      // Advance past cooldown
      vi.advanceTimersByTime(15001)
      ;(global.fetch as ReturnType<typeof vi.fn>)
        .mockResolvedValueOnce({
          ok: false,
        })
        .mockResolvedValueOnce({
          ok: true,
          json: async () => ({
            app_version: '1.0.0',
            build_number: '42',
            unique_build_id: '1.0.0.42',
          }),
        })

      await store.checkVersion()

      expect(global.fetch).toHaveBeenCalled()
    })
  })

  describe('canCheck computed', () => {
    it('should return true initially', () => {
      const store = useVersionCheckStore()

      expect(store.canCheck).toBe(true)
    })

    it('should return false within cooldown period', async () => {
      const store = useVersionCheckStore()
      store.currentVersion = '1.0.0.42'

      ;(global.fetch as ReturnType<typeof vi.fn>)
        .mockResolvedValueOnce({
          ok: false,
        })
        .mockResolvedValueOnce({
          ok: true,
          json: async () => ({
            unique_build_id: '1.0.0.42',
          }),
        })

      vi.advanceTimersByTime(15001)
      await store.checkVersion()

      expect(store.canCheck).toBe(false)
    })

    it('should return true after cooldown period', async () => {
      const store = useVersionCheckStore()
      store.currentVersion = '1.0.0.42'

      ;(global.fetch as ReturnType<typeof vi.fn>)
        .mockResolvedValueOnce({
          ok: false,
        })
        .mockResolvedValueOnce({
          ok: true,
          json: async () => ({
            unique_build_id: '1.0.0.42',
          }),
        })

      vi.advanceTimersByTime(15001)
      await store.checkVersion()

      expect(store.canCheck).toBe(false)

      vi.advanceTimersByTime(15001)

      expect(store.canCheck).toBe(true)
    })
  })

  describe('reloadApplication', () => {
    it('should clear storage and reload', () => {
      const mockReload = vi.fn()
      Object.defineProperty(window, 'location', {
        value: { reload: mockReload },
        writable: true,
      })

      const mockLocalStorageClear = vi.spyOn(localStorage, 'clear')
      const mockSessionStorageClear = vi.spyOn(sessionStorage, 'clear')

      const store = useVersionCheckStore()
      store.reloadApplication()

      expect(mockLocalStorageClear).toHaveBeenCalled()
      expect(mockSessionStorageClear).toHaveBeenCalled()
      expect(mockReload).toHaveBeenCalled()
    })
  })

  describe('reset', () => {
    it('should reset all state', async () => {
      const store = useVersionCheckStore()

      // Set some state
      store.currentVersion = '1.0.0.42'
      store.latestVersion = '1.0.1.43'
      store.isInMaintenanceMode = true
      store.isUpdateAvailable = true
      store.isChecking = true
      store.lastCheckTime = Date.now()

      store.reset()

      expect(store.currentVersion).toBeNull()
      expect(store.latestVersion).toBeNull()
      expect(store.isInMaintenanceMode).toBe(false)
      expect(store.isUpdateAvailable).toBe(false)
      expect(store.isChecking).toBe(false)
      expect(store.lastCheckTime).toBe(0)
    })
  })
})
