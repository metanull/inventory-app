import { ref, computed } from 'vue'
import { marked } from 'marked'

import manifestData from '@inventory-data/manifest.json'
import exhibitionData from '@inventory-data/exhibition.json'
import themesData from '@inventory-data/themes.json'
import relatedContentData from '@inventory-data/related_content.json'
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
export const exhibition = exhibitionData
export const themeTree = ref(themesData)
export const relatedContent = ref(relatedContentData)
export const items = ref(itemsData)
export const tags = ref(tagsData)
export const partners = ref(partnersData)
export const countries = ref(countriesData)
export const languages = ref(languagesData)
export const dynasties = ref(dynastiesData)
export const glossary = ref(glossaryData)
export const timelines = ref(timelinesData)
export const timelineEvents = ref(timelineEventsData)

// `languages` is the UI roster (thg_gallery_lang); `languages_enabled` is what
// `exhibition_i18n.enabled` actually publishes. The two differ here and the
// difference is load bearing: German has full theme translations and a live
// instance that answers `exhibitionTitle: null`. Per decision Q2 the sites are
// per-language builds, so the *enabled* list is what a build may offer.
export const siteLanguages = exhibition.languages_enabled?.length
  ? exhibition.languages_enabled
  : (exhibition.languages ?? ['en'])
export const defaultLang = siteLanguages.includes('en') ? 'en' : siteLanguages[0]

// Legacy media server for the exhibition chrome images and the related-content
// PDFs only. `banner_image_path`, `homepage_image_path` and `document_path`
// were never imported into inventory storage, so the package ships the legacy
// path and the viewer supplies the host — the one exception to the
// absolute-image-URL convention.
const LEGACY_IMAGES =
  import.meta.env.VITE_LEGACY_IMAGES_URL ?? 'https://images.museumwnf.org'

/** Legacy media URL. `size` ∈ zoom | hi_res | lo_res | small | full. */
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

// countries.json is keyed by the inventory id (ISO 3166-1 alpha-3), but the
// legacy two-letter code is what related_content and the timeline keyspaces
// carry. `code` is the country's own backward_compatibility, so this is the
// bridge between the two — and the reason it is a lookup rather than a parse
// is that several legacy codes are not ISO (`uk`, `pa`, `qt`, `ua`, `sb`).
export const countryByCode = computed(
  () => new Map(countries.value.filter(c => c.code).map(c => [c.code, c]))
)

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

export function itemFromUidPath(path) {
  return itemByUid.value.get(String(path).split('/').join(':').toLowerCase()) ?? null
}

// Partner identity: `mwnf3:museums:Mus21:ua` → { legacyId: 'Mus21', country: 'ua' }.
// Legacy's partner URL also carried a project id; the inventory model has no
// per-partner project (partners.project_id is null for every imported museum),
// so the route drops that segment rather than inventing one.
//
// Sharing History keys its partners differently — `mwnf3_sharing_history:
// sh_partners:at_01_d` has no country segment at all, the country is the key's
// own prefix. Reading parts[3] there yields an empty URL segment, so the two
// shapes are handled apart rather than by index.
export function partnerKey(partner) {
  const bc = partner?.backward_compatibility ?? ''
  const parts = bc.split(':')
  if (parts[0] === 'mwnf3_sharing_history') {
    const key = parts[2] ?? partner?.id ?? ''
    return { legacyId: key, countryCode: key.split('_')[0] }
  }
  return { legacyId: parts[2] ?? partner?.id, countryCode: parts[3] ?? '' }
}

// Institutions (monument owners) and museums both live in partners.json — the
// package ships the union of legacy's /partners and /institutions because a
// static package has no endpoints to split them across. The viewer routes by
// `type`, which is what legacy's two page templates keyed off.
export function isInstitution(partner) {
  return partner?.type === 'institution'
}

export function partnerRoute(partner, lang = defaultLang) {
  const { legacyId, countryCode } = partnerKey(partner)
  const base = isInstitution(partner) ? 'institution' : 'partner'
  return `/${base}/${countryCode}/${legacyId}/${lang}`
}

