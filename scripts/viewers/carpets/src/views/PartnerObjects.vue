<script setup>
import { computed } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import {
  items, partnerFromKey, partnerRoute, partnerLabel, countryLabel, tr, defaultLang,
} from '../composables/useGalleryData.js'
import { sortChronological, paginate } from '../composables/useCollection.js'
import ObjectGrid from '../components/ObjectGrid.vue'
import Pages from '../components/Pages.vue'
import BackLink from '../components/BackLink.vue'

// The member items one partner holds. Legacy paginated this at the API's page
// size; the same 9-per-page grid is used here as for collection results.
const route = useRoute()
const router = useRouter()

const partner = computed(() => partnerFromKey(route.params.country, route.params.id))
const held = computed(() => {
  const p = partner.value
  if (!p) return []
  return sortChronological(items.value.filter(i => i.partner_id === p.id))
})
const page = computed(() => paginate(held.value, route.params.page ?? 1))

function navigate(p) {
  router.push({
    name: 'partner-objects',
    params: { ...route.params, page: p },
  })
}

const city = computed(() => (partner.value ? tr('partners', partner.value.id, defaultLang).city ?? '' : ''))
</script>

<template>
  <div id="partner-objects-container" v-if="partner">
    <BackLink />

    <div id="partner-objects-header">
      <p id="partner-name">{{ partnerLabel(partner.id) }}</p>
      <p id="partner-location">{{ [city, countryLabel(partner.country_id)].filter(Boolean).join(', ') }}</p>
      <p id="partner-count">{{ page.total }} object(s) in this Gallery</p>
    </div>

    <Pages :page-info="page" @navigate="navigate" />

    <div id="content-container">
      <ObjectGrid v-if="page.rows.length" :results="page.rows" />
      <p v-else class="no-results">This partner holds no objects in this Gallery.</p>
      <div id="profile-link-container">
        <RouterLink id="profile-link" :to="partnerRoute(partner)">➤ Partner Profile</RouterLink>
      </div>
    </div>

    <Pages :page-info="page" @navigate="navigate" />
  </div>
</template>

<style scoped>
#partner-objects-container { background: #fff; width: 100%; min-height: 400px; padding-bottom: 30px; }
#partner-objects-header { padding: 0 20px 12px; }
#partner-name { font-size: 22px; font-weight: 700; color: var(--theme-dark); }
#partner-location { color: #555; }
#partner-count { font-size: 13px; color: #666; margin-top: 3px; }
#content-container { padding: 0 20px; }
#profile-link-container { padding-top: 16px; }
#profile-link { color: var(--link-blue); }
.no-results { padding: 30px 0; }
</style>
