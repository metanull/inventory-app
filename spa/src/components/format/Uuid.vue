<template>
  <span :class="className" :title="fullUuid">
    {{ displayedUuid }}
  </span>
</template>

<script setup lang="ts">
  import { computed } from 'vue'

  const props = defineProps<{
    uuid: string | null | undefined
    format?: 'short' | 'long'
    length?: number
    className?: string
  }>()

  const displayedUuid = computed(() => {
    if (!props.uuid) return 'N/A'

    const trimmedUuid = props.uuid.trim()
    if (!trimmedUuid) return 'N/A'

    // Use format parameter with 'short' as default
    const format = props.format || 'short'

    if (format === 'long') {
      return trimmedUuid
    } else {
      // Short format: default to 8 characters like Git short hash
      const shortLength = props.length || 8
      return trimmedUuid.substring(0, shortLength)
    }
  })

  const fullUuid = computed(() => {
    if (!props.uuid) return 'No UUID available'

    const trimmedUuid = props.uuid.trim()
    if (!trimmedUuid) return 'No UUID available'

    return trimmedUuid
  })
</script>
