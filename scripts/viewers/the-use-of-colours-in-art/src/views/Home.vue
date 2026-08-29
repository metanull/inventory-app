<script setup>
// Legacy's home route mounts four named views — banner, navigation, bottom
// banner and logos — and nothing in the default slot. All four live in
// App.vue here, so the page itself is empty by design rather than by omission.
//
// This is where an exhibition differs from a gallery: a gallery home page
// carries a featured-partners carousel and a sibling-galleries strip, and an
// exhibition home page is the banner alone.
</script>

<template>
  <div class="home-spacer"></div>
</template>

<style scoped>
.home-spacer { width: 100%; }
</style>
