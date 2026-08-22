<script setup>
import { ref, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useInventoryData } from '../composables/useInventoryData.js'

const route = useRoute()
const router = useRouter()
const {
  publicItems: items, countries, partners, collections,
  countryLabel, partnerLabel,
  itemLabel, enItemTranslations, mdInline, mdStrip,
  exhibitions, exhibitionThemes, enCollectionTranslations,
} = useInventoryData()

const PAGE_SIZE = 20

// ── Filter state (synced with URL query) ────────────────────────────────

const filterCountry    = ref(route.query.country    ?? '')
const filterExhibition = ref(route.query.exhibition ?? '')
const filterTheme      = ref(route.query.theme      ?? '')
const filterChapter    = ref(route.query.chapter    ?? '')
const filterPartner    = ref(route.query.partner    ?? '')
const filterBegin      = ref(route.query.begin      ?? '')
const filterEnd        = ref(route.query.end        ?? '')
const currentPage      = ref(parseInt(route.query.page ?? '1', 10) || 1)

watch(
  [filterCountry, filterExhibition, filterTheme, filterChapter, filterPartner, filterBegin, filterEnd],
  () => { currentPage.value = 1 }
)

watch(
  () => route.query,
  q => {
    filterCountry.value    = q.country    ?? ''
    filterExhibition.value = q.exhibition ?? ''
    filterTheme.value      = q.theme      ?? ''
    filterChapter.value    = q.chapter    ?? ''
    filterPartner.value    = q.partner    ?? ''
    filterBegin.value      = q.begin      ?? ''
    filterEnd.value        = q.end        ?? ''
    currentPage.value      = parseInt(q.page ?? '1', 10) || 1
  }
)

// Cascade resets on user interaction only (not on URL sync, which sets all
// three fields together): picking another Theme clears Subtheme + Chapter,
// picking another Subtheme clears Chapter — like legacy's dependent selects.
function onExhibitionChange() {
  filterTheme.value = ''
  filterChapter.value = ''
}
function onThemeChange() {
  filterChapter.value = ''
}

function applyFilters() {
  const q = {}
  if (filterCountry.value)    q.country    = filterCountry.value
  if (filterExhibition.value) q.exhibition = filterExhibition.value
  if (filterTheme.value)      q.theme      = filterTheme.value
  if (filterChapter.value)    q.chapter    = filterChapter.value
  if (filterPartner.value)    q.partner    = filterPartner.value
  if (filterBegin.value)      q.begin      = filterBegin.value
  if (filterEnd.value)        q.end        = filterEnd.value
  router.push({ path: '/permanent-collection/results', query: q })
}

