<template>
  <div class="relative">
    <TransitionGroup
      name="error-message"
      tag="div"
      class="fixed top-16 left-0 right-0 z-40 space-y-2 px-4"
    >
      <div
        v-for="message in errorStore.messages"
        :key="message.id"
        :class="[
          'flex items-center justify-between rounded-lg border px-4 py-3 shadow-lg',
          {
            'bg-red-50 border-red-200 text-red-800': message.type === 'error',
            'bg-yellow-50 border-yellow-200 text-yellow-800': message.type === 'warning',
            'bg-blue-50 border-blue-200 text-blue-800': message.type === 'info',
          },
        ]"
      >
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <ExclamationTriangleIcon
              v-if="message.type === 'error'"
              class="h-5 w-5 text-red-400"
              aria-hidden="true"
            />
            <ExclamationTriangleIcon
              v-else-if="message.type === 'warning'"
              class="h-5 w-5 text-yellow-400"
              aria-hidden="true"
            />
            <InformationCircleIcon v-else class="h-5 w-5 text-blue-400" aria-hidden="true" />
          </div>
          <div class="ml-3">
            <p class="text-sm font-medium">{{ message.text }}</p>
          </div>
        </div>
        <button
          type="button"
          class="ml-4 inline-flex rounded-md p-1.5 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2"
          :class="{
            'text-red-400 hover:bg-red-100 focus:ring-red-500': message.type === 'error',
            'text-yellow-400 hover:bg-yellow-100 focus:ring-yellow-500': message.type === 'warning',
            'text-blue-400 hover:bg-blue-100 focus:ring-blue-500': message.type === 'info',
          }"
          @click="errorStore.removeMessage(message.id)"
        >
          <span class="sr-only">Dismiss</span>
          <XMarkIcon class="h-5 w-5" aria-hidden="true" />
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup lang="ts">
  import { useErrorDisplayStore } from '@/stores/errorDisplay'
  import {
    ExclamationTriangleIcon,
    InformationCircleIcon,
    XMarkIcon,
  } from '@heroicons/vue/24/solid'

  const errorStore = useErrorDisplayStore()
</script>

<style scoped>
  .error-message-enter-active,
  .error-message-leave-active {
    transition: all 0.3s ease;
  }

  .error-message-enter-from {
    opacity: 0;
    transform: translateY(-20px);
  }

  .error-message-leave-to {
    opacity: 0;
    transform: translateY(-20px);
  }

  .error-message-move {
    transition: transform 0.3s ease;
  }
</style>
