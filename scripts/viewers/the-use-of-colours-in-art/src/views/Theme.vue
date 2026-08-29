<script setup>
import { computed, ref, watch, onMounted } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import {
  themes, aboutTheme, themeByRouteId, themeRouteId, themeText, pictureText,
  themePictures, itemRoute, itemLabel, tr, defaultLang, md, mdInline,
  exhibitionTitle, exhibitionSubtitle,
} from '../composables/useExhibitionData.js'
import { pictureParent, itemDetailString } from '../composables/useThemePresentation.js'
import { termsForText, linkGlossary } from '../composables/useGlossary.js'
import { uiLang, t } from '../composables/useUiStrings.js'

// The theme page — legacy's ThemeComponent, which also serves /about by
// rendering the About theme (display order 1) with the exhibition's own title
// and sub-title in place of the theme's.
//
// URL shape, kept from legacy so a legacy link still resolves:
//   /theme/:id/:subtheme?/:image?
//     :id       display_order - 1
//     :subtheme the literal `overview`, or a 1-based index into sub_themes
//     :image    the selection's display_order, which the importer sets to the
//               legacy theme_item id — so `?image=5` picks the same picture it
//               picked on the live site.
const props = defineProps({
  aboutMode: { type: Boolean, default: false },
})

const route = useRoute()
const router = useRouter()

const theme = computed(() =>
  props.aboutMode ? aboutTheme.value : themeByRouteId(route.params.id)
)

const subIndex = computed(() => {
  const raw = route.params.subtheme
  if (props.aboutMode || !raw || raw === 'overview') return null
  const n = Number(raw)
  return Number.isInteger(n) && n >= 1 ? n : null
})

const subTheme = computed(() => {
  if (subIndex.value === null) return null
  return (theme.value?.sub_themes ?? [])[subIndex.value - 1] ?? null
})

/** The node whose text and pictures the page shows: a sub-theme, or the theme. */
const node = computed(() => subTheme.value ?? theme.value)

const pictures = computed(() => themePictures(node.value))

// ── Related works ──────────────────────────────────────────────────────────
//
// `related` hangs off the source picture and names its target. Legacy derived
// two directions from the same table: a source shows its targets under "Related
// items", and a target shows the source it belongs to with the reciprocal text.
// A target is hidden from the thumbnail strip until "Add Related Works" is on,
// which is what keeps the strip to the curator's primary selection.
const relatedTo = computed(() => {
  const map = new Map()
  const inNode = new Set(pictures.value.map(p => p.picture_item_id))
  for (const source of pictures.value) {
    for (const link of source.related ?? []) {
      if (!inNode.has(link.picture_item_id)) continue
      map.set(link.picture_item_id, { source, link })
    }
  }
  return map
})

const hasRelated = computed(() => relatedTo.value.size > 0)

const showAll = ref(false)

const strip = computed(() =>
  showAll.value
    ? pictures.value
    : pictures.value.filter(p => !relatedTo.value.has(p.picture_item_id))
)

// ── Selection ──────────────────────────────────────────────────────────────

const selectedId = ref(null)

function defaultSelection() {
  const wanted = Number(route.params.image)
  const byOrder = pictures.value.find(p => p.display_order === wanted)
  return (byOrder ?? pictures.value[0])?.picture_item_id ?? null
}

function reset() {
  showAll.value = false
  selectedId.value = defaultSelection()
}

onMounted(reset)
watch(() => [route.params.id, route.params.subtheme, props.aboutMode], reset)

const selected = computed(
  () => pictures.value.find(p => p.picture_item_id === selectedId.value) ?? null
)

function select(picture) {
  selectedId.value = picture.picture_item_id
  if (!props.aboutMode) {
    router.replace({
      name: 'theme',
      params: {
        id: route.params.id,
        subtheme: route.params.subtheme ?? 'overview',
        image: String(picture.display_order),
      },
    })
  }
}

// ── Text ───────────────────────────────────────────────────────────────────

const nodeText = computed(() => themeText(node.value, uiLang.value))
const themeTitle = computed(
  () => themeText(theme.value, uiLang.value).title ?? theme.value?.internal_name ?? ''
)

const heading = computed(() =>
  props.aboutMode ? exhibitionTitle(uiLang.value) : themeTitle.value
)

