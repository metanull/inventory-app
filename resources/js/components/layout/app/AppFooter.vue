<template>
  <footer
    :class="[
      getThemeClass('footerBg'),
      getThemeClass('footerBorderTop'),
      'py-4 mt-8',
      getThemeClass('mobileBorderColor'),
    ]"
  >
    <div
      :class="[
        'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-4 items-center',
        getThemeClass('neutralText'),
        'text-sm',
      ]"
    >
      <!-- Column 1: App Info -->
      <div class="flex items-center justify-center md:justify-start gap-1">
        <span :class="['font-semibold', getThemeClass('appTitleColor')]">{{ appTitle }}</span>
        <span class="mx-1">|</span>
        <span>Version {{ appVersion }}</span>
        <template v-if="(versionData as any)?.build_timestamp?.DateTime">
          <span class="mx-1">|</span>
          <span>Build {{ formatBuildDate((versionData as any).build_timestamp.DateTime) }}</span>
        </template>
      </div>

      <!-- Column 2: API Client Info -->
      <div class="flex items-center justify-center gap-1">
        <span>API Client</span>
        <span class="mx-1">|</span>
        <span>Version {{ apiClientVersion }}</span>
      </div>

      <!-- Column 3: API Info -->
      <div class="flex items-center justify-center md:justify-end gap-1">
        <span>API</span>
        <template v-if="appInfo?.application?.version">
          <span class="mx-1">|</span>
          <span>Version {{ appInfo.application.version }}</span>
        </template>
        <span class="mx-1">|</span>
        <div class="flex items-center gap-2">
          <!-- Status Icon -->
          <div v-if="isApiUp" class="w-2 h-2 rounded-full bg-green-400" title="API Online"></div>
          <ExclamationTriangleIcon
            v-else-if="error"
            class="w-4 h-4 text-yellow-500"
            title="API Offline"
          />
          <div v-else class="w-2 h-2 rounded-full bg-yellow-400" title="Checking API..."></div>
          <!-- Status Text -->
          <span class="text-xs">
            {{ isApiUp ? 'Online' : error ? 'Offline' : 'Checking...' }}
          </span>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup lang="ts">
  import pkg from '../../../../../package.json'
  import apiPkg from '../../../../../api-client/package.json'
  import { getThemeClass } from '@/composables/useColors'
  import { useApiStatus } from '@/composables/useApiStatus'
  import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

  const appTitle = import.meta.env.VITE_APP_TITLE || pkg.name
  const appVersion = pkg.version
  const apiClientVersion = apiPkg.version

  // Get API status for system monitoring
  const { isApiUp, error, versionData, appInfo } = useApiStatus()

  // Format build timestamp for display
  const formatBuildDate = (dateString: string) => {
    try {
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      })
    } catch {
      return dateString
    }
  }
</script>

<style scoped>
  footer {
    @apply w-full;
  }
</style>
