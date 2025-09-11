import { defineStore } from 'pinia'
import { ref } from 'vue'
import { type PartnerResource, type PartnerStoreRequest } from '@metanull/inventory-app-api-client'
import { useApiClient } from '@/composables/useApiClient'

export const usePartnerStore = defineStore('partner', () => {
  // State
  const partners = ref<PartnerResource[]>([])
  const currentPartner = ref<PartnerResource | null>(null)
  const loading = ref(false)

  // Create API client instance with session-aware configuration
  const createApiClient = () => {
    return useApiClient().createPartnerApi()
  }

  // Clear current partner
  const clearCurrentPartner = () => {
    currentPartner.value = null
  }

  // Fetch all partners
  const fetchPartners = async (): Promise<void> => {
    try {
      loading.value = true
      const apiClient = createApiClient()
      const response = await apiClient.partnerIndex()
      partners.value = response.data.data || []
    } finally {
      loading.value = false
    }
  }

  // Fetch single partner by ID
  const fetchPartner = async (partnerId: string): Promise<void> => {
    try {
      loading.value = true
      const apiClient = createApiClient()
      const response = await apiClient.partnerShow(partnerId)
      currentPartner.value = response.data.data || null
    } finally {
      loading.value = false
    }
  }

  // Create a new partner
  const createPartner = async (partnerData: PartnerStoreRequest): Promise<PartnerResource> => {
    try {
      loading.value = true
      const apiClient = createApiClient()
      const response = await apiClient.partnerStore(partnerData)
      const newPartner = response.data.data as PartnerResource

      // Add to local state
      partners.value.push(newPartner)
      currentPartner.value = newPartner

      return newPartner
    } finally {
      loading.value = false
    }
  }

  // Update an existing partner
  const updatePartner = async (
    partnerId: string,
    partnerData: PartnerStoreRequest
  ): Promise<PartnerResource> => {
    try {
      loading.value = true
      const apiClient = createApiClient()
      const response = await apiClient.partnerUpdate(partnerId, partnerData)
      const updatedPartner = response.data.data as PartnerResource

      // Update local state
      const index = partners.value.findIndex(p => p.id === partnerId)
      if (index !== -1) {
        partners.value[index] = updatedPartner
      }

      if (currentPartner.value?.id === partnerId) {
        currentPartner.value = updatedPartner
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
      partners.value = partners.value.filter(p => p.id !== partnerId)

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

    // Actions
    clearCurrentPartner,
    fetchPartners,
    fetchPartner,
    createPartner,
    updatePartner,
    deletePartner,
  }
})
