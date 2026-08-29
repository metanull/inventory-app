<script setup>
import { ref, computed } from 'vue'
import { RouterLink } from 'vue-router'
import {
  listedThemes, themeText, themeRouteId, romanFor, mdStrip,
} from '../composables/useExhibitionData.js'
import { themeCover, pictureCaption, truncate } from '../composables/useThemePresentation.js'
import { uiLang, t } from '../composables/useUiStrings.js'

// Legacy's ThemesPage: an accordion of the exhibition's top-level themes, each
// collapsed to a strip of four crops of its cover and expanded to cover +
// caption, a 250-character extract of the presentation, the sub-theme list and
// a link into the theme's gallery.
//
// It starts at index 1 of the API's `?bt=1` response, skipping the About theme
// — `listedThemes` is that same set, selected by display order rather than by
// array position.
const themes = computed(() =>
  listedThemes.value.map(theme => {
    const cover = themeCover(theme)
    const text = themeText(theme, uiLang.value)
    return {
      theme,
      routeId: themeRouteId(theme),
      roman: romanFor(theme.display_order),
      title: text.title ?? theme.internal_name ?? '',
      presentation: truncate(250, mdStrip(text.presentation ?? '')),
      coverUrl: cover?.image_url ?? null,
      coverCaption: cover ? pictureCaption(cover) : '',
      subThemes: (theme.sub_themes ?? []).map(sub => ({
        id: sub.id,
        title: themeText(sub, uiLang.value).title ?? sub.internal_name ?? '',
      })),
    }
  })
)

// Legacy keeps one theme open at a time, plus a "show all" toggle that expands
// every theme and disables the per-theme click.
const showAll = ref(false)
const openIndex = ref(null)

function toggle(index) {
  if (showAll.value) return
  openIndex.value = openIndex.value === index ? null : index
}

function isOpen(index) {
  return showAll.value || openIndex.value === index
}
</script>

<template>
  <div id="themes-wrapper">
    <div id="themes-container">
      <div class="theme-show-all" @click="showAll = !showAll; openIndex = null">
        <span>{{ showAll ? t('collapseThemes') : t('expandThemes') }}</span>
      </div>

      <div class="theme-container" v-for="(entry, index) in themes" :key="entry.theme.id">
        <div
          class="theme-title"
          :class="{ 'theme-title-no-cursor': showAll }"
          @click="toggle(index)"
        >
          <span>
            Theme {{ entry.roman }} ▪ {{ entry.title }}
            <span v-if="!showAll" aria-hidden="true">{{ isOpen(index) ? '▴' : '▾' }}</span>
          </span>
        </div>

        <!-- Collapsed: legacy shows the same cover four times, cropped to a
             different band of the image in each slot. -->
        <div class="theme-collapsed-thumbnail" v-if="!isOpen(index) && entry.coverUrl">
          <img v-for="n in 4" :key="n" :src="entry.coverUrl" :alt="entry.coverCaption" loading="lazy" />
        </div>

        <div class="theme-image-information-wrapper" v-if="isOpen(index)">
          <div class="theme-image">
            <img v-if="entry.coverUrl" :src="entry.coverUrl" :alt="entry.coverCaption" loading="lazy" />
            <div class="theme-image-caption" v-if="entry.coverCaption">{{ entry.coverCaption }}</div>
          </div>

          <div class="theme-information-container">
            <div class="theme-description">
              <span>{{ entry.presentation }}</span>
              <RouterLink
                class="theme-list-overview theme-list-link"
                :to="`/theme/${entry.routeId}/overview`"
              >See more</RouterLink>
            </div>

            <div class="theme-subthemes-container" v-if="entry.subThemes.length">
              <div class="theme-subthemes-header">In This Theme</div>
              <div
                v-for="(sub, subIndex) in entry.subThemes"
                :key="sub.id"
                class="theme-list-subthemes"
              >
                <RouterLink class="theme-list-link" :to="`/theme/${entry.routeId}/${subIndex + 1}`">
                  {{ subIndex + 1 }}. {{ sub.title }}
                </RouterLink>
              </div>
            </div>

            <div class="theme-gallery-link">
              <RouterLink :to="`/theme-gallery/${entry.routeId}`">
                See Gallery for Theme {{ entry.roman }}
              </RouterLink>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Legacy paints the left half of this page in the main colour and lays the
   content over it — the panel that makes the theme cards read as inset. */
