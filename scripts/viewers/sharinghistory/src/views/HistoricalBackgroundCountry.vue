<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useInventoryData } from '../composables/useInventoryData.js'

const route = useRoute()
const router = useRouter()
const {
  historicalBackgroundRecords, historicalBackgroundPages,
  itemById, itemLabel,
  availableLangs, defaultLang,
  enCollectionTranslations,
  countryLabel,
  md, mdInline,
} = useInventoryData()

const record = computed(() =>
  historicalBackgroundRecords.value.find(r => r.id === decodeURIComponent(route.params.recordId)) ?? null
)

const pages = computed(() => (record.value ? historicalBackgroundPages(record.value.id) : []))

// ── Page pagination (legacy hb_result.php?country=xx&page=N) ────────────

const activePageIndex = ref(0)

watch([record, () => route.query.page], () => {
  const idx = parseInt(route.query.page ?? '1', 10) - 1
  activePageIndex.value = Number.isFinite(idx) && idx >= 0 && idx < pages.value.length ? idx : 0
}, { immediate: true })

function goToPage(idx) {
  if (idx < 0 || idx >= pages.value.length) return
  router.push({ query: { ...route.query, page: idx + 1 } })
}

const activePage = computed(() => pages.value[activePageIndex.value] ?? null)

// ── Per-language text ───────────────────────────────────────────────────

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

onMounted(() => loadCollectionLangTranslations(activeLang.value))
watch(activeLang, lang => loadCollectionLangTranslations(lang))

function collectionText(collectionId) {
  return collectionLangCache.value[activeLang.value]?.[collectionId]
    ?? enCollectionTranslations.value[collectionId]
    ?? {}
}

const recordText = computed(() => (record.value ? collectionText(record.value.id) : {}))
const pageText = computed(() => (activePage.value ? collectionText(activePage.value.id) : {}))

// Bibliography lives in the record translation's extra (structured
// bibliography injected by the importer), keyed by language. A language's
// value can be a single Markdown string or an array of entry strings.
const bibliography = computed(() => {
  const extra = recordText.value.extra ?? {}
  const bib = extra.bibliography
  if (!bib) return null
  const raw = bib[activeLang.value] ?? bib[defaultLang] ?? Object.values(bib)[0] ?? null
  if (raw == null) return null
  if (Array.isArray(raw)) return raw.filter(e => typeof e === 'string').join('\n\n')
  return typeof raw === 'string' ? raw : null
})

// Items illustrating the active page (legacy links HB images back to
// database items). display_status 'N' items are welcome here — this is
// exactly what they exist for.
const pageItems = computed(() => {
  const p = activePage.value
  if (!p?.items?.length) return []
  return p.items.map(e => itemById.value[e.id]).filter(Boolean)
})
</script>

