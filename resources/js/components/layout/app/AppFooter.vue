<template>
  <footer class="bg-white border-t border-gray-200 py-4 mt-8">
    <div
      class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center text-gray-500 text-sm"
    >
      <div>
        <span class="font-semibold text-gray-700">{{ appTitle }}</span>
        <span class="mx-2">|</span>
        <span>Version: {{ appVersion }}</span>
      </div>
      <div>
        <span>API Client Version: {{ apiClientVersion }}</span>
      </div>
    </div>
  </footer>
</template>

<script setup lang="ts">
  import pkg from '../../../../../package.json'
  import apiPkg from '../../../../../api-client/package.json'

  const appTitle = import.meta.env.VITE_APP_TITLE || pkg.name
  const appVersion = pkg.version
  const apiClientVersion = apiPkg.version
</script>

<style scoped>
  footer {
    @apply w-full;
  }
</style>
