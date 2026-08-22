<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useInventoryData } from '../composables/useInventoryData.js'

const route = useRoute()
const router = useRouter()
const {
  itemById,
  availableLangs, defaultLang,
  translationsCache, loadLangTranslations,
  partnerLabel,
  exhibitionById, exhibitionThemeById, chapterById,
  enCollectionTranslations,
  md, mdInline,
} = useInventoryData()

const exhibition = computed(() => exhibitionById(decodeURIComponent(route.params.exhibitionId)) ?? null)
const theme = computed(() => {
  const e = exhibition.value
  if (!e) return null
  return exhibitionThemeById(e.id, decodeURIComponent(route.params.themeId)) ?? null
})
const chapter = computed(() => {
  const e = exhibition.value
  const t = theme.value
  if (!e || !t) return null
  return chapterById(e.id, t.id, decodeURIComponent(route.params.chapterId)) ?? null
})

// Chapter position within the theme, for Previous/Next navigation (the
// legacy site navigates chapters sequentially inside a theme).
const chapterIndex = computed(() => {
  const t = theme.value
  const c = chapter.value
  if (!t || !c) return -1
  return t.chapters.findIndex(ch => ch.id === c.id)
})

function goToChapter(idx) {
  const t = theme.value
  if (!t || idx < 0 || idx >= t.chapters.length) return
  router.push(`/exhibitions/${exhibition.value.id}/theme/${t.id}/chapter/${t.chapters[idx].id}`)
}

// ── Language selector ───────────────────────────────────────────────────

const activeLang = ref(defaultLang)
const collectionLangCache = ref({})

async function loadCollectionLangTranslations(lang) {
  if (collectionLangCache.value[lang]) return
  try {
    const m = await import(`@inventory-data/translations/collections.${lang}.json`)
    collectionLangCache.value = { ...collectionLangCache.value, [lang]: m.default }
  } catch {
    collectionLangCache.value = { ...collectionLangCache.value, [lang]: {} }
  }
}

onMounted(() => {
  loadCollectionLangTranslations(activeLang.value)
  loadLangTranslations(activeLang.value)
})
watch(activeLang, lang => {
  loadCollectionLangTranslations(lang)
  loadLangTranslations(lang)
})

function collectionText(collectionId) {
  return collectionLangCache.value[activeLang.value]?.[collectionId] ?? {}
}

function resolveTitle(collectionId, fallbackName) {
  return collectionText(collectionId).title
    ?? enCollectionTranslations.value[collectionId]?.title
    ?? fallbackName
}

// See-also / further-reading blocks live in the collection translation's
// extra (imported from sh_exhibition_subthemenames).
const chapterExtra = computed(() => {
  const c = chapter.value
  if (!c) return {}
  return collectionText(c.id).extra ?? enCollectionTranslations.value[c.id]?.extra ?? {}
})

// ── Item grid with curated justifications ───────────────────────────────

const selectedItemId = ref(null)
const selectedVariantIndex = ref(0)

watch(chapter, c => {
  selectedItemId.value = c?.items?.[0]?.id ?? null
  selectedVariantIndex.value = 0
}, { immediate: true })

const gridItems = computed(() => {
  const c = chapter.value
  if (!c) return []
  return (c.items ?? [])
    .map(entry => ({ entry, item: itemById.value[entry.id] }))
    .filter(({ item }) => item)
})

const selected = computed(() => gridItems.value.find(g => g.item.id === selectedItemId.value) ?? gridItems.value[0] ?? null)
const selectedVariants = computed(() => selected.value?.entry.details ?? [])

function selectItem(itemId) {
  selectedItemId.value = itemId
  selectedVariantIndex.value = 0
}

function selectVariant(idx) {
  selectedVariantIndex.value = idx
}

