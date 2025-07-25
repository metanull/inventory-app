import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useDeleteConfirmationStore = defineStore('deleteConfirmation', () => {
  const visible = ref<boolean>(false)
  const title = ref<string>('')
  const description = ref<string>('')
  const result = ref<'cancel' | 'delete' | null>(null)

  const trigger = (titleText: string, descriptionText: string): Promise<'cancel' | 'delete'> => {
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

  const resolve = (resultValue: 'cancel' | 'delete'): void => {
    result.value = resultValue
  }

  const cancel = (): void => {
    resolve('cancel')
  }

  const confirmDelete = (): void => {
    resolve('delete')
  }

  return {
    // State
    visible,
    title,
    description,
    result,
    // Actions
    trigger,
    resolve,
    cancel,
    confirmDelete,
  }
})
