<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <!-- Application Header -->
    <AppHeader />
    <!-- Global Component: Error Display -->
    <ErrorDisplay />
    <!-- Application Body -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <RouterView />
    </main>
    <!-- Application Footer -->
    <AppFooter />

    <!-- Global Component: Loading spinner -->
    <LoadingOverlay />
    <!-- Global Component: Confirmation Modals -->
    <DeleteConfirmation />
    <!-- Global Component: Cancel Changes Confirmation -->
    <CancelChangesConfirmation />
  </div>
</template>

<script setup lang="ts">
  import { RouterView } from 'vue-router'
  import AppHeader from '@/components/layout/app/AppHeader.vue'
  import AppFooter from '@/components/layout/app/AppFooter.vue'
  import LoadingOverlay from '@/components/global/LoadingOverlay.vue'
  import ErrorDisplay from '@/components/global/ErrorDisplay.vue'
  import DeleteConfirmation from '@/components/global/DeleteConfirmation.vue'
  import CancelChangesConfirmation from '@/components/global/CancelChangesConfirmation.vue'
</script>