const selectedDisplay = computed(() => {
  const sel = selected.value
  if (!sel) return null

  const variantIdx = selectedVariantIndex.value
  const variant = variantIdx > 0 ? selectedVariants.value[variantIdx - 1] : null

  const just = sel.entry.justifications?.[activeLang.value]
    ?? sel.entry.justifications?.[defaultLang] ?? null

  if (variant) {
    const caption = variant.caption?.[activeLang.value] ?? {}
    return {
      name: caption.detail_name ?? caption.name ?? '',
      date: caption.date ?? '',
      location: caption.location ?? '',
      museum: caption.museum ?? '',
      justificationCurator: caption.justification ?? just?.curator ?? '',
      justificationPartner: just?.partner ?? '',
      image: variant.image_url ?? sel.item.images?.[0]?.url ?? null,
    }
  }

  const caption = sel.entry.caption?.[activeLang.value] ?? {}
  const t = translationsCache.value[activeLang.value]?.[sel.item.id] ?? {}
  return {
    name: caption.name ?? t.name ?? sel.item.internal_name ?? sel.item.id,
    date: caption.date ?? t.dates ?? '',
    location: caption.location ?? t.location ?? '',
    museum: caption.museum ?? (sel.item.partner_id ? partnerLabel(sel.item.partner_id) : ''),
    justificationCurator: just?.curator ?? caption.justification ?? '',
    justificationPartner: just?.partner ?? '',
    image: sel.item.images?.[0]?.url ?? null,
  }
})

function back() {
  if (exhibition.value && theme.value) {
    router.push(`/exhibitions/${exhibition.value.id}/theme/${theme.value.id}`)
  } else {
    router.push('/exhibitions')
  }
}
</script>

