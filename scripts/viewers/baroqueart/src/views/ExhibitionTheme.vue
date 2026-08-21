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
const pages = computed(() => theme.value?.pages ?? [])

// ── Active tab (page within the theme) ──────────────────────────────────

const activeTabIndex = ref(0)

function syncTabFromQuery() {
  const idx = parseInt(route.query.tab ?? '0', 10)
  activeTabIndex.value = Number.isFinite(idx) && idx >= 0 && idx < pages.value.length ? idx : 0
}

watch([theme, () => route.query.tab], syncTabFromQuery, { immediate: true })

function selectTab(idx) {
  router.push({ query: { ...route.query, tab: idx } })
}

const activePage = computed(() => pages.value[activeTabIndex.value] ?? null)

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

// The importer synthesizes a placeholder title ("Theme 5", "Page 17") when
// the legacy source has no page_title/theme_title for a given language.
// Treat that pattern as "missing" and fall back to the English title.
const PLACEHOLDER_TITLE = /^(Theme|Page) \d+$/

function resolveTitle(collectionId, fallbackName) {
  const local = collectionText(collectionId).title
  if (local && !PLACEHOLDER_TITLE.test(local)) return local
  const en = enCollectionTranslations.value[collectionId]?.title
  if (en && !PLACEHOLDER_TITLE.test(en)) return en
  return fallbackName
}

// The heading shows the *active page's* own title, not the theme's — legacy
// exhibition themes aren't always a fixed Monuments/Objects-style split (that
// pattern is specific to Artistic Introduction); a theme's pages are often
// just paginated continuations of one narrative, and legacy navigates them
// with Previous/Next arrows rather than named tabs (confirmed against the
// live site: exhibition.php shows "< Previous page | Next page >", never a
// tab strip). A page's own title is frequently identical to its theme's, but
// isn't guaranteed to be.
const pageTitle = computed(() => resolveTitle(activePage.value?.id, theme.value?.internal_name ?? ''))

function goToPage(idx) {
  if (idx < 0 || idx >= pages.value.length) return
  selectTab(idx)
}

// ── Thumbnail grid, detail panel, and multi-variant "detail" selector ───

const selectedItemId = ref(null)
const selectedVariantIndex = ref(0) // 0 = item's own main image; N = details[N-1]

watch(activePage, page => {
  selectedItemId.value = page?.items?.[0]?.id ?? null
  selectedVariantIndex.value = 0
})

const gridItems = computed(() => {
  const page = activePage.value
  if (!page) return []
  return page.items
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

  if (variant) {
    const caption = variant.caption?.[activeLang.value] ?? {}
    return {
      name: caption.detail_name ?? caption.name ?? '',
      date: caption.date ?? '',
      dynasty: caption.dynasty ?? '',
      location: caption.location ?? '',
      museum: caption.museum ?? '',
      justification: caption.justification ?? '',
      image: variant.image_url ?? sel.item.images?.[0]?.url ?? null,
    }
  }

  // Main/default view: caption override (current language) merged with the
  // item's own generic translation, mirroring the legacy behaviour.
  const caption = sel.entry.caption?.[activeLang.value] ?? {}
  const t = translationsCache.value[activeLang.value]?.[sel.item.id] ?? {}
  return {
    name: caption.name ?? t.name ?? sel.item.internal_name ?? sel.item.id,
    date: caption.date ?? t.dates ?? '',
    dynasty: caption.dynasty ?? '',
    location: caption.location ?? t.location ?? '',
    museum: caption.museum ?? (sel.item.partner_id ? partnerLabel(sel.item.partner_id) : ''),
    justification: caption.justification ?? '',
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
      <h1 class="theme-title" v-html="mdInline(pageTitle)" />

      <!-- Page navigation: Previous/Next, matching the legacy site (a
           theme's pages are paginated continuations, not fixed named tabs) -->
      <div v-if="pages.length > 1" class="page-nav-row">
        <button class="page-nav-btn" :disabled="activeTabIndex === 0" @click="goToPage(activeTabIndex - 1)">
          ← Previous page
        </button>
        <span class="page-nav-count">Page {{ activeTabIndex + 1 }} of {{ pages.length }}</span>
        <button class="page-nav-btn" :disabled="activeTabIndex === pages.length - 1" @click="goToPage(activeTabIndex + 1)">
          Next page →
        </button>
      </div>

      <div v-if="activePage" class="theme-grid">
        <!-- Left: narrative text -->
        <div class="theme-text-col">
          <p v-if="collectionText(activePage.id).quote" class="page-quote" v-html="mdInline(collectionText(activePage.id).quote)" />
          <div v-if="collectionText(activePage.id).description" class="prose" v-html="md(collectionText(activePage.id).description)" />
        </div>

        <!-- Right: detail panel + thumbnail grid -->
        <div class="theme-side-col">
          <div v-if="selectedDisplay" class="item-detail-panel">
            <div class="item-detail-img-wrap">
              <img v-if="selectedDisplay.image" :src="selectedDisplay.image" :alt="selectedDisplay.name" class="item-detail-img" />
              <div v-else class="item-detail-img-placeholder" />
            </div>

            <!-- Detail variant selector: small image buttons to switch between
                 the item's main view and its curated "detail" close-ups -->
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
            <p v-if="selectedDisplay.dynasty" class="item-detail-meta">{{ selectedDisplay.dynasty }}</p>
            <p v-if="selectedDisplay.date" class="item-detail-meta">{{ selectedDisplay.date }}</p>
            <p v-if="selectedDisplay.location" class="item-detail-meta">{{ selectedDisplay.location }}</p>
            <p v-if="selectedDisplay.museum" class="item-detail-meta">{{ selectedDisplay.museum }}</p>
            <p v-if="selectedDisplay.justification" class="item-detail-justification" v-html="mdInline(selectedDisplay.justification)" />
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

/* Page navigation (Previous/Next) */
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
.page-nav-btn:hover:not(:disabled) { color: var(--nav-active); border-color: var(--gold-dark); }
.page-nav-btn:disabled { opacity: 0.4; cursor: default; }
.page-nav-count {
  font-family: 'Roboto', sans-serif;
  font-size: 12px;
  color: var(--muted);
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
  background: #f0ebdc;
  margin-bottom: 10px;
}
.item-detail-img { width: 100%; height: 100%; object-fit: cover; display: block; }
.item-detail-img-placeholder { width: 100%; height: 100%; }

/* Variant selector */
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
  background: #f0ebdc;
  cursor: pointer;
  overflow: hidden;
}
.variant-btn.active { border-color: var(--gold-dark); }
.variant-btn:hover { border-color: var(--gold-amber); }
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
  background: #f0ebdc;
}
.thumb-cell.active { border-color: var(--gold-dark); }
.thumb-cell:hover { border-color: var(--gold-amber); }
.thumb-cell img { width: 100%; height: 100%; object-fit: cover; display: block; }
.thumb-placeholder { width: 100%; height: 100%; }
</style>