export function partnerObjectsRoute(partner, page = 1) {
  const { legacyId, countryCode } = partnerKey(partner)
  const base = isInstitution(partner) ? 'institution-monuments' : 'partner-objects'
  return `/${base}/${countryCode}/${legacyId}/${page}`
}

export function partnerFromKey(countryCode, legacyId) {
  return partners.value.find(p => {
    const k = partnerKey(p)
    return k.legacyId === legacyId && k.countryCode === countryCode
  }) ?? null
}

// E6: a hidden museum is exported but must not appear on any list or profile
// page. Its items still render — legacy hides the museum, not the object.
const hiddenPartners = new Set(exhibition.hidden_partner_ids ?? [])
export function isHiddenPartner(partner) {
  return hiddenPartners.has(partner?.id)
}

/** Every partner the site may list: museums and institutions, minus the hidden. */
export const visiblePartners = computed(() =>
  partners.value.filter(p => !hiddenPartners.has(p.id))
)

// ── Themes ─────────────────────────────────────────────────────────────────
//
// themes.json is the ordered tree: top-level themes, each with its sub-themes
// and its curated picture selections. Two rules the data fixes rather than
// taste:
//
//   * Theme 0 ("About the Exhibition") is an ordinary top-level theme that the
//     legacy client renders at /about and *skips* on /themes. Its display order
//     is 1, so the themes list starts at display order 2 and numbers those
//     "Theme I" upwards — which is why `romanFor` subtracts one.
//   * The theme id in the keyspace is not the display order. The route carries
//     `display_order - 1`, exactly as legacy's `theme.display - 1` did, so a
//     legacy URL pasted after the `#` lands on the same theme.

export const themes = computed(() => themeTree.value)

/** The About theme — display order 1, rendered at /about, absent from /themes. */
export const aboutTheme = computed(
  () => themes.value.find(t => t.display_order === 1) ?? null
)

/** The themes the /themes page lists: everything after the About theme. */
export const listedThemes = computed(() =>
  themes.value.filter(t => t.display_order > 1)
)

/** Route id ⇄ theme. Legacy's `/theme/:id` carries `display_order - 1`. */
export function themeByRouteId(id) {
  const n = Number(id)
  return themes.value.find(t => t.display_order - 1 === n) ?? null
}

export function themeRouteId(theme) {
  return (theme?.display_order ?? 1) - 1
}

/** Every node of the tree, top-level and sub-theme alike. */
export const allThemeNodes = computed(() => {
  const out = []
  for (const theme of themes.value) {
    out.push(theme)
    for (const sub of theme.sub_themes ?? []) out.push(sub)
  }
  return out
})

export const themeById = computed(
  () => new Map(allThemeNodes.value.map(t => [t.id, t]))
)

export const themeByBc = computed(
  () => new Map(allThemeNodes.value.map(t => [t.backward_compatibility, t]))
)

/** Legacy numbered its themes in Roman numerals, counting from the About theme. */
export function romanFor(displayOrder) {
  const lookup = [
    ['M', 1000], ['CM', 900], ['D', 500], ['CD', 400], ['C', 100], ['XC', 90],
    ['L', 50], ['XL', 40], ['X', 10], ['IX', 9], ['V', 5], ['IV', 4], ['I', 1],
  ]
  let n = displayOrder - 1
  let out = ''
  for (const [sym, value] of lookup) {
    while (n >= value) { out += sym; n -= value }
  }
  return out
}

/** The picture selections of a theme, ordered as the curator set them. */
export function themePictures(theme) {
  return [...(theme?.pictures ?? [])].sort(
    (a, b) => (a.display_order ?? 0) - (b.display_order ?? 0)
  )
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
  'themes',
]

let englishReady = null
export function loadEnglish() {
  if (!englishReady) {
    englishReady = Promise.all(EN_ENTITIES.map(e => loadTranslations(e, defaultLang)))
  }
  return englishReady
}
loadEnglish()

// ── Theme text ─────────────────────────────────────────────────────────────
//
// translations/themes.<lang>.json is keyed two ways: by theme id for the
// theme's own title/quote/presentation, and by `<theme id>/<picture item id>`
// for the curated text of one picture *in that theme*. The same picture in two
// themes carries two different descriptions, which is why the pivot key exists.

