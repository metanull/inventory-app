<script setup>
import { ref, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useInventoryData } from '../composables/useInventoryData.js'

const route = useRoute()
const router = useRouter()
const {
  timelines, timelineEvents, countryLabel, enTimelineEventTranslations, md,
  exhibitions, enCollectionTranslations, itemById, itemLabel,
} = useInventoryData()

const PAGE_SIZE = 15

// ── Filter state (synced with URL query) ────────────────────────────────
// `exhibition`: '' = all timelines, 'pc' = Permanent Collection timelines
// (collection_id null — the legacy hidden-sentinel toggle), or an
// exhibition id for that exhibition's thematic timeline.

const filterCountry    = ref(route.query.country ?? '')
const filterExhibition = ref(route.query.exhibition ?? '')
const filterBegin      = ref(route.query.begin   ?? '')
const filterEnd        = ref(route.query.end     ?? '')
const currentPage      = ref(parseInt(route.query.page ?? '1', 10) || 1)

watch(
  [filterCountry, filterExhibition, filterBegin, filterEnd],
  () => { currentPage.value = 1 }
)

watch(
  () => route.query,
  q => {
    filterCountry.value    = q.country ?? ''
    filterExhibition.value = q.exhibition ?? ''
    filterBegin.value      = q.begin   ?? ''
    filterEnd.value        = q.end     ?? ''
    currentPage.value      = parseInt(q.page ?? '1', 10) || 1
  }
)

function applyFilters() {
  const q = {}
  if (filterCountry.value)    q.country    = filterCountry.value
  if (filterExhibition.value) q.exhibition = filterExhibition.value
  if (filterBegin.value)      q.begin      = filterBegin.value
  if (filterEnd.value)        q.end        = filterEnd.value
  router.push({ path: '/timeline/results', query: q })
}

function resetFilters() {
  filterCountry.value = ''
  filterExhibition.value = ''
  filterBegin.value = ''
  filterEnd.value = ''
  applyFilters()
}

// ── Available countries / exhibitions (from timeline data) ──────────────

const availableCountries = computed(() => {
  const seen = new Map()
  for (const t of timelines.value) {
    if (t.country_id && !seen.has(t.country_id)) {
      seen.set(t.country_id, { id: t.country_id, name: countryLabel(t.country_id) })
    }
  }
  return [...seen.values()].sort((a, b) => a.name.localeCompare(b.name))
})

const availableExhibitions = computed(() => {
  const boundIds = new Set(timelines.value.map(t => t.collection_id).filter(Boolean))
  return exhibitions.value
    .filter(e => boundIds.has(e.id))
    .map(e => ({ id: e.id, name: enCollectionTranslations.value[e.id]?.title ?? e.internal_name }))
})

const timelineById = computed(() => {
  const m = {}
  for (const t of timelines.value) m[t.id] = t
  return m
})

// ── Filtered + sorted events ─────────────────────────────────────────────

// event.year_to === 0 mirrors the legacy convention for an open-ended period.
function effectiveYearTo(event) {
  return event.year_to && event.year_to !== 0 ? event.year_to : null
}

function overlapsRange(event, begin, end) {
  const yf = event.year_from
  const yt = effectiveYearTo(event)

  if (begin != null && end != null) {
    return yt !== null ? (yt > begin && yf < end) : (yf > begin && yf < end)
  }
  if (begin != null) {
    return yt !== null ? yt > begin : yf > begin
  }
  if (end != null) {
    return yf <= end
  }
  return true
}

const filteredEvents = computed(() => {
  let result = timelineEvents.value

  if (filterCountry.value) {
    result = result.filter(e => e.country_id === filterCountry.value)
  }

  if (filterExhibition.value === 'pc') {
    result = result.filter(e => timelineById.value[e.timeline_id]?.collection_id === null)
  } else if (filterExhibition.value) {
    result = result.filter(e => timelineById.value[e.timeline_id]?.collection_id === filterExhibition.value)
  }

  const begin = filterBegin.value ? parseInt(filterBegin.value, 10) : null
  const end = filterEnd.value ? parseInt(filterEnd.value, 10) : null
  if (begin != null || end != null) {
    result = result.filter(e => overlapsRange(e, begin, end))
  }

  return [...result].sort((a, b) => a.year_from - b.year_from)
})

// Items linked to an event (real curated links from the legacy timeline
// illustrations); tolerate ids not present in the export.
function eventItems(event) {
  return (event.item_ids ?? []).map(id => itemById.value[id]).filter(Boolean)
}