function resetFilters() {
  filterCountry.value    = ''
  filterExhibition.value = ''
  filterTheme.value      = ''
  filterChapter.value    = ''
  filterPartner.value    = ''
  filterBegin.value      = ''
  filterEnd.value        = ''
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

const availablePartners = computed(() => {
  const ids = new Set(items.value.map(i => i.partner_id).filter(Boolean))
  return partners.value
    .filter(p => ids.has(p.id))
    .map(p => ({ id: p.id, name: partnerLabel(p.id) }))
    .sort((a, b) => a.name.localeCompare(b.name))
})

// ── Theme / Subtheme / Chapter (Virtual Exhibition membership) ──────────
//
// Legacy pclist_all.php filters the Permanent Collection by exhibition
// ("Theme"), exhibition theme ("Subtheme") and subtheme ("Chapter").
// Membership is the collections' items[] lists; an exhibition-level filter
// covers the exhibition and every theme/chapter below it.

function collectionTitle(c) {
  return mdStrip(enCollectionTranslations.value[c.id]?.title ?? c.internal_name)
}

const availableExhibitions = computed(() =>
  exhibitions.value
    .map(e => ({ id: e.id, name: collectionTitle(e) }))
    .sort((a, b) => a.name.localeCompare(b.name))
)

const availableSubthemes = computed(() => {
  if (!filterExhibition.value) return []
  return exhibitionThemes(filterExhibition.value)
    .map(t => ({ id: t.id, name: collectionTitle(t) }))
})

const availableChapters = computed(() => {
  if (!filterTheme.value) return []
  const theme = exhibitionThemes(filterExhibition.value).find(t => t.id === filterTheme.value)
  return (theme?.chapters ?? []).map(c => ({ id: c.id, name: collectionTitle(c) }))
})

// Country-specific National Context variants hang under exhibitions but are
// not part of the theme tree — keep their items out of exhibition scopes.
const NC_BC_PREFIX = 'mwnf3_sharing_history:sh_national_context_'

const collectionById = computed(() => {
  const m = new Map()
  for (const c of collections.value) m.set(c.id, c)
  return m
})

const childrenByParent = computed(() => {
  const m = new Map()
  for (const c of collections.value) {
    if (!c.parent_id) continue
    if (!m.has(c.parent_id)) m.set(c.parent_id, [])
    m.get(c.parent_id).push(c)
  }
  return m
})

// All item ids attached to `collectionId` or any descendant (NC excluded).
function collectItemIds(collectionId) {
  const set = new Set()
  const stack = [collectionId]
  while (stack.length) {
    const id = stack.pop()
    const c = collectionById.value.get(id)
    if (!c) continue
    for (const entry of c.items ?? []) set.add(entry.id)
    for (const child of childrenByParent.value.get(id) ?? []) {
      if (child.backward_compatibility?.startsWith(NC_BC_PREFIX)) continue
      stack.push(child.id)
    }
  }
  return set
}

const collectionScopeIds = computed(() => {
  if (filterChapter.value)    return collectItemIds(filterChapter.value)
  if (filterTheme.value)      return collectItemIds(filterTheme.value)
  if (filterExhibition.value) return collectItemIds(filterExhibition.value)
  return null
})

// ── Filtered items ────────────────────────────────────────────────────

const filteredItems = computed(() => {
  let result = items.value

  if (filterCountry.value) {
    result = result.filter(item => item.country_id === filterCountry.value)
  }
  if (collectionScopeIds.value) {
    result = result.filter(item => collectionScopeIds.value.has(item.id))
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

  // Legacy orders results chronologically by start date (undated items last).
  return [...result].sort((a, b) => {
    const ad = a.start_date ?? Infinity
    const bd = b.start_date ?? Infinity
    return ad - bd
  })
})

// "[N objects, M monuments]" split, matching legacy's result-count phrasing.
const resultCounts = computed(() => {
  let objects = 0
  let monuments = 0
  for (const item of filteredItems.value) {
    if (item.type === 'monument') monuments++
    else objects++
  }
  return { objects, monuments }
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
  const parts = []
  if (filterCountry.value) parts.push(countryLabel(filterCountry.value))
  const scopeId = filterChapter.value || filterTheme.value || filterExhibition.value
  if (scopeId) {
    const c = collectionById.value.get(scopeId)
    if (c) parts.push(collectionTitle(c))
  }
  if (filterPartner.value) parts.push(partnerLabel(filterPartner.value))
  if (filterBegin.value)   parts.push(`from ${filterBegin.value}`)
  if (filterEnd.value)     parts.push(`up to ${filterEnd.value}`)
  return parts.length ? parts.join(' — ') : 'All Items'
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
        <label>Theme</label>
        <select v-model="filterExhibition" style="width:200px" @change="onExhibitionChange">
          <option value="">— any —</option>
          <option v-for="e in availableExhibitions" :key="e.id" :value="e.id">{{ e.name }}</option>
        </select>
      </div>

      <div class="filter-row">
        <label>Subtheme</label>
        <select v-model="filterTheme" style="width:200px" :disabled="!filterExhibition" @change="onThemeChange">
          <option value="">— any —</option>
          <option v-for="t in availableSubthemes" :key="t.id" :value="t.id">{{ t.name }}</option>
        </select>
      </div>

      <div class="filter-row">
        <label>Chapter</label>
        <select v-model="filterChapter" style="width:200px" :disabled="!filterTheme">
          <option value="">— any —</option>
          <option v-for="c in availableChapters" :key="c.id" :value="c.id">{{ c.name }}</option>
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
        {{ resultCounts.objects }} object{{ resultCounts.objects !== 1 ? 's' : '' }},
        {{ resultCounts.monuments }} monument{{ resultCounts.monuments !== 1 ? 's' : '' }}
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
            <div class="item-list-name" v-html="mdInline(enItemTranslations[item.id]?.name ?? item.internal_name ?? item.id)" />
            <div class="item-list-meta">
              <span v-if="item.country_id">{{ countryLabel(item.country_id) }}</span>
              <span v-if="enItemTranslations[item.id]?.dates">{{ enItemTranslations[item.id].dates }}</span>
              <span v-if="item.partner_id && partners.some(p => p.id === item.partner_id)">{{ partnerLabel(item.partner_id) }}</span>
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

.item-type-badge {
  text-transform: uppercase;
  letter-spacing: 0.06em;
  font-size: 10px;
  color: #888;
}

.page-ellipsis { padding: 4px 4px; color: var(--muted); font-family: 'Roboto', sans-serif; font-size: 12px; }
</style>
