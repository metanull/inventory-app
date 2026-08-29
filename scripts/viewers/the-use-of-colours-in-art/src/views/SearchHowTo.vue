<script setup>
import { tHtml, dirFor } from '../composables/useUiStrings.js'
import BackLink from '../components/BackLink.vue'

// Editorial page, straight from the site-i18n catalogue (`searchHowTo`).
// The whole essay is about MySQL boolean full-text operators, which is why
// useCollection.js implements that grammar rather than a plain substring match.
</script>

<template>
  <div class="editorial">
    <BackLink />
    <div class="prose" :dir="dirFor('searchHowTo')" v-html="tHtml('searchHowTo')"></div>
  </div>
</template>

<style scoped>
.editorial { background: #fff; width: 100%; min-height: 400px; padding-bottom: 40px; }
.prose { padding: 10px 50px 20px; max-width: 900px; line-height: 1.55; }
</style>
