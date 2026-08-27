import { computed } from 'vue'
import {
  timelines, timelineEvents, countries, countryById, countryLabel, tr, defaultLang,
} from './useGalleryData.js'
import { yearBucketsFromRange } from './useCollection.js'

// The global country timeline (legacy `mwnf3.hcr`, served through `/events`).
// It is country-scoped and project-independent — which is why the live amulets
// instance answers `/events/countries` with the worldwide list even though its
// own `hasCountryBasedTimeline` flag is false. Every gallery package therefore
// ships the same 18 per-country timelines.

// The 18 timeline countries and the 19 countries.json ships do NOT coincide:
// countries.json is scoped to "member item countries ∪ their holders'
// countries" (the package spec), while the chronology is worldwide. Ten of the
// timeline's countries — cz, eg, es, fr, hu, it, pt, pa, sy, tn — have no row
// in countries.json at all, so neither their name nor their legacy 2-letter
// code can be read from there.
//
// The timeline record itself carries both: `backward_compatibility` is
// `mwnf3:hcr:country:<legacy code>`, which is the value legacy's own URLs
// used. So the registry is built from timelines.json, with the display name
// taken from countries.json where it exists and otherwise formatted from the
// ISO code by the platform's own region names — a rendering of a code the
// package ships, never an invented label.
const regionNames = (() => {
  try {
    return new Intl.DisplayNames(['en'], { type: 'region' })
  } catch {
    return null
  }
})()

// Two legacy codes are not ISO 3166-1 alpha-2.
const LEGACY_TO_ISO = { uk: 'GB', pa: 'PS' }

function legacyCodeOf(timeline) {
  const parts = (timeline.backward_compatibility ?? '').split(':')
  return parts[parts.length - 1] || timeline.country_id
}

function nameFor(timeline) {
  const fromPackage = countries.value.some(c => c.id === timeline.country_id)
    ? countryLabel(timeline.country_id)
    : null
  if (fromPackage) return fromPackage
  const legacy = legacyCodeOf(timeline)
  const iso = LEGACY_TO_ISO[legacy] ?? legacy.toUpperCase()
  try {
    return regionNames?.of(iso) ?? iso
  } catch {
    return iso
  }
}

/** Countries that actually have a chronology, alphabetized, "All" first. */
export const timelineCountries = computed(() => {
  const rows = timelines.value
    .map(t => [legacyCodeOf(t), nameFor(t)])
    .sort((a, b) => a[1].localeCompare(b[1]))
  return [['all', 'All Countries'], ...rows]
})

/** Display name for an event's country, covering the ten countries.json omits. */
export function timelineCountryName(countryId) {
  if (countries.value.some(c => c.id === countryId)) return countryLabel(countryId)
  const timeline = timelines.value.find(t => t.country_id === countryId)
  return timeline ? nameFor(timeline) : countryId
}

/** Legacy 2-letter code → the inventory country id the events are keyed by. */
export function countryIdForCode(code) {
  if (!code || code === 'all') return null
  const timeline = timelines.value.find(t => legacyCodeOf(t) === code)
  if (timeline) return timeline.country_id
  // Countries with no chronology still reach here from the collection page's
  // "Timeline for this Search" link, which uses countries.json's own codes.
  return countries.value.find(c => c.code === code)?.id ?? null
}

/** Every event year present, as the year dropdown's source range. */
export const eventYearRange = computed(() => {
  const years = timelineEvents.value.map(e => e.year_from).filter(v => Number.isFinite(v) && v !== 0)
  if (!years.length) return [null, null]
  return [Math.min(...years), Math.max(...years)]
})

export const eventYearBuckets = computed(() => {
  const [min, max] = eventYearRange.value
  return yearBucketsFromRange(min, max)
})

/**
 * Legacy's `/events?ic[]=&ya=&yo=` — events for a country within a year range,
 * ordered chronologically.
 */
export function findEvents({ countryCode, start, end }) {
  const countryId = countryIdForCode(countryCode)
  const from = start === '' || start == null ? null : Number(start)
  const to = end === '' || end == null ? null : Number(end)

  return timelineEvents.value
    .filter(e => {
      if (countryId && e.country_id !== countryId) return false
      const year = e.year_from
      if (!Number.isFinite(year)) return false
      if (from != null && year < from) return false
      if (to != null && year > to) return false
      return true
    })
    .map(e => ({
      ...e,
      countryName: timelineCountryName(e.country_id),
      text: tr('timeline_events', e.id, defaultLang),
    }))
    .sort((a, b) => (a.year_from - b.year_from) || (a.display_order ?? 0) - (b.display_order ?? 0))
}

/** "1193 A.D." / "502 B.C." — legacy's era suffix rule. */
export function eraLabel(year) {
  if (!Number.isFinite(year) || year === 0) return ''
  return year < 0 ? `${Math.abs(year)} B.C.` : `${year} A.D.`
}

/**
 * The item sheet's "Timeline for this item" window rounds the item's own dates
 * outward to the nearest century before querying, which is how a single-year
 * object still lands on a readable stretch of chronology.
 */
export function roundOutward(start, end) {
  const from = Number.isFinite(start) ? Math.floor(start / 100) * 100 : null
  const last = Number.isFinite(end) ? end : start
  const to = Number.isFinite(last) ? Math.ceil(last / 100) * 100 : null
  return [from, to]
}
