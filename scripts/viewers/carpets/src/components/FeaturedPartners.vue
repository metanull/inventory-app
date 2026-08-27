<script setup>
import { computed, onMounted, onBeforeUnmount, ref } from 'vue'
import { RouterLink } from 'vue-router'
import {
  partners, partnerRoute, partnerObjectsRoute, partnerLabel, countryLabel,
  tr, defaultLang, mdStrip,
} from '../composables/useGalleryData.js'

// Legacy's `/partners/featured` returned a *random* subset of the partners
// flagged `showOnPortal`, sized by the API's page size, and rotated them in a
// carousel. A static package cannot reproduce a server-side random draw, so the
// exporter ships the flag (`featured`, from `partner_translations.extra
// .portal_display`) and the picking happens here — which is the only way a
// static site can behave the same way.
const CAROUSEL_SIZE = 8
const INTERVAL_MS = 8000

const featured = computed(() => {
  const pool = partners.value.filter(p => p.featured)
  for (let i = pool.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1))
    ;[pool[i], pool[j]] = [pool[j], pool[i]]
  }
  return pool.slice(0, CAROUSEL_SIZE).map(p => {
    const t = tr('partners', p.id, defaultLang)
    return {
      partner: p,
      name: partnerLabel(p.id),
      city: t.city ?? '',
      country: countryLabel(p.country_id),
      // Legacy truncated the carousel blurb rather than showing a whole profile.
      description: truncate(mdStrip(t.description ?? ''), 420),
      image: p.images?.[0]?.url ?? null,
    }
  })
})

function truncate(text, chars) {
  if (!text || text.length <= chars) return text
  const cut = text.lastIndexOf(' ', chars)
  return `${text.slice(0, cut > 0 ? cut : chars)}...`
}

const current = ref(0)
let timer = null

function show(index) {
  current.value = index
  restart()
}
function restart() {
  if (timer) clearInterval(timer)
  timer = setInterval(() => {
    if (featured.value.length) current.value = (current.value + 1) % featured.value.length
  }, INTERVAL_MS)
}
onMounted(restart)
onBeforeUnmount(() => timer && clearInterval(timer))
</script>

<template>
  <section id="featured-partner-container" v-if="featured.length">
    <div id="carousel-header">Featured Partners</div>
    <div id="carousel">
      <div
        v-for="(entry, index) in featured"
        :key="entry.partner.id"
        class="featured-partner"
        v-show="index === current"
      >
        <div class="image-container" v-if="entry.image">
          <img class="featured-image" :src="entry.image" :alt="entry.name" />
        </div>
        <div class="featured-information">
          <p class="featured-name">{{ entry.name }}</p>
          <p class="featured-location">
            <span v-if="entry.city">{{ entry.city }}, </span>{{ entry.country }}
          </p>
          <p class="featured-description">{{ entry.description }}</p>
          <p class="featured-read-more">
            <RouterLink :to="partnerRoute(entry.partner)">Read more &gt;&gt;</RouterLink>
          </p>
          <p class="featured-view-objects" v-if="entry.partner.item_count">
            <RouterLink :to="partnerObjectsRoute(entry.partner)">View objects &gt;&gt;</RouterLink>
          </p>
        </div>
      </div>
    </div>
    <div id="carousel-controls">
      <span
        v-for="(entry, index) in featured"
        :key="entry.partner.id"
        class="slideshow-bullet"
        :class="{ highlight: index === current }"
        @click="show(index)"
      ></span>
    </div>
  </section>
</template>

<style scoped>
#featured-partner-container { width: 100%; background: #fff; padding: 30px 40px 20px; }
#carousel-header {
  font-size: 24px;
  font-weight: 700;
  color: var(--theme-dark);
  margin-bottom: 16px;
}
#carousel { min-height: 260px; }
.featured-partner { display: flex; gap: 24px; }
.image-container { flex: 0 0 320px; max-width: 40%; }
.featured-image { width: 100%; height: 240px; object-fit: cover; display: block; }
.featured-information { flex: 1; min-width: 0; }
.featured-name { font-size: 19px; font-weight: 700; }
.featured-location { color: #555; margin-bottom: 10px; }
.featured-description { line-height: 1.45; margin-bottom: 10px; }
.featured-read-more a, .featured-view-objects a { color: var(--link-blue); text-decoration: none; }
.featured-read-more a:hover, .featured-view-objects a:hover { text-decoration: underline; }
#carousel-controls { display: flex; justify-content: center; gap: 8px; padding-top: 14px; }
.slideshow-bullet {
  width: 11px; height: 11px; border-radius: 50%;
  background: var(--theme-light); cursor: pointer;
}
.slideshow-bullet.highlight { background: var(--theme-dark); }

@media only screen and (max-width: 849px) {
  .featured-partner { flex-direction: column; }
  .image-container { max-width: 100%; flex: none; }
}
</style>
