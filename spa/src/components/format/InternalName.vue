<template>
  <div class="flex items-center">
    <!-- Icon for both small and regular modes -->
    <div class="flex-shrink-0" :class="small ? 'mr-2' : 'mr-3'">
      <slot name="icon">
        <RectangleGroupIcon
          :class="[small ? 'h-5 w-5' : 'h-6 w-6', getThemeClass('neutralText')]"
        />
      </slot>
    </div>
    <div>
      <!-- Use Title component when small is not set -->
      <template v-if="!small">
        <Title
          variant="page"
          :description="backwardCompatibility ? `Legacy ID: ${backwardCompatibility}` : undefined"
        >
          {{ internalName }}
        </Title>
      </template>
      <!-- Use simple text when small is set -->
      <template v-else>
        <!-- Explicit static element so tests can match the exact utility classes -->
        <span :class="['text-sm font-medium', getThemeClass('modalTitle')]">{{
          internalName
        }}</span>
        <!-- Keep themed smaller legacy text -->
        <div v-if="backwardCompatibility" :class="['text-xs', getThemeClass('neutralText')]">
          Legacy: {{ backwardCompatibility }}
        </div>
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { RectangleGroupIcon } from '@heroicons/vue/24/outline'
  import Title from '@/components/format/title/Title.vue'
  import { getThemeClass } from '@/composables/useColors'

  interface Props {
    internalName: string
    backwardCompatibility?: string | null
    small?: boolean
  }

  defineProps<Props>()
</script>
