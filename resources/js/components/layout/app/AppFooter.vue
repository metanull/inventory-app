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
        'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center',
        getThemeClass('neutralText'),
        'text-sm',
      ]"
    >
      <div>
        <span :class="['font-semibold', getThemeClass('appTitleColor')]">{{ appTitle }}</span>
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
  import { getThemeClass } from '@/composables/useColors'

  const appTitle = import.meta.env.VITE_APP_TITLE || pkg.name
  const appVersion = pkg.version
  const apiClientVersion = apiPkg.version
</script>

<style scoped>
  footer {
    @apply w-full;
  }
</style>
