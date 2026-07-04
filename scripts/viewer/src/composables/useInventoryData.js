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

// ── Artistic Introduction (legacy "gai") ──────────────────────────────────
//
// Imported as generic Collections, identified by internal_name prefix:
// root "mwnf3_artintro_{id}", themes "mwnf3_artintro_theme_{id}"
// (e.g. "The Umayyads"), pages "mwnf3_artintro_page_{id}" (tabs within a
// theme, e.g. "Monuments" / "Objects"). Reconstructed here into a tree since
// collections.json only carries parent_id, not the theme/page distinction.

const artIntroRoot = computed(() =>
  collections.value.find(
    c => c.internal_name?.startsWith('mwnf3_artintro_') &&
      !c.internal_name.startsWith('mwnf3_artintro_theme_') &&
      !c.internal_name.startsWith('mwnf3_artintro_page_')
  ) ?? null
)

const artIntroThemes = computed(() => {
  const root = artIntroRoot.value
  if (!root) return []
  const themes = collections.value
    .filter(c => c.internal_name?.startsWith('mwnf3_artintro_theme_') && c.parent_id === root.id)
    .sort((a, b) => (a.display_order ?? 9999) - (b.display_order ?? 9999))
  return themes.map(theme => ({
    ...theme,
    pages: collections.value
      .filter(c => c.internal_name?.startsWith('mwnf3_artintro_page_') && c.parent_id === theme.id)
      .sort((a, b) => (a.display_order ?? 9999) - (b.display_order ?? 9999)),
  }))
})

function artIntroThemeById(id) {
  return artIntroThemes.value.find(t => t.id === id) ?? null
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
    itemById,
    artIntroRoot,
    artIntroThemes,
    artIntroThemeById,
    md,
    mdInline,
    mdStrip,
  }
}
