<script setup>
import { ref, computed } from 'vue'
import { useInventoryData } from '../composables/useInventoryData.js'

const {
  hbGeneralPerspectives,
  hbGeneralTopics,
  historicalBackgroundProfiles,
  enCollectionTranslations,
  countryLabel,
  md, mdInline,
} = useInventoryData()

// Perspective pages (legacy historical_background_pages.php?page=N),
// rendered as a tabbed section like the legacy landing's three links.
const activePerspectiveIndex = ref(0)

const perspectives = computed(() =>
  hbGeneralPerspectives.value.map(p => ({
    ...p,
    title: enCollectionTranslations.value[p.id]?.title ?? p.internal_name,
    description: enCollectionTranslations.value[p.id]?.description ?? '',
  }))
)

const activePerspective = computed(
  () => perspectives.value[activePerspectiveIndex.value] ?? null
)

// "Read more" topics (legacy historical_background_readmore.php). Legacy
// never filled their texts in — the popup shows the bare title — so this is
// a plain list, exactly as much content as legacy had.
const topics = computed(() =>
  hbGeneralTopics.value.map(t => ({
    ...t,
    title: enCollectionTranslations.value[t.id]?.title ?? t.internal_name,
  }))
)

// Country Insight (legacy historical_background_insight.php): direct access
// to each country's Historical Profile and Political Context timeline.
const insightCountries = computed(() =>
  [...historicalBackgroundProfiles.value]
    .map(r => ({ record: r, name: countryLabel(r.country_id) }))
    .sort((a, b) => a.name.localeCompare(b.name))
)
</script>

<template>
  <div class="hb-wrap">
    <div class="content-box">
      <h1 class="section-heading">Historical Background</h1>
      <p class="hb-intro-note">
        The <em>Sharing History</em> Virtual Museum and Virtual Exhibitions
        address Arab–Ottoman–European relations in the 19th century from the
        specific perspectives of all parties.
      </p>

      <div v-if="perspectives.length" class="perspective-tabs">
        <button
          v-for="(p, idx) in perspectives"
          :key="p.id"
          class="perspective-tab"
          :class="{ active: idx === activePerspectiveIndex }"
          @click="activePerspectiveIndex = idx"
        >{{ p.title }}</button>
      </div>

      <div v-if="activePerspective">
        <h2 class="perspective-title" v-html="mdInline(activePerspective.title)" />
        <div class="prose" v-html="md(activePerspective.description)" />
      </div>
    </div>

    <div v-if="topics.length" class="content-box">
      <h2 class="section-heading">Read more</h2>
      <ul class="topic-list">
        <li v-for="t in topics" :key="t.id" class="topic-row">
          <span class="topic-name" v-html="mdInline(t.title)" />
        </li>
      </ul>
    </div>

    <div class="content-box">
      <h2 class="section-heading">Country Insight</h2>
      <p class="hb-intro-note">
        This page provides direct access to information on the historical
        background for each country. The Historical Profiles and Timeline
        reflect the specific views of the partner concerned.
      </p>
      <table class="insight-table">
        <tbody>
          <tr v-for="c in insightCountries" :key="c.record.id">
            <th>{{ c.name }}</th>
            <td>
              <RouterLink :to="`/historical-profiles/${encodeURIComponent(c.record.id)}`">
                Historical Profile
              </RouterLink>
            </td>
            <td>
              <RouterLink
                :to="{ path: '/timeline/results', query: { country: c.record.country_id, exhibition: 'pc' } }"
              >
                Political Context Timeline
              </RouterLink>
            </td>
          </tr>
        </tbody>
      </table>
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

.perspective-tabs {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 16px;
  border-bottom: 1px solid var(--border);
  padding-bottom: 10px;
}
.perspective-tab {
  font-family: 'Roboto', sans-serif;
  font-size: 13px;
  font-weight: 500;
  padding: 6px 14px;
  background: none;
  border: 1px solid var(--border);
  color: var(--heading);
  cursor: pointer;
}
.perspective-tab:hover { color: var(--nav-active); border-color: var(--accent); }
.perspective-tab.active {
  background: var(--accent);
  border-color: var(--accent);
  color: #fff;
}

.perspective-title {
  font-size: 17px;
  font-weight: 500;
  color: var(--heading);
  margin-bottom: 10px;
  font-family: 'Roboto', sans-serif;
}

.prose { font-size: 14px; line-height: 1.7; color: var(--text); font-family: 'Roboto', sans-serif; }
.prose :deep(p) { margin: 0 0 .75em; }

.topic-list { list-style: none; }
.topic-row {
  padding: 8px 4px;
  border-bottom: 1px solid var(--border-light);
}
.topic-row:last-child { border-bottom: none; }
.topic-name {
  font-family: 'Roboto', sans-serif;
  font-size: 14px;
  color: var(--heading);
}

.insight-table { border-collapse: collapse; width: 100%; font-family: 'Roboto', sans-serif; font-size: 13px; }
.insight-table th {
  text-align: left;
  font-weight: 500;
  color: var(--heading);
  padding: 7px 16px 7px 4px;
  border-bottom: 1px solid var(--border-light);
  white-space: nowrap;
}
.insight-table td {
  padding: 7px 16px 7px 0;
  border-bottom: 1px solid var(--border-light);
}
.insight-table a { color: var(--nav-active); }
</style>
