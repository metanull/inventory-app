import { defineStore } from 'pinia'
import { ref } from 'vue'
import { type DetailResource, type DetailStoreRequest } from '@metanull/inventory-app-api-client'
import { useApiClient } from '@/composables/useApiClient'
import {
  type IndexQueryOptions,
  type ShowQueryOptions,
  type PaginationMeta,
  extractPaginationMeta,
} from '@/utils/apiQueryParams'

export const useDetailStore = defineStore('detail', () => {
  // State
  const details = ref<DetailResource[]>([])
  const currentDetail = ref<DetailResource | null>(null)
  const loading = ref(false)
  const page = ref(1)
  const perPage = ref(20)
  const total = ref<number | null>(null)

  // Create API client instance with session-aware configuration
  const createApiClient = () => {
    return useApiClient().createDetailApi()
  }

  // Clear current detail
  const clearCurrentDetail = () => {
    currentDetail.value = null
  }

  // Fetch all details (with optional item filtering)
  const fetchDetails = async ({
    include = ['item'],
    page: p = 1,
    perPage: pp = 20,
    itemId,
  }: IndexQueryOptions & { itemId?: string } = {}): Promise<void> => {
    try {
      loading.value = true
      const apiClient = createApiClient()

      // Build request options with query parameters since this API doesn't support direct params
      const params: Record<string, unknown> = { page: p, per_page: pp }
      if (include.length > 0) {
        params.include = include.join(',')
      }
      if (itemId) {
        params.item_id = itemId
      }
      const options = { params }

      const response = await apiClient.detailIndex(options)
      const data = response.data?.data ?? []
      const meta: PaginationMeta | undefined = extractPaginationMeta(response.data)
      details.value = data

      // Update pagination state if meta is present
      if (meta) {
        total.value = typeof meta.total === 'number' ? meta.total : total.value
        page.value = typeof meta.current_page === 'number' ? meta.current_page : p
        perPage.value = typeof meta.per_page === 'number' ? meta.per_page : pp
      } else {
        // Fallback to requested values
        page.value = p
        perPage.value = pp
      }
    } finally {
      loading.value = false
    }
  }

  // Fetch single detail by ID
  const fetchDetail = async (
    detailId: string,
    { include = ['item'] }: ShowQueryOptions = {}
  ): Promise<void> => {
    try {
      loading.value = true
      const apiClient = createApiClient()
      const options: Record<string, unknown> = {}
      if (include.length > 0) {
        options.params = { include: include.join(',') }
      }
      const response = await apiClient.detailShow(detailId, options)
      currentDetail.value = response.data.data || null
    } finally {
      loading.value = false
    }
  }

  // Create a new detail
  const createDetail = async (
    detailData: DetailStoreRequest,
    options: ShowQueryOptions = { include: ['item'] }
  ): Promise<DetailResource> => {
    try {
      loading.value = true
      const apiClient = createApiClient()
      const response = await apiClient.detailStore(detailData)
      const newDetail = response.data.data as DetailResource

      // Add to local state
      details.value.unshift(newDetail)

      // Fetch full detail with includes if needed
      if (options.include && options.include.length > 0) {
        await fetchDetail(newDetail.id, options)
      } else {
        currentDetail.value = newDetail
      }

      return newDetail
    } finally {
      loading.value = false
    }
  }

  // Update an existing detail
  const updateDetail = async (
    detailId: string,
    detailData: DetailStoreRequest,
    options: ShowQueryOptions = { include: ['item'] }
  ): Promise<DetailResource> => {
    try {
      loading.value = true
      const apiClient = createApiClient()
      const response = await apiClient.detailUpdate(detailId, detailData)
      const updatedDetail = response.data.data as DetailResource

      // Update local state
      const index = details.value.findIndex((d: DetailResource) => d.id === detailId)
      if (index !== -1) {
        details.value[index] = updatedDetail
      }

      if (currentDetail.value?.id === detailId) {
        await fetchDetail(detailId, options)
      }

      return updatedDetail
    } finally {
      loading.value = false
    }
  }

  // Delete a detail
  const deleteDetail = async (detailId: string): Promise<void> => {
    try {
      loading.value = true
      const apiClient = createApiClient()
      await apiClient.detailDestroy(detailId)

      // Remove from local state
      details.value = details.value.filter((d: DetailResource) => d.id !== detailId)

      if (currentDetail.value?.id === detailId) {
        currentDetail.value = null
      }
    } finally {
      loading.value = false
    }
  }

  return {
    // State
    details,
    currentDetail,
    loading,
    page,
    perPage,
    total,

    // Actions
    clearCurrentDetail,
    fetchDetails,
    fetchDetail,
    createDetail,
    updateDetail,
    deleteDetail,
  }
})
