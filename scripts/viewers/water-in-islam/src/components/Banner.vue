<script setup>
import { computed, ref } from 'vue'
import { RouterLink } from 'vue-router'
import {
  exhibition, legacyImage, itemById, itemLabel, partnerLabel, countryLabel,
  tr, defaultLang, exhibitionTitle, exhibitionSubtitle, exhibitionHeadline,
  bannerCaption,
} from '../composables/useExhibitionData.js'
import { uiLang, t } from '../composables/useUiStrings.js'

// The exhibition home banner: image on the left, the exhibition's own curated
// chrome on the right — title, sub-title, the banner headline, and the ENTER
// link into /about. Legacy read the image from `/thg/galleries/self`'s
// `banner-image` link and the caption from its `banner-object`.
//
// The banner image is one of the few package values that is NOT an absolute
// URL: exhibition chrome lives on the legacy media server and was never
// imported, so the path ships and the host comes from config.
const imageUrl = computed(() => legacyImage(exhibition.banner_image_path, 'hi_res'))
const bannerItem = computed(() => itemById.value.get(exhibition.banner_item_id) ?? null)

const title = computed(() => exhibitionTitle(uiLang.value))
const subtitle = computed(() => exhibitionSubtitle(uiLang.value))
const headline = computed(() => exhibitionHeadline(uiLang.value))

// The caption legacy renders on hover. `banner_captions` is the curated line
// when there is one; otherwise it is assembled from the banner item's sheet,
// exactly as legacy's `objectInfo` was.
const curatedCaption = computed(() => bannerCaption(uiLang.value))

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

const hover = ref(false)
const failed = ref(false)
</script>

<template>
  <div id="banner-container" @mouseover="hover = true" @mouseleave="hover = false">
    <div id="banner-content-wrapper">
      <div id="banner-image-container">
        <img
          v-if="imageUrl && !failed"
          :src="imageUrl"
          :alt="caption ? `${t('detailCaption')} ${caption.name}` : title"
          @error="failed = true"
        />
        <div v-else class="banner-fallback"></div>

        <div id="banner-copyright" v-if="hover && (curatedCaption || caption)">
          <span v-if="curatedCaption">{{ curatedCaption }}</span>
          <template v-else-if="caption">
            <span id="banner-copyright-name">{{ t('detailCaption') }} {{ caption.name }}</span>
            <span>{{ [caption.partner, caption.location, caption.country].filter(Boolean).join(', ') }}</span>
          </template>
        </div>
      </div>

      <div class="banner-text-wrapper">
        <div class="banner-text">
          <div>{{ title }}</div>
          <div id="banner-subtitle">{{ subtitle }}</div>
        </div>
        <div id="banner-headline" v-html="headline"></div>
        <div id="banner-enter">
          <RouterLink to="/about">
            <div>ENTER</div>
            <div aria-hidden="true">↓</div>
          </RouterLink>
        </div>
        <div id="exhibition-tagline">A MWNF online exhibition.</div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Geometry from the legacy component: a 60/40 split, the image column at least
   700px tall, the text column dimmed over the same background image. */
#banner-container { width: 100%; }
#banner-content-wrapper { display: flex; width: 100%; }

#banner-image-container {
  position: relative;
  width: 60%;
  min-height: 700px;
  background: var(--main-color);
}
#banner-image-container img { width: 100%; height: 100%; object-fit: cover; display: block; }
.banner-fallback {
  width: 100%;
  height: 100%;
  min-height: 700px;
  background: linear-gradient(160deg, var(--main-color), var(--contrast-color));
}

#banner-copyright {
  position: absolute;
  top: 0;
  left: 0;
  z-index: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: flex-start;
  max-width: 35%;
  margin: 15px;
  padding: 15px;
  color: var(--main-text-color);
  font-size: 16px;
  font-style: italic;
}
#banner-copyright::before {
  content: '';
  position: absolute;
  inset: 0;
  z-index: -1;
  background: var(--main-color);
  opacity: 0.8;
}
#banner-copyright-name { font-weight: 700; }

.banner-text-wrapper {
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  width: 40%;
  padding: 50px 20px;
  background: var(--main-color);
}
.banner-text {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 100%;
  color: var(--contrast-color);
  text-align: center;
  font-weight: 700;
  font-size: calc(12px + 1.2vw);
}
#banner-subtitle { font-size: 65%; margin-top: 10px; }

#banner-headline {
  color: var(--main-text-color);
  font-size: 15px;
  line-height: 1.5;
  margin-top: 25px;
  max-width: 460px;
}
#banner-headline :deep(i) { font-style: italic; }
#banner-headline :deep(b) { font-weight: 700; }

#banner-enter { margin-top: 30px; }
#banner-enter a {
  display: flex;
  flex-direction: column;
  align-items: center;
  color: var(--contrast-color);
  font-weight: 700;
  font-size: 20px;
  text-decoration: none;
}
#banner-enter a:hover { text-decoration: underline; }

#exhibition-tagline {
  margin-top: 30px;
  color: var(--main-text-color);
  font-style: italic;
  font-size: 14px;
}

@media only screen and (max-width: 899px) {
  #banner-content-wrapper { flex-direction: column; }
  #banner-image-container { width: 100%; min-height: 300px; }
  .banner-fallback { min-height: 300px; }
  .banner-text-wrapper { width: 100%; }
}
</style>
