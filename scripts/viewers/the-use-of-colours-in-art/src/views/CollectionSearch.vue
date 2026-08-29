<script setup>
import { computed, ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { items, localiseLinks } from '../composables/useExhibitionData.js'
import {
  countryOptions, facetOptions, yearBuckets, FACET_CATEGORIES, FACET_LABELS,
} from '../composables/useCollection.js'
import { tHtml, dirFor } from '../composables/useUiStrings.js'

// The collection entry form. Every dropdown is built from the *whole* member
// universe here — narrowing only starts once a selection exists, which is what
// takes you to /collection-results.
const router = useRouter()

const collectionText = computed(() => localiseLinks(tHtml('txtCollection')))
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

    <!-- Unlike the gallery client, which hardcoded this copy in English, the
         exhibitions client renders `txtCollection` from the catalogue — and it
         is one of the nine keys this exhibition owns, so the text is the
         curator's rather than the platform's. Its links are absolute legacy
         URLs; they are rewritten to in-app routes on the way out so the page
         does not send a visitor off this build. -->
    <div id="description" class="prose" :dir="dirFor('txtCollection')" v-html="collectionText"></div>
    <p id="how-to-search-link">
      <RouterLink to="/how-to-search">How to search</RouterLink>
    </p>
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
#description { width: 60%; padding: 50px 75px 20px 0; margin-top: 45px; }
.italic { font-style: italic; }
#description a { color: var(--link-blue); }
#how-to-search-link { width: 60%; padding: 0 75px 40px 0; }
#how-to-search-link a { color: var(--link-blue); }

@media only screen and (max-width: 849px) {
  #collection-search-container { flex-direction: column; }
  #dropdowns, #description { width: 100%; padding: 30px; margin-top: 0; }
}
</style>