const subHeading = computed(() => {
  if (props.aboutMode) return exhibitionSubtitle(uiLang.value)
  if (subTheme.value) return nodeText.value.title ?? subTheme.value.internal_name ?? ''
  return 'Overview'
})

// Glossary linking. Legacy asked the API which terms occur in each block and
// rewrote the HTML in place; here the same match runs against the package's own
// glossary — see `termsForText`.
function withGlossary(text) {
  if (!text) return ''
  const html = md(text)
  return linkGlossary(html, termsForText(text, uiLang.value), uiLang.value)
}

const quoteHtml = computed(() => withGlossary(nodeText.value.quote))
const presentationHtml = computed(() => withGlossary(nodeText.value.presentation))

const selectedText = computed(() =>
  selected.value ? pictureText(node.value, selected.value, uiLang.value) : {}
)
const contextualHtml = computed(() => withGlossary(selectedText.value.contextual_description))

// The glossary pop-up. One definition at a time, as legacy did.
const openTerm = ref(null)
const terms = computed(() => {
  const map = new Map()
  for (const source of [nodeText.value.quote, nodeText.value.presentation, selectedText.value.contextual_description]) {
    for (const term of termsForText(source, uiLang.value)) map.set(term.id, term)
  }
  return map
})

function onProseClick(event) {
  const link = event.target.closest?.('a.glossary-link')
  if (!link) return
  event.preventDefault()
  openTerm.value = terms.value.get(link.dataset.term) ?? null
}

// ── The picture being shown ────────────────────────────────────────────────

function captionFor(picture) {
  const parent = pictureParent(picture)
  const text = pictureText(node.value, picture, uiLang.value)
  return {
    imageCaption: text.image_caption ?? '',
    name: parent ? itemLabel(parent) : '',
    detail: itemDetailString(parent),
    parent,
  }
}

const selectedCaption = computed(() => (selected.value ? captionFor(selected.value) : null))

const selectedSheet = computed(() => {
  const parent = selectedCaption.value?.parent
  return parent ? tr('items', parent.id, defaultLang) : {}
})

/** The related targets of the selected picture, if it is a source. */
const selectedTargets = computed(() => {
  const own = selected.value?.related ?? []
  const byId = new Map(pictures.value.map(p => [p.picture_item_id, p]))
  return own
    .map(link => ({ link, picture: byId.get(link.picture_item_id) }))
    .filter(entry => entry.picture)
})

/** The source of the selected picture, if it is itself a related target. */
const selectedSource = computed(() => relatedTo.value.get(selectedId.value) ?? null)

function relationText(link) {
  return link?.descriptions?.[uiLang.value] ?? link?.descriptions?.en ?? ''
}

function reciprocalText(link) {
  return link?.reciprocal_descriptions?.[uiLang.value] ?? link?.reciprocal_descriptions?.en ?? ''
}

// ── Previous / next ────────────────────────────────────────────────────────
//
// A verbatim port of legacy's `themeNavigation`: the tour runs About → theme 1
// overview → its sub-themes → theme 2 overview → …, and back the same way.

function subCount(routeId) {
  return (themeByRouteId(routeId)?.sub_themes ?? []).length
}

const lastRouteId = computed(() =>
  themes.value.length ? themeRouteId(themes.value[themes.value.length - 1]) : 0
)

const previous = computed(() => {
  if (props.aboutMode) return null
  const id = Number(route.params.id)
  const sub = subIndex.value
  if (id === 1 && sub === null) return '/about'
  if (sub === 1) return `/theme/${id}/overview`
  if (sub === null) {
    const count = subCount(id - 1)
    return count ? `/theme/${id - 1}/${count}` : `/theme/${id - 1}/overview`
  }
  return `/theme/${id}/${sub - 1}`
})

const next = computed(() => {
  if (props.aboutMode) return '/theme/1/overview'
  const id = Number(route.params.id)
  const sub = subIndex.value
  const count = subCount(id)
  if (sub === null) return count ? `/theme/${id}/1` : nextTheme(id)
  if (sub >= count) return nextTheme(id)
  return `/theme/${id}/${sub + 1}`
})

function nextTheme(id) {
  return id < lastRouteId.value ? `/theme/${id + 1}/overview` : null
}

