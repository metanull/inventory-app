<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useInventoryData } from '../composables/useInventoryData.js'

const router = useRouter()
const { availableLangs } = useInventoryData()

// Matches legacy database.php's field options, in order.
const FIELD_OPTIONS = [
  { value: 'keyword',    label: 'Keyword(s)' },
  { value: 'name',       label: 'Name' },
  { value: 'location',   label: 'Location' },
  { value: 'provenance', label: 'Provenance' },
  { value: 'patron',     label: 'Patron / Initial Owner' },
  { value: 'artist',     label: 'Architect / Artist / Master' },
  { value: 'material',   label: 'Material / Technique' },
  { value: 'other',      label: 'Other' },
]

// Legacy's fixed century-boundary date dropdowns (database.php:88-124).
// "From" runs 501-2001 (16 values), "to" runs 600-2000 (15 values) — not symmetric.
const DATE_FROM_OPTIONS = Array.from({ length: 16 }, (_, i) => 501 + i * 100)
const DATE_TO_OPTIONS   = Array.from({ length: 15 }, (_, i) => 600 + i * 100)

const keyword1 = ref('')
const field1   = ref('keyword')
const keyword2 = ref('')
const field2   = ref('keyword')
const keyword3 = ref('')
const field3   = ref('keyword')
const cond2    = ref('AND')
const cond3    = ref('AND')
const dateFrom = ref('')
const dateTo   = ref('')
const searchLanguage = ref('')

function search() {
  const q = {}
  if (keyword1.value) { q.keyword1 = keyword1.value; q.field1 = field1.value }
  if (keyword2.value) { q.keyword2 = keyword2.value; q.field2 = field2.value; q.cond2 = cond2.value }
  if (keyword3.value) { q.keyword3 = keyword3.value; q.field3 = field3.value; q.cond3 = cond3.value }
  // Date range and search language are applied once, globally, to the whole
  // query — unlike field1/field2/field3, which are genuinely per-row.
  if (dateFrom.value) q.date_from = dateFrom.value
  if (dateTo.value)   q.date_to   = dateTo.value
  if (searchLanguage.value) q.lang = searchLanguage.value
  router.push({ path: '/database/results', query: q })
}

function showAll() {
  router.push({ path: '/database/results', query: {} })
}
</script>

<template>
  <div>
    <h1 class="section-heading">Database</h1>

    <div class="content-box">
      <p class="intro-text">
        Search the collection by one or more keywords. Select the field to search in and
        enter a keyword for each row. Leave a row blank to ignore it.
        Optionally restrict results to a date range and/or a search language.
      </p>

      <table class="form-table db-form">
        <tbody>
          <!-- Row 1 -->
          <tr>
            <th>Keyword 1</th>
            <td>
              <select v-model="field1" style="width:200px">
                <option v-for="f in FIELD_OPTIONS" :key="f.value" :value="f.value">{{ f.label }}</option>
              </select>
              <input type="text" v-model="keyword1" placeholder="keyword…" style="width:220px; margin-left:8px" />
            </td>
          </tr>

          <!-- Row 2 -->
          <tr>
            <th>Keyword 2</th>
            <td>
              <select v-model="cond2" style="width:60px">
                <option value="AND">AND</option>
                <option value="OR">OR</option>
              </select>
              <select v-model="field2" style="width:200px; margin-left:8px">
                <option v-for="f in FIELD_OPTIONS" :key="f.value" :value="f.value">{{ f.label }}</option>
              </select>
              <input type="text" v-model="keyword2" placeholder="keyword…" style="width:220px; margin-left:8px" />
            </td>
          </tr>

          <!-- Row 3 -->
          <tr>
            <th>Keyword 3</th>
            <td>
              <select v-model="cond3" style="width:60px">
                <option value="AND">AND</option>
                <option value="OR">OR</option>
              </select>
              <select v-model="field3" style="width:200px; margin-left:8px">
                <option v-for="f in FIELD_OPTIONS" :key="f.value" :value="f.value">{{ f.label }}</option>
              </select>
              <input type="text" v-model="keyword3" placeholder="keyword…" style="width:220px; margin-left:8px" />
            </td>
          </tr>

          <tr><td colspan="2"><hr class="form-divider" /></td></tr>

          <!-- Date range -->
          <tr>
            <th>Date (from year)</th>
            <td>
              <select v-model="dateFrom" style="width:120px">
                <option value="">—</option>
                <option v-for="y in DATE_FROM_OPTIONS" :key="y" :value="y">{{ y }}</option>
              </select>
            </td>
          </tr>
          <tr>
            <th>Date (to year)</th>
            <td>
              <select v-model="dateTo" style="width:120px">
                <option value="">—</option>
                <option v-for="y in DATE_TO_OPTIONS" :key="y" :value="y">{{ y }}</option>
              </select>
            </td>
          </tr>

          <!-- Search language -->
          <tr>
            <th>Search language</th>
            <td>
              <select v-model="searchLanguage" style="width:120px">
                <option value="">Any</option>
                <option v-for="lang in availableLangs" :key="lang" :value="lang">{{ lang.toUpperCase() }}</option>
              </select>
            </td>
          </tr>

          <!-- Actions -->
          <tr>
            <th></th>
            <td style="padding-top:14px">
              <button class="btn" @click="search">Search</button>
              <button class="btn btn-secondary" style="margin-left:10px" @click="showAll">Show All</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style scoped>
.intro-text {
  font-size: 14px;
  line-height: 1.65;
  color: var(--text);
  margin-bottom: 16px;
  font-family: 'Roboto', sans-serif;
}
.db-form th {
  text-align: right;
  padding-right: 14px;
  font-size: 13px;
  font-family: 'Roboto', sans-serif;
  color: var(--muted);
  font-weight: 500;
  white-space: nowrap;
  width: 130px;
}
.form-divider {
  border: none;
  border-top: 1px solid var(--border);
  margin: 8px 0;
}
</style>
