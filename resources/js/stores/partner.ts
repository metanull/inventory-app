import { defineStore } from 'pinia'
import { ref } from 'vue'
import {
  PartnerApi,
  Configuration,
  type PartnerResource,
  type PartnerStoreRequest,
} from '@metanull/inventory-app-api-client'
import { useAuthStore } from './auth'

declare const process: {
  env: Record<string, string | undefined>
}

export const usePartnerStore = defineStore('partner', () => {
  // State
  const partners = ref<PartnerResource[]>([])
  const currentPartner = ref<PartnerResource | null>(null)
  const loading = ref(false)

  const authStore = useAuthStore()

  // Create API client instance with configuration
  const createApiClient = () => {
    // Support both Vite (import.meta.env) and Node (process.env) for baseURL
    let baseURL: string
    if (
      typeof import.meta !== 'undefined' &&
      import.meta.env &&
      import.meta.env.VITE_API_BASE_URL
    ) {
      baseURL = import.meta.env.VITE_API_BASE_URL
    } else if (typeof process !== 'undefined' && process.env && process.env.VITE_API_BASE_URL) {
      baseURL = process.env.VITE_API_BASE_URL
    } else {
      baseURL = 'http://127.0.0.1:8000/api'
    }

    const configParams: { basePath: string; accessToken?: string } = {
      basePath: baseURL,
    }

    if (authStore.token) {
      configParams.accessToken = authStore.token
    }

    // Create configuration for the API client
    const configuration = new Configuration(configParams)

    return new PartnerApi(configuration)
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
