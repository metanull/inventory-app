import { ref, computed } from 'vue'
import { marked } from 'marked'

import manifestData from '@inventory-data/manifest.json'
import galleryData from '@inventory-data/gallery.json'
import itemsData from '@inventory-data/items.json'
import tagsData from '@inventory-data/tags.json'
import partnersData from '@inventory-data/partners.json'
import countriesData from '@inventory-data/countries.json'
import languagesData from '@inventory-data/languages.json'
import dynastiesData from '@inventory-data/dynasties.json'
import glossaryData from '@inventory-data/glossary.json'
import timelinesData from '@inventory-data/timelines.json'
import timelineEventsData from '@inventory-data/timeline_events.json'

// ── Static entity data ─────────────────────────────────────────────────────
// Language-independent; every human-readable string lives under translations/.

export const manifest = manifestData
export const gallery = galleryData
export const items = ref(itemsData)
export const tags = ref(tagsData)
export const partners = ref(partnersData)
export const countries = ref(countriesData)
export const languages = ref(languagesData)
export const dynasties = ref(dynastiesData)
export const glossary = ref(glossaryData)
export const timelines = ref(timelinesData)
export const timelineEvents = ref(timelineEventsData)

// The gallery's own UI languages (thg_gallery_lang). Item sheets offer more —
// whatever languages the record itself carries — which is why languages.json
// flags the two apart rather than shipping one list.
export const siteLanguages = gallery.languages ?? ['en']
export const defaultLang = siteLanguages.includes('en') ? 'en' : siteLanguages[0]

// Legacy media server for the gallery chrome images only. `image_path`,
// `banner_image_path` and `homepage_image_path` were never imported into
// inventory storage, so the package ships the legacy path and the viewer
// supplies the host — the one exception to the absolute-image-URL convention.
const LEGACY_IMAGES =
  import.meta.env.VITE_LEGACY_IMAGES_URL ?? 'https://images.museumwnf.org'

/** Legacy media URL. `size` ∈ zoom | hi_res | lo_res | small. */
export function legacyImage(path, size = 'hi_res') {
  if (!path) return null
  return `${LEGACY_IMAGES}/${size}/${path}`
}

// ── Lookup maps ────────────────────────────────────────────────────────────

export const itemById = computed(() => new Map(items.value.map(i => [i.id, i])))
export const partnerById = computed(() => new Map(partners.value.map(p => [p.id, p])))
export const countryById = computed(() => new Map(countries.value.map(c => [c.id, c])))
export const tagById = computed(() => new Map(tags.value.map(t => [t.id, t])))
export const dynastyById = computed(() => new Map(dynasties.value.map(d => [d.id, d])))
export const glossaryById = computed(() => new Map(glossary.value.map(g => [g.id, g])))
export const timelineById = computed(() => new Map(timelines.value.map(t => [t.id, t])))
export const languageByCode = computed(() => new Map(languages.value.map(l => [l.code, l])))

// Legacy dbUid ⇄ item. The public item URL keeps the dbUid path, which is
// exactly `backward_compatibility` with ':' swapped for '/' — the identity rule
// in dxa-legacy-analysis.md §4.2. Matching is case-insensitive because Sharing
// History stores its keys lowercase.
export const itemByUid = computed(() => {
  const m = new Map()
  for (const item of items.value) {
    if (item.backward_compatibility) m.set(item.backward_compatibility.toLowerCase(), item)
  }
  return m
})

/** `mwnf3:objects:EPM:uk:Mus21:41` → `mwnf3/objects/EPM/uk/Mus21/41` */
export function itemUidPath(item) {
  return (item?.backward_compatibility ?? '').split(':').join('/')
}

/** The legacy item-sheet route for an item in a given language. */
export function itemRoute(item, lang = defaultLang) {
  return `/database-item/${itemUidPath(item)}/${lang}`
}

export function itemFromUidPath(path, ) {
  return itemByUid.value.get(String(path).split('/').join(':').toLowerCase()) ?? null
}

// Partner identity: `mwnf3:museums:Mus21:ua` → { legacyId: 'Mus21', country: 'ua' }.
// Legacy's partner URL also carried a project id; the inventory model has no
// per-partner project (partners.project_id is null for every imported museum),
// so the route drops that segment rather than inventing one.
export function partnerKey(partner) {
  const parts = (partner?.backward_compatibility ?? '').split(':')
  return { legacyId: parts[2] ?? partner?.id, countryCode: parts[3] ?? '' }
}

export function partnerRoute(partner, lang = defaultLang) {
  const { legacyId, countryCode } = partnerKey(partner)
  return `/partner/${countryCode}/${legacyId}/${lang}`
}

export function partnerObjectsRoute(partner, page = 1) {
  const { legacyId, countryCode } = partnerKey(partner)
  return `/partner-objects/${countryCode}/${legacyId}/${page}`
}

export function partnerFromKey(countryCode, legacyId) {
  return partners.value.find(p => {
    const k = partnerKey(p)
    return k.legacyId === legacyId && k.countryCode === countryCode
  }) ?? null
}

