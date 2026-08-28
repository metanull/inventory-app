<script setup>
import { computed, ref } from 'vue'
import { RouterLink } from 'vue-router'
import {
  partners, partnerRoute, partnerObjectsRoute, partnerLabel, countryLabel,
  tr, defaultLang,
} from '../composables/useGalleryData.js'
import { tHtml, dirFor } from '../composables/useUiStrings.js'
import BackLink from '../components/BackLink.vue'

// The partners list, grouped by country with an A–Z / Z–A toggle, exactly as
// legacy's PartnersPage.vue.
//
// ── Partners that hold nothing are listed, because legacy lists them ────────
// Legacy's partner query is a three-branch UNION and the third one — its own
// comment calls it MWNF-384 — selects every museum *created under the
// gallery's own project*, whether or not it holds a member item. Carpets is
// the first gallery where that branch fires: `jo/Mus31` (Greater Amman
// Municipality) and `pt/Mus31` (Centro de História d'Aquém e d'Além-Mar) were
// both created under DCA, both come back from the live `/partners` with
// `hasObjects: 0`, and both are in the package with `item_count: 0`.
//
// So they get a full entry here — name, city, logo, "Read more" — and only the
// "View objects" link is withheld, which is exactly what legacy does with
// `v-if="partner.hasObjects"`. Hiding the museums themselves would be a
// regression against the site this viewer reproduces.
//
// The one place this differs from legacy is the object-count line, which is
// this viewer's addition (legacy prints no count on the partners page). Left
// alone it would read "0 objects in this Gallery", which looks like a data
// fault rather than a fact about the partner, so a zero-count partner gets a
// line naming the reason it is listed instead.
//
// Legacy also printed a "Partner / Affiliate" badge from `isPartner`, a flag
// that describes a partner's relationship to a *project*, not to this gallery,
// and `partners.project_id` in the inventory model is the museum's creating
// project rather than that relationship. The badge is therefore omitted, as on
// the amulets fork.
const order = ref('a-z')

const grouped = computed(() => {
  const byCountry = new Map()
  for (const partner of partners.value) {
    const country = countryLabel(partner.country_id)
    if (!byCountry.has(country)) byCountry.set(country, [])
    byCountry.get(country).push(partner)
  }
  const rows = [...byCountry.entries()]
    .map(([country, list]) => [
      country,
      list.sort((a, b) => partnerLabel(a.id).localeCompare(partnerLabel(b.id))),
    ])
    .sort((a, b) => a[0].localeCompare(b[0]))
  return order.value === 'a-z' ? rows : rows.reverse()
})

function city(partner) {
  return tr('partners', partner.id, defaultLang).city ?? ''
}
</script>

<template>
  <div id="partners-container">
    <div id="partners-options-container">
      <BackLink />
      <div id="partners-order">
        <button class="legacy-button" @click="order = order === 'a-z' ? 'z-a' : 'a-z'">
          Click here to sort Countries from {{ order === 'a-z' ? 'Z - A' : 'A - Z' }}
        </button>
      </div>
    </div>

    <div id="partners-list-description" class="prose" :dir="dirFor('galleryPartners')" v-html="tHtml('galleryPartners')"></div>

    <div id="partners-list-wrapper">
      <section class="partners-list" v-for="[country, list] in grouped" :key="country">
        <h2 class="partners-country">{{ country }}</h2>
        <div class="partner" v-for="partner in list" :key="partner.id">
          <div class="partner-text-links-container">
            <div class="partner-name">
              <RouterLink :to="partnerRoute(partner)">
                {{ partnerLabel(partner.id) }}<span v-if="city(partner)">, {{ city(partner) }}</span>
              </RouterLink>
            </div>
            <div class="partner-meta" v-if="partner.item_count">
              {{ partner.item_count }} object{{ partner.item_count === 1 ? '' : 's' }} in this Gallery
            </div>
            <div class="partner-meta partner-meta-empty" v-else>
              Project Partner — no objects in this Gallery
            </div>
            <div class="partner-links">
              <RouterLink :to="partnerRoute(partner)">Read more</RouterLink>
              <template v-if="partner.item_count">
                <span class="partner-link-divider">|</span>
                <RouterLink :to="partnerObjectsRoute(partner)">View objects</RouterLink>
              </template>
            </div>
          </div>
          <div class="partner-logo" v-if="partner.logos?.length">
            <img :src="partner.logos[0].url" :alt="partnerLabel(partner.id)" loading="lazy" />
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<style scoped>
#partners-container { background: #fff; width: 100%; min-height: 400px; padding-bottom: 30px; }
#partners-options-container { display: flex; align-items: center; justify-content: space-between; padding-inline-end: 20px; }
#partners-list-description { padding: 6px 40px 20px; max-width: 900px; line-height: 1.5; }
#partners-list-wrapper { padding: 0 40px; }
.partners-country {
  font-size: 20px;
  font-weight: 700;
  color: var(--theme-dark);
  border-bottom: 2px solid var(--theme-medium);
  margin-top: 22px;
  padding-bottom: 3px;
}
.partner {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  padding: 12px 0;
  border-bottom: 1px solid var(--background-color);
}
.partner-text-links-container { flex: 1; min-width: 0; }
.partner-name a { font-weight: 700; color: var(--link-blue); text-decoration: none; }
.partner-name a:hover { text-decoration: underline; }
.partner-meta { font-size: 13px; color: #666; margin: 3px 0; }
.partner-meta-empty { font-style: italic; }
.partner-links a { color: var(--link-blue); font-size: 13px; text-decoration: none; }
.partner-links a:hover { text-decoration: underline; }
.partner-link-divider { margin: 0 6px; color: #999; }
.partner-logo img { max-width: 120px; max-height: 70px; object-fit: contain; display: block; }
</style>
