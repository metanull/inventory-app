<script setup>
import { computed } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { items, countries, tags, countryLabel, timelines, countryById } from '../composables/useGalleryData.js'
import {
  filterItems, sortChronological, countryOptions, facetOptions, yearBuckets,
  paginate, FACET_CATEGORIES, FACET_LABELS,
} from '../composables/useCollection.js'
import ObjectGrid from '../components/ObjectGrid.vue'
import Pages from '../components/Pages.vue'
import BackLink from '../components/BackLink.vue'

// Results plus "filter further by". The dependent behaviour is the point: every
// dropdown is rebuilt from the items that survive the *current* filter set, so
// picking a country shrinks the type list, exactly as legacy's re-queries did.
const route = useRoute()
const router = useRouter()

const FACET_KEYS = FACET_CATEGORIES

const query = computed(() => {
  const q = route.query
  const tagIds = FACET_KEYS.map(k => q[k]).filter(Boolean)
  return {
    country: q.country ?? '',
    tagIds,
    start: q.start ?? '',
    end: q.end ?? '',
    selections: Object.fromEntries(FACET_KEYS.map(k => [k, q[k] ?? ''])),
    page: q.page ?? 1,
  }
})

const matching = computed(() => sortChronological(filterItems(query.value)))
const page = computed(() => paginate(matching.value, query.value.page))
const total = computed(() => items.value.length)

const availableCountries = computed(() => countryOptions(matching.value))
const availableFacets = computed(() => facetOptions(matching.value))
const availableYears = computed(() => yearBuckets(matching.value))

const resultsExist = computed(() => matching.value.length > 0)
const isFirstSearch = computed(() => Object.keys(route.query).filter(k => k !== 'page').length <= 1)

// The filter summary line legacy printed as "Collection | <selections>".
const filterSummary = computed(() => {
  const parts = []
  if (query.value.country) parts.push(countryLabel(countryById.value.get(codeToId(query.value.country))?.id))
  for (const key of FACET_KEYS) {
    const legacyId = query.value.selections[key]
    if (!legacyId) continue
    const tag = tags.value.find(t => t.legacy_tag_id === legacyId)
    parts.push(tag ? tag.label : legacyId)
  }
  if (query.value.start) parts.push(`from ${query.value.start}`)
  if (query.value.end) parts.push(`to ${query.value.end}`)
  return parts.filter(Boolean).join(' | ')
})

function codeToId(code) {
  return countries.value.find(c => c.code === code)?.id ?? null
}

function applyFilter(key, value) {
  router.push({ name: 'collection-results', query: { ...route.query, [key]: value, page: undefined } })
}

function resetFilters() {
  router.push({ name: 'collection' })
}

function navigate(p) {
  router.push({ name: 'collection-results', query: { ...route.query, page: p } })
}

// "Timeline for this Search" — legacy offered it whenever the chosen country
// actually has a chronology. The 18 global country timelines ship in every
// gallery package, so the check is a lookup rather than a request.
const timelineCountryIds = computed(() => new Set(timelines.value.map(t => t.country_id)))
const showTimelineLink = computed(() => {
  const id = codeToId(query.value.country)
  return Boolean(id && timelineCountryIds.value.has(id))
})
</script>

<template>
  <div id="collection-results-container">
    <BackLink />

    <div id="info-container">
      <p>Collection | <span>{{ filterSummary }}</span></p>
      <p>{{ page.total }} result(s) out of {{ total }} objects</p>
    </div>

    <Pages :page-info="page" @navigate="navigate" />

    <div id="content-container">
      <div id="collection-results" v-if="resultsExist">
        <ObjectGrid :results="page.rows" />
      </div>
      <div id="no-results" v-else>
        No results. Click <button class="linkish" @click="resetFilters()">here</button> to reset all filters.
      </div>

      <aside id="options-container">
        <div id="refine">
          <div class="options-label">{{ isFirstSearch ? 'Filter by:' : 'Filter further by:' }}</div>

          <select
            class="legacy-select"
            :value="query.country"
            :disabled="!resultsExist"
            @change="applyFilter('country', $event.target.value)"
          >
            <option value="" disabled>Select Country</option>
            <option v-for="c in availableCountries" :key="c[0]" :value="c[0]">{{ c[1] }}</option>
          </select>

          <select
            v-for="category in FACET_KEYS"
            v-show="(availableFacets[category] ?? []).length > 0"
            :key="category"
            class="legacy-select"
            :value="query.selections[category]"
            :disabled="!resultsExist"
            @change="applyFilter(category, $event.target.value)"
          >
            <option value="" disabled>{{ FACET_LABELS[category] }}</option>
            <option v-for="tag in availableFacets[category]" :key="tag[0]" :value="tag[0]">{{ tag[1] }}</option>
          </select>

          <div id="date-wrapper">
            <select class="legacy-select" :value="query.start" :disabled="!resultsExist" @change="applyFilter('start', $event.target.value)">
              <option value="" disabled>Start Date</option>
              <option v-for="d in availableYears" :key="`s${d[0]}`" :value="d[0]">{{ d[1] }}</option>
            </select>
            <select class="legacy-select" :value="query.end" :disabled="!resultsExist" @change="applyFilter('end', $event.target.value)">
              <option value="" disabled>End Date</option>
              <option v-for="d in availableYears" :key="`e${d[0]}`" :value="d[0]">{{ d[1] }}</option>
            </select>
          </div>

          <div id="reset-container">
            <button class="legacy-button" @click="resetFilters()">Reset filters</button>
          </div>
        </div>

        <div id="timeline-link-box" v-if="showTimelineLink">
          <div class="options-label">Timeline for this Search</div>
          <p>
            ➤
            <RouterLink :to="{ name: 'timeline-results', query: { c: query.country, start: query.start, end: query.end } }">
              Timeline | {{ countryLabel(codeToId(query.country)) }}
            </RouterLink>
          </p>
        </div>
      </aside>
    </div>

    <Pages :page-info="page" @navigate="navigate" />
  </div>
</template>

<style scoped>
#collection-results-container { background: #fff; width: 100%; min-height: 400px; }
#info-container { padding: 0 20px 12px; font-size: 15px; }
#info-container span { font-weight: 700; }
#content-container { display: flex; gap: 20px; padding: 0 20px 20px; align-items: flex-start; }
#collection-results { flex: 1; min-width: 0; }
#no-results { flex: 1; padding: 40px 0; }
.linkish { background: none; border: none; color: var(--link-blue); text-decoration: underline; cursor: pointer; font: inherit; }
#options-container { flex: 0 0 260px; }
#refine { background: var(--background-color); padding: 14px; }
.options-label { font-weight: 700; margin-bottom: 4px; }
#date-wrapper { display: flex; gap: 8px; }
#reset-container { margin-top: 14px; }
#timeline-link-box { margin-top: 16px; background: var(--background-color); padding: 14px; }
#timeline-link-box a { color: var(--link-blue); }

@media only screen and (max-width: 849px) {
  #content-container { flex-direction: column; }
  #options-container { flex: none; width: 100%; }
}
</style>