const subThemeNav = computed(() =>
  (theme.value?.sub_themes ?? []).map((sub, index) => ({
    index: index + 1,
    title: themeText(sub, uiLang.value).title ?? sub.internal_name ?? '',
  }))
)

const routeId = computed(() => themeRouteId(theme.value))
</script>

<template>
  <div class="theme-component-wrapper" v-if="node">
    <div class="theme-component">
      <!-- Thumbnail strip -->
      <div class="theme-component-images-wrapper">
        <div class="theme-component-images" v-if="strip.length">
          <div
            v-for="picture in strip"
            :key="picture.picture_item_id"
            class="theme-component-image"
            :class="{
              border: picture.picture_item_id === selectedId,
              'related-overlay': relatedTo.has(picture.picture_item_id),
            }"
          >
            <img
              :src="picture.image_url"
              :alt="captionFor(picture).name"
              loading="lazy"
              @click="select(picture)"
            />
            <div class="thumbnail-text">
              <span v-if="captionFor(picture).imageCaption">{{ captionFor(picture).imageCaption }}, </span>
              <span class="italic" v-html="mdInline(captionFor(picture).name)"></span>
              <span v-if="captionFor(picture).detail">, {{ captionFor(picture).detail }}</span>
            </div>
          </div>
        </div>

        <div class="thumbnails-section-controls" v-if="hasRelated">
          <label class="toggle">
            <input type="checkbox" v-model="showAll" />
            <span>{{ showAll ? 'Hide' : 'Add' }} Related Works</span>
          </label>
        </div>
      </div>

      <div class="theme-component-content-wrapper">
        <!-- Selected picture -->
        <div class="theme-component-selected-container" v-if="selected">
          <div class="theme-component-selected-image">
            <img :src="selected.image_url" :alt="selectedCaption.name" />
          </div>
          <div class="theme-component-selected-details-wrapper">
            <div class="theme-component-selected-detail title">
              <span v-if="selectedCaption.imageCaption">{{ selectedCaption.imageCaption }}, </span>
              <span v-html="mdInline(selectedCaption.name)"></span>
            </div>
            <div class="theme-component-selected-detail" v-if="selectedSheet.alternate_name">
              Also known as: <span v-html="mdInline(selectedSheet.alternate_name)"></span>
            </div>
            <div class="theme-component-selected-detail" v-if="selectedCaption.parent?.artist_names?.length">
              {{ selectedCaption.parent.artist_names.join(', ') }}
            </div>
            <div class="theme-component-selected-detail" v-if="selectedSheet.dates">{{ selectedSheet.dates }}</div>
            <div class="theme-component-selected-detail" v-if="selectedCaption.detail">{{ selectedCaption.detail }}</div>
            <RouterLink
              v-if="selectedCaption.parent"
              class="theme-component-selected-detail database-entry-link"
              :to="itemRoute(selectedCaption.parent, defaultLang)"
            >See Database entry for this Item</RouterLink>
            <!-- Decision Q3: a picture whose parent is not a member of this
                 exhibition says so rather than linking nowhere. -->
            <div class="theme-component-selected-detail unresolved" v-else>
              The full record for this picture is not part of this exhibition.
            </div>
          </div>
        </div>
        <div v-else class="theme-component-no-images">Additional content coming soon.</div>

        <!-- Text column -->
        <div class="theme-component-content" @click="onProseClick">
          <div class="theme-component-theme-title">{{ heading }}</div>
          <div class="theme-component-title">{{ subHeading }}</div>

          <div class="theme-component-quote prose" v-if="quoteHtml" v-html="quoteHtml"></div>
          <div class="theme-component-presentation prose" v-if="presentationHtml" v-html="presentationHtml"></div>
          <div class="theme-component-selected-justification prose" v-if="contextualHtml" v-html="contextualHtml"></div>

          <!-- Related items: the selected picture as a source -->
          <div class="related-items-container" v-if="selectedTargets.length">
            <div class="related-items-header">{{ t('relatedItems') }}</div>
            <div class="related-items-images-container">
              <div class="related-items-image main-image">
                <img :src="selected.image_url" :alt="selectedCaption.name" />
                <div class="thumbnail-text">
                  <span class="italic" v-html="mdInline(selectedCaption.name)"></span>
                  <span v-if="selectedCaption.detail">, {{ selectedCaption.detail }}</span>
                </div>
              </div>
              <div
                v-for="entry in selectedTargets"
                :key="entry.picture.picture_item_id"
                class="related-items-image"
              >
                <img
                  :src="entry.picture.image_url"
                  :alt="captionFor(entry.picture).name"
                  @click="select(entry.picture)"
                />
                <div class="thumbnail-text">
                  <span class="italic" v-html="mdInline(captionFor(entry.picture).name)"></span>
                  <span v-if="captionFor(entry.picture).detail">, {{ captionFor(entry.picture).detail }}</span>
                </div>
                <div class="related-relation" v-if="relationText(entry.link)">{{ relationText(entry.link) }}</div>
              </div>
            </div>
          </div>

          <!-- Related to: the selected picture as a target -->
          <div class="related-to-container" v-if="selectedSource">
            <div class="related-to-header">{{ t('relatedItems') }}</div>
            <div class="theme-component-selected-justification reciprocal-description">
              {{ reciprocalText(selectedSource.link) || t('reciprocalDescription') }}
            </div>
            <div class="related-to-image">
              <img
                :src="selectedSource.source.image_url"
                :alt="captionFor(selectedSource.source).name"
                @click="select(selectedSource.source)"
              />
              <div class="thumbnail-text">
                <span class="italic" v-html="mdInline(captionFor(selectedSource.source).name)"></span>
                <span v-if="captionFor(selectedSource.source).detail">, {{ captionFor(selectedSource.source).detail }}</span>
              </div>
            </div>
          </div>

          <!-- Tour navigation -->
          <div class="theme-component-navigation-next-previous-wrapper">
            <RouterLink v-if="previous" :to="previous" class="theme-nav previous">← PREVIOUS</RouterLink>
            <span v-else></span>
            <RouterLink v-if="next" :to="next" class="theme-nav next">NEXT →</RouterLink>
          </div>

          <div class="theme-component-link-navigation-container" v-if="subThemeNav.length && !aboutMode">
            <div class="theme-component-link-navigation-section-label">In This Theme</div>
            <div class="theme-component-link-navigation-overview">
              <RouterLink
                :to="`/theme/${routeId}/overview`"
                :class="{ bold: subIndex === null }"
              >Overview</RouterLink>
            </div>
            <div class="theme-component-link-navigation" v-for="entry in subThemeNav" :key="entry.index">
              <RouterLink
                :to="`/theme/${routeId}/${entry.index}`"
                :class="{ bold: subIndex === entry.index }"
              >{{ entry.index }}. {{ entry.title }}</RouterLink>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Glossary pop-up -->
    <div class="glossary-entry-container" v-if="openTerm">
      <button class="glossary-close" @click="openTerm = null" aria-label="Close">✕</button>
      <div class="glossary-header">{{ t('glossary') }}</div>
      <div class="glossary-entry">
        <div class="glossary-word">{{ openTerm.word }}</div>
        <div class="glossary-definition prose" v-html="md(openTerm.definition)"></div>
      </div>
    </div>
  </div>

  <div class="loader" v-else>This theme is not part of the exhibition.</div>
