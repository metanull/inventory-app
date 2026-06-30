<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useInventoryData } from '../composables/useInventoryData.js'

const router = useRouter()
const { items, itemLabel, enItemTranslations } = useInventoryData()

// Pick a random item that has an image, as the featured spotlight
const featured = computed(() => {
  const withImages = items.value.filter(i => i.images?.length > 0)
  if (!withImages.length) return items.value[0] ?? null
  const idx = Math.floor(Math.random() * withImages.length)
  return withImages[idx]
})

function goToItem(item) {
  router.push({ path: `/item/${encodeURIComponent(item.id)}` })
}
</script>

<template>
  <div class="home">
    <!-- Welcome banner -->
    <div class="home-banner content-box">
      <h1 class="home-title">Welcome to Islamic Art</h1>
      <p class="home-intro">
        The Islamic Art digital exhibition presents a wide range of objects and monuments
        from the Islamic world, held in museums and historic sites across numerous countries.
        Explore the collection through the Permanent Collection browser or the full-text
        Database search.
      </p>
    </div>

    <!-- Navigation cards -->
    <div class="home-cards">
      <div class="home-card content-box" @click="$router.push('/permanent-collection')">
        <h2 class="home-card-title">Permanent Collection</h2>
        <p class="home-card-desc">
          Browse the collection by country, period&nbsp;/&nbsp;dynasty, holding institution,
          or date range.
        </p>
        <span class="home-card-link">Browse →</span>
      </div>

      <div class="home-card content-box" @click="$router.push('/database')">
        <h2 class="home-card-title">Database</h2>
        <p class="home-card-desc">
          Search the full inventory by name, location, provenance, material, patron
          and more. Combine up to three keyword fields.
        </p>
        <span class="home-card-link">Search →</span>
      </div>
    </div>

    <!-- Featured item spotlight -->
    <div v-if="featured" class="home-featured content-box">
      <h2 class="section-heading">Item on Display</h2>
      <div class="featured-inner" @click="goToItem(featured)" title="Click to view details">
        <div v-if="featured.images?.length" class="featured-img-wrap">
          <img
            :src="featured.images[0].url"
            :alt="itemLabel(featured)"
            class="featured-img"
            loading="eager"
          />
        </div>
        <div class="featured-info">
          <p class="featured-type">{{ featured.type }}</p>
          <h3 class="featured-name">{{ itemLabel(featured) }}</h3>
          <p v-if="enItemTranslations[featured.id]?.location" class="featured-meta">
            {{ enItemTranslations[featured.id].location }}
          </p>
          <p v-if="enItemTranslations[featured.id]?.dates" class="featured-meta">
            {{ enItemTranslations[featured.id].dates }}
          </p>
          <span class="featured-link">View details →</span>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.home { display: flex; flex-direction: column; gap: 16px; }

.home-banner { border-top: 3px solid var(--gold-dark); }
.home-title {
  font-size: 20px;
  font-weight: 400;
  color: var(--heading);
  margin-bottom: 10px;
  font-family: 'Roboto', sans-serif;
}
.home-intro {
  font-size: 14px;
  line-height: 1.7;
  color: var(--text);
  max-width: 680px;
  font-family: 'Roboto', sans-serif;
}

/* Cards */
.home-cards { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
@media (max-width: 600px) { .home-cards { grid-template-columns: 1fr; } }

.home-card {
  cursor: pointer;
  border-top: 3px solid var(--gold-dark);
  transition: box-shadow 0.15s;
}
.home-card:hover { box-shadow: 0 2px 10px rgba(0,0,0,0.12); }
.home-card-title {
  font-size: 16px;
  font-weight: 500;
  color: var(--heading);
  margin-bottom: 8px;
  font-family: 'Roboto', sans-serif;
}
.home-card-desc { font-size: 13px; line-height: 1.65; color: var(--muted); margin-bottom: 12px; font-family: 'Roboto', sans-serif; }
.home-card-link {
  font-size: 13px;
  font-family: 'Roboto', sans-serif;
  font-weight: 500;
  color: var(--nav-active);
}

/* Featured */
.home-featured { border-top: 3px solid var(--gold-dark); }
.featured-inner {
  display: flex;
  gap: 20px;
  cursor: pointer;
  align-items: flex-start;
}
.featured-inner:hover .featured-name { color: var(--nav-active); }

.featured-img-wrap {
  flex-shrink: 0;
  width: 200px;
  border: 1px solid var(--border);
  overflow: hidden;
}
.featured-img { width: 100%; height: 160px; object-fit: cover; display: block; }

.featured-info { flex: 1; }
.featured-type {
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: var(--muted);
  font-family: 'Roboto', sans-serif;
  margin-bottom: 6px;
}
.featured-name {
  font-size: 18px;
  font-weight: 400;
  color: var(--heading);
  margin-bottom: 8px;
  line-height: 1.3;
  font-family: 'Roboto', sans-serif;
}
.featured-meta {
  font-size: 13px;
  color: var(--muted);
  font-family: 'Roboto', sans-serif;
  margin-bottom: 4px;
}
.featured-link {
  display: inline-block;
  margin-top: 10px;
  font-size: 13px;
  font-weight: 500;
  color: var(--nav-active);
  font-family: 'Roboto', sans-serif;
}
</style>