<template>
  <div v-if="!chapter" class="content-box not-found">
    <p>Chapter not found.</p>
    <router-link to="/exhibitions">← Return to Exhibitions</router-link>
  </div>

  <div v-else class="chapter-wrap">
    <a class="back-link" href="#" @click.prevent="back">← Back to {{ resolveTitle(theme.id, theme.internal_name) }}</a>

    <div v-if="availableLangs.length > 1" class="lang-selector">
      <label class="lang-label">Text language:</label>
      <select v-model="activeLang" class="lang-select">
        <option v-for="lang in availableLangs" :key="lang" :value="lang">{{ lang.toUpperCase() }}</option>
      </select>
    </div>

    <div class="content-box">
      <p class="chapter-crumb">
        <span v-html="mdInline(resolveTitle(exhibition.id, exhibition.internal_name))" /> ·
        <span v-html="mdInline(resolveTitle(theme.id, theme.internal_name))" />
      </p>
      <h1 class="chapter-title" v-html="mdInline(resolveTitle(chapter.id, chapter.internal_name))" />

      <div v-if="theme.chapters.length > 1" class="page-nav-row">
        <button class="page-nav-btn" :disabled="chapterIndex <= 0" @click="goToChapter(chapterIndex - 1)">
          ← Previous chapter
        </button>
        <span class="page-nav-count">Chapter {{ chapterIndex + 1 }} of {{ theme.chapters.length }}</span>
        <button class="page-nav-btn" :disabled="chapterIndex >= theme.chapters.length - 1" @click="goToChapter(chapterIndex + 1)">
          Next chapter →
        </button>
      </div>

      <div class="chapter-grid">
        <!-- Left: quotation + narrative -->
        <div class="chapter-text-col">
          <blockquote v-if="collectionText(chapter.id).quote" class="chapter-quote" v-html="md(collectionText(chapter.id).quote)" />
          <div v-if="collectionText(chapter.id).description" class="prose" v-html="md(collectionText(chapter.id).description)" />

          <div v-if="chapterExtra.see_also_links" class="chapter-extra">
            <h3 class="chapter-extra-heading">See also</h3>
            <div class="prose" v-html="md(chapterExtra.see_also_links)" />
          </div>
          <div v-if="chapterExtra.further_reading" class="chapter-extra">
            <h3 class="chapter-extra-heading">Further reading</h3>
            <div class="prose" v-html="md(chapterExtra.further_reading)" />
          </div>
        </div>

        <!-- Right: detail panel + thumbnail grid -->
        <div class="chapter-side-col" v-if="gridItems.length">
          <div v-if="selectedDisplay" class="item-detail-panel">
            <div class="item-detail-img-wrap">
              <img v-if="selectedDisplay.image" :src="selectedDisplay.image" :alt="selectedDisplay.name" class="item-detail-img" />
              <div v-else class="item-detail-img-placeholder" />
            </div>

            <div v-if="selectedVariants.length" class="variant-row">
              <button
                class="variant-btn"
                :class="{ active: selectedVariantIndex === 0 }"
                title="Main view"
                @click="selectVariant(0)"
              >
                <img v-if="selected.item.images?.[0]?.url" :src="selected.item.images[0].url" alt="Main view" />
              </button>
              <button
                v-for="(variant, idx) in selectedVariants"
                :key="idx"
                class="variant-btn"
                :class="{ active: selectedVariantIndex === idx + 1 }"
                title="Detail view"
                @click="selectVariant(idx + 1)"
              >
                <img v-if="variant.image_url" :src="variant.image_url" alt="Detail view" />
              </button>
            </div>

            <h3 class="item-detail-name" v-html="mdInline(selectedDisplay.name)" />
            <p v-if="selectedDisplay.date" class="item-detail-meta">{{ selectedDisplay.date }}</p>
            <p v-if="selectedDisplay.location" class="item-detail-meta">{{ selectedDisplay.location }}</p>
            <p v-if="selectedDisplay.museum" class="item-detail-meta">{{ selectedDisplay.museum }}</p>
            <p v-if="selectedDisplay.justificationCurator" class="item-detail-justification">
              <span class="just-label">Curator's justification</span>
              <span v-html="mdInline(selectedDisplay.justificationCurator)" />
            </p>
            <p v-if="selectedDisplay.justificationPartner" class="item-detail-justification">
              <span class="just-label">Partner's justification</span>
              <span v-html="mdInline(selectedDisplay.justificationPartner)" />
            </p>
            <RouterLink :to="`/item/${encodeURIComponent(selected.item.id)}`" class="more-info-link">
              More info →
            </RouterLink>
          </div>

          <div class="thumb-grid">
            <div
              v-for="g in gridItems"
              :key="g.item.id"
              class="thumb-cell"
              :class="{ active: g.item.id === selectedItemId }"
              @click="selectItem(g.item.id)"
            >
              <img v-if="g.item.images?.length" :src="g.item.images[0].url" :alt="g.item.internal_name ?? ''" loading="lazy" />
              <div v-else class="thumb-placeholder" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.not-found { color: var(--muted); font-family: 'Roboto', sans-serif; font-size: 13px; }

.chapter-wrap { display: flex; flex-direction: column; gap: 10px; }

.lang-selector {
  display: flex;
  align-items: center;
  gap: 8px;
  font-family: 'Roboto', sans-serif;
  font-size: 12px;
  color: var(--muted);
  justify-content: flex-end;
}
.lang-select { font-size: 12px; padding: 3px 6px; }

.chapter-crumb {
  font-family: 'Roboto', sans-serif;
  font-size: 12px;
  color: var(--muted);
  margin-bottom: 4px;
}
.chapter-title {
  font-size: 22px;
  font-weight: 400;
  color: var(--heading);
  margin-bottom: 14px;
  line-height: 1.3;
  font-family: 'Roboto', sans-serif;
}

