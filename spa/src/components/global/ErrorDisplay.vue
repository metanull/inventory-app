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
        data-testid="error-message"
        :data-theme-classes="
          message.type === 'error'
            ? getThemeClass('messageError')
            : message.type === 'warning'
              ? getThemeClass('messageWarning')
              : getThemeClass('messageInfo')
        "
        :class="[
          'flex items-center justify-between rounded-lg border px-4 py-3 shadow-lg',
          message.type === 'error' ? getThemeClass('messageError') : '',
          message.type === 'warning' ? getThemeClass('messageWarning') : '',
          message.type === 'info' ? getThemeClass('messageInfo') : '',
        ]"
      >
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <ExclamationTriangleIcon
              v-if="message.type === 'error'"
              :class="['h-5 w-5', errorBadge]"
              aria-hidden="true"
            />
            <ExclamationTriangleIcon
              v-else-if="message.type === 'warning'"
              :class="['h-5 w-5', warningBadge]"
              aria-hidden="true"
            />
            <InformationCircleIcon v-else :class="['h-5 w-5', infoBadge]" aria-hidden="true" />
          </div>
          <div class="ml-3">
            <p class="text-sm font-medium">{{ message.text }}</p>
          </div>
        </div>
        <button
          type="button"
          :class="[
            'ml-4 inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2',
            message.type === 'error' ? `${errorBadge} ${errorButtonHover} ${errorRing}` : '',
            message.type === 'warning'
              ? `${warningBadge} ${warningButtonHover} ${warningRing}`
              : '',
            message.type === 'info' ? `${infoBadge} ${infoButtonHover} ${infoRing}` : '',
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
  import { useUIColors, getThemeClass } from '@/composables/useColors'

  const errorStore = useErrorDisplayStore()

  // Centralized color management for different message types
  const errorColors = useUIColors('danger')
  const warningColors = useUIColors('warning')
  const infoColors = useUIColors('info')

  // Aliases to color fragments (read once from computed refs; no test-only fallbacks)
  const errorBadge = errorColors.value.badge
  const errorButtonHover = errorColors.value.buttonHover
  const errorRing = errorColors.value.ring

  const warningBadge = warningColors.value.badge
  const warningButtonHover = warningColors.value.buttonHover
  const warningRing = warningColors.value.ring

  const infoBadge = infoColors.value.badge
  const infoButtonHover = infoColors.value.buttonHover
  const infoRing = infoColors.value.ring
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
