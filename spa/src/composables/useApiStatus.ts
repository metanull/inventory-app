import { ref, onMounted } from 'vue'
import {
  Configuration,
  InfoApi,
  type InfoVersion200Response,
} from '@metanull/inventory-app-api-client'

interface AppInfo {
  application: {
    name: string
    version: string
    environment: string
  }
  health?: {
    status: string
    checks?: Record<string, unknown>
  }
  timestamp?: string
}

export function useApiStatus() {
  const isApiUp = ref(false)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const versionData = ref<InfoVersion200Response | null>(null)
  const appInfo = ref<AppInfo | null>(null)

  const checkApiStatus = async () => {
    loading.value = true
    error.value = null

    try {
      // Support both Vite (import.meta.env) and Node (process.env) for baseURL
      let baseURL: string
      if (
        typeof import.meta !== 'undefined' &&
        import.meta.env &&
        import.meta.env.VITE_API_BASE_URL
      ) {
        baseURL = import.meta.env.VITE_API_BASE_URL
      } else {
        baseURL = 'http://127.0.0.1:8000/api'
      }

      const config = new Configuration({ basePath: baseURL })
      const infoApi = new InfoApi(config)

      // Call both version and info endpoints
      const [versionResponse, infoResponse] = await Promise.all([
        infoApi.infoVersion(),
        infoApi.infoIndex(),
      ])

      versionData.value = versionResponse.data
      // Parse the info response if it's a string, otherwise use as-is
      if (typeof infoResponse.data === 'string') {
        try {
          appInfo.value = JSON.parse(infoResponse.data) as AppInfo
        } catch {
          appInfo.value = null
        }
      } else {
        appInfo.value = infoResponse.data as AppInfo
      }

      isApiUp.value = true
    } catch (e) {
      isApiUp.value = false
      error.value = e instanceof Error ? e.message : 'Unknown error'
      versionData.value = null
      appInfo.value = null
    } finally {
      loading.value = false
    }
  }

  onMounted(() => {
    checkApiStatus()
  })

  return {
    isApiUp,
    loading,
    error,
    versionData,
    appInfo,
    checkApiStatus,
  }
}
