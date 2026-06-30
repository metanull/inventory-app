<script setup>
import { ref, computed, watch } from 'vue'
import manifestData from '@inventory-data/manifest.json'
import itemsData from '@inventory-data/items.json'
import dynastiesData from '@inventory-data/dynasties.json'

const items = ref(itemsData)
const dynasties = ref(dynastiesData)
const availableLangs = ref(manifestData.languages ?? [])
const activeLang = ref(
  (manifestData.languages ?? []).includes('en')
    ? 'en'
    : (manifestData.languages?.[0] ?? 'en')
)
// { [langCode]: { [itemId]: { name, description, ... } } }
const translationsCache = ref({})
// { [langCode]: { [dynastyId]: { name, history, ... } } }
const dynastyTranslationsCache = ref({})
const selected = ref(null)

async function loadTranslations(lang) {
  if (translationsCache.value[lang]) return
  try {
    const module = await import(`@inventory-data/translations/items.${lang}.json`)
    translationsCache.value = { ...translationsCache.value, [lang]: module.default }
  } catch {
    // silently fall back to internal_name
  }
}

async function loadDynastyTranslations(lang) {
  if (dynastyTranslationsCache.value[lang]) return
  try {
    const module = await import(`@inventory-data/translations/dynasties.${lang}.json`)
    dynastyTranslationsCache.value = { ...dynastyTranslationsCache.value, [lang]: module.default }
  } catch {
    // no dynasty translations for this language
  }
}

// Load initial language translations
loadTranslations(activeLang.value)
loadDynastyTranslations(activeLang.value)

watch(activeLang, (lang) => {
  loadTranslations(lang)
  loadDynastyTranslations(lang)
})

function t(item) {
  return translationsCache.value[activeLang.value]?.[item.id] ?? {}
}

function label(item) {
  return t(item).name ?? item.internal_name ?? item.id
}

function selectItem(item) {
  selected.value = item
  window.scrollTo(0, 0)
}

function back() {
  selected.value = null
}

const DETAIL_FIELDS = [
  ['type', 'Type'],
  ['alternate_name', 'Alternate name'],
  ['description', 'Description'],
  ['dates', 'Dates'],
  ['location', 'Location'],
  ['holder', 'Holder'],
  ['owner', 'Owner'],
  ['initial_owner', 'Initial owner'],
  ['dimensions', 'Dimensions'],
  ['place_of_production', 'Place of production'],
  ['method_for_datation', 'Method for datation'],
  ['method_for_provenance', 'Method for provenance'],
  ['provenance', 'Provenance'],
  ['obtention', 'Obtention'],
  ['bibliography', 'Bibliography'],
]

const detailFields = computed(() => {
  if (!selected.value) return []
  const fields = t(selected.value)
  return DETAIL_FIELDS
    .map(([key, label]) => ({ label, value: fields[key] }))
    .filter(f => f.value)
})

// Build an id→item lookup once for fast related-item resolution
const itemById = computed(() => {
  const map = {}
  for (const item of items.value) map[item.id] = item
  return map
})

const relatedItems = computed(() => {
  if (!selected.value?.related_item_ids?.length) return []
  return selected.value.related_item_ids
    .map(id => itemById.value[id])
    .filter(Boolean)
})

const dynastyById = computed(() => {
  const map = {}
  for (const d of dynasties.value) map[d.id] = d
  return map
})

function tDynasty(dynastyId) {
  return dynastyTranslationsCache.value[activeLang.value]?.[dynastyId] ?? {}
}

const selectedDynasties = computed(() => {
  if (!selected.value?.dynasty_ids?.length) return []
  return selected.value.dynasty_ids
    .map(id => {
      const d = dynastyById.value[id]
      if (!d) return null
      const tr = tDynasty(id)
      return { ...d, ...tr }
    })
    .filter(Boolean)
})
</script>

