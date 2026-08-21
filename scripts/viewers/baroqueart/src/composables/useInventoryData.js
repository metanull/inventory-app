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

// The Baroque Art package exports a single project ('BAR'); this helper stays
// generic in case a companion project is ever exported alongside it.
function itemProjectKey(item) {
  return projectKeyById.get(item.project_id) ?? null
}

const enItemTranslations = ref({})
const enCountryTranslations = ref({})
const enPartnerTranslations = ref({})
const enTimelineEventTranslations = ref({})
const enCollectionTranslations = ref({})
const translationsCache = ref({}) // lang -> item translations (for detail view)

let enLoaded = false

async function loadEnglishTranslations() {
  if (enLoaded) return
  enLoaded = true
  await Promise.allSettled([
    import('@inventory-data/translations/items.en.json')
      .then(m => { enItemTranslations.value = m.default }),
    import('@inventory-data/translations/countries.en.json')
      .then(m => { enCountryTranslations.value = m.default }),
    import('@inventory-data/translations/partners.en.json')
      .then(m => { enPartnerTranslations.value = m.default }),
    import('@inventory-data/translations/timeline_events.en.json')
      .then(m => { enTimelineEventTranslations.value = m.default }),
    import('@inventory-data/translations/collections.en.json')
      .then(m => { enCollectionTranslations.value = m.default }),
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
// "mwnf3:exhibitions:root:BAR", a child of the Baroque Art project
// collection, created by the importer's project-exhibition-root-keying
// step) — needed because neither type='exhibition' nor the
// mwnf3:exhibitions:{id} backward_compatibility key are project-scoped in
// the legacy schema (shared with Islamic Art, Sharing History, etc). From
// that anchor: exhibitions are its children, themes are an exhibition's
// children, pages are a theme's children (tabs). "Introduction" is not a
// theme — it's the exhibition's own translation (extra.intro_header /
// extra.intro_text) plus items attached directly to the exhibition
// collection itself (not to any theme/page).

const EXHIBITIONS_MARKER_BC = 'mwnf3:exhibitions:root:BAR'

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

function exhibitionThemes(exhibitionId) {
  return collections.value
    .filter(c => c.parent_id === exhibitionId)
    .sort((a, b) => (a.display_order ?? 9999) - (b.display_order ?? 9999))
    .map(theme => ({
      ...theme,
      pages: collections.value
        .filter(c => c.parent_id === theme.id)
        .sort((a, b) => (a.display_order ?? 9999) - (b.display_order ?? 9999)),
    }))
}

function exhibitionThemeById(exhibitionId, themeId) {
  return exhibitionThemes(exhibitionId).find(t => t.id === themeId) ?? null
}

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
    // Either attached directly to the exhibition itself (an "introduction"
    // item — see the Exhibitions section comment above), or to a page
    // nested under a theme nested under the exhibition.
    let exhibition = null
    let themeId = null
    if (c.parent_id === marker.id) {
      exhibition = c
    } else {
      const theme = collections.value.find(t => t.id === c.parent_id)
      const ex = theme && collections.value.find(e => e.id === theme.parent_id)
      if (ex && ex.parent_id === marker.id) {
        exhibition = ex
        themeId = theme.id
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
    countries,
    partners,
    timelines,
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
    exhibitionLinksForItem,
    md,
    mdInline,
    mdStrip,
  }
}
