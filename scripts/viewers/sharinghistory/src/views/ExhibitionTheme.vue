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
  exhibitionById, exhibitionThemeById,
  enCollectionTranslations,
  md, mdInline,
} = useInventoryData()

const exhibition = computed(() => exhibitionById(decodeURIComponent(route.params.exhibitionId)) ?? null)
const theme = computed(() => {
  const e = exhibition.value
  if (!e) return null
  return exhibitionThemeById(e.id, decodeURIComponent(route.params.themeId)) ?? null
})

// SH themes have no "pages" — their children are chapters (subthemes), each
// a full narrative level of its own with a dedicated view.
const chapters = computed(() => theme.value?.chapters ?? [])

// ── Language selector (collection text loaded on demand, per-lang) ──────

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

// The importer synthesizes a placeholder internal name when the legacy
// source has no title for a given language (e.g. SH theme 26 has no name
// row at all). Fall back gracefully.
function resolveTitle(collectionId, fallbackName) {
  const local = collectionText(collectionId).title
  if (local) return local
  const en = enCollectionTranslations.value[collectionId]?.title
  if (en) return en
  return fallbackName
}

// ── Theme's own item grid (items attached at theme level) ───────────────

const selectedItemId = ref(null)

watch(theme, t => {
  selectedItemId.value = t?.items?.[0]?.id ?? null
}, { immediate: true })

const gridItems = computed(() => {
  const t = theme.value
  if (!t) return []
  return (t.items ?? [])
    .map(entry => ({ entry, item: itemById.value[entry.id] }))
    .filter(({ item }) => item)
})

const selected = computed(() => gridItems.value.find(g => g.item.id === selectedItemId.value) ?? gridItems.value[0] ?? null)

function selectItem(itemId) {
  selectedItemId.value = itemId
}

const selectedDisplay = computed(() => {
  const sel = selected.value
  if (!sel) return null
  const caption = sel.entry.caption?.[activeLang.value] ?? {}
  const just = sel.entry.justifications?.[activeLang.value]
    ?? sel.entry.justifications?.[defaultLang] ?? null
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
  if (window.history.length > 2) {
    router.back()
  } else if (exhibition.value) {
    router.push(`/exhibitions/${exhibition.value.id}`)
  } else {
    router.push('/exhibitions')
  }
}
</script>

<template>
  <div v-if="!theme" class="content-box not-found">
    <p>Theme not found.</p>
    <router-link v-if="exhibition" :to="`/exhibitions/${exhibition.id}`">← Return to exhibition</router-link>
    <router-link v-else to="/exhibitions">← Return to Exhibitions</router-link>
  </div>

  <div v-else class="theme-wrap">
    <a class="back-link" href="#" @click.prevent="back">← Back to {{ resolveTitle(exhibition.id, exhibition.internal_name) }}</a>

    <div v-if="availableLangs.length > 1" class="lang-selector">
      <label class="lang-label">Text language:</label>
      <select v-model="activeLang" class="lang-select">
        <option v-for="lang in availableLangs" :key="lang" :value="lang">{{ lang.toUpperCase() }}</option>
      </select>
    </div>

    <div class="content-box">
      <h1 class="theme-title" v-html="mdInline(resolveTitle(theme.id, theme.internal_name))" />

      <div class="theme-grid">
        <!-- Left: theme introduction + chapter list -->
        <div class="theme-text-col">
          <p v-if="collectionText(theme.id).quote" class="page-quote" v-html="mdInline(collectionText(theme.id).quote)" />
          <div v-if="collectionText(theme.id).description" class="prose" v-html="md(collectionText(theme.id).description)" />

          <div v-if="chapters.length" class="chapter-list">
            <h2 class="chapter-list-heading">Chapters</h2>
            <RouterLink
              v-for="(chapter, idx) in chapters"
              :key="chapter.id"
              :to="`/exhibitions/${exhibition.id}/theme/${theme.id}/chapter/${chapter.id}`"
              class="chapter-row"
            >
              <span class="chapter-num">{{ idx + 1 }}</span>
              <span class="chapter-name" v-html="mdInline(resolveTitle(chapter.id, chapter.internal_name))" />
              <span class="chapter-count" v-if="chapter.items?.length">{{ chapter.items.length }} items</span>
            </RouterLink>
          </div>
        </div>

        <!-- Right: theme-level item highlight panel -->
        <div class="theme-side-col" v-if="gridItems.length">
          <div v-if="selectedDisplay" class="item-detail-panel">
            <div class="item-detail-img-wrap">
              <img v-if="selectedDisplay.image" :src="selectedDisplay.image" :alt="selectedDisplay.name" class="item-detail-img" />
              <div v-else class="item-detail-img-placeholder" />
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

.theme-wrap { display: flex; flex-direction: column; gap: 10px; }

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

.theme-title {
  font-size: 22px;
  font-weight: 400;
  color: var(--heading);
  margin-bottom: 14px;
  line-height: 1.3;
  font-family: 'Roboto', sans-serif;
}

/* Two-column layout */
.theme-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 24px;
}
@media (max-width: 700px) { .theme-grid { grid-template-columns: 1fr; } }

.theme-text-col { min-width: 0; }
.page-quote {
  font-size: 15px;
  font-style: italic;
  color: var(--heading);
  margin-bottom: 14px;
  line-height: 1.5;
  font-family: 'Roboto', sans-serif;
}
.prose { font-size: 14px; line-height: 1.7; color: var(--text); font-family: 'Roboto', sans-serif; }
.prose :deep(p) { margin: 0 0 .75em; }
.prose :deep(p:last-child) { margin-bottom: 0; }

/* Chapter list */
.chapter-list { margin-top: 20px; }
.chapter-list-heading {
  font-size: 16px;
  font-weight: 500;
  color: var(--heading);
  border-bottom: 2px solid var(--accent-soft);
  padding-bottom: 4px;
  margin-bottom: 8px;
  font-family: 'Roboto', sans-serif;
}
.chapter-row {
  display: flex;
  align-items: baseline;
  gap: 10px;
  padding: 8px 4px;
  border-bottom: 1px solid var(--border-light);
  text-decoration: none !important;
}
.chapter-row:hover .chapter-name { color: var(--nav-active); }
.chapter-num {
  font-family: 'Roboto Condensed', 'Roboto', sans-serif;
  font-size: 13px;
  font-weight: 700;
  color: var(--accent-soft);
  min-width: 20px;
}
.chapter-name {
  flex: 1;
  font-family: 'Roboto', sans-serif;
  font-size: 14px;
  font-weight: 500;
  color: var(--text);
}
.chapter-count {
  font-family: 'Roboto', sans-serif;
  font-size: 11px;
  color: var(--muted);
}

/* Detail panel */
.theme-side-col { display: flex; flex-direction: column; gap: 14px; }

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

/* Thumbnail grid */
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