<template>
  <div class="shell">
    <header>
      <span class="logo">Inventory Viewer</span>
      <span class="count">{{ items.length }} items</span>
      <select
        v-if="availableLangs.length > 1"
        v-model="activeLang"
        class="lang-select"
      >
        <option v-for="lang in availableLangs" :key="lang" :value="lang">{{ lang }}</option>
      </select>
    </header>

    <main>
      <!-- Detail -->
      <template v-if="selected">
        <a class="back" href="#" @click.prevent="back">← Back to list</a>

        <div class="detail">
          <div class="detail-type">{{ selected.type }}</div>
          <h1>{{ label(selected) }}</h1>

          <div v-if="selected.images?.length" class="images">
            <figure v-for="(img, i) in selected.images" :key="i">
              <img :src="img.url" :alt="img.captions?.[activeLang] ?? ''" loading="lazy" />
              <figcaption v-if="img.captions?.[activeLang] || img.photographer">
                <span v-if="img.captions?.[activeLang]">{{ img.captions[activeLang] }}</span>
                <span v-if="img.photographer" class="credit">© {{ img.photographer }}</span>
              </figcaption>
            </figure>
          </div>

          <dl v-if="detailFields.length">
            <template v-for="f in detailFields" :key="f.label">
              <dt>{{ f.label }}</dt>
              <dd>{{ f.value }}</dd>
            </template>
          </dl>

          <div v-if="selectedDynasties.length" class="dynasties">
            <h2 class="section-title">{{ selectedDynasties.length === 1 ? 'Dynasty' : 'Dynasties' }}</h2>
            <div v-for="d in selectedDynasties" :key="d.id" class="dynasty-card">
              <div class="dynasty-header">
                <span class="dynasty-name">{{ d.name ?? '—' }}</span>
                <span v-if="d.also_known_as" class="dynasty-aka">also known as {{ d.also_known_as }}</span>
                <span v-if="d.from_ad || d.to_ad" class="dynasty-dates">
                  {{ d.date_description_ad ?? (d.from_ad + (d.to_ad ? ' – ' + d.to_ad : '')) }}
                </span>
              </div>
              <p v-if="d.history" class="dynasty-history">{{ d.history }}</p>
              <p v-if="d.area" class="dynasty-area">Area: {{ d.area }}</p>
            </div>
          </div>

          <div v-if="relatedItems.length" class="related">
            <h2 class="section-title">Related items</h2>
            <ul class="related-list">
              <li
                v-for="rel in relatedItems"
                :key="rel.id"
                class="related-item"
                @click="selectItem(rel)"
              >
                <div class="related-thumb">
                  <img v-if="rel.images?.length" :src="rel.images[0].url" :alt="label(rel)" loading="lazy" />
                  <div v-else class="related-thumb-placeholder"></div>
                </div>
                <div class="related-info">
                  <span class="related-name">{{ label(rel) }}</span>
                  <span class="related-type">{{ rel.type }}</span>
                </div>
              </li>
            </ul>
          </div>

          <div class="meta">
            <span v-if="selected.country_id">Country: {{ selected.country_id }}</span>
            <span v-if="selected.start_date">
              Period: {{ selected.start_date }}{{ selected.end_date ? ' – ' + selected.end_date : '' }}
            </span>
            <span v-if="selected.mwnf_reference">Ref: {{ selected.mwnf_reference }}</span>
          </div>
        </div>
      </template>

      <!-- List -->
      <template v-else>
        <ul class="list">
          <li
            v-for="item in items"
            :key="item.id"
            class="list-item"
            @click="selectItem(item)"
          >
            <div class="item-thumb" v-if="item.images?.length">
              <img :src="item.images[0].url" :alt="label(item)" loading="lazy" />
            </div>
            <div class="item-thumb placeholder" v-else></div>
            <div class="item-info">
              <span class="item-name">{{ label(item) }}</span>
              <span class="item-type">{{ item.type }}</span>
            </div>
          </li>
        </ul>
      </template>
    </main>
  </div>
</template>

<style scoped>
.shell { min-height: 100vh; display: flex; flex-direction: column; }

header {
  position: sticky; top: 0; z-index: 10;
  display: flex; align-items: center; gap: 1rem;
  padding: .75rem 1.5rem;
  background: #1a1a2e; color: #fff;
}
.logo { font-weight: 700; font-size: 1rem; letter-spacing: .02em; }
.count { font-size: .8rem; opacity: .6; }

.lang-select {
  margin-left: auto;
  background: #2a2a4e;
  color: #fff;
  border: 1px solid #4a4a7e;
  border-radius: 4px;
  padding: .25rem .5rem;
  font-size: .8rem;
  cursor: pointer;
}

