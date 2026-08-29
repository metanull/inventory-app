<script setup>
import { computed, ref } from 'vue'
import { useRoute } from 'vue-router'
import {
  exhibition, legacyImage, itemById, itemLabel, partnerLabel, countryLabel,
  tr, defaultLang, bannerCaption,
} from '../composables/useExhibitionData.js'
import { uiLang, t } from '../composables/useUiStrings.js'

// The narrow banner shown on every page but Home, with the section title
// overlaid. Section titles are the legacy client's own literals (SubBanner.vue
// `setHeader`) — they never came from the i18n catalogue.
const route = useRoute()

const header = computed(() => {
  const path = route.path
  if (path.startsWith('/themes') || path.startsWith('/theme')) return 'Themes'
  if (path.startsWith('/collection')) return 'Collection'
  if (path.startsWith('/database-item') || path.startsWith('/search')) return 'Database'
  if (path.startsWith('/how-to-search')) return 'Database'
  if (path.startsWith('/partner') || path.startsWith('/institution')) return 'Partners & Contributors'
  if (path.startsWith('/related')) return 'Related Content'
  if (path.startsWith('/timeline')) return 'Timeline'
  if (path.startsWith('/about')) return 'About'
  if (path.startsWith('/credits')) return 'Credits'
  return 'Error'
})

const imageUrl = computed(() => legacyImage(exhibition.banner_image_path, 'hi_res'))
const bannerItem = computed(() => itemById.value.get(exhibition.banner_item_id) ?? null)
const curatedCaption = computed(() => bannerCaption(uiLang.value))
const failed = ref(false)

const caption = computed(() => {
  const item = bannerItem.value
  if (!item) return null
  const record = tr('items', item.id, defaultLang)
  return {
    name: itemLabel(item),
    partner: partnerLabel(item.partner_id),
    location: record.location ?? '',
    country: countryLabel(item.country_id),
  }
})
</script>

<template>
  <div id="sub-banner-image-container">
    <img
      v-if="imageUrl && !failed"
      :src="imageUrl"
      :alt="caption ? `${t('detailCaption')} ${caption.name}` : header"
      @error="failed = true"
    />
    <div v-else class="sub-banner-fallback"></div>
    <div id="sub-banner-overlay">
      <div id="sub-banner-overlay-text">{{ header }}</div>
      <div id="sub-banner-copyright" v-if="curatedCaption || caption">
        <span v-if="curatedCaption">{{ curatedCaption }}</span>
        <template v-else-if="caption">
          <span id="sub-banner-copyright-name">{{ t('detailCaption') }} {{ caption.name }}</span>
          <span>{{ [caption.partner, caption.location, caption.country].filter(Boolean).join(', ') }}</span>
        </template>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Geometry taken from the legacy component: a 20vh strip capped at 150px, with
   a translucent contrast-coloured bar pinned to its bottom edge. */
#sub-banner-image-container {
  position: relative;
  height: 20vh;
  min-height: 50px;
  max-height: 150px;
  width: 100%;
  overflow: hidden;
  background: var(--main-color);
}
#sub-banner-image-container img { width: 100%; height: 100%; object-fit: cover; object-position: center; display: block; }
.sub-banner-fallback { width: 100%; height: 100%; background: linear-gradient(160deg, var(--main-color), var(--contrast-color)); }

#sub-banner-overlay {
  position: absolute;
  bottom: 0;
  z-index: 1;
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  min-height: 50px;
}
#sub-banner-overlay::before {
  content: '';
  position: absolute;
  inset: 0;
  z-index: -1;
  background: var(--contrast-color);
  opacity: 0.6;
}
#sub-banner-overlay-text {
  color: var(--contrast-text-color);
  padding: 10px 10px 10px 50px;
  font-size: clamp(20px, 3vw, 38px);
}
#sub-banner-copyright {
  display: flex;
  flex-direction: column;
  justify-content: center;
  max-width: 600px;
  padding-right: 50px;
  color: var(--contrast-text-color);
  font-size: 15px;
  font-style: italic;
  text-align: right;
}
#sub-banner-copyright-name { font-weight: 700; }

@media only screen and (max-width: 849px) {
  #sub-banner-overlay-text { padding-left: 20px; }
  #sub-banner-copyright { display: none; }
}
</style>
