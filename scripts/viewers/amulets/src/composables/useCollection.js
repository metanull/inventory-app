import {
  items, tags, countries, countryById, tagById,
  countryLabel, itemLabel, partnerLabel, tr, defaultLang, mdStrip,
} from './useGalleryData.js'

// The collection search, rebuilt client-side.
//
// Legacy asked the API for everything: `/items?ic[]=…&id[]=…&na=…&nz=…` for the
// results, and `/items/countries`, `/items/tags`, `/items/years` *with the same
// filters applied* for the dropdowns — which is what made the facets dependent
// (pick a country, and the type list shrinks to the types still reachable).
// A static package has no API, so the same three vocabularies are derived here
// from the matching subset, filter for filter.

// The five THG facet categories, in the order the legacy form shows them.
// `artist` has no dropdown in dxa-client, but the category exists in the data
// and the exporter ships it, so it renders whenever it has more than one value
// — a superset of legacy, never a different answer.
export const FACET_CATEGORIES = ['type', 'dynasty', 'subject', 'material', 'artist']

export const FACET_LABELS = {
  type: 'Type',
  dynasty: 'Period / Dynasty',
  subject: 'Subject',
  material: 'Material',
  artist: 'Artist',
}

// legacy_tag_id (what the URL carries) → tag record
const tagByLegacyId = () => new Map(tags.value.map(t => [t.legacy_tag_id, t]))

// legacy 2-letter country code (what the URL carries) → country record
const countryByCode = () => new Map(countries.value.map(c => [c.code, c]))

/**
 * Apply the legacy `/items` predicate.
 *
 * - `ic[]` — country, matched on the legacy 2-letter code.
 * - `id[]` — facet tags, ANDed: `Objects.blade.php` requires the count of
 *   matched tags to equal the count of requested tags.
 * - `na` / `nz` — containment, not overlap: `na <= startDate` and
 *   `nz >= COALESCE(endDate, startDate)`.
 */
export function filterItems(query) {
  const byCode = countryByCode()
  const byLegacy = tagByLegacyId()

  const countryId = query.country ? byCode.get(query.country)?.id : null
  const tagIds = (query.tagIds ?? [])
    .map(legacy => byLegacy.get(legacy)?.id)
    .filter(Boolean)
  const start = query.start === '' || query.start == null ? null : Number(query.start)
  const end = query.end === '' || query.end == null ? null : Number(query.end)

  return items.value.filter(item => {
    if (query.country && item.country_id !== countryId) return false
    if (tagIds.length && !tagIds.every(id => item.tag_ids?.includes(id))) return false
    if (start != null && !(start <= (item.start_date ?? -Infinity))) return false
    if (end != null) {
      const last = item.end_date ?? item.start_date
      if (last == null || !(end >= last)) return false
    }
    return true
  })
}

/** Sort chronologically by start_date, as legacy's results grid does. */
export function sortChronological(list) {
  return [...list].sort((a, b) => (a.start_date ?? 0) - (b.start_date ?? 0))
}

/** Country vocabulary reachable from a subset, alphabetized. */
export function countryOptions(subset) {
  const ids = new Set(subset.map(i => i.country_id).filter(Boolean))
  return [...ids]
    .map(id => [countryById.value.get(id)?.code ?? id, countryLabel(id)])
    .filter(([code]) => code)
    .sort((a, b) => a[1].localeCompare(b[1]))
}

/**
 * Facet vocabularies reachable from a subset, by category.
 * Legacy upper-cases the first character of every label; keep that.
 */
export function facetOptions(subset) {
  const reachable = new Set()
  for (const item of subset) for (const id of item.tag_ids ?? []) reachable.add(id)

  const out = {}
  for (const category of FACET_CATEGORIES) out[category] = []
  for (const id of reachable) {
    const tag = tagById.value.get(id)
    if (!tag || !out[tag.category]) continue
    const label = (tag.label ?? '').charAt(0).toUpperCase() + (tag.label ?? '').slice(1)
    out[tag.category].push([tag.legacy_tag_id, label])
  }
  for (const category of FACET_CATEGORIES) {
    out[category].sort((a, b) => a[1].localeCompare(b[1]))
  }
  return out
}

