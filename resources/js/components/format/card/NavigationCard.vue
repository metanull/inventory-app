<template>
  <Card :title="title" :description="description" :main-color="mainColor">
    <template #icon>
      <slot name="icon" />
    </template>

    <template #footer>
      <!-- If a buttonAction is provided, render a regular button and allow a left icon via the button-icon slot -->
      <button v-if="buttonAction" type="button" :class="buttonClasses" @click="handleAction">
        <slot name="button-icon" />
        {{ buttonText }}
      </button>

      <!-- Default behaviour: router-link primary action with chevron -->
      <router-link v-else :to="buttonRoute ?? '#'" :class="buttonClasses">
        {{ buttonText }}
        <ChevronRightIcon class="ml-2 h-4 w-4" />
      </router-link>
    </template>
  </Card>
</template>

<script setup lang="ts">
  import { computed } from 'vue'
  import { ChevronRightIcon } from '@heroicons/vue/24/outline'
  import Card from './Card.vue'
  import { useColors, type ColorName } from '@/composables/useColors'

  const props = defineProps<{
    title: string
    description: string
    mainColor: ColorName
    buttonText: string
    buttonRoute?: string
    buttonAction?: () => unknown
  }>()

  const colorClasses = useColors(computed(() => props.mainColor))
  const buttonClasses = computed(
    () =>
      `${colorClasses.value.button} inline-flex items-center px-4 py-2 text-sm font-medium rounded-md transition-colors`
  )

  function handleAction() {
    if (props.buttonAction) {
      // allow async actions
      void Promise.resolve(props.buttonAction())
    }
  }
</script>
