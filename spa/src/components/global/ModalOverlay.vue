<template>
  <div
    v-if="visible"
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="modal-title"
    role="dialog"
    aria-modal="true"
  >
    <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
      <!-- Background overlay -->
      <div
        :class="['fixed inset-0 transition-opacity', getThemeClass('modalOverlay'), overlayClass]"
        @click="$emit('backgroundClick')"
      ></div>

      <!-- Modal content -->
      <div
        class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
        :class="contentClass"
      >
        <!-- Two-section layout (for confirmation dialogs) -->
        <template v-if="variant === 'dialog'">
          <!-- Header section with icon, title, description -->
          <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div
                class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full sm:mx-0 sm:h-10 sm:w-10"
                :class="iconBgClass"
              >
                <slot name="icon" />
              </div>
              <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                <h3
                  id="modal-title"
                  :class="['text-base font-semibold leading-6', getThemeClass('modalTitle')]"
                >
                  {{ title }}
                </h3>
                <div class="mt-2">
                  <p :class="['text-sm', getThemeClass('modalDescription')]">
                    {{ description }}
                  </p>
                </div>
              </div>
            </div>
          </div>
          <!-- Actions section -->
          <div
            :class="[
              'px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6',
              getThemeClass('modalActionsBg'),
            ]"
          >
            <slot name="actions" />
          </div>
        </template>

        <!-- Single content layout (for loading overlay) -->
        <template v-else>
          <div class="flex flex-col items-center">
            <slot />
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { getThemeClass } from '@/composables/useColors'

  interface Props {
    visible: boolean
    variant?: 'dialog' | 'content'
    overlayClass?: string
    contentClass?: string
    title?: string
    description?: string
    iconBgClass?: string
  }

  const raw = withDefaults(defineProps<Props>(), {
    overlayClass: getThemeClass('modalOverlay'),
    contentClass: getThemeClass('modalContent'),
    iconBgClass: undefined,
    title: undefined,
    description: undefined,
  })

  // Do not provide a default icon background here; prefer the caller to pass one.
  const iconBgClass = raw.iconBgClass
  const overlayClass = raw.overlayClass
  const contentClass = raw.contentClass
  const title = raw.title
  const description = raw.description

  defineEmits<{
    backgroundClick: []
  }>()
</script>
