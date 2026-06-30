<script setup>
import { ref, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useInventoryData } from '../composables/useInventoryData.js'

const route = useRoute()
const router = useRouter()
const {
  items, dynasties,
  itemLabel, countryLabel, dynastyLabel,
  enItemTranslations,
} = useInventoryData()

const PAGE_SIZE = 20

// ── Parse query params ────────────────────────────────────────────────

function parseQuery(q) {
  return {
    keyword1: q.keyword1 ?? '',
    field1:   q.field1   ?? 'keyword',
    keyword2: q.keyword2 ?? '',
    field2:   q.field2   ?? 'keyword',
    cond2:    q.cond2    ?? 'AND',
    keyword3: q.keyword3 ?? '',
    field3:   q.field3   ?? 'keyword',
    cond3:    q.cond3    ?? 'AND',
    dateFrom: q.date_from ?? '',
    dateTo:   q.date_to   ?? '',
    page:     parseInt(q.page ?? '1', 10) || 1,
    // refine row
    keyword4: q.keyword4 ?? '',
    field4:   q.field4   ?? 'keyword',
    cond4:    q.cond4    ?? 'AND',
  }
}

const search = ref(parseQuery(route.query))
const currentPage = ref(search.value.page)

watch(() => route.query, q => {
  search.value = parseQuery(q)
  currentPage.value = search.value.page
})

// ── Refine row ────────────────────────────────────────────────────────

const refineKeyword = ref('')
const refineField   = ref('keyword')
const refineCond    = ref('AND')
const showRefine    = ref(false)

const FIELD_OPTIONS = [
  { value: 'keyword',   label: 'Name / Keyword' },
  { value: 'location',  label: 'Location' },
  { value: 'provenance', label: 'Provenance' },
  { value: 'dynasty',   label: 'Period / Dynasty' },
  { value: 'patron',    label: 'Patron / Initial Owner' },
  { value: 'artist',    label: 'Architect / Artist / Master' },
  { value: 'material',  label: 'Material / Technique' },
]

function fieldLabel(val) {
  return FIELD_OPTIONS.find(f => f.value === val)?.label ?? val
}

function applyRefine() {
  if (!refineKeyword.value.trim()) return
  const q = {
    ...route.query,
    keyword4: refineKeyword.value,
    field4:   refineField.value,
    cond4:    refineCond.value,
  }
  delete q.page
  router.push({ path: '/database/results', query: q })
  showRefine.value = false
}

// ── Keyword matching ─────────────────────────────────────────────────

function matchField(item, field, keyword) {
  if (!keyword) return true
  const kw = keyword.toLowerCase().trim()
  if (!kw) return true
  const tr = enItemTranslations.value[item.id] ?? {}

  switch (field) {
    case 'keyword':
      return (tr.name ?? item.internal_name ?? '').toLowerCase().includes(kw) ||
             (tr.alternate_name ?? '').toLowerCase().includes(kw)
    case 'location':
      return (tr.location ?? '').toLowerCase().includes(kw)
    case 'provenance':
      return (tr.provenance ?? '').toLowerCase().includes(kw)
    case 'dynasty': {
      const names = item.dynasty_ids.map(id => dynastyLabel(id)).join(' ')
      return names.toLowerCase().includes(kw)
    }
    case 'patron':
      return (tr.patrons ?? tr.initial_owner ?? '').toLowerCase().includes(kw)
    case 'artist':
      return (tr.initial_owner ?? '').toLowerCase().includes(kw)
    case 'material':
      return (tr.type ?? '').toLowerCase().includes(kw)
    default:
      return (tr.name ?? item.internal_name ?? '').toLowerCase().includes(kw)
  }
}

function itemMatches(item, s) {
  const noSearch = !s.keyword1 && !s.keyword2 && !s.keyword3 && !s.keyword4

  // Date filter (always applied)
  if (s.dateFrom) {
    const begin = parseInt(s.dateFrom, 10)
    if (!isNaN(begin)) {
      const ok = item.end_date !== null ? item.end_date >= begin
               : item.start_date !== null ? item.start_date >= begin
               : true
      if (!ok) return false
    }
  }
  if (s.dateTo) {
    const end = parseInt(s.dateTo, 10)
    if (!isNaN(end)) {
      const ok = item.start_date !== null ? item.start_date <= end
               : item.end_date !== null   ? item.end_date <= end
               : true
      if (!ok) return false
    }
  }

  if (noSearch) return true

  // Keyword rows combined
  let result = null

  function combine(current, next, cond) {
    if (current === null) return next
    if (cond === 'OR') return current || next
    return current && next
  }

  if (s.keyword1) {
    result = combine(result, matchField(item, s.field1, s.keyword1), 'AND')
  }
  if (s.keyword2) {
    result = combine(result, matchField(item, s.field2, s.keyword2), s.cond2)
  }
  if (s.keyword3) {
    result = combine(result, matchField(item, s.field3, s.keyword3), s.cond3)
  }
  if (s.keyword4) {
    result = combine(result, matchField(item, s.field4, s.keyword4), s.cond4)
  }

  return result === null ? true : result
}

// ── Filtered results ──────────────────────────────────────────────────

const filteredItems = computed(() => {
  const s = search.value
  return items.value.filter(item => itemMatches(item, s))
})

const totalPages = computed(() => Math.max(1, Math.ceil(filteredItems.value.length / PAGE_SIZE)))

