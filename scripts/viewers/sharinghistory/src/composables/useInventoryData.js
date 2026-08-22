import { ref, computed } from 'vue'
import { marked } from 'marked'
import manifestData from '@inventory-data/manifest.json'
import itemsData from '@inventory-data/items.json'
import countriesData from '@inventory-data/countries.json'
import partnersData from '@inventory-data/partners.json'
import timelinesData from '@inventory-data/timelines.json'
import timelineEventsData from '@inventory-data/timeline_events.json'
import collectionsData from '@inventory-data/collections.json'

// Module-level singletons — loaded once, shared across all views
const items = ref(itemsData)
const countries = ref(countriesData)
const partners = ref(partnersData)
const timelines = ref(timelinesData)
const timelineEvents = ref(timelineEventsData)
const collections = ref(collectionsData)
const availableLangs = ref(manifestData.languages ?? [])
const defaultLang = (manifestData.languages ?? []).includes('en')
  ? 'en'
  : ((manifestData.languages ?? [])[0] ?? 'en')

// Legacy project key (e.g. 'ISL', 'EPM') by project UUID — manifest.json's
// projectIds/projectKeys are parallel arrays, one exported project per index.
const projectKeyById = new Map(
  (manifestData.projectIds ?? []).map((id, i) => [id, manifestData.projectKeys?.[i]])
)

// The Sharing History package exports a single project ('awe'); this helper
// stays generic in case a companion project is ever exported alongside it.
function itemProjectKey(item) {
  return projectKeyById.get(item.project_id) ?? null
}

// Items legacy kept only to illustrate Historical Background / timeline
// pages (display_status 'N'): excluded from database search and Permanent
// Collection browsing, exactly like the legacy site
// (modules/database_results.php AND o.display_status='A').
const publicItems = computed(() => items.value.filter(i => i.display_status !== 'N'))

const enItemTranslations = ref({})
const enCountryTranslations = ref({})
const enPartnerTranslations = ref({})
const enTimelineEventTranslations = ref({})
const enCollectionTranslations = ref({})
const translationsCache = ref({}) // lang -> item translations (for detail view)

let enLoaded = false

// Glob instead of literal imports: which translation files exist varies by
// dataset/export (e.g. some entities ship without
// translations), and a literal import of an absent file fails the build.
// The glob only binds files that actually exist in the installed package;
// absent ones resolve to empty maps.
const enTranslationLoaders = import.meta.glob('@inventory-data/translations/*.en.json')

function loadEnFile(entity) {
  const suffix = `/translations/${entity}.en.json`
  const key = Object.keys(enTranslationLoaders).find(k => k.endsWith(suffix))
  return key ? enTranslationLoaders[key]() : Promise.resolve({ default: {} })
}

async function loadEnglishTranslations() {
  if (enLoaded) return
  enLoaded = true
  await Promise.allSettled([
    loadEnFile('items').then(m => { enItemTranslations.value = m.default }),
    loadEnFile('countries').then(m => { enCountryTranslations.value = m.default }),
    loadEnFile('partners').then(m => { enPartnerTranslations.value = m.default }),
    loadEnFile('timeline_events').then(m => { enTimelineEventTranslations.value = m.default }),
    loadEnFile('collections').then(m => { enCollectionTranslations.value = m.default }),
  ])
  // Seed English into the detail-view cache too
  if (!translationsCache.value['en']) {
    translationsCache.value = { ...translationsCache.value, en: enItemTranslations.value }
  }
}

async function loadLangTranslations(lang) {
  if (translationsCache.value[lang]) return
  try {
    const m = await import(`@inventory-data/translations/items.${lang}.json`)
    translationsCache.value = { ...translationsCache.value, [lang]: m.default }
  } catch {
    translationsCache.value = { ...translationsCache.value, [lang]: {} }
  }
}

// Call immediately so lists are populated as soon as the app boots
loadEnglishTranslations()

// ── Label helpers (always English) ─────────────────────────────────────────