.page-nav-row {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 18px;
  padding-bottom: 12px;
  border-bottom: 1px solid var(--border);
}
.page-nav-btn {
  font-family: 'Roboto', sans-serif;
  font-size: 13px;
  font-weight: 500;
  padding: 6px 12px;
  background: none;
  border: 1px solid var(--border);
  color: var(--heading);
  cursor: pointer;
}
.page-nav-btn:hover:not(:disabled) { color: var(--nav-active); border-color: var(--accent); }
.page-nav-btn:disabled { opacity: 0.4; cursor: default; }
.page-nav-count {
  font-family: 'Roboto', sans-serif;
  font-size: 12px;
  color: var(--muted);
}

.chapter-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 24px;
}
@media (max-width: 700px) { .chapter-grid { grid-template-columns: 1fr; } }

.chapter-text-col { min-width: 0; }
.chapter-quote {
  font-size: 15px;
  font-style: italic;
  color: var(--heading);
  border-left: 3px solid var(--gold-band);
  padding-left: 12px;
  margin-bottom: 14px;
  line-height: 1.5;
  font-family: 'Roboto', sans-serif;
}
.prose { font-size: 14px; line-height: 1.7; color: var(--text); font-family: 'Roboto', sans-serif; }
.prose :deep(p) { margin: 0 0 .75em; }
.prose :deep(p:last-child) { margin-bottom: 0; }

.chapter-extra { margin-top: 18px; }
.chapter-extra-heading {
  font-size: 14px;
  font-weight: 500;
  color: var(--heading);
  border-bottom: 1px solid var(--accent-soft);
  padding-bottom: 3px;
  margin-bottom: 6px;
  font-family: 'Roboto', sans-serif;
}

.chapter-side-col { display: flex; flex-direction: column; gap: 14px; }

.item-detail-panel {
  border: 1px solid var(--border);
  background: var(--section-bg);
  padding: 14px;
}
.item-detail-img-wrap {
  width: 100%;
  aspect-ratio: 4 / 3;
  overflow: hidden;
  border: 1px solid var(--border);
  background: var(--tile-bg);
  margin-bottom: 10px;
}
.item-detail-img { width: 100%; height: 100%; object-fit: cover; display: block; }
.item-detail-img-placeholder { width: 100%; height: 100%; }

.variant-row {
  display: flex;
  gap: 6px;
  margin-bottom: 10px;
  flex-wrap: wrap;
}
.variant-btn {
  width: 40px;
  height: 40px;
  padding: 0;
  border: 2px solid transparent;
  background: var(--tile-bg);
  cursor: pointer;
  overflow: hidden;
}
.variant-btn.active { border-color: var(--accent); }
.variant-btn:hover { border-color: var(--accent-soft); }
.variant-btn img { width: 100%; height: 100%; object-fit: cover; display: block; }

.item-detail-name {
  font-size: 16px;
  font-weight: 500;
  color: var(--heading);
  margin-bottom: 6px;
  line-height: 1.3;
  font-family: 'Roboto', sans-serif;
}
.item-detail-meta {
  font-size: 12px;
  color: var(--muted);
  font-family: 'Roboto', sans-serif;
  margin-bottom: 2px;
}
.item-detail-justification {
  font-size: 13px;
  line-height: 1.6;
  color: var(--text);
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px solid var(--border);
  font-family: 'Roboto', sans-serif;
}
.just-label {
  display: block;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--accent-soft);
  margin-bottom: 2px;
}
.more-info-link {
  display: inline-block;
  margin-top: 10px;
  font-size: 13px;
  font-weight: 500;
  color: var(--nav-active);
  font-family: 'Roboto', sans-serif;
}

.thumb-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 6px;
}
.thumb-cell {
  aspect-ratio: 1;
  overflow: hidden;
  border: 2px solid transparent;
  cursor: pointer;
  background: var(--tile-bg);
}
.thumb-cell.active { border-color: var(--accent); }
.thumb-cell:hover { border-color: var(--accent-soft); }
.thumb-cell img { width: 100%; height: 100%; object-fit: cover; display: block; }
.thumb-placeholder { width: 100%; height: 100%; }
</style>
