<script setup>
import { tHtml, dirFor } from '../composables/useUiStrings.js'
import BackLink from '../components/BackLink.vue'

// `galleryAbout` is one of the two keys the Carpets site actually owns — the
// rest of its catalogue is the shared MWNF Galleries layer. Legacy rendered
// this key and nothing else; the page title comes from the sub-banner.
</script>

<template>
  <div class="editorial">
    <BackLink />
    <div class="prose" :dir="dirFor('galleryAbout')" v-html="tHtml('galleryAbout')"></div>
  </div>
</template>

<style scoped>
.editorial { background: #fff; width: 100%; min-height: 400px; padding-bottom: 40px; }
.prose { padding: 10px 50px 20px; max-width: 900px; line-height: 1.55; }
</style>