main { flex: 1; max-width: 900px; width: 100%; margin: 0 auto; padding: 1.5rem; }

/* List */
.list { list-style: none; display: flex; flex-direction: column; gap: .5rem; }

.list-item {
  display: flex; align-items: center; gap: 1rem;
  padding: .75rem 1rem;
  background: #fff; border-radius: 8px;
  cursor: pointer; transition: box-shadow .15s;
}
.list-item:hover { box-shadow: 0 2px 12px rgba(0,0,0,.12); }

.item-thumb { width: 56px; height: 56px; flex-shrink: 0; border-radius: 4px; overflow: hidden; background: #eee; }
.item-thumb img { width: 100%; height: 100%; object-fit: cover; }
.item-thumb.placeholder { background: #e0e0e0; }

.item-info { display: flex; flex-direction: column; gap: .2rem; min-width: 0; }
.item-name { font-weight: 600; font-size: .95rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.item-type { font-size: .75rem; color: #888; text-transform: uppercase; letter-spacing: .05em; }

/* Detail */
.back {
  display: inline-block; margin-bottom: 1.25rem;
  color: #1a1a2e; font-size: .9rem; text-decoration: none;
}
.back:hover { text-decoration: underline; }

.detail { background: #fff; border-radius: 8px; padding: 1.5rem; }
.detail-type { font-size: .75rem; text-transform: uppercase; letter-spacing: .08em; color: #888; margin-bottom: .5rem; }
.detail h1 { font-size: 1.5rem; margin-bottom: 1.25rem; }

.images {
  display: flex; gap: 1rem; flex-wrap: wrap;
  margin-bottom: 1.5rem;
}
.images figure { width: 180px; }
.images img { width: 100%; height: 130px; object-fit: cover; border-radius: 4px; }
.images figcaption { font-size: .75rem; color: #666; margin-top: .3rem; }
.images .credit { display: block; }

dl { display: grid; grid-template-columns: max-content 1fr; gap: .4rem 1rem; margin-bottom: 1.25rem; }
dt { font-weight: 600; font-size: .85rem; color: #555; white-space: nowrap; }
dd { font-size: .9rem; line-height: 1.5; }

.meta { display: flex; flex-wrap: wrap; gap: .5rem 1.5rem; font-size: .8rem; color: #888; border-top: 1px solid #eee; padding-top: 1rem; }

.section-title { font-size: .9rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #555; margin-bottom: .75rem; }

.dynasties { margin-bottom: 1.25rem; border-top: 1px solid #eee; padding-top: 1.25rem; }
.dynasty-card { background: #f7f5ee; border-left: 3px solid #b5953a; border-radius: 4px; padding: .75rem 1rem; margin-bottom: .6rem; }
.dynasty-header { display: flex; flex-wrap: wrap; align-items: baseline; gap: .4rem 1rem; margin-bottom: .4rem; }
.dynasty-name { font-weight: 700; font-size: 1rem; }
.dynasty-aka { font-size: .82rem; color: #777; font-style: italic; }
.dynasty-dates { font-size: .82rem; color: #888; margin-left: auto; }
.dynasty-history { font-size: .88rem; line-height: 1.6; color: #333; margin: 0 0 .35rem; }
.dynasty-area { font-size: .8rem; color: #777; margin: 0; }

.related { margin-bottom: 1.25rem; border-top: 1px solid #eee; padding-top: 1.25rem; }
.related-list { list-style: none; display: flex; flex-direction: column; gap: .4rem; }
.related-item {
  display: flex; align-items: center; gap: .75rem;
  padding: .5rem .75rem;
  background: #f7f7f9; border-radius: 6px;
  cursor: pointer; transition: background .15s;
}
.related-item:hover { background: #eeeef6; }
.related-thumb { width: 44px; height: 44px; flex-shrink: 0; border-radius: 4px; overflow: hidden; background: #ddd; }
.related-thumb img { width: 100%; height: 100%; object-fit: cover; }
.related-thumb-placeholder { width: 100%; height: 100%; }
.related-info { display: flex; flex-direction: column; gap: .15rem; min-width: 0; }
.related-name { font-size: .88rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.related-type { font-size: .72rem; color: #888; text-transform: uppercase; letter-spacing: .05em; }
</style>
