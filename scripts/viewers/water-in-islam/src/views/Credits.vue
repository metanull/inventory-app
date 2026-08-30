<script setup>
import { tHtml, dirFor } from '../composables/useUiStrings.js'
import BackLink from '../components/BackLink.vue'

// `exhibitionCredits` is one of the nine keys this exhibition owns — the rest
// of its catalogue is the shared MWNF Exhibitions layer (group 59). Legacy
// rendered this key and nothing else; the page title comes from the sub-banner.
</script>

<template>
  <div class="editorial">
    <BackLink />
    <div class="prose" :dir="dirFor('exhibitionCredits')" v-html="tHtml('exhibitionCredits')"></div>
  </div>
</template>

<style scoped>
.editorial { background: var(--secondary-color); width: 100%; min-height: 400px; padding-bottom: 40px; }
.prose { padding: 10px 50px 20px; max-width: 900px; line-height: 1.55; }
</style>
