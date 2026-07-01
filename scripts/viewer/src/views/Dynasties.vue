<script setup>
import { computed } from 'vue'
import { useInventoryData } from '../composables/useInventoryData.js'

const { dynasties, items, dynastyLabel, enDynastyTranslations } = useInventoryData()

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
      <p class="intro-text">
        Browse the dynasties and ruling periods of the Islamic world. Select a
        dynasty below to read its history and see the objects and monuments
        linked to it.
      </p>

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
.intro-text {
  font-size: 13px;
  line-height: 1.65;
  color: var(--muted);
  margin-bottom: 16px;
  font-family: 'Roboto', sans-serif;
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
