<script setup>
import { ref, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useInventoryData } from '../composables/useInventoryData.js'

const route = useRoute()
const router = useRouter()
const {
  items, countries, partners, dynasties,
  countryLabel, dynastyLabel, partnerLabel,
  itemLabel, enItemTranslations,
} = useInventoryData()

const PAGE_SIZE = 20

// ── Filter state (synced with URL query) ────────────────────────────────

const filterCountry = ref(route.query.country ?? '')
const filterDynasty = ref(route.query.dynasty ?? '')
const filterPartner = ref(route.query.partner ?? '')
const filterBegin   = ref(route.query.begin   ?? '')
const filterEnd     = ref(route.query.end     ?? '')
const currentPage   = ref(parseInt(route.query.page ?? '1', 10) || 1)

watch(
  [filterCountry, filterDynasty, filterPartner, filterBegin, filterEnd],
  () => { currentPage.value = 1 }
)

watch(
  () => route.query,
  q => {
    filterCountry.value = q.country ?? ''
    filterDynasty.value = q.dynasty ?? ''
    filterPartner.value = q.partner ?? ''
    filterBegin.value   = q.begin   ?? ''
    filterEnd.value     = q.end     ?? ''
    currentPage.value   = parseInt(q.page ?? '1', 10) || 1
  }
)

function applyFilters() {
  const q = {}
  if (filterCountry.value) q.country = filterCountry.value
  if (filterDynasty.value) q.dynasty = filterDynasty.value
  if (filterPartner.value) q.partner = filterPartner.value
  if (filterBegin.value)   q.begin   = filterBegin.value
  if (filterEnd.value)     q.end     = filterEnd.value
  router.push({ path: '/permanent-collection/results', query: q })
}

function resetFilters() {
  filterCountry.value = ''
  filterDynasty.value = ''
  filterPartner.value = ''
  filterBegin.value   = ''
  filterEnd.value     = ''
  applyFilters()
}

// ── Build available options from actual items ───────────────────────────

const availableCountries = computed(() => {
  const ids = new Set(items.value.map(i => i.country_id).filter(Boolean))
  return countries.value
    .filter(c => ids.has(c.id))
    .map(c => ({ id: c.id, name: countryLabel(c.id) }))
    .sort((a, b) => a.name.localeCompare(b.name))
})

const availableDynasties = computed(() => {
  const ids = new Set(items.value.flatMap(i => i.dynasty_ids))
  return dynasties.value
    .filter(d => ids.has(d.id))
    .map(d => ({ id: d.id, name: dynastyLabel(d.id), from_ad: d.from_ad }))
    .sort((a, b) => (a.from_ad ?? 9999) - (b.from_ad ?? 9999))
})

const availablePartners = computed(() => {
  const ids = new Set(items.value.map(i => i.partner_id).filter(Boolean))
  return partners.value
    .filter(p => ids.has(p.id))
    .map(p => ({ id: p.id, name: partnerLabel(p.id) }))
    .sort((a, b) => a.name.localeCompare(b.name))
})

// ── Filtered items ────────────────────────────────────────────────────

const filteredItems = computed(() => {
  let result = items.value

  if (filterCountry.value) {
    result = result.filter(item => item.country_id === filterCountry.value)
  }
  if (filterDynasty.value) {
    result = result.filter(item => item.dynasty_ids.includes(filterDynasty.value))
  }
  if (filterPartner.value) {
    result = result.filter(item => item.partner_id === filterPartner.value)
  }
  if (filterBegin.value) {
    const begin = parseInt(filterBegin.value, 10)
    if (!isNaN(begin)) {
      result = result.filter(item => {
        if (item.end_date !== null) return item.end_date >= begin
        if (item.start_date !== null) return item.start_date >= begin
        return true
      })
    }
  }
  if (filterEnd.value) {
    const end = parseInt(filterEnd.value, 10)
    if (!isNaN(end)) {
      result = result.filter(item => {
        if (item.start_date !== null) return item.start_date <= end
        if (item.end_date !== null) return item.end_date <= end
        return true
      })
    }
  }

  return result
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
  router.replace({ path: '/permanent-collection/results', query: q })
  window.scrollTo(0, 0)
}

// ── Active filter label ───────────────────────────────────────────────

const activeFilterLabel = computed(() => {
  if (filterCountry.value) return countryLabel(filterCountry.value)
  if (filterDynasty.value) return dynastyLabel(filterDynasty.value)
  if (filterPartner.value) return partnerLabel(filterPartner.value)
  if (filterBegin.value)   return `from ${filterBegin.value}`
  if (filterEnd.value)     return `up to ${filterEnd.value}`
  return 'All Items'
})
</script>

<template>
  <div>
    <h1 class="section-heading">
      Permanent Collection
      <span v-if="activeFilterLabel !== 'All Items'" class="heading-filter"> — {{ activeFilterLabel }}</span>
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
        <label>Period / Dynasty</label>
        <select v-model="filterDynasty" style="width:200px">
          <option value="">— any —</option>
          <option v-for="d in availableDynasties" :key="d.id" :value="d.id">{{ d.name }}</option>
        </select>
      </div>

      <div class="filter-row">
        <label>Holding Institution</label>
        <select v-model="filterPartner" style="width:200px">
          <option value="">— any —</option>
          <option v-for="p in availablePartners" :key="p.id" :value="p.id">{{ p.name }}</option>
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
              <span v-if="item.type" class="item-type-badge">{{ item.type }}</span>
            </div>
          </div>
        </li>
      </ul>

      <p v-else class="no-results">No items match the selected filter.</p>

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
.filter-label { font-family: Arial, sans-serif; font-size: 12px; font-weight: bold; color: var(--muted); }
.filter-row { display: flex; align-items: center; gap: 6px; font-family: Arial, sans-serif; font-size: 12px; color: var(--muted); }
.filter-actions { margin-left: auto; }

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

.page-ellipsis { padding: 4px 4px; color: var(--muted); font-family: Arial, sans-serif; font-size: 12px; }
</style>