</template>

<style scoped>
.theme-component-wrapper {
  position: relative;
  width: 100%;
  background: var(--secondary-color);
  padding-bottom: 40px;
}
.theme-component { display: flex; flex-direction: column; width: 100%; }

/* ── Thumbnail strip ─────────────────────────────────────────────────────── */
.theme-component-images-wrapper { width: 100%; padding: 20px 30px 0; }
.theme-component-images {
  display: flex;
  gap: 14px;
  overflow-x: auto;
  padding-bottom: 8px;
}
.theme-component-image {
  position: relative;
  flex: 0 0 auto;
  width: 130px;
}
.theme-component-image img {
  width: 130px;
  height: 130px;
  object-fit: cover;
  cursor: pointer;
  display: block;
}
.theme-component-image.border img { outline: 4px solid var(--contrast-color); outline-offset: -4px; }
.theme-component-image.related-overlay img { opacity: 0.75; }
.thumbnail-text {
  display: none;
  position: absolute;
  top: 100%;
  left: 0;
  z-index: 5;
  width: 260px;
  padding: 8px;
  font-size: 13px;
  background: var(--main-color);
  color: var(--main-text-color);
}
.theme-component-image:hover .thumbnail-text,
.related-items-image:hover .thumbnail-text,
.related-to-image:hover .thumbnail-text { display: block; }
.italic { font-style: italic; }

