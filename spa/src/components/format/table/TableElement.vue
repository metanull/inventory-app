<template>
  <table :class="['min-w-full', getThemeClass('dropdownBorder')]">
    <thead :class="[getThemeClass('modalActionsBg')]">
      <slot name="headers" />
    </thead>
    <tbody :class="['bg-white', getThemeClass('dropdownBorder')]">
      <slot name="rows" />
    </tbody>
  </table>
</template>

<script setup lang="ts">
  import { getThemeClass } from '@/composables/useColors'

  // Table element with thead and tbody sections; uses theme tokens for borders/backgrounds
</script>
