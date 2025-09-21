import { describe, it, expect, beforeEach, vi, afterEach } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { usePartnerStore } from '../partner'
import { createMockPartner } from '../../__tests__/test-utils'
import type { PartnerResource } from '@metanull/inventory-app-api-client'

// Mock the API client
const mockPartnerApi = {
  partnerIndex: vi.fn(),
  partnerShow: vi.fn(),
  partnerStore: vi.fn(),
  partnerUpdate: vi.fn(),
  partnerDestroy: vi.fn(),
}

vi.mock('@/composables/useApiClient', () => ({
  useApiClient: () => ({
    createPartnerApi: () => mockPartnerApi,
  }),
}))

const mockPartners: PartnerResource[] = [
  createMockPartner({
    id: '123e4567-e89b-12d3-a456-426614174000',
    internal_name: 'Test Partner 1',
  }),
  createMockPartner({
    id: '123e4567-e89b-12d3-a456-426614174001',
    internal_name: 'Test Partner 2',
  }),
]

describe('Partner Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  afterEach(() => {
    vi.clearAllMocks()
  })

  it('should initialize with empty state', () => {
    const store = usePartnerStore()

    expect(store.partners).toEqual([])
    expect(store.currentPartner).toBeNull()
    expect(store.loading).toBe(false)
  })

  it('should clear current partner', () => {
    const store = usePartnerStore()

    store.currentPartner = mockPartners[0]
    store.clearCurrentPartner()

    expect(store.currentPartner).toBeNull()
  })

  it('should handle fetchPartners success', async () => {
    const store = usePartnerStore()

    mockPartnerApi.partnerIndex.mockResolvedValue({
      data: { data: mockPartners },
    })

    await store.fetchPartners()

    expect(mockPartnerApi.partnerIndex).toHaveBeenCalledWith({
      params: { page: 1, per_page: 20, include: 'country' },
      __storeMethod: {
        needsPagination: true,
        supportsInclude: true,
      },
    })
    expect(store.partners).toEqual(mockPartners)
    expect(store.loading).toBe(false)
  })

  it('should handle fetchPartners error', async () => {
    const store = usePartnerStore()
    const error = new Error('Network error')

    mockPartnerApi.partnerIndex.mockRejectedValue(error)

    await expect(store.fetchPartners()).rejects.toThrow('Network error')

    expect(store.loading).toBe(false)
  })

  it('should handle fetchPartner success', async () => {
    const store = usePartnerStore()
    const partner = mockPartners[0]

    mockPartnerApi.partnerShow.mockResolvedValue({
      data: { data: partner },
    })

    await store.fetchPartner('123e4567-e89b-12d3-a456-426614174000')

    expect(mockPartnerApi.partnerShow).toHaveBeenCalledWith(
      '123e4567-e89b-12d3-a456-426614174000',
      {
        params: { include: 'country' },
        __storeMethod: {
          needsPagination: false,
          supportsInclude: true,
        },
      }
    )
    expect(store.currentPartner).toEqual(partner)
  })
})