// ── Year buckets ───────────────────────────────────────────────────────────
//
// A verbatim port of `loadYears` in the legacy client's collection.js mixin.
// The API returned only min/max; the client built the era buckets itself, with
// coarser steps the further back you go (500 → 250 → 100 → 50), an explicit
// "Before 1000 B.C." bucket, and an "After …" label when the final step is
// short. Reproducing the algorithm rather than inventing nicer buckets is the
// whole point: the option values end up in shareable URLs.

const nextMultiple = (n, m) => Math.ceil(n / m) * m
const previousMultiple = (n, m) => Math.floor(n / m) * m

function pushIncrements(from, maxRounded, incrementMax, increment, array) {
  for (let i = from; i <= incrementMax; i += increment) {
    if (i > maxRounded) return
    array.push(i)
  }
}

export function yearBuckets(subset) {
  const starts = subset.map(i => i.start_date).filter(v => Number.isFinite(v))
  const ends = subset.map(i => i.end_date ?? i.start_date).filter(v => Number.isFinite(v))
  if (!starts.length || !ends.length) return []
  return yearBucketsFromRange(Math.min(...starts), Math.max(...ends))
}

/**
 * The same buckets from an explicit min/max — the timeline pages feed it event
 * years instead of item dates (legacy's timeline.js mixin ran this identical
 * algorithm over `/events/years`).
 */
export function yearBucketsFromRange(min, max) {
  if (!Number.isFinite(min) || !Number.isFinite(max)) return []

  const increments = [500, 250, 100, 50]
  let smallestIncrement
  const array = [min]
  let minRounded, maxRounded

  if (max >= 1800) maxRounded = previousMultiple(max, increments[3])
  else if (max >= 1000) maxRounded = previousMultiple(max, increments[2])
  else if (max >= 0) maxRounded = previousMultiple(max, increments[1])
  else maxRounded = previousMultiple(max, increments[0])

  if (min <= 0) minRounded = nextMultiple(min, increments[0])
  else if (min <= 1000) minRounded = nextMultiple(min, increments[1])
  else if (min <= 1800) minRounded = nextMultiple(min, increments[2])
  else minRounded = nextMultiple(min, increments[3])

  let guard = 0
  do {
    if (minRounded < 0) {
      pushIncrements(minRounded, maxRounded, 0, increments[0], array)
      smallestIncrement = increments[0]
    } else if (minRounded < 1000) {
      pushIncrements(minRounded, maxRounded, 1000, increments[1], array)
      smallestIncrement = increments[1]
    } else if (minRounded < 1800) {
      pushIncrements(minRounded, maxRounded, 1800, increments[2], array)
      smallestIncrement = increments[2]
    } else {
      pushIncrements(minRounded, maxRounded, maxRounded, increments[3], array)
      smallestIncrement = increments[3]
    }
    const last = array[array.length - 1]
    // The legacy loop relies on the array growing; if a step adds nothing it
    // would spin forever. Legacy never hit that because its data always did.
    if (last === minRounded && guard++ > 0) break
    minRounded = last
  } while (array[array.length - 1] < maxRounded && guard++ < 64)

  array.push(max)
  const unique = [...new Set(array)]

  const labelled = []
  for (const year of unique) {
    if (year < 0) labelled.push([year, `${Math.abs(year)} B.C.`])
    else if (year > 0) labelled.push([year, `${year} A.D.`])
  }
  if (!labelled.length) return []

  let display = labelled
  if (labelled[0][0] < -1000) {
    display = labelled.filter(([y]) => y >= -1000)
    display.unshift([min, 'Before 1000 B.C.'])
  }

  if (display.length > 1) {
    const lastGap = display[display.length - 1][0] - display[display.length - 2][0]
    if (lastGap < smallestIncrement) {
      display[display.length - 1][1] = `After ${display[display.length - 2][1]}`
    }
  }
  return display
}

