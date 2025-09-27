import { defineStore } from 'pinia'
import { ref } from 'vue'
import { type PartnerResource, type PartnerStoreRequest } from '@metanull/inventory-app-api-client'
import { useApiClient } from '@/composables/useApiClient'
import {
  type IndexQueryOptions,
  type ShowQueryOptions,
  type PaginationMeta,
  extractPaginationMeta,
} from '@/utils/apiQueryParams'

export const usePartnerStore = defineStore('partner', () => {
  // State
  const partners = ref<PartnerResource[]>([])
  const currentPartner = ref<PartnerResource | null>(null)
  const loading = ref(false)
  const page = ref(1)
  const perPage = ref(20)
  const total = ref<number | null>(null)

  // Create API client instance with session-aware configuration
  const createApiClient = () => {
    return useApiClient().createPartnerApi()
  }

  // Clear current partner
  const clearCurrentPartner = () => {
    currentPartner.value = null
  }

  // Fetch all partners
  const fetchPartners = async ({
    include = ['country'],
    page: p = 1,
    perPage: pp = 20,
  }: IndexQueryOptions = {}): Promise<void> => {
    try {
      loading.value = true
      const apiClient = createApiClient()
      const includeStr = include.length > 0 ? include.join(',') : undefined
      const response = await apiClient.partnerIndex(p, pp, includeStr)
      const data = response.data?.data ?? []
      const meta: PaginationMeta | undefined = extractPaginationMeta(response.data)
      partners.value = data
      if (meta) {
        total.value = typeof meta.total === 'number' ? meta.total : total.value
        page.value = typeof meta.current_page === 'number' ? meta.current_page : p
        perPage.value = typeof meta.per_page === 'number' ? meta.per_page : pp
      } else {
        page.value = p
        perPage.value = pp
      }
    } finally {
      loading.value = false
    }
  }

  // Fetch single partner by ID
  const fetchPartner = async (
    partnerId: string,
    { include = ['country'] }: ShowQueryOptions = {}
  ): Promise<void> => {
    try {
      loading.value = true
      const apiClient = createApiClient()
      const includeStr = include.length > 0 ? include.join(',') : undefined
      const response = await apiClient.partnerShow(partnerId, includeStr)
      currentPartner.value = response.data.data || null
    } finally {
      loading.value = false
    }
  }

  // Create a new partner
  const createPartner = async (
    partnerData: PartnerStoreRequest,
    options: ShowQueryOptions = { include: ['country'] }
  ): Promise<PartnerResource> => {
    try {
      loading.value = true
      const apiClient = createApiClient()
      const response = await apiClient.partnerStore(partnerData)
      const newPartner = response.data.data as PartnerResource

      // Add to local state if not present
      const exists = partners.value.some((p: PartnerResource) => p.id === newPartner.id)
      if (!exists) partners.value.unshift(newPartner)
      // Reload with includes
      await fetchPartner(newPartner.id, options)

      return newPartner
    } finally {
      loading.value = false
    }
  }

  // Update an existing partner
  const updatePartner = async (
    partnerId: string,
    partnerData: PartnerStoreRequest,
    options: ShowQueryOptions = { include: ['country'] }
  ): Promise<PartnerResource> => {
    try {
      loading.value = true
      const apiClient = createApiClient()
      const response = await apiClient.partnerUpdate(partnerId, partnerData)
      const updatedPartner = response.data.data as PartnerResource

      // Update local state
      const index = partners.value.findIndex((p: PartnerResource) => p.id === partnerId)
      if (index !== -1) {
        partners.value[index] = updatedPartner
      }

      if (currentPartner.value?.id === partnerId) {
        await fetchPartner(partnerId, options)
      }

      return updatedPartner
    } finally {
      loading.value = false
    }
  }

  // Delete a partner
  const deletePartner = async (partnerId: string): Promise<void> => {
    try {
      loading.value = true
      const apiClient = createApiClient()
      await apiClient.partnerDestroy(partnerId)

      // Remove from local state
      partners.value = partners.value.filter((p: PartnerResource) => p.id !== partnerId)

      if (currentPartner.value?.id === partnerId) {
        currentPartner.value = null
      }
    } finally {
      loading.value = false
    }
  }

  return {
    // State
    partners,
    currentPartner,
    loading,
    page,
    perPage,
    total,

    // Actions
    clearCurrentPartner,
    fetchPartners,
    fetchPartner,
    createPartner,
    updatePartner,
    deletePartner,
  }
})
