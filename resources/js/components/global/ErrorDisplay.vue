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
            [getMessageClasses('error').background]: message.type === 'error',
            [getMessageClasses('warning').background]: message.type === 'warning',
            [getMessageClasses('info').background]: message.type === 'info',
          },
        ]"
      >
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <ExclamationTriangleIcon
              v-if="message.type === 'error'"
              :class="['h-5 w-5', getMessageClasses('error').icon]"
              aria-hidden="true"
            />
            <ExclamationTriangleIcon
              v-else-if="message.type === 'warning'"
              :class="['h-5 w-5', getMessageClasses('warning').icon]"
              aria-hidden="true"
            />
            <InformationCircleIcon
              v-else
              :class="['h-5 w-5', getMessageClasses('info').icon]"
              aria-hidden="true"
            />
          </div>
          <div class="ml-3">
            <p class="text-sm font-medium">{{ message.text }}</p>
          </div>
        </div>
        <button
          type="button"
          :class="[
            'ml-4 inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2',
            {
              [getMessageClasses('error').button]: message.type === 'error',
              [getMessageClasses('warning').button]: message.type === 'warning',
              [getMessageClasses('info').button]: message.type === 'info',
            },
          ]"
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
  import { useUIColors } from '@/composables/useColors'

  const errorStore = useErrorDisplayStore()

  // Centralized color management for different message types
  const errorColors = useUIColors('danger')
  const warningColors = useUIColors('warning')
  const infoColors = useUIColors('info')

  function getMessageClasses(type: 'error' | 'warning' | 'info') {
    switch (type) {
      case 'error':
        return {
          background: `bg-red-50 border-red-200 ${errorColors.value.badgeText}`,
          icon: errorColors.value.badge,
          button: `${errorColors.value.badge} ${errorColors.value.buttonHover} ${errorColors.value.ring}`,
        }
      case 'warning':
        return {
          background: `bg-yellow-50 border-yellow-200 ${warningColors.value.badgeText}`,
          icon: warningColors.value.badge,
          button: `${warningColors.value.badge} ${warningColors.value.buttonHover} ${warningColors.value.ring}`,
        }
      case 'info':
        return {
          background: `bg-blue-50 border-blue-200 ${infoColors.value.badgeText}`,
          icon: infoColors.value.badge,
          button: `${infoColors.value.badge} ${infoColors.value.buttonHover} ${infoColors.value.ring}`,
        }
    }
  }
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
