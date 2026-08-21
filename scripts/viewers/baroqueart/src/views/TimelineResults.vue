<script setup>
import { ref, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useInventoryData } from '../composables/useInventoryData.js'

const route = useRoute()
const router = useRouter()
const { timelines, timelineEvents, countryLabel, enTimelineEventTranslations, md } = useInventoryData()

const PAGE_SIZE = 15

// ── Filter state (synced with URL query) ────────────────────────────────

const filterCountry = ref(route.query.country ?? '')
const filterBegin   = ref(route.query.begin   ?? '')
const filterEnd     = ref(route.query.end     ?? '')
const currentPage    = ref(parseInt(route.query.page ?? '1', 10) || 1)

watch(
  [filterCountry, filterBegin, filterEnd],
  () => { currentPage.value = 1 }
)

watch(
  () => route.query,
  q => {
    filterCountry.value = q.country ?? ''
    filterBegin.value   = q.begin   ?? ''
    filterEnd.value     = q.end     ?? ''
    currentPage.value   = parseInt(q.page ?? '1', 10) || 1
  }
)

function applyFilters() {
  const q = {}
  if (filterCountry.value) q.country = filterCountry.value
  if (filterBegin.value)   q.begin   = filterBegin.value
  if (filterEnd.value)     q.end     = filterEnd.value
  router.push({ path: '/timeline/results', query: q })
}

function resetFilters() {
  filterCountry.value = ''
  filterBegin.value = ''
  filterEnd.value = ''
  applyFilters()
}

// ── Available countries (from timeline data) ────────────────────────────

const availableCountries = computed(() =>
  timelines.value
    .filter(t => t.country_id)
    .map(t => ({ id: t.country_id, name: countryLabel(t.country_id) }))
    .sort((a, b) => a.name.localeCompare(b.name))
)

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

  const begin = filterBegin.value ? parseInt(filterBegin.value, 10) : null
  const end = filterEnd.value ? parseInt(filterEnd.value, 10) : null
  if (begin != null || end != null) {
    result = result.filter(e => overlapsRange(e, begin, end))
  }

  return [...result].sort((a, b) => a.year_from - b.year_from)
})

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
            <div class="timeline-country">{{ countryLabel(event.country_id) }}</div>
            <div
              class="timeline-description"
              v-html="md(enTimelineEventTranslations[event.id]?.description ?? '')"
            />
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
.timeline-row {
  display: flex;
  gap: 16px;
  padding: 14px 0;
  border-bottom: 1px solid #e8dcc8;
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
