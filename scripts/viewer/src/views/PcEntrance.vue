<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useInventoryData } from '../composables/useInventoryData.js'

const router = useRouter()
const {
  items, countries, partners, dynasties,
  countryLabel, dynastyLabel, partnerLabel,
  enCountryTranslations, enDynastyTranslations, enPartnerTranslations,
} = useInventoryData()

const filterType = ref('country') // country | dynasty | partner | begin | end

// Build option lists from items actually present
const availableCountries = computed(() => {
  const ids = new Set(items.value.map(i => i.country_id).filter(Boolean))
  return countries.value
    .filter(c => ids.has(c.id))
    .map(c => ({ id: c.id, name: countryLabel(c.id) }))
    .sort((a, b) => a.name.localeCompare(b.name))
})

const availableDynasties = computed(() => {
  const ids = new Set(items.value.flatMap(i => i.dynasty_ids))
  return dynasties.value
    .filter(d => ids.has(d.id))
    .map(d => ({
      id: d.id,
      name: dynastyLabel(d.id),
      from_ad: d.from_ad,
    }))
    .sort((a, b) => (a.from_ad ?? 9999) - (b.from_ad ?? 9999))
})

const availablePartners = computed(() => {
  const ids = new Set(items.value.map(i => i.partner_id).filter(Boolean))
  return partners.value
    .filter(p => ids.has(p.id))
    .map(p => ({ id: p.id, name: partnerLabel(p.id) }))
    .sort((a, b) => a.name.localeCompare(b.name))
})

const selectedCountry = ref('')
const selectedDynasty = ref('')
const selectedPartner = ref('')
const beginDate = ref('')
const endDate = ref('')

function search() {
  const q = {}
  if (filterType.value === 'country' && selectedCountry.value)   q.country = selectedCountry.value
  if (filterType.value === 'dynasty' && selectedDynasty.value)   q.dynasty = selectedDynasty.value
  if (filterType.value === 'partner' && selectedPartner.value)   q.partner = selectedPartner.value
  if (filterType.value === 'begin'   && beginDate.value)         q.begin   = beginDate.value
  if (filterType.value === 'end'     && endDate.value)           q.end     = endDate.value
  router.push({ path: '/permanent-collection/results', query: q })
}
</script>

<template>
  <div>
    <h1 class="section-heading">Permanent Collection</h1>

    <div class="content-box">
      <p class="intro-text">
        Select a filter to browse the Permanent Collection. Choose a category below,
        then select a value and click <strong>Browse</strong>.
      </p>

      <table class="form-table filter-table">
        <tbody>
          <!-- Filter type selector -->
          <tr v-for="opt in [
            { value: 'country', label: 'Country' },
            { value: 'dynasty', label: 'Period / Dynasty' },
            { value: 'partner', label: 'Holding Institution' },
            { value: 'begin',   label: 'Start Date (from year)' },
            { value: 'end',     label: 'End Date (up to year)' },
          ]" :key="opt.value">
            <th>
              <label :for="'filter-' + opt.value">
                <input
                  type="radio"
                  :id="'filter-' + opt.value"
                  name="filterType"
                  :value="opt.value"
                  v-model="filterType"
                />
                {{ opt.label }}
              </label>
            </th>
            <td>
              <!-- Country -->
              <template v-if="opt.value === 'country'">
                <select v-model="selectedCountry" :disabled="filterType !== 'country'" style="width:280px">
                  <option value="">— select a country —</option>
                  <option v-for="c in availableCountries" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
              </template>

              <!-- Dynasty -->
              <template v-else-if="opt.value === 'dynasty'">
                <select v-model="selectedDynasty" :disabled="filterType !== 'dynasty'" style="width:280px">
                  <option value="">— select a period / dynasty —</option>
                  <option v-for="d in availableDynasties" :key="d.id" :value="d.id">{{ d.name }}</option>
                </select>
              </template>

              <!-- Partner -->
              <template v-else-if="opt.value === 'partner'">
                <select v-model="selectedPartner" :disabled="filterType !== 'partner'" style="width:280px">
                  <option value="">— select an institution —</option>
                  <option v-for="p in availablePartners" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
              </template>

              <!-- Begin date -->
              <template v-else-if="opt.value === 'begin'">
                <input
                  type="number"
                  v-model="beginDate"
                  :disabled="filterType !== 'begin'"
                  placeholder="e.g. 800"
                  style="width:120px"
                />
              </template>

              <!-- End date -->
              <template v-else-if="opt.value === 'end'">
                <input
                  type="number"
                  v-model="endDate"
                  :disabled="filterType !== 'end'"
                  placeholder="e.g. 1200"
                  style="width:120px"
                />
              </template>
            </td>
          </tr>

          <!-- Submit -->
          <tr>
            <th></th>
            <td style="padding-top:12px">
              <button class="btn" @click="search">Browse</button>
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
  font-family: Arial, sans-serif;
}

.filter-table th {
  text-align: left;
  font-weight: normal;
  padding: 6px 16px 6px 0;
  font-family: Arial, sans-serif;
  font-size: 13px;
  color: var(--text);
  vertical-align: middle;
  width: auto;
}
.filter-table th label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  font-weight: normal;
}
.filter-table input[type="radio"] { cursor: pointer; }

select:disabled, input:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
</style>
