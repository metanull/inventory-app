<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useInventoryData } from '../composables/useInventoryData.js'

const router = useRouter()
const { dynasties, items, dynastyLabel, enDynastyTranslations, md } = useInventoryData()

// Legacy intro copy from https://islamicart.museumwnf.org/dynasties/ — not
// backed by any database table (confirmed: dynasty-importer.ts only imports
// dynasty records/texts, no landing-page content), so it's hardcoded here
// per product decision. English only for now — no source text was available
// to hardcode for other languages. See Epic 13 in the islamicart parity backlog.
const introHeading = 'What do we intend by ‘Islamic Dynasties’?'
const introBody = `After the first four caliphs, the ‘Rightly Guided’, who had established the Rashidun, the first Islamic Caliphate, the Islamic world was reigned by families of great local influence who carried out an ambitious military, social and cultural programme, often with impact far beyond their geographic area of origin. Many visionary rulers shaped Islamic lands through almost thirteen centuries and left a legacy that continues inspiring in all fields: religion, arts, culture, social life and also policies.

Similar to the terminology used for the succession of ruling families in other civilisations, also Islamic histography refers to those families as '**Dynasties**'. Some ruled in specific regions, others over the entire Islamic empire.

In this section of the Discover Islamic Art website **we introduce the Islamic dynasties who had ruled, from the Umayyad Caliphate until the end of the Ottoman empire**. Initially focus was given on the Mediterranean region but we started to expand also to other parts of the Islamic world.

You will be able to read the profile of the selected Dynasty and view related content in the MWNF database. This will include Objects, Monuments, and, if available, also an Artistic Introduction to the specific stylistic characteristics of the selected Dynasty and/or an Exhibition presenting the cultural and historical background.

All texts have been compiled by local experts of the country concerned; names of authors can be found on the page itself or on the general Discover Islamic Art credits page.

All texts are available in Arabic and English, and most also in French and Spanish.`

function goToDynasty(id) {
  if (id) router.push(`/dynasty/${encodeURIComponent(id)}`)
}

// Only list dynasties actually linked to items in the collection, sorted
// chronologically (from_ad), mirroring the "Period / Dynasty" ordering
// already used by the Permanent Collection filters.
const dynastyList = computed(() => {
  const ids = new Set(items.value.flatMap(i => i.dynasty_ids))
  return dynasties.value
    .filter(d => ids.has(d.id))
    .map(d => ({
      ...d,
      name: dynastyLabel(d.id),
      also_known_as: enDynastyTranslations.value[d.id]?.also_known_as ?? '',
      area: enDynastyTranslations.value[d.id]?.area ?? '',
      date_description_ad: enDynastyTranslations.value[d.id]?.date_description_ad ?? '',
      itemCount: items.value.filter(i => i.dynasty_ids.includes(d.id)).length,
    }))
    .sort((a, b) => (a.from_ad ?? 9999) - (b.from_ad ?? 9999))
})

function dateRangeLabel(d) {
  if (d.date_description_ad) return d.date_description_ad
  if (d.from_ad || d.to_ad) return `${d.from_ad ?? '?'} – ${d.to_ad ?? '?'} AD`
  return ''
}
</script>

<template>
  <div>
    <h1 class="section-heading">Islamic Dynasties</h1>

    <div class="content-box">
      <h2 class="intro-heading">{{ introHeading }}</h2>
      <div class="intro-text" v-html="md(introBody)"></div>

      <div class="dynasty-quicknav">
        <label for="dynasty-quicknav-select" class="quicknav-label">Jump to a dynasty:</label>
        <select
          id="dynasty-quicknav-select"
          class="quicknav-select"
          @change="goToDynasty($event.target.value)"
        >
          <option value="">Select a dynasty…</option>
          <option v-for="d in dynastyList" :key="d.id" :value="d.id">{{ d.name }}</option>
        </select>
      </div>

      <p class="result-count">
        {{ dynastyList.length }} dynast{{ dynastyList.length !== 1 ? 'ies' : 'y' }}
      </p>

      <ul v-if="dynastyList.length" class="dynasty-list">
        <li
          v-for="d in dynastyList"
          :key="d.id"
          class="dynasty-row"
          @click="$router.push(`/dynasty/${encodeURIComponent(d.id)}`)"
        >
          <div class="dynasty-date">{{ dateRangeLabel(d) }}</div>
          <div class="dynasty-body">
            <div class="dynasty-name">{{ d.name }}</div>
            <div v-if="d.also_known_as" class="dynasty-aka">also known as {{ d.also_known_as }}</div>
            <div v-if="d.area" class="dynasty-area">{{ d.area }}</div>
          </div>
          <div class="dynasty-count">{{ d.itemCount }} item{{ d.itemCount !== 1 ? 's' : '' }}</div>
        </li>
      </ul>

      <p v-else class="no-results">No dynasties found.</p>
    </div>
  </div>
</template>

<style scoped>
.intro-heading {
  font-size: 16px;
  font-weight: 500;
  color: var(--heading);
  margin-bottom: 10px;
  font-family: 'Roboto', sans-serif;
}

.intro-text {
  font-size: 13px;
  line-height: 1.65;
  color: var(--muted);
  margin-bottom: 16px;
  font-family: 'Roboto', sans-serif;
}
.intro-text :deep(p) { margin: 0 0 .75em; }
.intro-text :deep(p:last-child) { margin-bottom: 0; }
.intro-text :deep(strong) { font-weight: 600; color: var(--text); }

.dynasty-quicknav {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 16px;
}
.quicknav-label {
  font-size: 12px;
  font-weight: 500;
  color: var(--heading);
  font-family: 'Roboto', sans-serif;
  white-space: nowrap;
}
.quicknav-select {
  font-size: 13px;
  font-family: 'Roboto', sans-serif;
  color: var(--text);
  padding: 6px 8px;
  border: 1px solid var(--border);
  border-radius: 4px;
  background: var(--bg);
  flex: 1;
  max-width: 320px;
}

.result-count {
  font-family: 'Roboto', sans-serif;
  font-size: 12px;
  color: var(--muted);
  margin-bottom: 12px;
  padding-bottom: 8px;
  border-bottom: 1px solid var(--border);
}

.no-results { color: var(--muted); font-family: 'Roboto', sans-serif; font-size: 13px; padding: 20px 0; }

.dynasty-list { list-style: none; }
.dynasty-row {
  display: flex;
  align-items: baseline;
  gap: 16px;
  padding: 12px 4px;
  border-bottom: 1px solid #e8dcc8;
  cursor: pointer;
}
.dynasty-row:last-child { border-bottom: none; }
.dynasty-row:hover .dynasty-name { color: var(--nav-active); }

.dynasty-date {
  flex-shrink: 0;
  width: 140px;
  font-family: 'Roboto', sans-serif;
  font-size: 13px;
  font-weight: 500;
  color: var(--heading);
}

.dynasty-body { flex: 1; min-width: 0; }
.dynasty-name {
  font-size: 15px;
  font-weight: 500;
  color: var(--heading);
  font-family: 'Roboto', sans-serif;
}
.dynasty-aka {
  font-size: 12px;
  font-style: italic;
  color: var(--muted);
  font-family: 'Roboto', sans-serif;
  margin-top: 2px;
}
.dynasty-area {
  font-size: 12px;
  color: var(--muted);
  font-family: 'Roboto', sans-serif;
  margin-top: 2px;
}

.dynasty-count {
  flex-shrink: 0;
  font-size: 12px;
  color: var(--muted);
  font-family: 'Roboto', sans-serif;
  white-space: nowrap;
}
</style>
