<template>
  <ModalOverlay
    :visible="cancelChangesStore.visible"
    variant="dialog"
    :title="cancelChangesStore.title"
    :description="cancelChangesStore.description"
    icon-bg-class="bg-yellow-100"
    @background-click="cancelChangesStore.stay()"
  >
    <template #icon>
      <ExclamationTriangleIcon class="h-6 w-6 text-yellow-600" aria-hidden="true" />
    </template>

    <template #actions>
      <button
        type="button"
        class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto"
        @click="cancelChangesStore.leave()"
      >
        Leave
      </button>
      <button
        ref="stayButton"
        type="button"
        class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
        @click="cancelChangesStore.stay()"
        @keydown.enter="cancelChangesStore.stay()"
        @keydown.escape="cancelChangesStore.stay()"
      >
        Stay
      </button>
    </template>
  </ModalOverlay>
</template>

<script setup lang="ts">
  import { useCancelChangesConfirmationStore } from '@/stores/cancelChangesConfirmation'
  import { ExclamationTriangleIcon } from '@heroicons/vue/24/solid'
  import ModalOverlay from '@/components/global/ModalOverlay.vue'
  import { ref, watch, nextTick } from 'vue'

  const cancelChangesStore = useCancelChangesConfirmationStore()
  const stayButton = ref<HTMLElement>()

  // Focus the stay button when the modal becomes visible
  watch(
    () => cancelChangesStore.visible,
    (visible: boolean) => {
      if (visible) {
        nextTick(() => {
          stayButton.value?.focus()
        })
      }
    }
  )
</script>
