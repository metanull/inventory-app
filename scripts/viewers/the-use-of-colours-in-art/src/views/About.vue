<script setup>
import Theme from './Theme.vue'

// Legacy's AboutPage is a one-line wrapper around ThemeComponent: /about is the
// About theme (display order 1) rendered with the exhibition's own title and
// sub-title standing in for the theme's.
//
// It is NOT `exhibition.json.abouts` — that field carries `exhibition_i18n.about`
// and is empty for this exhibition, exactly as the live API's `exhibitionAbout`
// is null. The introduction the visitor reads is theme 0's presentation and its
// seven curated pictures.
</script>

<template>
  <div id="about-container">
    <Theme about-mode />
  </div>
</template>

<style scoped>
#about-container {
  display: flex;
  justify-content: center;
  width: 100%;
  background: var(--secondary-color);
}
#about-container > * { width: 100%; }
</style>
