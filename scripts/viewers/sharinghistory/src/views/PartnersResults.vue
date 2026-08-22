<script setup>
import { computed } from 'vue'
import { useInventoryData } from '../composables/useInventoryData.js'

const { partners, countryLabel, partnerLabel, enPartnerTranslations } = useInventoryData()

// SH has a single Partners concept (no museum/institution split — the
// legacy pm_partner_list.php lists ALL sh_partners grouped by country).
// Like legacy's INNER JOINs on sh_partner_names + mwnf3.countrynames, only
// partners with a name translation AND a country are listed — this
// reproduces the live site's 114-partner list (27 main + 87 associated) out
// of the 120 in the package (the rest are placeholder rows: "Not know yet",
// "Public Domain", or nameless).
const groupedByCountry = computed(() => {
  const named = partners.value.filter(p => enPartnerTranslations.value[p.id]?.name && p.country_id)

  const countries = new Map()
  for (const p of named) {
    const key = p.country_id ?? ''
    if (!countries.has(key)) countries.set(key, { main: [], associated: [] })
    const bucket = countries.get(key)
    if (p.level === 'associated_partner' || p.level === 'minor_contributor') {
      bucket.associated.push(p)
    } else {
      bucket.main.push(p)
    }
  }

  return [...countries.entries()]
    .map(([countryId, group]) => ({
      countryId,
      name: countryId ? countryLabel(countryId) : 'Other',
      main: group.main.sort((a, b) => partnerLabel(a.id).localeCompare(partnerLabel(b.id))),
      associated: group.associated.sort((a, b) => partnerLabel(a.id).localeCompare(partnerLabel(b.id))),
    }))
    .sort((a, b) => a.name.localeCompare(b.name))
})

const totalCount = computed(() =>
  groupedByCountry.value.reduce((sum, g) => sum + g.main.length + g.associated.length, 0)
)

function partnerLink(partner) {
  return { path: `/partner/${encodeURIComponent(partner.id)}` }
}
</script>

<template>
  <div>
    <RouterLink to="/partners" class="back-link">‹ Back to Partners</RouterLink>

    <h1 class="section-heading">
      Partners
      <span class="heading-project"> — Sharing History</span>
    </h1>

    <div class="content-box">
      <p class="result-count">
        {{ totalCount }} partner{{ totalCount !== 1 ? 's' : '' }} found
      </p>

      <div v-if="groupedByCountry.length" class="country-accordion">
        <details v-for="group in groupedByCountry" :key="group.countryId" class="country-group" open>
          <summary class="country-head">
            <h3>{{ group.name }}</h3>
          </summary>

          <div class="country-body">
            <div class="partner-col">
              <p v-for="p in group.main" :key="p.id">
                <RouterLink :to="partnerLink(p)">{{ partnerLabel(p.id) }}</RouterLink>
              </p>
            </div>

            <div v-if="group.associated.length" class="partner-col associated-col">
              <p class="associated-label">Associated Partners</p>
              <p v-for="p in group.associated" :key="p.id">
                <RouterLink :to="partnerLink(p)">{{ partnerLabel(p.id) }}</RouterLink>
              </p>
            </div>
          </div>
        </details>
      </div>

      <p v-else class="no-results">No partners found.</p>
    </div>
  </div>
</template>

<style scoped>
.heading-project { font-weight: normal; font-size: 14px; color: var(--muted); }

.result-count {
  font-family: 'Roboto', sans-serif;
  font-size: 12px;
  color: var(--muted);
  margin-bottom: 12px;
  padding-bottom: 8px;
  border-bottom: 1px solid var(--border);
}

.no-results { color: var(--muted); font-family: 'Roboto', sans-serif; font-size: 13px; padding: 20px 0; }

.country-group {
  border-bottom: 1px solid var(--border-light);
  padding: 10px 0;
}
.country-group:last-child { border-bottom: none; }

.country-head {
  cursor: pointer;
  list-style: none;
}
.country-head::-webkit-details-marker { display: none; }
.country-head h3 {
  display: inline-block;
  font-size: 15px;
  font-weight: 500;
  color: var(--heading);
  font-family: 'Roboto', sans-serif;
}
.country-head h3::before {
  content: '▸ ';
  color: var(--accent);
}
details[open] > .country-head h3::before { content: '▾ '; }

.country-body {
  display: flex;
  gap: 32px;
  padding: 8px 0 4px 16px;
  flex-wrap: wrap;
}
.partner-col { flex: 1; min-width: 220px; }
.partner-col p {
  font-size: 13px;
  font-family: 'Roboto', sans-serif;
  padding: 3px 0;
}
.associated-label {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--muted);
  font-weight: bold;
  margin-bottom: 4px;
}
</style>