#themes-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  background: var(--secondary-color);
}
#themes-wrapper::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  height: 100%;
  width: 50%;
  background: var(--main-color);
}
#themes-container {
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 95%;
  padding: 30px 50px 50px;
}

.theme-show-all {
  display: flex;
  justify-content: flex-end;
  width: 100%;
  padding: 0 20px 10px;
  color: var(--secondary-text-color);
  cursor: pointer;
}

.theme-container {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  width: 100%;
  padding: 20px;
  margin-bottom: 10px;
}

.theme-title {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  padding: 10px;
  font-size: 20px;
  font-weight: 700;
  text-align: center;
  background: var(--contrast-color);
  cursor: pointer;
}
.theme-title span { color: var(--contrast-text-color); }
.theme-title-no-cursor { cursor: default; }

.theme-collapsed-thumbnail {
  display: flex;
  height: 70px;
  width: 100%;
  background: var(--secondary-color);
}
.theme-collapsed-thumbnail img { width: 25%; object-fit: cover; }
.theme-collapsed-thumbnail img:nth-child(1) { object-position: top; }
.theme-collapsed-thumbnail img:nth-child(2) { object-position: 50% 25%; }
.theme-collapsed-thumbnail img:nth-child(3) { object-position: 50% 50%; }
.theme-collapsed-thumbnail img:nth-child(4) { object-position: bottom; }

.theme-image-information-wrapper { display: flex; width: 100%; }
.theme-image {
  display: flex;
  flex-direction: column;
  max-height: 420px;
  width: 50%;
  padding-right: 30px;
  margin-top: 30px;
}
.theme-image img { width: 100%; object-fit: cover; max-height: 380px; }
.theme-image-caption {
  padding: 5px 0;
  color: var(--main-text-color);
  font-style: italic;
}

.theme-information-container {
  display: flex;
  flex: 1;
  flex-direction: column;
  color: var(--secondary-text-color);
}
.theme-description,
.theme-subthemes-container { margin: 30px 30px 0; }
.theme-subthemes-header { font-weight: 700; }
.theme-list-link { color: var(--contrast-text-color); text-decoration: none; }
.theme-list-overview { font-weight: 700; text-decoration: underline; margin-left: 6px; }
.theme-list-link:hover { background: var(--contrast-color); }
.theme-gallery-link {
  margin: 30px 30px 0;
  font-style: italic;
}
.theme-gallery-link a { color: var(--secondary-text-color); text-decoration: none; }
.theme-gallery-link a:hover { background: var(--contrast-color); }

@media only screen and (max-width: 1199px) {
  #themes-container { width: 100%; padding: 30px; }
}

@media only screen and (max-width: 899px) {
  .theme-collapsed-thumbnail img { width: 50%; }
  .theme-collapsed-thumbnail img:nth-child(2) { object-position: 50% 50%; }
  .theme-collapsed-thumbnail img:nth-child(3),
  .theme-collapsed-thumbnail img:nth-child(4) { display: none; }
}

@media only screen and (max-width: 649px) {
  #themes-wrapper::before { background: transparent; }
  .theme-image-information-wrapper { flex-direction: column; }
  .theme-image { width: 100%; padding: 20px 0; margin: 0; background: var(--main-color); }
  .theme-description,
  .theme-subthemes-container,
  .theme-gallery-link { margin: 20px 0 0; }
}
</style>
