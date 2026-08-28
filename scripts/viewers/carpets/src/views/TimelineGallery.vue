<script setup>
import { computed } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { items, countryLabel } from '../composables/useGalleryData.js'
import { sortChronological, paginate } from '../composables/useCollection.js'
import { countryIdForCode, eraLabel } from '../composables/useTimeline.js'
import ObjectGrid from '../components/ObjectGrid.vue'
import Pages from '../components/Pages.vue'
import BackLink from '../components/BackLink.vue'

// The member items whose dates overlap the timeline search's country and
// period. Legacy asked `/items` for this; here it is the same join, done
// client-side, which is exactly what the package spec anticipated
// ("the timeline-gallery page joins events to member items by country + year
// range client-side").
const route = useRoute()
const router = useRouter()

const countryId = computed(() => countryIdForCode(route.params.country))
const start = computed(() => (route.params.start === 'any' ? null : Number(route.params.start)))
const end = computed(() => (route.params.end === 'any' ? null : Number(route.params.end)))

const matching = computed(() => sortChronological(items.value.filter(i => {
  if (countryId.value && i.country_id !== countryId.value) return false
  const itemStart = i.start_date
  const itemEnd = i.end_date ?? i.start_date
  if (!Number.isFinite(itemStart)) return false
  if (start.value != null && itemEnd < start.value) return false
  if (end.value != null && itemStart > end.value) return false
  return true
})))

const page = computed(() => paginate(matching.value, route.params.page ?? 1))

function navigate(p) {
  router.push({ name: 'timeline-gallery', params: { ...route.params, page: p } })
}
</script>

<template>
  <div id="timeline-gallery-container">
    <BackLink />

    <div id="gallery-header">
      <p>
        Timeline Gallery |
        <span>{{ countryId ? countryLabel(countryId) : 'All Countries' }}</span>
        <span v-if="start != null || end != null">
          | {{ start != null ? eraLabel(start) : 'earliest' }} to {{ end != null ? eraLabel(end) : 'latest' }}
        </span>
      </p>
      <p>{{ page.total }} object(s)</p>
      <p class="back-to-events">
        <RouterLink :to="{ name: 'timeline-results', query: { c: route.params.country, start: route.params.start === 'any' ? '' : route.params.start, end: route.params.end === 'any' ? '' : route.params.end } }">
          ➤ Back to the events
        </RouterLink>
      </p>
    </div>

    <Pages :page-info="page" @navigate="navigate" />

    <div id="content-container">
      <ObjectGrid v-if="page.rows.length" :results="page.rows" />
      <p v-else class="no-results">No objects from this Gallery fall in that country and period.</p>
    </div>

    <Pages :page-info="page" @navigate="navigate" />
  </div>
</template>

<style scoped>
#timeline-gallery-container { background: #fff; width: 100%; min-height: 400px; padding-bottom: 30px; }
#gallery-header { padding: 0 20px 12px; }
#gallery-header span { font-weight: 700; }
.back-to-events a { color: var(--link-blue); font-size: 13px; }
#content-container { padding: 0 20px; }
.no-results { padding: 30px 0; }
</style>
