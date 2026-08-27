<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { timelineCountries, eventYearBuckets } from '../composables/useTimeline.js'

// Timeline entry form. The chronology is the global, project-independent
// country timeline — `mwnf3.hcr` merged with Sharing History exhibition 2, the
// way legacy's `/v2/events` serves it — which every gallery package ships in
// full. That is why this page works even though the gallery's own
// `has_country_timeline` flag is false, exactly as on the live site.
const router = useRouter()

const ISLAMIC_ART = 'https://islamicart.museumwnf.org'
const BAROQUE_ART = 'https://baroqueart.museumwnf.org'
const SHARING_HISTORY = 'https://sharinghistory.museumwnf.org'
const CONTACT = 'office@museumwnf.net'

const country = ref('')
const start = ref('')
const end = ref('')

function goToResults() {
  router.push({
    name: 'timeline-results',
    query: { c: country.value || 'all', start: start.value, end: end.value },
  })
}
</script>

<template>
  <div id="timeline-page">
    <div id="timeline-form">
      <select class="legacy-select" v-model="country">
        <option value="" disabled>Select a Country</option>
        <option v-for="c in timelineCountries" :key="c[0]" :value="c[0]">{{ c[1] }}</option>
      </select>

      <div id="timeline-dates-container">
        <select class="legacy-select" v-model="start">
          <option value="" disabled>Start Date</option>
          <option v-for="d in eventYearBuckets" :key="`s${d[0]}`" :value="d[0]">{{ d[1] }}</option>
        </select>
        <select class="legacy-select" v-model="end">
          <option value="" disabled>End Date</option>
          <option v-for="d in eventYearBuckets" :key="`e${d[0]}`" :value="d[0]">{{ d[1] }}</option>
        </select>
      </div>

      <div id="timeline-go">
        <button class="legacy-button" @click="goToResults()">Go</button>
      </div>
    </div>

    <!-- Legacy hardcoded this copy in English; it is not an i18n catalogue key. -->
    <div id="timeline-description" class="prose" dir="ltr">
      <p>Here you will find information about historical events related to the items on display.</p>
      <p>
        The Timeline is a work-in-progress and was initially set up for three projects only:
        <a :href="`${ISLAMIC_ART}/`" target="_blank" rel="noopener"><em>Discover Islamic Art</em></a>,
        <a :href="`${BAROQUE_ART}/`" target="_blank" rel="noopener"><em>Discover Baroque Art</em></a>, and
        <a :href="`${SHARING_HISTORY}/`" target="_blank" rel="noopener"><em>Sharing History</em></a>.
        The purpose was to contextualise the items on display, with the projects’ specific thematic
        focus. These texts were compiled by experts from each country concerned.
      </p>
      <p>
        Currently, this page allows exploration of the events added to the Timeline for the
        above-mentioned projects, covering the relevant time ranges and participating countries.
      </p>
      <p>
        On database pages, a ‘Timeline for this item’ link will display available events within the
        period of the item’s creation.
      </p>
      <p>
        We are doing our best to further develop our Timeline. If you are willing to contribute,
        please contact us at <a :href="`mailto:${CONTACT}`">{{ CONTACT }}</a>.
      </p>
    </div>
  </div>
</template>

<style scoped>
#timeline-page { display: flex; background: #fff; width: 100%; min-height: 400px; }
#timeline-form { display: flex; flex-direction: column; width: 40%; padding: 50px; max-width: 350px; }
#timeline-dates-container { display: flex; gap: 10px; }
#timeline-go { margin-top: 16px; }
#timeline-description { width: 60%; padding: 50px 75px 50px 0; line-height: 1.55; }
#timeline-description a { color: var(--link-blue); }

@media only screen and (max-width: 849px) {
  #timeline-page { flex-direction: column; }
  #timeline-form, #timeline-description { width: 100%; max-width: none; padding: 30px; }
}
</style>