function itemLabel(item) {
  if (!item) return ''
  return mdStrip(enItemTranslations.value[item.id]?.name ?? item.internal_name ?? item.id)
}

function countryLabel(countryId) {
  if (!countryId) return ''
  const fallback = countries.value.find(c => c.id === countryId)
  return mdStrip(enCountryTranslations.value[countryId]?.name ?? fallback?.internal_name ?? countryId)
}

function partnerLabel(partnerId) {
  if (!partnerId) return ''
  const fallback = partners.value.find(p => p.id === partnerId)
  return mdStrip(enPartnerTranslations.value[partnerId]?.name ?? fallback?.id ?? partnerId)
}

// ── Lookup maps ────────────────────────────────────────────────────────────

const itemById = computed(() => {
  const m = {}
  for (const item of items.value) m[item.id] = item
  return m
})

// ── Exhibitions ────────────────────────────────────────────────────────────
//
// Imported as generic Collections, nested under a dedicated "Virtual
// Exhibitions" marker collection (backward_compatibility
// "mwnf3_sharing_history:sh_exhibitions:root:awe", a child of the Sharing
// History project collection, created by the importer's
// sh-exhibition-root-keying step). From that anchor: exhibitions are its
// children, themes are an exhibition's children, and — unlike the mwnf3
// datasets — a theme's children are SUBTHEMES ("Chapters" in the legacy UI),
// a full third narrative level with its own intro, quotation and item grid.

const EXHIBITIONS_MARKER_BC = 'mwnf3_sharing_history:sh_exhibitions:root:awe'

const exhibitions = computed(() => {
  const marker = collections.value.find(c => c.backward_compatibility === EXHIBITIONS_MARKER_BC)
  if (!marker) return []
  return collections.value
    .filter(c => c.parent_id === marker.id)
    .sort((a, b) => (a.display_order ?? 9999) - (b.display_order ?? 9999))
})

function exhibitionById(id) {
  return exhibitions.value.find(e => e.id === id) ?? null
}

// Country-specific "National Context" variants of an exhibition
// (…:sh_national_context_exhibitions:{country}:{exh}) are attached under
// the exhibition collection but are NOT themes — they carry no English
// translations and legacy renders them through a separate country
// selector. Keep them out of the theme tree.
const NC_BC_PREFIX = 'mwnf3_sharing_history:sh_national_context_'

function exhibitionThemes(exhibitionId) {
  return collections.value
    .filter(c => c.parent_id === exhibitionId && !c.backward_compatibility?.startsWith(NC_BC_PREFIX))
    .sort((a, b) => (a.display_order ?? 9999) - (b.display_order ?? 9999))
    .map(theme => ({
      ...theme,
      chapters: collections.value
        .filter(c => c.parent_id === theme.id)
        .sort((a, b) => (a.display_order ?? 9999) - (b.display_order ?? 9999)),
    }))
}

function exhibitionThemeById(exhibitionId, themeId) {
  return exhibitionThemes(exhibitionId).find(t => t.id === themeId) ?? null
}

function chapterById(exhibitionId, themeId, chapterId) {
  const theme = exhibitionThemeById(exhibitionId, themeId)
  return theme?.chapters.find(c => c.id === chapterId) ?? null
}

// ── Historical Background ──────────────────────────────────────────────────
//
// SH-only: per-country multi-page illustrated essays (+ historical maps),
// plus one "general text" record (country_id null). Imported as regular
// collections; resolved structurally by backward_compatibility prefix.
// Records: …:sh_countries_historicalbackground:{hb_id}; each record's pages
// are its child collections (…_pages:{page_id}). Note: record 20 is the USA
// project's "Historical Profile / Germany", leaked into awe by a known
// importer hard-coding (#1494) — shipped with a written disposition.

const HB_RECORD_BC_PREFIX = 'mwnf3_sharing_history:sh_countries_historicalbackground:'

