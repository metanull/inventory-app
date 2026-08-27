<script setup>
import { computed, ref } from 'vue'
import { RouterLink } from 'vue-router'
import {
  gallery, legacyImage, itemById, itemLabel, partnerLabel, countryLabel, tr, defaultLang,
} from '../composables/useGalleryData.js'
import { uiLang } from '../composables/useUiStrings.js'

// The home banner: the gallery's own banner image, captioned with the banner
// item's sheet. Legacy resolved both from `/thg/galleries/self`'s links[2..3];
// gallery.json carries the same two values as `banner_image_path` and
// `banner_item_id`.
//
// The banner image is the one place a package image is NOT an absolute URL: the
// gallery chrome lives on the legacy media server and was never imported, so
// the path ships and the host comes from config.
const imageUrl = computed(() => legacyImage(gallery.banner_image_path, 'hi_res'))
const bannerItem = computed(() => itemById.value.get(gallery.banner_item_id) ?? null)
const galleryName = computed(() => gallery.names?.[uiLang.value] ?? gallery.names?.en ?? '')

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

const hover = ref(false)
const failed = ref(false)
</script>

<template>
  <div id="banner-container" @mouseover="hover = true" @mouseleave="hover = false">
    <img v-if="imageUrl && !failed" :src="imageUrl" :alt="caption ? `Detail from ${caption.name}` : ''" @error="failed = true" />
    <div v-else class="banner-fallback"></div>

    <div id="banner-copyright" v-if="caption && hover">
      <span id="banner-copyright-name">Detail from {{ caption.name }}</span>
      <span v-if="caption.partner">{{ caption.partner }}</span>
      <span>{{ [caption.location, caption.country].filter(Boolean).join(', ') }}</span>
    </div>

    <div id="banner-text">
      <div id="banner-galleries-text">Discover MWNF Galleries</div>
      <div>
        {{ galleryName }}
        <RouterLink to="/collection" aria-label="Go to the collection">»</RouterLink>
      </div>
    </div>
  </div>
</template>

<style scoped>
#banner-container {
  position: relative;
  height: 45vh;
  max-height: 450px;
  width: 100%;
  background: var(--theme-dark);
  overflow: hidden;
}
#banner-container img { width: 100%; height: 100%; object-fit: cover; display: block; }
.banner-fallback {
  width: 100%;
  height: 100%;
  background: linear-gradient(160deg, var(--theme-dark), var(--theme-medium));
}
#banner-copyright {
  position: absolute;
  inset-inline-end: 0;
  bottom: 0;
  display: flex;
  flex-direction: column;
  max-width: 25%;
  color: rgba(255,255,255,0.85);
  font-size: 90%;
  font-style: italic;
  background: rgba(0,0,0,0.6);
  padding: 15px;
  margin: 15px;
}
#banner-copyright-name { font-weight: 700; margin-bottom: 10px; }
#banner-text {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: var(--light-text);
  font-size: 70px;
  font-weight: 700;
  text-align: center;
  text-shadow: 0 0 30px #000;
  padding: 0 50px;
}
#banner-galleries-text { font-size: 30px; }
#banner-text a { color: var(--light-text); text-decoration: none; padding-inline-start: 10px; }

@media only screen and (max-width: 974px) {
  #banner-text { font-size: 48px; }
  #banner-galleries-text { font-size: 24px; }
}
@media only screen and (max-width: 599px) {
  #banner-container { height: 180px; }
  #banner-text { font-size: 32px; }
  #banner-galleries-text { font-size: 18px; }
  #banner-copyright { display: none; }
}
</style>