export function themeText(theme, lang = defaultLang) {
  return tr('themes', theme?.id, lang)
}

export function pictureText(theme, picture, lang = defaultLang) {
  if (!theme?.id || !picture?.picture_item_id) return {}
  return tr('themes', `${theme.id}/${picture.picture_item_id}`, lang)
}

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

/** The same label from a legacy two-letter code (`uk` → United Kingdom). */
export function countryLabelFromCode(code) {
  if (!code) return ''
  const country = countryByCode.value.get(code)
  return country ? countryLabel(country.id) : code
}

export function partnerLabel(partnerId) {
  if (!partnerId) return ''
  return mdStrip(tr('partners', partnerId, defaultLang).name ?? '')
}

export function dynastyLabel(dynastyId) {
  return mdStrip(tr('dynasties', dynastyId, defaultLang).name ?? '')
}

/** The exhibition's own per-language chrome text. */
export function exhibitionTitle(lang = defaultLang) {
  return exhibition.titles?.[lang] ?? exhibition.titles?.en ?? ''
}

export function exhibitionSubtitle(lang = defaultLang) {
  return exhibition.subtitles?.[lang] ?? exhibition.subtitles?.en ?? ''
}

export function exhibitionHeadline(lang = defaultLang) {
  return exhibition.headlines?.[lang] ?? exhibition.headlines?.en ?? ''
}

export function bannerCaption(lang = defaultLang) {
  return exhibition.banner_captions?.[lang] ?? exhibition.banner_captions?.en ?? ''
}

// ── Sibling sites ──────────────────────────────────────────────────────────
//
// Decision Q3: these are reference objects, not resolved links. The exporter
// records identity plus whatever the import carried; where a `legacy_host` came
// across we can link to it, and where it did not the entry still renders — it
// just does not become an anchor.

export const siblingSites = computed(() =>
  (exhibition.sibling_sites ?? []).filter(s => !s.hidden)
)

export function siblingUrl(sibling) {
  return sibling?.legacy_host || null
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

/**
 * Rewrite this exhibition's own absolute legacy URLs to in-app hash routes.
 *
 * The curated catalogue keys (`txtCollection`, `txtPartners`, `txtTimeline`)
 * link to the exhibition's own sections by their full legacy address —
 * `https://exhibitions.museumwnf.org/the_use_of_colours_in_art/en/themes`.
 * Left alone those send a visitor off this build and onto the site it
 * replaces. Only links whose host and slug match this exhibition are touched;
 * every other link, including the ones into museumwnf.org, is left exactly as
 * the curator wrote it.
 */
export function localiseLinks(html) {
  if (!html) return ''
  const host = (exhibition.legacy_host ?? '').replace(/\/$/, '')
  const slug = exhibition.slug
  if (!host || !slug) return html
  const prefix = `${host}/${slug}/`
  return html.replace(
    new RegExp(`${prefix.replace(/[.*+?^${}()|[\\]\\\\]/g, '\\\\$&')}[a-z-]+/?([^"'\\s]*)`, 'gi'),
    (_match, path) => `#/${path ?? ''}`
  )
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

export function useExhibitionData() {
  return {
    manifest, exhibition, items, tags, partners, countries, languages,
    dynasties, glossary, timelines, timelineEvents,
    themes, aboutTheme, listedThemes, allThemeNodes, themeById, themeByBc,
    themeByRouteId, themeRouteId, romanFor, themePictures,
    themeText, pictureText, relatedContent,
    siteLanguages, defaultLang,
    itemById, partnerById, countryById, tagById, dynastyById, glossaryById,
    timelineById, languageByCode, itemByUid,
    itemUidPath, itemRoute, itemFromUidPath,
    partnerKey, partnerRoute, partnerObjectsRoute, partnerFromKey,
    isInstitution, isHiddenPartner, visiblePartners,
    legacyImage,
    loadTranslations, translations, tr, availableLanguages, loadEnglish,
    itemLabel, countryLabel, countryLabelFromCode, countryByCode, partnerLabel, dynastyLabel,
    exhibitionTitle, exhibitionSubtitle, exhibitionHeadline, bannerCaption,
    siblingSites, siblingUrl,
    md, mdInline, mdStrip, localiseLinks,
  }
}
