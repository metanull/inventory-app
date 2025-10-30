import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useLoadingOverlayStore = defineStore('loadingOverlay', () => {
  const visible = ref<boolean>(false)
  const disabled = ref<boolean>(false)
  const text = ref<string>('Loading...')

  const show = (loadingText: string = 'Loading...'): void => {
    if (!disabled.value) {
      text.value = loadingText
      visible.value = true
    }
  }

  const hide = (): void => {
    visible.value = false
  }

  const disable = (): void => {
    disabled.value = true
    visible.value = false
  }

  const enable = (): void => {
    disabled.value = false
  }

  return {
    // State
    visible,
    disabled,
    text,
    // Actions
    show,
    hide,
    disable,
    enable,
  }
})