const historicalBackgroundRecords = computed(() =>
  collections.value
    .filter(c => c.backward_compatibility?.startsWith(HB_RECORD_BC_PREFIX))
    .sort((a, b) => (a.display_order ?? 9999) - (b.display_order ?? 9999))
)

// The project-level introduction (legacy gn='yes'), if present.
const historicalBackgroundGeneral = computed(
  () => historicalBackgroundRecords.value.find(r => !r.country_id) ?? null
)

// Country profiles, alphabetical by (English) country label — the view sorts.
const historicalBackgroundProfiles = computed(() =>
  historicalBackgroundRecords.value.filter(r => r.country_id)
)

function historicalBackgroundPages(recordId) {
  return collections.value
    .filter(c => c.parent_id === recordId)
    .sort((a, b) => (a.display_order ?? 9999) - (b.display_order ?? 9999))
}

// ── General Historical Background (project-level) ──────────────────────────
//
// The legacy "Historical Background" nav section — distinct from the
// per-country Historical Profiles above. Imported by the sh-hb-general
// step (#1498) as a marker subtree under the project root:
// perspectives (Arab / Ottoman / European Perspective,
// historical_background_pages.php) and the "Read more" topics
// (historical_background_readmore.php — titles only; legacy never filled
// their texts in).

const HB_GENERAL_ROOT_BC = 'mwnf3_sharing_history:sh_project_about_historical_background:root:awe'
const HB_TOPICS_ROOT_BC = 'mwnf3_sharing_history:sh_project_about_topics:root:awe'

const hbGeneralPerspectives = computed(() => {
  const root = collections.value.find(c => c.backward_compatibility === HB_GENERAL_ROOT_BC)
  if (!root) return []
  return collections.value
    .filter(c => c.parent_id === root.id && c.backward_compatibility !== HB_TOPICS_ROOT_BC)
    .sort((a, b) => (a.display_order ?? 9999) - (b.display_order ?? 9999))
})

const hbGeneralTopics = computed(() => {
  const root = collections.value.find(c => c.backward_compatibility === HB_TOPICS_ROOT_BC)
  if (!root) return []
  return collections.value
    .filter(c => c.parent_id === root.id)
    .sort((a, b) => (a.display_order ?? 9999) - (b.display_order ?? 9999))
})

// ── Timelines ──────────────────────────────────────────────────────────────
//
// SH timelines are per (country × exhibition), each bound to its exhibition
// collection. Timelines with collection_id null are the legacy "Permanent
// Collection timeline" (hidden sentinel exhibition 2 — remapped by the
// exporter). The legacy timeline page filters by period × country ×
// exhibition, with a thematic-vs-Permanent-Collection toggle.

const pcTimelines = computed(() => timelines.value.filter(t => t.collection_id === null))
const thematicTimelines = computed(() => timelines.value.filter(t => t.collection_id !== null))

// ── Item cross-links: Artistic Introduction pages / Exhibitions that
// feature a given item ───────────────────────────────────────────────────
//
// No separate export is needed for this: collections.json already lists
// each collection's items[] (used to render Artistic Introduction pages and
// Exhibition theme/page grids), so "which collections reference this item"
// is just a client-side reverse lookup over the same data. See Epic 12 in
// the islamicart parity backlog.

function collectionsContainingItem(itemId) {
  return collections.value.filter(c => c.items?.some(it => it.id === itemId))
}

