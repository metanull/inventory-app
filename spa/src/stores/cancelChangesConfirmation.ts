import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useCancelChangesConfirmationStore = defineStore('cancelChangesConfirmation', () => {
  const visible = ref<boolean>(false)
  const pendingChanges = ref<boolean>(false)
  const title = ref<string>('')
  const description = ref<string>('')
  const result = ref<'stay' | 'leave' | null>(null)

  const trigger = (titleText: string, descriptionText: string): Promise<'stay' | 'leave'> => {
    return new Promise(resolve => {
      title.value = titleText
      description.value = descriptionText
      result.value = null
      visible.value = true

      // Create a simple watcher using setTimeout
      const checkResult = () => {
        if (result.value !== null) {
          visible.value = false
          resolve(result.value)
        } else {
          setTimeout(checkResult, 100)
        }
      }
      checkResult()
    })
  }

  const resolve = (resultValue: 'stay' | 'leave'): void => {
    result.value = resultValue
  }

  const stay = (): void => {
    resolve('stay')
  }

  const leave = (): void => {
    resolve('leave')
  }

  const addChange = (): void => {
    pendingChanges.value = true
  }

  const resetChanges = (): void => {
    pendingChanges.value = false
  }

  return {
    // State
    visible,
    pendingChanges,
    title,
    description,
    result,
    // Actions
    trigger,
    resolve,
    stay,
    leave,
    addChange,
    resetChanges,
  }
})
