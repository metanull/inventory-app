<script setup>
import { RouterLink } from 'vue-router'
</script>

<template>
  <div class="editorial">
    <h1 class="page-heading">Page not found</h1>
    <div class="prose">
      <p>That page does not exist in this Gallery.</p>
      <p>
        Try the <RouterLink to="/collection">Collection</RouterLink>,
        the <RouterLink to="/partners">Partners</RouterLink>,
        or go back <RouterLink to="/">Home</RouterLink>.
      </p>
    </div>
  </div>
</template>

<style scoped>
.editorial { background: #fff; width: 100%; min-height: 400px; }
.prose { padding: 10px 40px 40px; }
.prose a { color: var(--link-blue); }
</style>
