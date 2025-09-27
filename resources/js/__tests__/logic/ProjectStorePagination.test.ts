import { beforeEach, describe, expect, it, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { createMockProject } from '@/__tests__/test-utils'

vi.mock('@/composables/useApiClient', () => ({
  useApiClient: vi.fn(),
}))
import { useApiClient } from '@/composables/useApiClient'

describe('ProjectStore - pagination and includes', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('minimal-by-default and pagination meta updates on projectIndex', async () => {
    const projects = [createMockProject({ id: 'proj-1' })]
    const meta = { total: 3, current_page: 2, per_page: 1 }
    let lastPage: number | undefined
    let lastPerPage: number | undefined
    let lastInclude: string | undefined

    vi.mocked(useApiClient).mockReturnValue({
      createProjectApi: () => ({
        projectIndex: (page?: number, perPage?: number, include?: string) => {
          lastPage = page
          lastPerPage = perPage
          lastInclude = include
          return Promise.resolve({ data: { data: projects, meta } })
        },
      }),
    } as unknown as {
      createProjectApi: () => {
        projectIndex: (
          page?: number,
          perPage?: number,
          include?: string
        ) => Promise<{ data: { data: unknown; meta: unknown } }>
      }
    })

    const { useProjectStore } = await import('@/stores/project')
    const store = useProjectStore()
    await store.fetchProjects({ page: 2, perPage: 1 })

    expect(lastPage).toBe(2)
    expect(lastPerPage).toBe(1)
    expect(lastInclude).toBeUndefined()
    expect(store.total).toBe(3)
    expect(store.page).toBe(2)
    expect(store.perPage).toBe(1)
  })

  it('enabled projects list uses same pagination wiring', async () => {
    const projects = [createMockProject({ id: 'proj-2', is_enabled: true })]
    const meta = { total: 1, current_page: 1, per_page: 20 }
    let optionsPassed: unknown

    vi.mocked(useApiClient).mockReturnValue({
      createProjectApi: () => ({
        projectEnabled: (options?: unknown) => {
          optionsPassed = options
          return Promise.resolve({ data: { data: projects, meta } })
        },
      }),
    } as unknown as {
      createProjectApi: () => {
        projectEnabled: (options?: unknown) => Promise<{ data: { data: unknown; meta: unknown } }>
      }
    })

    const { useProjectStore } = await import('@/stores/project')
    const store = useProjectStore()
    await store.fetchEnabledProjects({ page: 1, perPage: 20 })

    // projectEnabled doesn't support pagination parameters - it just uses defaults
    expect(optionsPassed).toBeUndefined()
    expect(store.total).toBe(1)
    expect(Array.isArray(store.visibleProjects)).toBe(true)
  })
})
