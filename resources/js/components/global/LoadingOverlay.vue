<template>
  <ModalOverlay
    :visible="loadingStore.visible"
    variant="content"
    :overlay-class="getThemeClass('loadingOverlay')"
    content-class="!bg-transparent !shadow-none !border-none !rounded-none !transform-none !overflow-visible"
  >
    <ArrowPathIcon class="h-12 w-12 text-white animate-spin" />
    <p class="mt-4 text-white">{{ loadingStore.text }}</p>
  </ModalOverlay>
</template>

<script setup lang="ts">
  import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
  import ModalOverlay from '@/components/global/ModalOverlay.vue'
  import { ArrowPathIcon } from '@heroicons/vue/24/outline'
  import { getThemeClass } from '@/composables/useColors'

  const loadingStore = useLoadingOverlayStore()
</script>
