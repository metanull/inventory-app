<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  themeByRouteId, themeText, themePictures, romanFor,
} from '../composables/useExhibitionData.js'
import { pictureParent } from '../composables/useThemePresentation.js'
import { sortChronological } from '../composables/useCollection.js'
import ObjectGrid from '../components/ObjectGrid.vue'
import BackLink from '../components/BackLink.vue'
import { uiLang } from '../composables/useUiStrings.js'

// Legacy's ThemeGallery: every record a theme touches, as one grid, with a
// dropdown that narrows it to a single sub-theme.
//
// Legacy built the query by looping over the theme's *sub-themes* only
// (`for (let subtheme in this.subthemes) addURL += ...`), which means a theme
// with no sub-themes produced the literal string "undefined" and an empty
// grid. Here the default is the theme's own selections **plus** its
// sub-themes', so the page works in both shapes; for the four themes that do
// have sub-themes the extra rows are the overview pictures, which legacy's
// dropdown had no option for either way.
const route = useRoute()
const router = useRouter()

const theme = computed(() => themeByRouteId(route.params.id))

const subIndex = computed(() => {
  const raw = route.params.subtheme
  if (!raw) return null
  const n = Number(raw)
  return Number.isInteger(n) && n >= 1 ? n : null
})

const subThemes = computed(() =>
  (theme.value?.sub_themes ?? []).map((sub, index) => ({
    index: index + 1,
    id: sub.id,
    title: themeText(sub, uiLang.value).title ?? sub.internal_name ?? '',
    node: sub,
  }))
)

const selectedSub = computed(() =>
  subIndex.value === null ? null : subThemes.value[subIndex.value - 1] ?? null
)

const sourceNodes = computed(() => {
  if (!theme.value) return []
  if (selectedSub.value) return [selectedSub.value.node]
  return [theme.value, ...(theme.value.sub_themes ?? [])]
})

// A theme selects pictures; the grid shows records. Two selections can share a
// parent (a different crop of the same object), which is why legacy deduplicated
// by thumbnail — deduplicating by parent id is the same rule stated on the
// entity the grid actually renders.
const results = computed(() => {
  const seen = new Set()
  const out = []
  for (const node of sourceNodes.value) {
    for (const picture of themePictures(node)) {
      const parent = pictureParent(picture)
      if (!parent || seen.has(parent.id)) continue
      seen.add(parent.id)
      out.push(parent)
    }
  }
  return sortChronological(out)
})

const title = computed(() => themeText(theme.value, uiLang.value).title ?? theme.value?.internal_name ?? '')
const roman = computed(() => romanFor(theme.value?.display_order ?? 1))

function onSubThemeChange(event) {
  const value = event.target.value
  router.push(
    value
      ? `/theme-gallery/${route.params.id}/${value}`
      : `/theme-gallery/${route.params.id}`
  )
}
</script>

<template>
  <div id="theme-gallery-container" v-if="theme">
    <BackLink />

    <div id="theme-gallery-header">
      <span>
        Theme {{ roman }} | <span class="bold">{{ title }}</span>
        <span v-if="selectedSub" class="bold"> ▪ {{ selectedSub.title }}</span> | Gallery
      </span>
    </div>

    <div id="theme-gallery-content">
      <div id="theme-gallery-objects">
        <ObjectGrid v-if="results.length" :results="results" />
        <p v-else class="no-results">This theme has no records in the exhibition.</p>
      </div>

      <div class="subtheme-filter" v-if="subThemes.length">
        <div class="options-label">Filter by Subtheme:</div>
        <select class="legacy-select" :value="subIndex ?? ''" @change="onSubThemeChange">
          <option value="">Select Subtheme</option>
          <option v-for="sub in subThemes" :key="sub.id" :value="sub.index">
            {{ sub.index }}. {{ sub.title }}
          </option>
        </select>
        <div class="reset-container" v-if="subIndex">
          <button class="legacy-button" @click="router.push(`/theme-gallery/${route.params.id}`)">
            See all Items in this Theme
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="loader" v-else>This theme is not part of the exhibition.</div>
</template>

<style scoped>
#theme-gallery-container {
  background: var(--secondary-color);
  width: 100%;
  min-height: 400px;
  padding-bottom: 30px;
}
#theme-gallery-header {
  padding: 10px 30px 20px;
  font-size: 18px;
}
.bold { font-weight: 700; }

#theme-gallery-content { display: flex; gap: 20px; padding: 0 20px; }
#theme-gallery-objects { flex: 1; min-width: 0; }
.subtheme-filter { width: 260px; padding-top: 10px; }
.options-label { font-weight: 700; }
.reset-container { padding-top: 12px; }
.no-results { padding: 30px 10px; font-style: italic; }

@media only screen and (max-width: 974px) {
  #theme-gallery-content { flex-direction: column-reverse; }
  .subtheme-filter { width: 100%; }
}
</style>