const pagedItems = computed(() => {
  const start = (currentPage.value - 1) * PAGE_SIZE
  return filteredItems.value.slice(start, start + PAGE_SIZE)
})

function goToPage(n) {
  currentPage.value = n
  const q = { ...route.query, page: String(n) }
  if (n === 1) delete q.page
  router.replace({ path: '/database/results', query: q })
  window.scrollTo(0, 0)
}

// ── Active search summary ────────────────────────────────────────────

const searchSummary = computed(() => {
  const s = search.value
  const parts = []
  if (s.keyword1) parts.push(`${fieldLabel(s.field1)}: "${s.keyword1}"`)
  if (s.keyword2) parts.push(`${s.cond2} ${fieldLabel(s.field2)}: "${s.keyword2}"`)
  if (s.keyword3) parts.push(`${s.cond3} ${fieldLabel(s.field3)}: "${s.keyword3}"`)
  if (s.keyword4) parts.push(`${s.cond4} ${fieldLabel(s.field4)}: "${s.keyword4}"`)
  if (s.dateFrom) parts.push(`from ${s.dateFrom}`)
  if (s.dateTo)   parts.push(`to ${s.dateTo}`)
  return parts
})
</script>

<template>
  <div>
    <h1 class="section-heading">Database Results</h1>

    <!-- Search summary -->
    <div class="content-box search-summary">
      <span class="summary-label">Search:</span>
      <template v-if="searchSummary.length">
        <span v-for="(part, i) in searchSummary" :key="i" class="summary-part">{{ part }}</span>
      </template>
      <span v-else class="summary-part muted">all items</span>

      <div class="summary-actions">
        <router-link to="/database" class="btn btn-secondary" style="font-size:12px; padding:4px 12px; text-decoration:none">
          New Search
        </router-link>
        <button
          class="btn"
          style="margin-left:8px; font-size:12px; padding:4px 12px"
          @click="showRefine = !showRefine"
        >
          Refine
        </button>
      </div>
    </div>

    <!-- Refine panel -->
    <div v-if="showRefine" class="content-box refine-panel">
      <strong class="filter-label">Add a keyword to refine results:</strong>
      <div class="refine-row">
        <select v-model="refineCond" style="width:60px">
          <option value="AND">AND</option>
          <option value="OR">OR</option>
        </select>
        <select v-model="refineField" style="width:200px; margin-left:8px">
          <option v-for="f in FIELD_OPTIONS" :key="f.value" :value="f.value">{{ f.label }}</option>
        </select>
        <input type="text" v-model="refineKeyword" placeholder="keyword…" style="width:200px; margin-left:8px" @keyup.enter="applyRefine" />
        <button class="btn" style="margin-left:10px" @click="applyRefine">Add</button>
      </div>
    </div>

    <!-- Results -->
    <div class="content-box">
      <p class="result-count">
        {{ filteredItems.length }} item{{ filteredItems.length !== 1 ? 's' : '' }} found
      </p>

      <ul v-if="pagedItems.length" class="item-list">
        <li
          v-for="item in pagedItems"
          :key="item.id"
          class="item-list-row"
          @click="$router.push(`/item/${encodeURIComponent(item.id)}`)"
        >
          <div class="item-thumb">
            <img v-if="item.images?.length" :src="item.images[0].url" :alt="itemLabel(item)" loading="lazy" />
            <div v-else class="item-thumb-placeholder" />
          </div>
          <div class="item-list-info">
            <div class="item-list-name">{{ itemLabel(item) }}</div>
            <div class="item-list-meta">
              <span v-if="item.country_id">{{ countryLabel(item.country_id) }}</span>
              <span v-if="enItemTranslations[item.id]?.dates">{{ enItemTranslations[item.id].dates }}</span>
              <span v-if="enItemTranslations[item.id]?.location">{{ enItemTranslations[item.id].location }}</span>
              <span class="item-type-badge">{{ item.type }}</span>
            </div>
          </div>
        </li>
      </ul>

      <p v-else class="no-results">No items match your search. <router-link to="/database">Try a new search.</router-link></p>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="pagination">
        <span class="pagination-info">Page {{ currentPage }} of {{ totalPages }}</span>
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
.search-summary {
  display: flex;
  align-items: flex-start;
  flex-wrap: wrap;
  gap: 6px;
  font-family: Arial, sans-serif;
  font-size: 12px;
}
.summary-label { font-weight: bold; color: var(--muted); }
.summary-part { color: var(--text); }
.summary-part.muted { color: var(--muted); }
.summary-actions { margin-left: auto; display: flex; align-items: center; }

.refine-panel { display: flex; flex-direction: column; gap: 10px; }
.filter-label { font-family: Arial, sans-serif; font-size: 12px; font-weight: bold; color: var(--muted); }
.refine-row { display: flex; align-items: center; flex-wrap: wrap; gap: 0; }

.result-count {
  font-family: Arial, sans-serif;
  font-size: 12px;
  color: var(--muted);
  margin-bottom: 12px;
  padding-bottom: 8px;
  border-bottom: 1px solid var(--border);
}

.no-results { color: var(--muted); font-family: Arial, sans-serif; font-size: 13px; padding: 20px 0; }

.item-type-badge {
  text-transform: uppercase;
  letter-spacing: 0.06em;
  font-size: 10px;
  color: #888;
}

.page-ellipsis { padding: 4px; color: var(--muted); font-family: Arial, sans-serif; font-size: 12px; }
</style>
