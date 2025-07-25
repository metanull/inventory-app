<template>
  <table class="min-w-full divide-y divide-gray-300">
    <thead class="bg-gray-50">
      <slot name="headers" />
    </thead>
    <tbody class="divide-y divide-gray-200 bg-white">
      <slot name="rows" />
    </tbody>
  </table>
</template>

<script setup lang="ts">
  // Table element with thead and tbody sections
</script>
