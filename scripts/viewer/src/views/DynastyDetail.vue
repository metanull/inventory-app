<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useInventoryData } from '../composables/useInventoryData.js'

const route = useRoute()
const router = useRouter()
const {
  dynasties, items,
  availableLangs, defaultLang,
  md, mdInline,
} = useInventoryData()

const dynasty = computed(() => dynasties.value.find(d => d.id === decodeURIComponent(route.params.id)) ?? null)

// ── Language selector (dynasty translations are loaded on demand, per-lang) ──

const activeLang = ref(defaultLang)
const dynastyTranslationsCache = ref({})

async function loadDynastyLangTranslations(lang) {
  if (dynastyTranslationsCache.value[lang]) return
  try {
    const m = await import(`@inventory-data/translations/dynasties.${lang}.json`)
    dynastyTranslationsCache.value = { ...dynastyTranslationsCache.value, [lang]: m.default }
  } catch {
    dynastyTranslationsCache.value = { ...dynastyTranslationsCache.value, [lang]: {} }
  }
}

onMounted(() => loadDynastyLangTranslations(activeLang.value))
watch(activeLang, lang => loadDynastyLangTranslations(lang))

const t = computed(() => dynastyTranslationsCache.value[activeLang.value]?.[dynasty.value?.id] ?? {})

// ── Key facts ──────────────────────────────────────────────────────────

const keyFacts = computed(() => {
  if (!dynasty.value) return []
  const facts = []
  if (t.value.also_known_as) facts.push({ label: 'Also known as', value: t.value.also_known_as })
  if (t.value.area) facts.push({ label: 'Area', value: t.value.area })
  if (t.value.date_description_ah) facts.push({ label: 'Dates (AH)', value: t.value.date_description_ah })
  if (t.value.date_description_ad) facts.push({ label: 'Dates (AD)', value: t.value.date_description_ad })
  return facts
})

// ── Related items (objects / monuments linked to this dynasty) ─────────

const relatedItems = computed(() => {
  if (!dynasty.value) return []
  return items.value.filter(i => i.dynasty_ids.includes(dynasty.value.id))
})

function relatedItemsLink() {
  return { path: '/permanent-collection/results', query: { dynasty: dynasty.value.id } }
}

// ── Timeline (historical cross-reference over the dynasty's date range) ─

const timelineLink = computed(() => {
  if (!dynasty.value || (dynasty.value.from_ad == null && dynasty.value.to_ad == null)) return null
  const q = {}
  if (dynasty.value.from_ad != null) q.begin = String(dynasty.value.from_ad)
  if (dynasty.value.to_ad != null) q.end = String(dynasty.value.to_ad)
  return { path: '/timeline/results', query: q }
})

function back() {
  if (window.history.length > 2) {
    router.back()
  } else {
    router.push('/dynasties')
  }
}
</script>

