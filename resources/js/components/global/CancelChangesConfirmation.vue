<template>
  <ModalOverlay
    :visible="cancelChangesStore.visible"
    variant="dialog"
    :title="cancelChangesStore.title"
    :description="cancelChangesStore.description"
    :icon-bg-class="iconBgClass"
    @background-click="cancelChangesStore.stay()"
  >
    <template #icon>
      <ExclamationTriangleIcon :class="['h-6 w-6', iconClass]" aria-hidden="true" />
    </template>

    <template #actions>
      <button
        type="button"
        :class="[getThemeClass('dangerButton')]"
        @click="cancelChangesStore.leave()"
      >
        Leave
      </button>
      <button
        ref="stayButton"
        type="button"
        :class="[getThemeClass('secondaryButton'), getThemeClass('formBorder')]"
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
  import { ref, watch, nextTick, computed } from 'vue'
  import { getThemeClass, useColors } from '@/composables/useColors'

  const cancelChangesStore = useCancelChangesConfirmationStore()
  const stayButton = ref<HTMLElement>()

  const warning = useColors('yellow')
  const iconBgClass = computed(() => warning.value.badgeBackground)
  const iconClass = computed(() => warning.value.icon)

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
