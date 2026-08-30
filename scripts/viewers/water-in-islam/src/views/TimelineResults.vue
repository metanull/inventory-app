<script setup>
import { computed, ref, watch } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { items, md } from '../composables/useExhibitionData.js'
import {
  findEvents, eraLabel, timelineCountries, eventYearBuckets, countryIdForCode,
  usesLocalTimeline,
} from '../composables/useTimeline.js'
import { paginate } from '../composables/useCollection.js'
import Pages from '../components/Pages.vue'
import BackLink from '../components/BackLink.vue'

// Timeline results, with the "See Gallery" cross-link legacy showed whenever
// the chosen country/period actually contains member items — the timeline
// gallery page joins events to items by country and year range client-side.
const route = useRoute()
const router = useRouter()

const EVENTS_PER_PAGE = 25

const country = ref(String(route.query.c ?? 'all'))
const start = ref(route.query.start ?? '')
const end = ref(route.query.end ?? '')

watch(() => route.fullPath, () => {
  country.value = String(route.query.c ?? 'all')
  start.value = route.query.start ?? ''
  end.value = route.query.end ?? ''
})

const events = computed(() => findEvents({
  countryCode: String(route.query.c ?? 'all'),
  start: route.query.start,
  end: route.query.end,
}))
const page = computed(() => paginate(events.value, route.query.page ?? 1, EVENTS_PER_PAGE))

const countryName = computed(() =>
  timelineCountries.value.find(c => c[0] === String(route.query.c ?? 'all'))?.[1] ?? 'All Countries'
)

// The exhibition's own chronology names each period itself ("ca. 50.000-30.000
// BCE", "The 19th century"), which is what its events are captioned with —
// `year_from` is a sort key there, not a label. The worldwide timeline has no
// such name and is captioned with the derived era label, as in the galleries.
function dateLabel(event) {
  if (usesLocalTimeline.value && event.text?.name) return event.text.name
  return eraLabel(event.year_from)
}

function goToResults() {
  router.push({
    name: 'timeline-results',
    query: { c: country.value || 'all', start: start.value, end: end.value },
  })
}

function navigate(p) {
  router.push({ name: 'timeline-results', query: { ...route.query, page: p } })
}

// Member items in the same country and period — the condition legacy used to
// decide whether to offer the gallery view.
const galleryItems = computed(() => {
  const countryId = countryIdForCode(String(route.query.c ?? 'all'))
  if (!countryId) return []
  const from = route.query.start ? Number(route.query.start) : null
  const to = route.query.end ? Number(route.query.end) : null
  return items.value.filter(i => {
    if (i.country_id !== countryId) return false
    const itemStart = i.start_date
    const itemEnd = i.end_date ?? i.start_date
    if (!Number.isFinite(itemStart)) return false
    if (from != null && itemEnd < from) return false
    if (to != null && itemStart > to) return false
    return true
  })
})
</script>

<template>
  <div id="timeline-results-wrapper">
    <div id="timeline-results-search-container">
      <p id="current-search">
        Timeline | {{ route.query.start ? eraLabel(Number(route.query.start)) : 'earliest' }}
        to {{ route.query.end ? eraLabel(Number(route.query.end)) : 'latest' }} |
        <span v-if="!usesLocalTimeline">{{ countryName }} | </span>
        <span>{{ page.total }} Results</span>
      </p>

      <div id="search-fields">
        <label>Start Date
          <select class="legacy-select" v-model="start">
            <option value="">Any</option>
            <option v-for="d in eventYearBuckets" :key="`s${d[0]}`" :value="d[0]">{{ d[1] }}</option>
          </select>
        </label>
        <label>End Date
          <select class="legacy-select" v-model="end">
            <option value="">Any</option>
            <option v-for="d in eventYearBuckets" :key="`e${d[0]}`" :value="d[0]">{{ d[1] }}</option>
          </select>
        </label>
        <label v-if="!usesLocalTimeline">Country
          <select class="legacy-select" v-model="country">
            <option v-for="c in timelineCountries" :key="c[0]" :value="c[0]">{{ c[1] }}</option>
          </select>
        </label>
        <button class="legacy-button" @click="goToResults()">Go</button>
      </div>

      <div id="related-container" v-if="galleryItems.length">
        <p class="related-header">RELATED CONTENT</p>
        <p>
          ➤
          <RouterLink :to="{
            name: 'timeline-gallery',
            params: {
              country: String(route.query.c ?? 'all'),
              start: route.query.start || 'any',
              end: route.query.end || 'any',
              page: 1,
            },
          }">See Gallery ({{ galleryItems.length }})</RouterLink>
        </p>
      </div>
    </div>

    <BackLink />
    <Pages :page-info="page" @navigate="navigate" />

    <div id="timeline-results-container">
      <div id="labels-container" v-if="page.rows.length">
        <div id="date-label">Date</div>
        <div id="country-label">{{ usesLocalTimeline ? 'Description' : 'Country | Description' }}</div>
      </div>
      <div v-if="page.rows.length">
        <div class="event-container" v-for="event in page.rows" :key="event.id">
          <div class="year">{{ dateLabel(event) }}</div>
          <div class="event">
            <div class="country" v-if="!usesLocalTimeline">{{ event.countryName }}</div>
            <div class="description" v-html="md(event.text.description)"></div>
          </div>
        </div>
      </div>
      <div id="timeline-no-results" v-else>
        No results. Please use the drop-down fields above to start a new search.
      </div>
    </div>

    <Pages :page-info="page" @navigate="navigate" />
  </div>
</template>

<style scoped>
#timeline-results-wrapper { background: #fff; width: 100%; min-height: 400px; padding-bottom: 30px; }
#timeline-results-search-container { background: var(--background-color); padding: 16px 20px; }
#current-search { font-size: 16px; margin-bottom: 10px; }
#current-search span { font-weight: 700; }
#search-fields { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; }
#search-fields label { display: flex; flex-direction: column; font-size: 13px; }
#search-fields .legacy-select { margin-top: 2px; min-width: 180px; }
#related-container { margin-top: 14px; }
.related-header { font-weight: 700; color: var(--theme-dark); }
#related-container a { color: var(--link-blue); }

#timeline-results-container { padding: 0 20px; }
#labels-container { display: flex; gap: 12px; font-weight: 700; color: var(--theme-dark); border-bottom: 2px solid var(--theme-medium); padding-bottom: 4px; }
#date-label { flex: 0 0 120px; }
.event-container { display: flex; gap: 12px; padding: 8px 0; border-bottom: 1px solid var(--background-color); }
.year { flex: 0 0 120px; font-weight: 700; }
.event { flex: 1; min-width: 0; }
.country { font-weight: 700; color: var(--theme-medium-dark); }
.description { line-height: 1.5; }
#timeline-no-results { padding: 30px 0; }

@media only screen and (max-width: 599px) {
  .event-container, #labels-container { flex-direction: column; gap: 2px; }
  .year, #date-label { flex: none; }
}
</style>
