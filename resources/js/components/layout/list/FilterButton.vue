<template>
  <button
    :class="[
      isActive ? activeClasses : inactiveClasses,
      'px-3 py-2 font-medium text-sm rounded-md',
    ]"
    @click="$emit('click')"
  >
    {{ label }}
    <!-- Count pill/text responsive display -->
    <span v-if="count !== undefined" :class="pillClasses">
      <!-- Pill on md+, text on sm+ -->
      <span class="md:hidden">({{ count }})</span>
      <span class="hidden md:inline">{{ count }}</span>
    </span>
  </button>
</template>

<script setup lang="ts">
  import { computed } from 'vue'
  import { useColors, type ColorName } from '@/composables/useColors'
  import { getThemeClass } from '@/composables/useColors'

  const props = defineProps<{
    label: string
    isActive: boolean
    count?: number
    variant?: 'primary' | 'success' | 'info'
    color?: ColorName
  }>()

  defineEmits<{
    click: []
  }>()

  // Color classes from centralized system
  const colorClasses = useColors(computed(() => props.color || 'indigo'))

  const activeClasses = computed(() => {
    // If color prop is provided, use centralized color system
    if (props.color) {
      return `${colorClasses.value.badgeBackground} ${colorClasses.value.badgeText}`
    }

    // Use centralized color names matching previous hardcoded fallbacks
    const variantColorName =
      props.variant === 'success' ? 'green' : props.variant === 'info' ? 'blue' : 'indigo'
    const vColors = useColors(variantColorName)
    return `${vColors.value.badgeBackground} ${vColors.value.badgeText}`
  })

  const inactiveClasses = getThemeClass('navLinkColor')

  const pillClasses = computed(() => {
    const base = [
      'ml-1',
      'hidden sm:inline',
      'md:inline-flex md:items-center md:justify-center md:px-2 md:py-0.5 md:text-xs md:font-semibold md:rounded-full',
    ]
    // If color prop provided use its classes
    if (props.color) {
      base.push(`md:${colorClasses.value.badgeBackground}`, `md:${colorClasses.value.badgeText}`)
      return base.join(' ')
    }

    // Preserve original fallback behavior: zero -> orange, non-zero -> blue
    if (props.count === 0) {
      const vColors = useColors('orange')
      base.push(`md:${vColors.value.badgeBackground}`, `md:${vColors.value.badgeText}`)
      return base.join(' ')
    }

    const vColors = useColors('blue')
    base.push(`md:${vColors.value.badgeBackground}`, `md:${vColors.value.badgeText}`)
    return base.join(' ')
  })
</script>
