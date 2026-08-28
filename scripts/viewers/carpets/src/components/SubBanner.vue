<script setup>
import { computed, ref } from 'vue'
import { useRoute } from 'vue-router'
import {
  gallery, legacyImage, itemById, itemLabel, partnerLabel, countryLabel, tr, defaultLang,
} from '../composables/useGalleryData.js'

// The narrow banner shown on every page but Home, with the section title
// overlaid. Section titles are the legacy client's own literals (SubBanner.vue
// `setHeader`) — they never came from the i18n catalogue.
const route = useRoute()

const header = computed(() => {
  const path = route.path
  if (path.startsWith('/collection')) return 'Collection'
  if (path.startsWith('/database-item') || path.startsWith('/search')) return 'Database'
  if (path.startsWith('/partner')) return 'Partners & Contributors'
  if (path.startsWith('/timeline')) return 'Timeline'
  if (path.startsWith('/how-to-search')) return 'Database'
  if (path.startsWith('/about')) return 'About'
  if (path.startsWith('/credits')) return 'Credits'
  return 'Error'
})

const imageUrl = computed(() => legacyImage(gallery.banner_image_path, 'hi_res'))
const bannerItem = computed(() => itemById.value.get(gallery.banner_item_id) ?? null)
const failed = ref(false)

const caption = computed(() => {
  const item = bannerItem.value
  if (!item) return null
  const t = tr('items', item.id, defaultLang)
  return {
    name: itemLabel(item),
    partner: partnerLabel(item.partner_id),
    location: t.location ?? '',
    country: countryLabel(item.country_id),
  }
})
</script>

<template>
  <div id="sub-banner-image-container">
    <img v-if="imageUrl && !failed" :src="imageUrl" :alt="caption ? `Detail from ${caption.name}` : ''" @error="failed = true" />
    <div v-else class="sub-banner-fallback"></div>
    <div id="sub-banner-overlay">
      <div id="sub-banner-overlay-text">{{ header }}</div>
      <div id="sub-banner-copyright" v-if="caption">
        <span id="sub-banner-copyright-name">Detail from {{ caption.name }}</span>
        <span>{{ [caption.partner, caption.location, caption.country].filter(Boolean).join(', ') }}</span>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Geometry taken from the legacy component: a 20vh strip capped at 150px, with
   a translucent theme-coloured bar pinned to its bottom edge. */
#sub-banner-image-container {
  position: relative;
  height: 20vh;
  min-height: 50px;
  max-height: 150px;
  width: 100%;
  overflow: hidden;
  background: var(--theme-dark);
}
#sub-banner-image-container img { width: 100%; height: 100%; object-fit: cover; object-position: center; display: block; }
.sub-banner-fallback { width: 100%; height: 100%; background: linear-gradient(160deg, var(--theme-dark), var(--theme-medium)); }
#sub-banner-overlay {
  position: absolute;
  bottom: 0;
  inset-inline: 0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  min-height: 50px;
  background: rgba(var(--theme-dark-rgb), 0.6);
}
#sub-banner-overlay-text {
  color: var(--light-text);
  font-size: 190%;
  padding: 10px 10px 10px 50px;
}
#sub-banner-copyright {
  display: flex;
  flex-direction: column;
  justify-content: center;
  color: rgba(255,255,255,0.8);
  font-size: 90%;
  font-style: italic;
  padding-inline-end: 50px;
  text-align: end;
}
#sub-banner-copyright-name { font-weight: 700; }

@media only screen and (max-width: 724px) {
  #sub-banner-copyright { display: none; }
  #sub-banner-image-container { max-height: 100px; }
  #sub-banner-overlay { min-height: 40px; }
  #sub-banner-overlay-text { font-size: 145%; padding: 5px 5px 5px 50px; }
}
@media only screen and (max-width: 599px) {
  #sub-banner-image-container { max-height: 80px; }
  #sub-banner-overlay { min-height: 35px; }
  #sub-banner-overlay-text { font-size: 130%; padding: 5px 5px 5px 30px; }
}
</style>