// ── Translations ───────────────────────────────────────────────────────────
//
// One file per entity per language; a file is simply absent when that entity
// has no translation in that language, so every load path must tolerate a miss.
// English is loaded eagerly (it drives every list and label); other languages
// are loaded on demand by the item sheet and the partner profile.

const loaders = import.meta.glob('@inventory-data/translations/*.json')

function loaderFor(entity, lang) {
  const suffix = `/translations/${entity}.${lang}.json`
  const key = Object.keys(loaders).find(k => k.endsWith(suffix))
  return key ? loaders[key] : null
}

/** Which languages this export actually has a file for, per entity. */
export function availableLanguages(entity) {
  const prefix = `/translations/${entity}.`
  return Object.keys(loaders)
    .filter(k => k.includes(prefix))
    .map(k => k.slice(k.lastIndexOf(prefix) + prefix.length, -'.json'.length))
}

// cache: `${entity}.${lang}` → record map (or {} when the file is absent)
const cache = ref({})

export async function loadTranslations(entity, lang) {
  const key = `${entity}.${lang}`
  if (cache.value[key]) return cache.value[key]
  const load = loaderFor(entity, lang)
  let data = {}
  if (load) {
    try {
      data = (await load()).default ?? {}
    } catch {
      data = {}
    }
  }
  cache.value = { ...cache.value, [key]: data }
  return data
}

export function translations(entity, lang) {
  return cache.value[`${entity}.${lang}`] ?? {}
}

/** One record's translation, falling back to English then to nothing. */
export function tr(entity, id, lang) {
  return translations(entity, lang)[id] ?? translations(entity, defaultLang)[id] ?? {}
}

const EN_ENTITIES = [
  'items', 'partners', 'countries', 'glossary', 'dynasties', 'timeline_events',
]

let englishReady = null
export function loadEnglish() {
  if (!englishReady) {
    englishReady = Promise.all(EN_ENTITIES.map(e => loadTranslations(e, defaultLang)))
  }
  return englishReady
}
loadEnglish()

// ── English labels (lists, dropdowns, alt text) ────────────────────────────

export function itemLabel(item) {
  if (!item) return ''
  return mdStrip(tr('items', item.id, defaultLang).name ?? item.internal_name ?? '')
}

export function countryLabel(countryId) {
  if (!countryId) return ''
  return tr('countries', countryId, defaultLang).name
    ?? countryById.value.get(countryId)?.internal_name
    ?? countryId
}

export function partnerLabel(partnerId) {
  if (!partnerId) return ''
  return mdStrip(tr('partners', partnerId, defaultLang).name ?? '')
}

export function dynastyLabel(dynastyId) {
  return mdStrip(tr('dynasties', dynastyId, defaultLang).name ?? '')
}

// ── Sibling galleries ──────────────────────────────────────────────────────
//
// Decision Q3: these are reference objects, not resolved links. The exporter
// records identity plus whatever the import carried; where a `legacy_host` came
// across we can link to it, and where it did not the entry still renders — it
// just does not become an anchor. Legacy showed four random siblings from the
// active roster; the package ships the whole roster and the viewer picks.

export const siblingGalleries = computed(() =>
  (gallery.sibling_galleries ?? []).filter(g => !g.hidden)
)

export function siblingUrl(sibling) {
  return sibling?.legacy_host || null
}

/** Legacy's `/thg/galleries/featured`: four at random, reshuffled per visit. */
export function pickSiblings(count = 4) {
  const pool = [...siblingGalleries.value]
  for (let i = pool.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1))
    ;[pool[i], pool[j]] = [pool[j], pool[i]]
  }
  return pool.slice(0, count)
}

// ── Markdown ───────────────────────────────────────────────────────────────

export function md(text) {
  if (!text) return ''
  return marked.parse(text, { breaks: true })
}

export function mdInline(text) {
  if (!text) return ''
  return marked.parseInline(text)
}

export function mdStrip(text) {
  if (!text) return ''
  const walk = (tokens) => tokens.map(t => {
    if (t.tokens?.length) return walk(t.tokens)
    if (t.type === 'image') return t.text ?? ''
    if (t.type === 'html') return ''
    if (t.type === 'br' || t.type === 'softbreak') return ' '
    return t.text ?? ''
  }).join('')
  return walk(marked.Lexer.lexInline(text))
}

export function useGalleryData() {
  return {
    manifest, gallery, items, tags, partners, countries, languages,
    dynasties, glossary, timelines, timelineEvents,
    siteLanguages, defaultLang,
    itemById, partnerById, countryById, tagById, dynastyById, glossaryById,
    timelineById, languageByCode, itemByUid,
    itemUidPath, itemRoute, itemFromUidPath,
    partnerKey, partnerRoute, partnerObjectsRoute, partnerFromKey,
    legacyImage,
    loadTranslations, translations, tr, availableLanguages, loadEnglish,
    itemLabel, countryLabel, partnerLabel, dynastyLabel,
    siblingGalleries, siblingUrl, pickSiblings,
    md, mdInline, mdStrip,
  }
}
