<script setup>
import { computed } from 'vue'
import { useInventoryData } from '../composables/useInventoryData.js'

const {
  historicalBackgroundGeneral,
  historicalBackgroundProfiles,
  enCollectionTranslations,
  countryLabel,
  md, mdInline,
} = useInventoryData()

const general = historicalBackgroundGeneral
const generalText = computed(() =>
  general.value ? (enCollectionTranslations.value[general.value.id] ?? {}) : {}
)

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
  <div class="hb-wrap">
    <div class="content-box">
      <h1 class="section-heading">Historical Background</h1>
      <p class="hb-intro-note">
        Sharing History — Arab World – Europe covers the period 1815 – 1918.
        Explore the general historical background and the per-country
        historical profiles below.
      </p>

      <div v-if="generalText.description" class="prose" v-html="md(generalText.description)" />
    </div>

    <div class="content-box">
      <h2 class="section-heading">Historical Profiles by Country</h2>
      <div class="hb-country-grid">
        <RouterLink
          v-for="record in profiles"
          :key="record.id"
          :to="`/historical-background/${encodeURIComponent(record.id)}`"
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
  </div>
</template>

<style scoped>
.hb-wrap { display: flex; flex-direction: column; gap: 16px; }

.hb-intro-note {
  font-family: 'Roboto', sans-serif;
  font-size: 13px;
  color: var(--muted);
  margin-bottom: 14px;
}

.prose { font-size: 14px; line-height: 1.7; color: var(--text); font-family: 'Roboto', sans-serif; }
.prose :deep(p) { margin: 0 0 .75em; }

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
