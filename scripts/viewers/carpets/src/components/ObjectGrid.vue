<script setup>
import { ref } from 'vue'
import { RouterLink } from 'vue-router'
import {
  itemRoute, itemLabel, partnerLabel, countryLabel, tr, defaultLang,
} from '../composables/useGalleryData.js'

// The results grid shared by collection results, free-text search, partner
// objects and the timeline gallery — one component in legacy too
// (ObjectGrid.vue), hover-revealing a detail card over each thumbnail.
defineProps({
  results: { type: Array, required: true },
})

const hovered = ref(-1)

// Legacy read these off the API, which read `mwnf3.projectnames`. Carpets draws
// members from seven projects — including one EXTHE and one GALLERIES record
// that the amulets fork never had to name — so the map covers the whole table's
// English row for the keys a gallery can meet, and still falls back to the key.
const PROJECT_NAMES = {
  ISL: 'Discover Islamic Art',
  EPM: 'Explore Islamic Art Collections',
  DBA: 'Discover Baroque Art',
  BAR: 'Discover Baroque Art',
  AWE: 'Sharing History',
  awe: 'Sharing History',
  DCA: 'Discover Carpet Art',
  DGA: 'Discover Glass Art',
  EXTHE: 'The Table Is Set',
  GALLERIES: 'MWNF Galleries',
}

function sheet(item) {
  return tr('items', item.id, defaultLang)
}

// Legacy truncates the date line at 80 characters (characters.js `cutOff`).
function shortDate(text) {
  if (!text) return ''
  return text.length > 80 ? `${text.slice(0, 79)}[...]` : text
}

function projectName(key) {
  return PROJECT_NAMES[key] ?? key
}
</script>

<template>
  <div class="entry-wrapper">
    <div
      v-for="(item, index) in results"
      :key="item.id"
      class="entry-container"
      @mouseover="hovered = index"
      @mouseleave="hovered = -1"
    >
      <div class="entry">
        <div class="entry-image-wrapper">
          <img v-if="item.images?.length" class="entry-image" :src="item.images[0].url" :alt="itemLabel(item)" loading="lazy" />
          <div v-else class="entry-image entry-image-empty"></div>
        </div>
        <div class="entry-details" :class="{ shown: hovered === index }">
          <RouterLink class="entry-details-img-wrapper" :to="itemRoute(item)">
            <img v-if="item.images?.length" :src="item.images[0].url" :alt="itemLabel(item)" />
          </RouterLink>
          <div class="entry-text-container">
            <p class="entry-name">{{ itemLabel(item) }}</p>
            <p class="entry-date">{{ shortDate(sheet(item).dates) }}</p>
            <p class="entry-partner">{{ partnerLabel(item.partner_id) }}</p>
            <p class="entry-location">
              {{ [sheet(item).location, countryLabel(item.country_id)].filter(Boolean).join(', ') }}
            </p>
            <p class="entry-project">for <span>{{ projectName(item.project_key) }}</span></p>
            <RouterLink :to="itemRoute(item)">
              <p class="entry-link">See Database Entry &gt;</p>
            </RouterLink>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.entry-wrapper {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 4px;
  width: 100%;
}
.entry-container { position: relative; }
.entry { position: relative; width: 100%; padding-top: 100%; }
.entry-image-wrapper { position: absolute; inset: 0; overflow: hidden; background: #fff; }
.entry-image { width: 100%; height: 100%; object-fit: cover; display: block; }
.entry-image-empty { background: repeating-linear-gradient(45deg, #f3f3f3, #f3f3f3 10px, #e9e9e9 10px, #e9e9e9 20px); }

.entry-details {
  position: absolute;
  inset: 0;
  display: none;
  background: rgba(var(--theme-dark-rgb), 0.93);
  color: var(--light-text);
  padding: 10px;
  overflow: auto;
}
.entry-details.shown { display: flex; gap: 10px; }
.entry-details-img-wrapper { flex: 0 0 38%; }
.entry-details-img-wrapper img { width: 100%; height: auto; display: block; }
.entry-text-container { flex: 1; font-size: 13px; line-height: 1.35; min-width: 0; }
.entry-name { font-weight: 700; margin-bottom: 4px; }
.entry-date, .entry-partner, .entry-location, .entry-project { margin-bottom: 3px; }
.entry-project span { font-style: italic; }
.entry-details a { color: var(--light-text); text-decoration: none; }
.entry-link { margin-top: 6px; text-decoration: underline; }

@media only screen and (max-width: 974px) {
  .entry-wrapper { grid-template-columns: repeat(2, 1fr); }
  .entry { padding-top: 0; height: auto; }
  .entry-image-wrapper { position: static; }
  .entry-image { height: 220px; }
  .entry-details { position: static; display: flex !important; }
  .entry-details-img-wrapper { display: none; }
}
@media only screen and (max-width: 499px) {
  .entry-wrapper { grid-template-columns: 1fr; }
}
</style>
