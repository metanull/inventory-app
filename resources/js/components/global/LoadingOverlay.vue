<template>
  <ModalOverlay
    :visible="loadingStore.visible"
    variant="content"
    overlay-class="bg-black bg-opacity-50"
    content-class="!bg-transparent !shadow-none !border-none !rounded-none !transform-none !overflow-visible"
  >
    <SpinnerIcon class="h-12 w-12 text-white" />
    <p class="mt-4 text-white">{{ loadingStore.text }}</p>
  </ModalOverlay>
</template>

<script setup lang="ts">
  import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
  import ModalOverlay from '@/components/global/ModalOverlay.vue'
  import SpinnerIcon from '@/components/icons/SpinnerIcon.vue'

  const loadingStore = useLoadingOverlayStore()
</script>