.thumbnails-section-controls { padding: 8px 0 0; }
.toggle { display: inline-flex; align-items: center; gap: 8px; cursor: pointer; }

/* ── Selected picture + text ─────────────────────────────────────────────── */
.theme-component-content-wrapper { display: flex; width: 100%; padding: 20px 30px; gap: 30px; }
.theme-component-selected-container { width: 45%; }
.theme-component-selected-image img { width: 100%; object-fit: contain; max-height: 520px; }
.theme-component-selected-details-wrapper { padding-top: 12px; font-size: 15px; line-height: 1.45; }
.theme-component-selected-detail.title { font-weight: 700; }
.database-entry-link { display: inline-block; margin-top: 10px; color: var(--link-blue); }
.unresolved { margin-top: 10px; font-style: italic; color: var(--shadow-grey); }
.theme-component-no-images { width: 45%; padding: 40px 0; font-style: italic; }

.theme-component-content { flex: 1; min-width: 0; }
.theme-component-theme-title {
  background: var(--contrast-color);
  color: var(--contrast-text-color);
  font-size: 22px;
  font-weight: 700;
  padding: 8px 12px;
}
.theme-component-title { font-size: 18px; font-weight: 700; padding: 12px 0 4px; }
.theme-component-quote { font-style: italic; padding: 8px 0; }
.theme-component-presentation,
.theme-component-selected-justification { padding: 8px 0; line-height: 1.6; }

:deep(a.glossary-link) {
  color: var(--link-blue);
  text-decoration: underline dotted;
  cursor: pointer;
}

.related-items-container, .related-to-container { padding-top: 20px; }
.related-items-header, .related-to-header { font-weight: 700; margin-bottom: 8px; }
.related-items-images-container { display: flex; flex-wrap: wrap; gap: 14px; }
.related-items-image, .related-to-image { position: relative; width: 130px; }
.related-items-image img, .related-to-image img {
  width: 130px; height: 130px; object-fit: cover; cursor: pointer; display: block;
}
.related-items-image.main-image img { outline: 4px solid var(--main-color); outline-offset: -4px; cursor: default; }
.related-relation { font-size: 12px; padding-top: 4px; }
.reciprocal-description { font-style: italic; }

.theme-component-navigation-next-previous-wrapper {
  display: flex;
  justify-content: space-between;
  padding: 25px 0 10px;
  font-weight: 700;
}
.theme-nav { color: var(--secondary-text-color); text-decoration: none; }
.theme-nav:hover { background: var(--contrast-color); }

.theme-component-link-navigation-container { padding-top: 15px; }
.theme-component-link-navigation-section-label { font-weight: 700; }
.theme-component-link-navigation-overview a,
.theme-component-link-navigation a { color: var(--secondary-text-color); text-decoration: none; }
.theme-component-link-navigation-overview a:hover,
.theme-component-link-navigation a:hover { background: var(--contrast-color); }
.bold { font-weight: 700; }

/* ── Glossary pop-up ─────────────────────────────────────────────────────── */
.glossary-entry-container {
  position: fixed;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 150;
  width: min(520px, 92vw);
  max-height: 50vh;
  overflow-y: auto;
  padding: 30px 20px 20px;
  background: var(--secondary-color);
  border: 3px solid var(--contrast-color);
  box-shadow: 0 4px 16px 2px var(--shadow-grey);
}
.glossary-close {
  position: absolute; top: 6px; right: 10px;
  background: none; border: none; font-size: 18px; cursor: pointer;
}
.glossary-header { font-weight: 700; margin-bottom: 6px; }
.glossary-word { font-weight: 700; font-style: italic; }

@media only screen and (max-width: 974px) {
  .theme-component-content-wrapper { flex-direction: column; }
  .theme-component-selected-container, .theme-component-no-images { width: 100%; }
  .thumbnail-text { display: none !important; }
}
</style>