// Legacy hcr_result.php labels every row "Country | Theme"; PC-timeline
// events (collection_id null) are labelled "Political Context".
function eventThemeLabel(event) {
  const t = timelineById.value[event.timeline_id]
  if (!t) return ''
  if (t.collection_id === null) return 'Political Context'
  return enCollectionTranslations.value[t.collection_id]?.title ?? ''
}

const totalPages = computed(() => Math.max(1, Math.ceil(filteredEvents.value.length / PAGE_SIZE)))

const pagedEvents = computed(() => {
  const start = (currentPage.value - 1) * PAGE_SIZE
  return filteredEvents.value.slice(start, start + PAGE_SIZE)
})

function goToPage(n) {
  currentPage.value = n
  const q = { ...route.query, page: String(n) }
  if (n === 1) delete q.page
  router.replace({ path: '/timeline/results', query: q })
  window.scrollTo(0, 0)
}

// ── Display helpers ──────────────────────────────────────────────────────

function dateRangeLabel(event) {
  const t = enTimelineEventTranslations.value[event.id]
  if (t?.date_from_description) {
    return t.date_to_description
      ? `${t.date_from_description} – ${t.date_to_description}`
      : t.date_from_description
  }
  const yt = effectiveYearTo(event)
  return yt !== null ? `${event.year_from} – ${yt} AD` : `${event.year_from} AD –`
}

function itemsLink(event) {
  const q = { country: event.country_id, begin: String(event.year_from) }
  const yt = effectiveYearTo(event)
  q.end = String(yt !== null ? yt : event.year_from)
  return { path: '/permanent-collection/results', query: q }
}

const activeFilterLabel = computed(() => {
  const parts = []
  if (filterCountry.value) parts.push(countryLabel(filterCountry.value))
  if (filterBegin.value) parts.push(`from ${filterBegin.value}`)
  if (filterEnd.value) parts.push(`to ${filterEnd.value}`)
  return parts.length ? parts.join(' — ') : 'All Periods'
})
</script>

<template>
  <div>
    <RouterLink to="/timeline" class="back-link">‹ Back to Timeline</RouterLink>

    <h1 class="section-heading">
      Timeline
      <span v-if="activeFilterLabel !== 'All Periods'" class="heading-filter"> — {{ activeFilterLabel }}</span>
    </h1>

    <!-- Filter panel -->
    <div class="content-box filter-panel">
      <strong class="filter-label">Filter:</strong>

      <div class="filter-row">
        <label>Country</label>
        <select v-model="filterCountry" style="width:200px">
          <option value="">— any —</option>
          <option v-for="c in availableCountries" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
      </div>

      <div class="filter-row">
        <label>Timeline</label>
        <select v-model="filterExhibition" style="width:200px">
          <option value="">— all —</option>
          <option value="pc">Permanent Collection</option>
          <option v-for="e in availableExhibitions" :key="e.id" :value="e.id">{{ e.name }}</option>
        </select>
      </div>

      <div class="filter-row">
        <label>From year</label>
        <input type="number" v-model="filterBegin" placeholder="e.g. 800" style="width:100px" />
      </div>

      <div class="filter-row">
        <label>To year</label>
        <input type="number" v-model="filterEnd" placeholder="e.g. 1400" style="width:100px" />
      </div>

      <div class="filter-actions">
        <button class="btn" @click="applyFilters">Apply</button>
        <button class="btn btn-secondary" style="margin-left:8px" @click="resetFilters">Reset</button>
      </div>
    </div>

    <!-- Results -->
    <div class="content-box">
      <p class="result-count">
        {{ filteredEvents.length }} event{{ filteredEvents.length !== 1 ? 's' : '' }} found
      </p>

      <ul v-if="pagedEvents.length" class="timeline-list">
        <li v-for="event in pagedEvents" :key="event.id" class="timeline-row">
          <div class="timeline-date">{{ dateRangeLabel(event) }}</div>
          <div class="timeline-body">
            <div class="timeline-country">
              {{ countryLabel(event.country_id) }}<span v-if="eventThemeLabel(event)"> | {{ eventThemeLabel(event) }}</span>
            </div>
            <div
              class="timeline-description"
              v-html="md(enTimelineEventTranslations[event.id]?.description ?? '')"
            />

            <div v-if="event.images?.length || eventItems(event).length" class="timeline-media-row">
              <img
                v-for="(img, idx) in event.images"
                :key="'img' + idx"
                :src="img.url"
                :alt="img.alt_text ?? ''"
                class="timeline-media-img"
                loading="lazy"
              />
              <RouterLink
                v-for="item in eventItems(event)"
                :key="item.id"
                :to="`/item/${encodeURIComponent(item.id)}`"
                class="timeline-media-item"
              >
                <img v-if="item.images?.length" :src="item.images[0].url" :alt="itemLabel(item)" loading="lazy" />
                <span class="timeline-media-caption">
                  {{ itemLabel(item) }}
                  <span class="timeline-media-see">See Database Entry →</span>
                </span>
              </RouterLink>
            </div>

            <RouterLink :to="itemsLink(event)" class="timeline-items-link">
              View items from this period →
            </RouterLink>
          </div>
        </li>
      </ul>

      <p v-else class="no-results">No events match the selected filter.</p>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="pagination">
        <span class="pagination-info">
          Page {{ currentPage }} of {{ totalPages }}
        </span>
        <button class="page-btn" :disabled="currentPage === 1" @click="goToPage(currentPage - 1)">‹ Prev</button>
        <template v-for="p in totalPages" :key="p">
          <button
            v-if="Math.abs(p - currentPage) <= 3 || p === 1 || p === totalPages"
            class="page-btn"
            :class="{ active: p === currentPage }"
            @click="goToPage(p)"
          >{{ p }}</button>
          <span v-else-if="Math.abs(p - currentPage) === 4" class="page-ellipsis">…</span>
        </template>
        <button class="page-btn" :disabled="currentPage === totalPages" @click="goToPage(currentPage + 1)">Next ›</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.heading-filter { font-weight: normal; font-size: 14px; color: var(--muted); }

