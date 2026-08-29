<script setup>
import { computed } from 'vue'
import { exhibition } from '../composables/useExhibitionData.js'

// Legacy's LogosComponent renders the sponsor logos grouped by category, with
// the group heading coming from the i18n catalogue's `footer_logo_section_<n>`
// keys, each logo linked to its sponsor and captioned with its label.
//
// The package carries the image and the display order and nothing else:
// `mwnf3_thematic_gallery.exhibition_logo` has `label`, `link`, `category_id`
// and `visible`, and `ThgGalleryContentImporter` writes only `alt` and
// `display_order` because `collection_images` has no `extra` column. Story
// #1592 tracks the schema change.
//
// So this renders one ungrouped strip. It deliberately does NOT guess a
// category: Colours' single logo belongs to "Footer 2" ("Under the Patronage
// of"), and inventing that heading from the one row present would be a
// fabrication that silently breaks on the next exhibition.
const logos = computed(() =>
  [...(exhibition.logos ?? [])].sort(
    (a, b) => (a.display_order ?? 0) - (b.display_order ?? 0)
  )
)
</script>

<template>
  <div id="logos-container" v-if="logos.length">
    <div id="logos">
      <div class="logo-wrapper" v-for="logo in logos" :key="logo.id ?? logo.image_url">
        <img :src="logo.image_url" :alt="logo.alt_text ?? ''" />
      </div>
    </div>
  </div>
</template>

<style scoped>
#logos-container {
  width: 100%;
  color: var(--secondary-text-color);
  background: var(--secondary-color);
  border-bottom: 5px solid var(--secondary-color);
}
#logos {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: center;
  gap: 30px;
  padding: 25px 20px;
}
.logo-wrapper img { max-height: 70px; max-width: 240px; object-fit: contain; }
</style>
