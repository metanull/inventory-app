<template>
  <ModalOverlay
    :visible="shouldShowOverlay"
    variant="content"
    :overlay-class="overlayClasses"
    content-class="!bg-white !shadow-2xl !border-2 !border-orange-500 !rounded-lg max-w-md"
  >
    <div class="text-center p-8">
      <!-- Icon -->
      <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-orange-100 mb-4">
        <WrenchScrewdriverIcon class="h-10 w-10 text-orange-600" />
      </div>

      <!-- Title -->
      <h3 class="text-xl font-semibold text-gray-900 mb-2">
        {{ title }}
      </h3>

      <!-- Message -->
      <p class="text-sm text-gray-600 mb-4">
        {{ message }}
      </p>

      <!-- Status indicator -->
      <div class="flex items-center justify-center gap-2 text-sm text-gray-500">
        <ArrowPathIcon class="h-4 w-4 animate-spin" />
        <span>{{ statusText }}</span>
      </div>
    </div>
  </ModalOverlay>
</template>

<script setup lang="ts">
  import { computed, watch, onMounted } from 'vue'
  import { storeToRefs } from 'pinia'
  import ModalOverlay from '@/components/global/ModalOverlay.vue'
  import { WrenchScrewdriverIcon, ArrowPathIcon } from '@heroicons/vue/24/outline'
  import { useVersionCheckStore } from '@/stores/versionCheck'
  import { getThemeClass } from '@/composables/useColors'

  const versionStore = useVersionCheckStore()
  const { isUpdateAvailable, isInMaintenanceMode } = storeToRefs(versionStore)

  // Determine if overlay should be shown
  const shouldShowOverlay = computed(
    () => isUpdateAvailable.value || isInMaintenanceMode.value
  )

  // Dynamic overlay classes
  const overlayClasses = computed(() => getThemeClass('loadingOverlay'))

  // Dynamic title
  const title = computed(() => {
    if (isUpdateAvailable.value) {
      return 'Update Available'
    }
    if (isInMaintenanceMode.value) {
      return 'Under Maintenance'
    }
    return 'Please Wait'
  })

  // Dynamic message
  const message = computed(() => {
    if (isUpdateAvailable.value) {
      return 'A new version of the application is available. The page will reload automatically to apply the update.'
    }
    if (isInMaintenanceMode.value) {
      return 'The application is currently undergoing maintenance. Please wait while we complete the update.'
    }
    return 'Processing...'
  })

  // Dynamic status text
  const statusText = computed(() => {
    if (isUpdateAvailable.value) {
      return 'Reloading application...'
    }
    if (isInMaintenanceMode.value) {
      return 'Checking for recovery...'
    }
    return 'Please wait...'
  })

  // Watch for update availability and trigger reload
  watch(isUpdateAvailable, newValue => {
    if (newValue) {
      // Short delay to let user see the message, then reload
      setTimeout(() => {
        versionStore.reloadApplication()
      }, 2000)
    }
  })

  // Watch for maintenance mode recovery
  watch(isInMaintenanceMode, (newValue, oldValue) => {
    // If we were in maintenance mode and now we're not, reload
    if (oldValue && !newValue && !isUpdateAvailable.value) {
      setTimeout(() => {
        versionStore.reloadApplication()
      }, 2000)
    }
  })

  // Poll for recovery when in maintenance mode
  let recoveryCheckInterval: number | null = null

  watch(
    shouldShowOverlay,
    newValue => {
      if (newValue && isInMaintenanceMode.value) {
        // Start polling for recovery every 10 seconds
        recoveryCheckInterval = window.setInterval(() => {
          versionStore.checkVersion()
        }, 10000)
      } else {
        // Stop polling when overlay is hidden
        if (recoveryCheckInterval) {
          clearInterval(recoveryCheckInterval)
          recoveryCheckInterval = null
        }
      }
    },
    { immediate: true }
  )

  // Cleanup on unmount
  onMounted(() => {
    return () => {
      if (recoveryCheckInterval) {
        clearInterval(recoveryCheckInterval)
      }
    }
  })
</script>
