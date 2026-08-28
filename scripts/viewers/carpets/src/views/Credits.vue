<script setup>
import { tHtml, dirFor } from '../composables/useUiStrings.js'
import BackLink from '../components/BackLink.vue'
</script>

<template>
  <div class="editorial">
    <BackLink />
    <div class="prose" :dir="dirFor('galleryCredits')" v-html="tHtml('galleryCredits')"></div>
  </div>
</template>

<style scoped>
.editorial { background: #fff; width: 100%; min-height: 400px; padding-bottom: 40px; }
.prose { padding: 10px 50px 20px; max-width: 900px; line-height: 1.55; }
</style>
