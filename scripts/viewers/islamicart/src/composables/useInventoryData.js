import { ref, computed } from 'vue'
import { marked } from 'marked'
import manifestData from '@inventory-data/manifest.json'
import itemsData from '@inventory-data/items.json'
import countriesData from '@inventory-data/countries.json'
import partnersData from '@inventory-data/partners.json'
import dynastiesData from '@inventory-data/dynasties.json'
import timelinesData from '@inventory-data/timelines.json'
import timelineEventsData from '@inventory-data/timeline_events.json'
import collectionsData from '@inventory-data/collections.json'

// Module-level singletons — loaded once, shared across all views
const items = ref(itemsData)
const countries = ref(countriesData)
const partners = ref(partnersData)
const dynasties = ref(dynastiesData)
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

// 'ISL' ("Discover Islamic Art") is always the default/primary project; any other
// exported project (e.g. 'EPM', "Explore Islamic Art Collections") is opt-in —
// mirrors legacy's database.php "Include Explore Islamic Art Collections" checkbox.
function itemProjectKey(item) {
  return projectKeyById.get(item.project_id) ?? null
}

const enItemTranslations = ref({})
const enCountryTranslations = ref({})
const enDynastyTranslations = ref({})
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
    import('@inventory-data/translations/dynasties.en.json')
      .then(m => { enDynastyTranslations.value = m.default }),
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

function dynastyLabel(dynastyId) {
  if (!dynastyId) return ''
  return mdStrip(enDynastyTranslations.value[dynastyId]?.name ?? dynastyId)
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

// Section anchors are resolved by `purpose` (#1505) —
// `backward_compatibility` is informational only and never parsed. The
// export is scoped to one project context, within which each root purpose
// occurs at most once.
function findByPurpose(purpose) {
  return collections.value.find(c => c.purpose === purpose) ?? null
}

// ── Artistic Introduction (legacy "gai") ──────────────────────────────────
//
// Imported as generic Collections, nested under a dedicated "Artistic
// Introduction" marker collection (purpose "artistic-introduction-root", a
// child of the Islamic Art project collection). From that single,
// unambiguous anchor the rest of the tree — root, themes (e.g. "The
// Umayyads"), pages (tabs within a theme, e.g. "Monuments" / "Objects") —
// is just parent_id lookups, no internal_name guessing.

const artIntroRoot = computed(() => {
  const marker = findByPurpose('artistic-introduction-root')
  if (!marker) return null
  return collections.value.find(c => c.parent_id === marker.id) ?? null
})

const artIntroThemes = computed(() => {
  const root = artIntroRoot.value
  if (!root) return []
  const themes = collections.value
    .filter(c => c.parent_id === root.id)
    .sort((a, b) => (a.display_order ?? 9999) - (b.display_order ?? 9999))
  return themes.map(theme => ({
    ...theme,
    pages: collections.value
      .filter(c => c.parent_id === theme.id)
      .sort((a, b) => (a.display_order ?? 9999) - (b.display_order ?? 9999)),
  }))
})

function artIntroThemeById(id) {
  return artIntroThemes.value.find(t => t.id === id) ?? null
}

// ── Exhibitions ────────────────────────────────────────────────────────────
//
// Imported as generic Collections, nested under a dedicated "Virtual
// Exhibitions" marker collection (purpose "exhibitions-root", a child of
// the Islamic Art project collection) — needed because type='exhibition'
// alone is not project-scoped in the legacy schema (shared with Baroque
// Art, Sharing History, etc). From that anchor: exhibitions are its
// children, themes are an exhibition's children, pages are a theme's
// children (tabs). "Introduction" is not a theme — it's the exhibition's
// own translation (extra.intro_header / extra.intro_text) plus items
// attached directly to the exhibition collection itself (not to any
// theme/page).

const exhibitions = computed(() => {
  const marker = findByPurpose('exhibitions-root')
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

function artIntroLinksForItem(itemId) {
  const root = artIntroRoot.value
  if (!root) return []
  const links = []
  const seen = new Set()
  for (const page of collectionsContainingItem(itemId)) {
    // Items are attached to a theme's page; the page's parent is the theme.
    const theme = collections.value.find(c => c.id === page.parent_id)
    if (!theme || theme.parent_id !== root.id || seen.has(theme.id)) continue
    seen.add(theme.id)
    links.push({
      themeId: theme.id,
      label: enCollectionTranslations.value[theme.id]?.title ?? theme.internal_name,
    })
  }
  return links
}

function exhibitionLinksForItem(itemId) {
  const marker = findByPurpose('exhibitions-root')
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
    dynasties,
    timelines,
    timelineEvents,
    collections,
    availableLangs,
    defaultLang,
    enItemTranslations,
    enCountryTranslations,
    enDynastyTranslations,
    enPartnerTranslations,
    enTimelineEventTranslations,
    enCollectionTranslations,
    translationsCache,
    loadEnglishTranslations,
    loadLangTranslations,
    itemLabel,
    countryLabel,
    dynastyLabel,
    partnerLabel,
    itemProjectKey,
    itemById,
    artIntroRoot,
    artIntroThemes,
    artIntroThemeById,
    exhibitions,
    exhibitionById,
    exhibitionThemes,
    exhibitionThemeById,
    artIntroLinksForItem,
    exhibitionLinksForItem,
    md,
    mdInline,
    mdStrip,
  }
}
