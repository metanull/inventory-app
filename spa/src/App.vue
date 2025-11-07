<template>
  <div id="app" :class="['min-h-screen', getThemeClass('modalActionsBg')]">
    <!-- Application Header -->
    <AppHeader />
    <!-- Global Component: Error Display -->
    <ErrorDisplay />
    <!-- Application Body -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <template v-if="!requiresAuth || isAuthenticated">
        <RouterView />
      </template>
      <template v-else>
        <!-- Keep it lean: small placeholder while redirecting to login -->
        <div class="text-sm text-gray-500">Redirecting to login…</div>
      </template>
    </main>
    <!-- Application Footer -->
    <AppFooter />

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
  import { computed, onMounted } from 'vue'
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
  import { useVersionCheckStore } from '@/stores/versionCheck'
  import { useRoute } from 'vue-router'

  const auth = useAuthStore()
  const { isAuthenticated } = storeToRefs(auth)
  const route = useRoute()
  const requiresAuth = computed(() => route.meta?.requiresAuth === true)

  // Initialize version checking on app mount
  const versionStore = useVersionCheckStore()
  onMounted(async () => {
    // Load initial version from server
    await versionStore.loadInitialVersion()
  })
</script>