.filter-panel { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; }
.filter-label { font-family: 'Roboto', sans-serif; font-size: 12px; font-weight: bold; color: var(--muted); }
.filter-row { display: flex; align-items: center; gap: 6px; font-family: 'Roboto', sans-serif; font-size: 12px; color: var(--muted); }
.filter-actions { margin-left: auto; }

.result-count {
  font-family: 'Roboto', sans-serif;
  font-size: 12px;
  color: var(--muted);
  margin-bottom: 12px;
  padding-bottom: 8px;
  border-bottom: 1px solid var(--border);
}

.no-results { color: var(--muted); font-family: 'Roboto', sans-serif; font-size: 13px; padding: 20px 0; }

.timeline-list { list-style: none; }
.timeline-media-row {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
  margin: 8px 0;
}
.timeline-media-img,
.timeline-media-item img {
  height: 64px;
  border: 1px solid var(--border);
  background: var(--tile-bg);
  display: block;
}
.timeline-media-item:hover img { border-color: var(--accent); }
.timeline-media-item {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  max-width: 320px;
  text-decoration: none !important;
}
.timeline-media-caption {
  font-family: 'Roboto', sans-serif;
  font-size: 12px;
  line-height: 1.4;
  color: var(--text);
  display: flex;
  flex-direction: column;
  gap: 2px;
  padding-top: 2px;
}
.timeline-media-see {
  font-size: 11px;
  font-weight: 500;
  color: var(--nav-active);
}
.timeline-media-item:hover .timeline-media-caption { color: var(--nav-active); }
.timeline-row {
  display: flex;
  gap: 16px;
  padding: 14px 0;
  border-bottom: 1px solid var(--border-light);
}
.timeline-row:last-child { border-bottom: none; }

.timeline-date {
  flex-shrink: 0;
  width: 140px;
  font-family: 'Roboto', sans-serif;
  font-size: 13px;
  font-weight: 500;
  color: var(--heading);
}

.timeline-body { flex: 1; min-width: 0; }
.timeline-country {
  font-family: 'Roboto', sans-serif;
  font-size: 12px;
  font-weight: bold;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: var(--muted);
  margin-bottom: 4px;
}
.timeline-description {
  font-family: 'Roboto', sans-serif;
  font-size: 13px;
  line-height: 1.6;
  color: var(--text);
}
.timeline-description :deep(p) { margin-bottom: 6px; }
.timeline-items-link {
  display: inline-block;
  margin-top: 6px;
  font-family: 'Roboto', sans-serif;
  font-size: 12px;
  font-weight: 500;
  color: var(--nav-active);
}

.page-ellipsis { padding: 4px 4px; color: var(--muted); font-family: 'Roboto', sans-serif; font-size: 12px; }
</style>
