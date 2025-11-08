<template>
  <div id="app" :class="['min-h-screen', getThemeClass('modalActionsBg')]">
    <!-- Application Header - only show when permissions are ready -->
    <AppHeader v-if="!requiresAuth || permissionsReady" />
    <!-- Global Component: Error Display -->
    <ErrorDisplay />
    <!-- Application Body -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <RouterView v-if="!requiresAuth || (isAuthenticated && permissionsReady)" />
    </main>
    <!-- Application Footer - only show when permissions are ready -->
    <AppFooter v-if="!requiresAuth || permissionsReady" />

    <!-- Global Component: Loading spinner -->
    <LoadingOverlay />
    <!-- Global Component: Maintenance/Update Overlay -->
    <MaintenanceOverlay />
    <!-- Global Component: Confirmation Modals -->
    <DeleteConfirmation />
    <!-- Global Component: Cancel Changes Confirmation -->
    <CancelChangesConfirmation />
  </div>
</template>

<script setup lang="ts">
  import { RouterView } from 'vue-router'
  import { computed, onMounted, ref } from 'vue'
  import AppHeader from '@/components/layout/app/AppHeader.vue'
  import AppFooter from '@/components/layout/app/AppFooter.vue'
  import LoadingOverlay from '@/components/global/LoadingOverlay.vue'
  import MaintenanceOverlay from '@/components/global/MaintenanceOverlay.vue'
  import ErrorDisplay from '@/components/global/ErrorDisplay.vue'
  import DeleteConfirmation from '@/components/global/DeleteConfirmation.vue'
  import CancelChangesConfirmation from '@/components/global/CancelChangesConfirmation.vue'
  import { getThemeClass } from '@/composables/useColors'
  import { storeToRefs } from 'pinia'
  import { useAuthStore } from '@/stores/auth'
  import { usePermissionsStore } from '@/stores/permissions'
  import { useVersionCheckStore } from '@/stores/versionCheck'
  import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
  import { useRoute } from 'vue-router'

  const auth = useAuthStore()
  const permissionsStore = usePermissionsStore()
  const loadingStore = useLoadingOverlayStore()
  const { isAuthenticated } = storeToRefs(auth)
  const route = useRoute()
  const requiresAuth = computed(() => route.meta?.requiresAuth === true)
  const permissionsReady = ref(false)

  // Initialize app on mount
  onMounted(async () => {
    // Load permissions if authenticated
    if (isAuthenticated.value) {
      loadingStore.show('Loading permissions...')
      try {
        await permissionsStore.fetchPermissions()
        permissionsReady.value = true
      } catch (error) {
        console.error('Failed to load permissions:', error)
        // If permissions fail to load, still mark as ready to avoid infinite loading
        // The user will see an empty interface but can logout
        permissionsReady.value = true
      } finally {
        loadingStore.hide()
      }
    } else {
      // Not authenticated, permissions not needed
      permissionsReady.value = true
    }

    // Initialize version checking
    const versionStore = useVersionCheckStore()
    await versionStore.loadInitialVersion()
  })
</script>
