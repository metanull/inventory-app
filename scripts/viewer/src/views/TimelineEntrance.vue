<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useInventoryData } from '../composables/useInventoryData.js'

const router = useRouter()
const { timelines, timelineEvents, countryLabel } = useInventoryData()

// Countries available in the timeline data (one Timeline per ISL country)
const availableCountries = computed(() =>
  timelines.value
    .filter(t => t.country_id)
    .map(t => ({ id: t.country_id, name: countryLabel(t.country_id) }))
    .sort((a, b) => a.name.localeCompare(b.name))
)

// Century marks spanning the actual event data, mirroring the legacy
// hcr_home.php generation (100-year increments from min to max year_from/year_to).
const centuryMarks = computed(() => {
  const years = timelineEvents.value.flatMap(e => {
    const arr = [e.year_from]
    if (e.year_to && e.year_to !== 0) arr.push(e.year_to)
    return arr
  }).filter(y => y != null)
  if (!years.length) return []
  const min = Math.floor(Math.min(...years) / 100) * 100
  const max = Math.ceil(Math.max(...years) / 100) * 100
  const marks = []
  for (let y = min; y <= max; y += 100) marks.push(y)
  return marks
})

const selectedCountry = ref('')
const selectedBegin = ref('')
const selectedEnd = ref('')
const errorMessage = ref('')

function search() {
  errorMessage.value = ''

  if (!selectedCountry.value && !(selectedBegin.value && selectedEnd.value)) {
    errorMessage.value = 'Please select a country, or a start and end date.'
    return
  }
  if (selectedBegin.value && selectedEnd.value && Number(selectedBegin.value) >= Number(selectedEnd.value)) {
    errorMessage.value = 'Please select a valid time period (start must be before end).'
    return
  }

  const q = {}
  if (selectedCountry.value) q.country = selectedCountry.value
  if (selectedBegin.value) q.begin = selectedBegin.value
  if (selectedEnd.value) q.end = selectedEnd.value
  router.push({ path: '/timeline/results', query: q })
}
</script>

<template>
  <div>
    <h1 class="section-heading">Timeline</h1>

    <div class="content-box">
      <p class="intro-text">
        Explore historical events from the Islamic world. Select a country and/or a time
        period below, then click <strong>Go</strong>.
      </p>

      <table class="form-table filter-table">
        <tbody>
          <tr>
            <th><label for="tl-country">Country</label></th>
            <td>
              <select id="tl-country" v-model="selectedCountry" style="width:280px">
                <option value="">— All Countries —</option>
                <option v-for="c in availableCountries" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </td>
          </tr>
          <tr>
            <th><label for="tl-begin">Start Date</label></th>
            <td>
              <select id="tl-begin" v-model="selectedBegin" style="width:160px">
                <option value="">— none —</option>
                <option v-for="y in centuryMarks" :key="y" :value="y">{{ y }} AD</option>
              </select>
            </td>
          </tr>
          <tr>
            <th><label for="tl-end">End Date</label></th>
            <td>
              <select id="tl-end" v-model="selectedEnd" style="width:160px">
                <option value="">— none —</option>
                <option v-for="y in centuryMarks" :key="y" :value="y">{{ y }} AD</option>
              </select>
            </td>
          </tr>
          <tr>
            <th></th>
            <td style="padding-top:12px">
              <button class="btn" @click="search">Go</button>
              <span v-if="errorMessage" class="error-message">{{ errorMessage }}</span>
            </td>
          </tr>
        </tbody>
      </table>
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

.filter-table th {
  text-align: left;
  font-weight: normal;
  padding: 6px 16px 6px 0;
  font-family: 'Roboto', sans-serif;
  font-size: 13px;
  color: var(--text);
  vertical-align: middle;
  width: auto;
}

.error-message {
  margin-left: 12px;
  font-size: 12px;
  color: var(--nav-active);
  font-family: 'Roboto', sans-serif;
}
</style>