function exhibitionLinksForItem(itemId) {
  const marker = collections.value.find(c => c.backward_compatibility === EXHIBITIONS_MARKER_BC)
  if (!marker) return []
  const links = []
  const seen = new Set()
  for (const c of collectionsContainingItem(itemId)) {
    // SH items can be attached at three depths: to the exhibition itself
    // (rel_*_exhibitions), to a theme (rel_*_themes), or to a chapter/
    // subtheme (rel_*_subthemes — handled with chapter granularity by
    // chapterLinksForItem; collapsed to its theme here).
    let exhibition = null
    let themeId = null
    if (c.parent_id === marker.id) {
      exhibition = c
    } else {
      const parent = collections.value.find(t => t.id === c.parent_id)
      if (parent && parent.parent_id === marker.id) {
        // c is a theme directly under an exhibition
        exhibition = parent
        themeId = c.id
      } else {
        const grandparent = parent && collections.value.find(e => e.id === parent.parent_id)
        if (grandparent && grandparent.parent_id === marker.id) {
          // c is a chapter under a theme under an exhibition
          exhibition = grandparent
          themeId = parent.id
        }
      }
    }
    if (!exhibition) continue
    const key = `${exhibition.id}:${themeId ?? ''}`
    if (seen.has(key)) continue
    seen.add(key)
    links.push({
      exhibitionId: exhibition.id,
      themeId,
      label: enCollectionTranslations.value[exhibition.id]?.title ?? exhibition.internal_name,
    })
  }
  return links
}

// SH adds a third level: an item can also be attached to a chapter
// (subtheme). Walk one extra parent step: chapter → theme → exhibition.
function chapterLinksForItem(itemId) {
  const marker = collections.value.find(c => c.backward_compatibility === EXHIBITIONS_MARKER_BC)
  if (!marker) return []
  const links = []
  for (const c of collectionsContainingItem(itemId)) {
    if (c.type !== 'subtheme') continue
    const theme = collections.value.find(t => t.id === c.parent_id)
    const ex = theme && collections.value.find(e => e.id === theme.parent_id)
    if (!ex || ex.parent_id !== marker.id) continue
    links.push({
      exhibitionId: ex.id,
      themeId: theme.id,
      chapterId: c.id,
      label: enCollectionTranslations.value[c.id]?.title ?? c.internal_name,
      exhibitionLabel: enCollectionTranslations.value[ex.id]?.title ?? ex.internal_name,
    })
  }
  return links
}

// ── Markdown helpers ───────────────────────────────────────────────────────

// Full block markdown → HTML (for prose sections)
function md(text) {
  if (!text) return ''
  return marked.parse(text, { breaks: true })
}

// Inline markdown → HTML without block-level <p> wrapping (for titles, names)
function mdInline(text) {
  if (!text) return ''
  return marked.parseInline(text)
}

// Strip all markdown to plain text (for alt attributes, search matching, etc.)
// Walks marked's inline token tree directly — no HTML intermediate, no regex.
function mdStrip(text) {
  if (!text) return ''
  function tokensToText(tokens) {
    return tokens.map(t => {
      if (t.tokens?.length) return tokensToText(t.tokens)
      if (t.type === 'image') return t.text ?? ''   // alt text
      if (t.type === 'html') return ''              // discard raw HTML nodes
      if (t.type === 'br' || t.type === 'softbreak') return ' '
      return t.text ?? ''
    }).join('')
  }
  return tokensToText(marked.Lexer.lexInline(text))
}

export function useInventoryData() {
  return {
    items,
    publicItems,
    countries,
    partners,
    timelines,
    pcTimelines,
    thematicTimelines,
    timelineEvents,
    collections,
    availableLangs,
    defaultLang,
    enItemTranslations,
    enCountryTranslations,
    enPartnerTranslations,
    enTimelineEventTranslations,
    enCollectionTranslations,
    translationsCache,
    loadEnglishTranslations,
    loadLangTranslations,
    itemLabel,
    countryLabel,
    partnerLabel,
    itemProjectKey,
    itemById,
    exhibitions,
    exhibitionById,
    exhibitionThemes,
    exhibitionThemeById,
    chapterById,
    exhibitionLinksForItem,
    chapterLinksForItem,
    historicalBackgroundRecords,
    historicalBackgroundGeneral,
    historicalBackgroundProfiles,
    historicalBackgroundPages,
    hbGeneralPerspectives,
    hbGeneralTopics,
    md,
    mdInline,
    mdStrip,
  }
}
