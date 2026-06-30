import { ref, computed } from 'vue'
import { marked } from 'marked'
import manifestData from '@inventory-data/manifest.json'
import itemsData from '@inventory-data/items.json'
import countriesData from '@inventory-data/countries.json'
import partnersData from '@inventory-data/partners.json'
import dynastiesData from '@inventory-data/dynasties.json'

// Module-level singletons — loaded once, shared across all views
const items = ref(itemsData)
const countries = ref(countriesData)
const partners = ref(partnersData)
const dynasties = ref(dynastiesData)
const availableLangs = ref(manifestData.languages ?? [])
const defaultLang = (manifestData.languages ?? []).includes('en')
  ? 'en'
  : ((manifestData.languages ?? [])[0] ?? 'en')

const enItemTranslations = ref({})
const enCountryTranslations = ref({})
const enDynastyTranslations = ref({})
const enPartnerTranslations = ref({})
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
  return enItemTranslations.value[item.id]?.name ?? item.internal_name ?? item.id
}

function countryLabel(countryId) {
  if (!countryId) return ''
  const fallback = countries.value.find(c => c.id === countryId)
  return enCountryTranslations.value[countryId]?.name ?? fallback?.internal_name ?? countryId
}

function dynastyLabel(dynastyId) {
  if (!dynastyId) return ''
  return enDynastyTranslations.value[dynastyId]?.name ?? dynastyId
}

function partnerLabel(partnerId) {
  if (!partnerId) return ''
  const fallback = partners.value.find(p => p.id === partnerId)
  return enPartnerTranslations.value[partnerId]?.name ?? fallback?.id ?? partnerId
}

// ── Lookup maps ────────────────────────────────────────────────────────────

const itemById = computed(() => {
  const m = {}
  for (const item of items.value) m[item.id] = item
  return m
})

// ── Markdown helper ────────────────────────────────────────────────────────

function md(text) {
  if (!text) return ''
  return marked.parse(text, { breaks: true })
}

export function useInventoryData() {
  return {
    items,
    countries,
    partners,
    dynasties,
    availableLangs,
    defaultLang,
    enItemTranslations,
    enCountryTranslations,
    enDynastyTranslations,
    enPartnerTranslations,
    translationsCache,
    loadEnglishTranslations,
    loadLangTranslations,
    itemLabel,
    countryLabel,
    dynastyLabel,
    partnerLabel,
    itemById,
    md,
  }
}
