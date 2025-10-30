import { defineStore } from 'pinia'
import { ref } from 'vue'
import { v4 as uuidv4 } from 'uuid'

export interface ErrorMessage {
  id: string
  type: 'error' | 'warning' | 'info'
  text: string
  timestamp: number
}

export const useErrorDisplayStore = defineStore('errorDisplay', () => {
  const messages = ref<ErrorMessage[]>([])

  const addMessage = (type: 'error' | 'warning' | 'info', text: string): string => {
    const id = uuidv4()
    const message: ErrorMessage = {
      id,
      type,
      text,
      timestamp: Date.now(),
    }

    messages.value.push(message)

    // Log to console for debugging
    if (type === 'error') {
      console.error(`[ErrorDisplay] ${text}`)
    } else if (type === 'warning') {
      console.warn(`[ErrorDisplay] ${text}`)
    } else {
      console.info(`[ErrorDisplay] ${text}`)
    }

    // Auto-dismiss info and warning messages after 5 seconds
    if (type === 'info' || type === 'warning') {
      setTimeout(() => {
        removeMessage(id)
      }, 5000)
    }

    return id
  }

  const removeMessage = (id: string): void => {
    const index = messages.value.findIndex(msg => msg.id === id)
    if (index > -1) {
      messages.value.splice(index, 1)
    }
  }

  const clearAll = (): void => {
    messages.value = []
  }

  return {
    // State
    messages,
    // Actions
    addMessage,
    removeMessage,
    clearAll,
  }
})