<template>
  <div v-if="!record" class="content-box not-found">
    <p>Historical profile not found.</p>
    <router-link to="/historical-profiles">← Return to Historical Profiles</router-link>
  </div>

  <div v-else class="hb-wrap">
    <router-link class="back-link" to="/historical-profiles">← Historical Profiles</router-link>

    <div v-if="availableLangs.length > 1" class="lang-selector">
      <label class="lang-label">Text language:</label>
      <select v-model="activeLang" class="lang-select">
        <option v-for="lang in availableLangs" :key="lang" :value="lang">{{ lang.toUpperCase() }}</option>
      </select>
    </div>

    <div class="content-box">
      <p v-if="record.country_id" class="hb-country-tag">{{ countryLabel(record.country_id) }}</p>
      <h1 class="hb-title" v-html="mdInline(recordText.title ?? record.internal_name)" />
      <div v-if="recordText.description" class="prose" v-html="md(recordText.description)" />

      <div v-if="pages.length" class="page-nav-row">
        <button class="page-nav-btn" :disabled="activePageIndex === 0" @click="goToPage(activePageIndex - 1)">
          ← Previous page
        </button>
        <span class="page-nav-count">Page {{ activePageIndex + 1 }} of {{ pages.length }}</span>
        <button class="page-nav-btn" :disabled="activePageIndex === pages.length - 1" @click="goToPage(activePageIndex + 1)">
          Next page →
        </button>
      </div>

      <div v-if="activePage">
        <h2 v-if="pageText.title" class="hb-page-title" v-html="mdInline(pageText.title)" />
        <div v-if="pageText.description" class="prose" v-html="md(pageText.description)" />

        <div v-if="activePage.images?.length" class="hb-image-strip">
          <img
            v-for="(img, idx) in activePage.images"
            :key="idx"
            :src="img.url"
            :alt="img.alt_text ?? ''"
            loading="lazy"
          />
        </div>

        <div v-if="pageItems.length" class="hb-item-row">
          <h3 class="hb-item-heading">Related items</h3>
          <div class="hb-item-grid">
            <RouterLink
              v-for="item in pageItems"
              :key="item.id"
              :to="`/item/${encodeURIComponent(item.id)}`"
              class="hb-item-card"
            >
              <img v-if="item.images?.length" :src="item.images[0].url" :alt="itemLabel(item)" loading="lazy" />
              <div v-else class="hb-item-placeholder" />
              <span class="hb-item-name">{{ itemLabel(item) }}</span>
            </RouterLink>
          </div>
        </div>
      </div>

      <!-- Legacy hb_result.php "Related Content" box -->
      <div class="hb-related">
        <h3 class="hb-item-heading">Related Content</h3>
        <ul class="hb-related-list">
          <li><RouterLink to="/historical-background">Historical Background</RouterLink></li>
          <li v-if="record.country_id">
            <RouterLink
              :to="{ path: '/timeline/results', query: { country: record.country_id, exhibition: 'pc' } }"
            >
              Political Context Timeline {{ countryLabel(record.country_id) }}
            </RouterLink>
          </li>
          <li v-if="bibliography"><a href="#hb-bibliography">Bibliography</a></li>
          <li v-if="record.images?.length"><a href="#hb-maps">View Map(s)</a></li>
        </ul>
      </div>

      <!-- Record-level images are the legacy historical maps -->
      <div v-if="record.images?.length" id="hb-maps" class="hb-maps">
        <h3 class="hb-item-heading">Maps</h3>
        <div class="hb-image-strip">
          <img
            v-for="(img, idx) in record.images"
            :key="idx"
            :src="img.url"
            :alt="img.alt_text ?? 'Historical map'"
            loading="lazy"
          />
        </div>
      </div>

      <div v-if="bibliography" id="hb-bibliography" class="hb-bibliography">
        <h3 class="hb-item-heading">Bibliography</h3>
        <div class="prose" v-html="md(bibliography)" />
      </div>
    </div>
  </div>
</template>

<style scoped>
.not-found { color: var(--muted); font-family: 'Roboto', sans-serif; font-size: 13px; }

.hb-wrap { display: flex; flex-direction: column; gap: 10px; }

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

.hb-country-tag {
  display: inline-block;
  font-family: 'Roboto', sans-serif;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #fff;
  background: var(--accent);
  padding: 2px 8px;
  margin-bottom: 8px;
}
.hb-title {
  font-size: 22px;
  font-weight: 400;
  color: var(--heading);
  margin-bottom: 12px;
  font-family: 'Roboto', sans-serif;
}
.hb-page-title {
  font-size: 17px;
  font-weight: 500;
  color: var(--heading);
  margin: 6px 0 10px;
  font-family: 'Roboto', sans-serif;
}

.prose { font-size: 14px; line-height: 1.7; color: var(--text); font-family: 'Roboto', sans-serif; }
.prose :deep(p) { margin: 0 0 .75em; }

.hb-image-strip {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  margin: 12px 0;
}
.hb-image-strip img {
  height: 120px;
  border: 1px solid var(--border);
  background: var(--tile-bg);
}

.page-nav-row {
  display: flex;
  align-items: center;
  gap: 16px;
  margin: 16px 0;
  padding: 10px 0;
  border-top: 1px solid var(--border);
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
.page-nav-count { font-family: 'Roboto', sans-serif; font-size: 12px; color: var(--muted); }

.hb-item-row { margin-top: 18px; }
.hb-item-heading {
  font-size: 14px;
  font-weight: 500;
  color: var(--heading);
  border-bottom: 1px solid var(--accent-soft);
  padding-bottom: 3px;
  margin-bottom: 8px;
  font-family: 'Roboto', sans-serif;
}
.hb-item-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
  gap: 10px;
}
.hb-item-card { display: flex; flex-direction: column; gap: 4px; text-decoration: none !important; }
.hb-item-card:hover .hb-item-name { color: var(--nav-active); }
.hb-item-card img, .hb-item-placeholder {
  width: 100%;
  aspect-ratio: 1;
  object-fit: cover;
  border: 1px solid var(--border);
  background: var(--tile-bg);
  display: block;
}
.hb-item-name {
  font-family: 'Roboto', sans-serif;
  font-size: 11px;
  color: var(--text);
  line-height: 1.3;
}

.hb-bibliography { margin-top: 18px; }
.hb-maps { margin-top: 18px; }
.hb-related { margin-top: 18px; }
.hb-related-list {
  list-style: none;
  font-family: 'Roboto', sans-serif;
  font-size: 13px;
}
.hb-related-list li { padding: 3px 0; }
.hb-related-list a { color: var(--nav-active); }
</style>