<template>
  <div v-if="!dynasty" class="content-box not-found">
    <p>Dynasty not found.</p>
    <router-link to="/dynasties">← Return to Islamic Dynasties</router-link>
  </div>

  <div v-else class="detail-wrap">
    <a class="back-link" href="#" @click.prevent="back">← Back to Islamic Dynasties</a>

    <div v-if="availableLangs.length > 1" class="lang-selector">
      <label class="lang-label">Dynasty language:</label>
      <select v-model="activeLang" class="lang-select">
        <option v-for="lang in availableLangs" :key="lang" :value="lang">{{ lang.toUpperCase() }}</option>
      </select>
    </div>

    <div class="detail content-box">
      <h1 class="detail-title" v-html="mdInline(t.name ?? dynasty.id)" />

      <!-- View objects / monuments -->
      <div v-if="relatedItems.length || timelineLink" class="view-items-row">
        <RouterLink v-if="relatedItems.length" :to="relatedItemsLink()" class="btn">
          View Objects &amp; Monuments ({{ relatedItems.length }}) →
        </RouterLink>
        <RouterLink v-if="timelineLink" :to="timelineLink" class="homepage-link">
          View on Timeline →
        </RouterLink>
      </div>

      <!-- Key facts table -->
      <table v-if="keyFacts.length" class="key-facts">
        <tbody>
          <tr v-for="fact in keyFacts" :key="fact.label">
            <th>{{ fact.label }}</th>
            <td>{{ fact.value }}</td>
          </tr>
        </tbody>
      </table>

      <!-- History -->
      <section v-if="t.history" class="content-section">
        <h2 class="content-section-heading">History</h2>
        <div v-html="md(t.history)" class="prose" />
      </section>

      <!-- Related items -->
      <div v-if="relatedItems.length" class="related">
        <h2 class="sub-section-title">Objects &amp; Monuments from this Dynasty</h2>
        <ul class="related-list item-list">
          <li
            v-for="rel in relatedItems.slice(0, 12)"
            :key="rel.id"
            class="item-list-row"
            @click="$router.push(`/item/${encodeURIComponent(rel.id)}`)"
          >
            <div class="item-thumb">
              <img v-if="rel.images?.length" :src="rel.images[0].url" :alt="rel.internal_name ?? ''" loading="lazy" />
              <div v-else class="item-thumb-placeholder" />
            </div>
            <div class="item-list-info">
              <div class="item-list-name">{{ rel.internal_name ?? rel.id }}</div>
              <div class="item-list-meta">
                <span class="item-type-badge">{{ rel.type }}</span>
              </div>
            </div>
          </li>
        </ul>
        <RouterLink v-if="relatedItems.length > 12" :to="relatedItemsLink()" class="see-all-link">
          See all {{ relatedItems.length }} objects &amp; monuments →
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<style scoped>
.not-found { color: var(--muted); font-family: 'Roboto', sans-serif; font-size: 13px; }

.detail-wrap { display: flex; flex-direction: column; gap: 10px; }

.lang-selector {
  display: flex;
  align-items: center;
  gap: 8px;
  font-family: 'Roboto', sans-serif;
  font-size: 12px;
  color: var(--muted);
  justify-content: flex-end;
}
.lang-select { font-size: 12px; padding: 3px 6px; }

.detail-title {
  font-size: 24px;
  font-weight: 400;
  color: var(--heading);
  margin-bottom: 16px;
  line-height: 1.3;
  font-family: 'Roboto', sans-serif;
}

.view-items-row {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 20px;
}
.homepage-link {
  font-size: 13px;
  font-weight: 500;
  color: var(--nav-active);
  font-family: 'Roboto', sans-serif;
}

/* Key facts table */
.key-facts {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 20px;
  font-size: 14px;
}
.key-facts th,
.key-facts td {
  padding: 6px 10px;
  border: 1px solid var(--border);
  vertical-align: top;
  text-align: left;
}
.key-facts th {
  background: var(--gold-pale);
  width: 36%;
  font-weight: 500;
  color: var(--heading);
  font-family: 'Roboto', sans-serif;
  font-size: 13px;
  white-space: nowrap;
}
.key-facts td { color: var(--text); font-family: 'Roboto', sans-serif; }

/* Content sections */
.content-section {
  margin-bottom: 20px;
  padding-top: 16px;
  border-top: 1px solid var(--border);
}
.content-section-heading {
  font-size: 13px;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.07em;
  color: var(--heading);
  margin-bottom: 10px;
  font-family: 'Roboto', sans-serif;
}

.prose { font-size: 14px; line-height: 1.7; color: var(--text); font-family: 'Roboto', sans-serif; }
.prose :deep(p) { margin: 0 0 .75em; }
.prose :deep(p:last-child) { margin-bottom: 0; }

/* Related items */
.sub-section-title {
  font-size: 12px;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--muted);
  margin-bottom: 10px;
  font-family: 'Roboto', sans-serif;
}
.related { margin-top: 20px; border-top: 1px solid var(--border); padding-top: 16px; }
.related-list { list-style: none; }

.item-type-badge {
  text-transform: uppercase;
  letter-spacing: 0.06em;
  font-size: 10px;
  color: var(--muted);
  font-family: 'Roboto', sans-serif;
}

.see-all-link {
  display: inline-block;
  margin-top: 10px;
  font-size: 13px;
  font-weight: 500;
  color: var(--nav-active);
  font-family: 'Roboto', sans-serif;
}
</style>
