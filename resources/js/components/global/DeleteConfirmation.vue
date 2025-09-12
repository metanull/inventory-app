<template>
  <ModalOverlay
    :visible="deleteStore.visible"
    variant="dialog"
    :title="deleteStore.title"
    :description="deleteStore.description"
    :icon-bg-class="iconBgClass"
    @background-click="deleteStore.cancel()"
  >
    <template #icon>
      <ExclamationTriangleIcon :class="['h-6 w-6', iconClass]" aria-hidden="true" />
    </template>

    <template #actions>
      <button
        type="button"
        :class="[getThemeClass('dangerButton')]"
        @click="deleteStore.confirmDelete()"
      >
        Delete
      </button>
      <button
        ref="cancelButton"
        type="button"
        :class="[getThemeClass('secondaryButton'), getThemeClass('formBorder')]"
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
  import { ref, watch, nextTick, computed } from 'vue'
  import { getThemeClass, useColors } from '@/composables/useColors'

  const deleteStore = useDeleteConfirmationStore()
  const cancelButton = ref<HTMLElement>()
  // Danger color classes (used for modal icon and icon background)
  const dangerClasses = useColors('red')
  const iconBgClass = computed(() => dangerClasses.value.badgeBackground)
  const iconClass = computed(() => dangerClasses.value.icon)

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