// ── Free-text search ───────────────────────────────────────────────────────
//
// Legacy used MySQL boolean full-text search (`/items?ts=…`) — the reason the
// How-to-search page is a whole essay about `+`, `-`, `*` and `""`. A static
// site cannot run MySQL, so this implements the same operator grammar over a
// client-side index of the translated sheets: required (+), excluded (-),
// optional (bare, contributes to rank), truncation (*) and quoted phrases.

function itemHaystack(item) {
  const t = tr('items', item.id, defaultLang)
  const parts = [
    t.name, t.description, t.short_description, t.type, t.holder, t.dates,
    t.location, t.provenance, t.alternate_name, t.place_of_production,
    (t.keywords ?? []).join(' '), (t.materials ?? []).join(' '),
    item.internal_name, item.owner_reference, item.mwnf_reference,
    partnerLabel(item.partner_id), countryLabel(item.country_id),
  ]
  return mdStrip(parts.filter(Boolean).join(' \n ')).toLowerCase()
}

let haystacks = null
function haystackFor(item) {
  if (!haystacks) haystacks = new Map()
  if (!haystacks.has(item.id)) haystacks.set(item.id, itemHaystack(item))
  return haystacks.get(item.id)
}

/** Invalidate the text index (English translations arrive asynchronously). */
export function resetSearchIndex() {
  haystacks = null
}

export function parseQuery(input) {
  const terms = []
  const re = /([+\-~<>]?)(?:"([^"]+)"|(\S+))/g
  let m
  while ((m = re.exec(input ?? '')) !== null) {
    const [, op, phrase, word] = m
    let text = (phrase ?? word ?? '').toLowerCase()
    if (!text) continue
    let prefix = false
    if (!phrase && text.endsWith('*')) {
      prefix = true
      text = text.slice(0, -1)
    }
    if (!text) continue
    terms.push({ op: op || '', text, prefix, phrase: Boolean(phrase) })
  }
  return terms
}

function matches(hay, term) {
  if (term.prefix) {
    return new RegExp(`\\b${term.text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}`).test(hay)
  }
  return hay.includes(term.text)
}

/** Rank items against a boolean full-text style query. */
export function textSearch(input) {
  const terms = parseQuery(input)
  if (!terms.length) return []

  const required = terms.filter(t => t.op === '+')
  const excluded = terms.filter(t => t.op === '-')
  const optional = terms.filter(t => !['+', '-'].includes(t.op))

  const scored = []
  for (const item of items.value) {
    const hay = haystackFor(item)
    if (!required.every(t => matches(hay, t))) continue
    if (excluded.some(t => matches(hay, t))) continue

    let score = required.length * 2
    let anyOptional = false
    for (const t of optional) {
      if (!matches(hay, t)) continue
      anyOptional = true
      // '~' contributes negatively, '>' more, '<' less — legacy's relevance
      // operators, reproduced as simple weights.
      score += t.op === '~' ? -1 : t.op === '>' ? 2 : t.op === '<' ? 0.5 : 1
    }
    // With no required terms, a bare-term query still has to match something.
    if (!required.length && !anyOptional) continue
    scored.push({ item, score })
  }
  scored.sort((a, b) => b.score - a.score || itemLabel(a.item).localeCompare(itemLabel(b.item)))
  return scored.map(s => s.item)
}

/** Paginate, legacy style: 9 per page for the results grids. */
export const PAGE_SIZE = 9

export function paginate(list, page, size = PAGE_SIZE) {
  const total = list.length
  const lastPage = Math.max(1, Math.ceil(total / size))
  const current = Math.min(Math.max(1, Number(page) || 1), lastPage)
  return {
    total,
    lastPage,
    currentPage: current,
    from: total === 0 ? 0 : (current - 1) * size + 1,
    to: Math.min(current * size, total),
    rows: list.slice((current - 1) * size, current * size),
  }
}
