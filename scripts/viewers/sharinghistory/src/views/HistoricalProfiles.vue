<script setup>
import { computed } from 'vue'
import { useInventoryData } from '../composables/useInventoryData.js'

const {
  historicalBackgroundProfiles,
  enCollectionTranslations,
  countryLabel,
  mdInline,
} = useInventoryData()

// Country profiles, alphabetical by English country name (legacy nav order).
const profiles = computed(() =>
  [...historicalBackgroundProfiles.value].sort((a, b) =>
    countryLabel(a.country_id).localeCompare(countryLabel(b.country_id))
  )
)

function profileTitle(record) {
  return enCollectionTranslations.value[record.id]?.title ?? record.internal_name
}
</script>

<template>
  <div class="content-box">
    <h1 class="section-heading">Historical Profiles</h1>
    <p class="hb-intro-note">
      Each profile presents the period 1815 – 1918 from the perspective of the
      country concerned, illustrated with items from the database.
    </p>
    <div class="hb-country-grid">
      <RouterLink
        v-for="record in profiles"
        :key="record.id"
        :to="`/historical-profiles/${encodeURIComponent(record.id)}`"
        class="hb-country-card"
      >
        <img
          v-if="record.images?.length"
          :src="record.images[0].url"
          :alt="countryLabel(record.country_id)"
          class="hb-country-img"
          loading="lazy"
        />
        <div v-else class="hb-country-img hb-country-img-placeholder" />
        <span class="hb-country-name">{{ countryLabel(record.country_id) }}</span>
        <span class="hb-country-title" v-html="mdInline(profileTitle(record))" />
      </RouterLink>
    </div>
  </div>
</template>

<style scoped>
.hb-intro-note {
  font-family: 'Roboto', sans-serif;
  font-size: 13px;
  color: var(--muted);
  margin-bottom: 14px;
}

.hb-country-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 14px;
}
.hb-country-card {
  display: flex;
  flex-direction: column;
  gap: 4px;
  text-decoration: none !important;
}
.hb-country-card:hover .hb-country-name { color: var(--nav-active); }
.hb-country-img {
  width: 100%;
  aspect-ratio: 4 / 3;
  object-fit: cover;
  border: 1px solid var(--border);
  background: var(--tile-bg);
  display: block;
}
.hb-country-name {
  font-family: 'Roboto', sans-serif;
  font-size: 14px;
  font-weight: 500;
  color: var(--heading);
}
.hb-country-title {
  font-family: 'Roboto', sans-serif;
  font-size: 11px;
  color: var(--muted);
}
</style>
