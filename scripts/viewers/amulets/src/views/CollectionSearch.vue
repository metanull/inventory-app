<script setup>
import { computed, ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { items, gallery } from '../composables/useGalleryData.js'
import {
  countryOptions, facetOptions, yearBuckets, FACET_CATEGORIES, FACET_LABELS,
} from '../composables/useCollection.js'
import { uiLang } from '../composables/useUiStrings.js'

// The collection entry form. Every dropdown is built from the *whole* member
// universe here — narrowing only starts once a selection exists, which is what
// takes you to /collection-results.
const router = useRouter()
const PORTAL = 'https://www.museumwnf.org'

const galleryName = computed(() => gallery.names?.[uiLang.value] ?? gallery.names?.en ?? '')
const all = computed(() => items.value)
const countries = computed(() => countryOptions(all.value))
const facets = computed(() => facetOptions(all.value))
const years = computed(() => yearBuckets(all.value))

const selection = ref({ country: '', type: '', dynasty: '', subject: '', material: '', artist: '', start: '', end: '' })

function goToResults(key, value) {
  router.push({ name: 'collection-results', query: { [key]: value } })
}

const visibleFacets = computed(() =>
  FACET_CATEGORIES.filter(c => (facets.value[c] ?? []).length > 0)
)
</script>

<template>
  <div id="collection-search-container">
    <div id="dropdowns">
      <div id="dropdown-label">Filter by:</div>
      <div id="select-container">
        <select class="legacy-select" v-model="selection.country" @change="goToResults('country', selection.country)">
          <option value="" disabled>Select Country</option>
          <option v-for="c in countries" :key="c[0]" :value="c[0]">{{ c[1] }}</option>
        </select>

        <select
          v-for="category in visibleFacets"
          :key="category"
          class="legacy-select"
          v-model="selection[category]"
          @change="goToResults(category, selection[category])"
        >
          <option value="" disabled>{{ FACET_LABELS[category] }}</option>
          <option v-for="tag in facets[category]" :key="tag[0]" :value="tag[0]">{{ tag[1] }}</option>
        </select>

        <div id="dates-container">
          <select class="legacy-select" v-model="selection.start" @change="goToResults('start', selection.start)">
            <option value="" disabled>Start Date</option>
            <option v-for="d in years" :key="`s${d[0]}`" :value="d[0]">{{ d[1] }}</option>
          </select>
          <select class="legacy-select" v-model="selection.end" @change="goToResults('end', selection.end)">
            <option value="" disabled>End Date</option>
            <option v-for="d in years" :key="`e${d[0]}`" :value="d[0]">{{ d[1] }}</option>
          </select>
        </div>
      </div>
    </div>

    <!-- The legacy client hardcoded this copy in English; it is not one of the
         i18n catalogue keys, so it is reproduced verbatim rather than looked
         up. -->
    <div id="description" class="prose" dir="ltr">
      <p>
        As a first approach, this might be the best choice to start exploring the
        <span class="italic">{{ galleryName }}</span> database. A range of filters allows you to
        choose what you want to see.
      </p>
      <p>
        For research, you can use the <strong>Search*</strong> bar, located on the top right of the
        page. If you are interested in learning about contributions from a specific institution, the
        <RouterLink to="/partners"><strong>Partner</strong></RouterLink> section would be the best
        place to start. On the <RouterLink to="/timeline"><strong>Timeline</strong></RouterLink> you
        will find countries’ historical context information, while
        <a :href="`${PORTAL}/mycollection/index.php`" target="_blank" rel="noopener"><strong>My Collection</strong></a>
        lets you add any item from this project to your personal collection.
      </p>
      <p>
        * Please be aware that the Search tool covers only the artefacts in this Gallery. If you wish
        to carry out a search in the overall MWNF Database, please click
        <a :href="`${PORTAL}/database_searchform.php`" target="_blank" rel="noopener">here</a>
        or go to the above-mentioned projects to search their databases.
        <RouterLink to="/how-to-search">How to search</RouterLink>.
      </p>
    </div>
  </div>
</template>

<style scoped>
#collection-search-container {
  display: flex;
  background: #fff;
  width: 100%;
}
#dropdowns { display: flex; flex-direction: column; width: 40%; padding: 50px; }
#dropdown-label { max-width: 300px; padding-bottom: 6px; font-size: 125%; font-weight: 700; }
#select-container { width: 100%; max-width: 300px; }
#dates-container { display: flex; gap: 10px; max-width: 300px; }
#description { width: 60%; padding: 50px 75px 50px 0; margin-top: 45px; }
.italic { font-style: italic; }
#description a { color: var(--link-blue); }

@media only screen and (max-width: 849px) {
  #collection-search-container { flex-direction: column; }
  #dropdowns, #description { width: 100%; padding: 30px; margin-top: 0; }
}
</style>
