<template>
  <ModalOverlay
    :visible="deleteStore.visible"
    variant="dialog"
    :title="deleteStore.title"
    :description="deleteStore.description"
    icon-bg-class="bg-red-100"
    @background-click="deleteStore.cancel()"
  >
    <template #icon>
      <ExclamationTriangleIcon class="h-6 w-6 text-red-600" aria-hidden="true" />
    </template>

    <template #actions>
      <button
        type="button"
        class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto"
        @click="deleteStore.confirmDelete()"
      >
        Delete
      </button>
      <button
        ref="cancelButton"
        type="button"
        class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
        @click="deleteStore.cancel()"
        @keydown.enter="deleteStore.cancel()"
        @keydown.escape="deleteStore.cancel()"
      >
        Cancel
      </button>
    </template>
  </ModalOverlay>
</template>

<script setup lang="ts">
  import { useDeleteConfirmationStore } from '@/stores/deleteConfirmation'
  import { ExclamationTriangleIcon } from '@heroicons/vue/24/solid'
  import ModalOverlay from '@/components/global/ModalOverlay.vue'
  import { ref, watch, nextTick } from 'vue'

  const deleteStore = useDeleteConfirmationStore()
  const cancelButton = ref<HTMLElement>()

  // Focus the cancel button when the modal becomes visible
  watch(
    () => deleteStore.visible,
    (visible: boolean) => {
      if (visible) {
        nextTick(() => {
          cancelButton.value?.focus()
        })
      }
    }
  )
</script>
