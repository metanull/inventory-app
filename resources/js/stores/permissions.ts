import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useApiClient } from '@/composables/useApiClient'

export const usePermissionsStore = defineStore('permissions', () => {
  const permissions = ref<string[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)
  const lastFetch = ref<Date | null>(null)

  const hasAnyPermission = computed(() => permissions.value.length > 0)

  const hasPermission = (permission: string): boolean => {
    return permissions.value.includes(permission)
  }

  const hasAllPermissions = (requiredPermissions: string[]): boolean => {
    return requiredPermissions.every(perm => permissions.value.includes(perm))
  }

  const hasAnyOfPermissions = (requiredPermissions: string[]): boolean => {
    return requiredPermissions.some(perm => permissions.value.includes(perm))
  }

  const fetchPermissions = async (): Promise<void> => {
    loading.value = true
    error.value = null

    try {
      const apiClient = useApiClient().createUserPermissionsApi()
      const response = await apiClient.userPermissions()

      const data = response.data.data
      permissions.value = Array.isArray(data) ? data : []
      lastFetch.value = new Date()
    } catch (err: unknown) {
      const errorMessage =
        (err as { message?: string })?.message || 'Failed to fetch user permissions'
      error.value = errorMessage
      console.error('Failed to fetch permissions:', err)
      // Don't throw - allow app to continue with empty permissions
      permissions.value = []
    } finally {
      loading.value = false
    }
  }

  const clearPermissions = (): void => {
    permissions.value = []
    lastFetch.value = null
    error.value = null
  }

  const refreshPermissions = async (): Promise<void> => {
    await fetchPermissions()
  }

  return {
    permissions,
    loading,
    error,
    lastFetch,
    hasAnyPermission,
    hasPermission,
    hasAllPermissions,
    hasAnyOfPermissions,
    fetchPermissions,
    clearPermissions,
    refreshPermissions,
  }
})
