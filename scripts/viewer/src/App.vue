<script setup>
import { ref, computed, watch } from 'vue'
import { marked } from 'marked'
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
const translationsCache = ref({})
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
  } catch {}
}

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

function md(text) {
  if (!text) return ''
  return marked.parse(text, { breaks: true })
}

const itemById = computed(() => {
  const map = {}
  for (const item of items.value) map[item.id] = item
  return map
})

const relatedItems = computed(() => {
  if (!selected.value?.related_item_ids?.length) return []
  // deduplicate
  const seen = new Set()
  return selected.value.related_item_ids
    .map(id => itemById.value[id])
    .filter(item => {
      if (!item || seen.has(item.id)) return false
      seen.add(item.id)
      return true
    })
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

const isMonument = computed(() => selected.value?.type === 'monument')

// Key facts: metadata rows in the info table, matching legacy page field order
const keyFacts = computed(() => {
  if (!selected.value) return []
  const item = selected.value
  const tr = t(item)
  const dynastyNames = selectedDynasties.value.map(d => d.name).filter(Boolean).join(', ')
  const facts = []

  if (isMonument.value) {
    // Monument field order from legacy: Also known as, Location, Date, Period/Dynasty, Patron(s)
    if (tr.alternate_name)              facts.push({ label: 'Also known as', value: tr.alternate_name })
    if (tr.location)                    facts.push({ label: 'Location', value: tr.location })
    if (tr.dates)                       facts.push({ label: 'Date of Monument', value: tr.dates })
    if (dynastyNames)                   facts.push({ label: 'Period / Dynasty', value: dynastyNames })
    // patrons comes from extra.patrons (monument-specific); fall back to initial_owner if absent
    const patronValue = tr.patrons ?? tr.initial_owner
    if (patronValue)                    facts.push({ label: 'Patron(s)', value: patronValue })
  } else {
    // Object field order from legacy: Location, Holding Museum, Inventory No, Date, Period/Dynasty, Provenance, Material/Technique, Dimensions
    if (tr.location)            facts.push({ label: 'Location', value: tr.location })
    if (tr.holder)              facts.push({ label: 'Holding Museum', value: tr.holder })
    if (item.owner_reference)   facts.push({ label: 'Museum Inventory Number', value: item.owner_reference })
    if (tr.dates)               facts.push({ label: 'Date of Object', value: tr.dates })
    if (dynastyNames)           facts.push({ label: 'Period / Dynasty', value: dynastyNames })
    if (tr.provenance)          facts.push({ label: 'Provenance', value: tr.provenance })
    if (tr.type)                facts.push({ label: 'Material(s) / Technique(s)', value: tr.type })
    if (tr.dimensions)          facts.push({ label: 'Dimensions', value: tr.dimensions })
    if (tr.owner)               facts.push({ label: 'Owner', value: tr.owner })
    if (tr.initial_owner)       facts.push({ label: 'Initial Owner', value: tr.initial_owner })
    if (tr.place_of_production) facts.push({ label: 'Place of Production', value: tr.place_of_production })
  }

  return facts
})

// Content sections: named text blocks rendered as markdown, matching legacy section order
const contentSections = computed(() => {
  if (!selected.value) return []
  const tr = t(selected.value)
  const monument = isMonument.value
  const sections = []

  if (monument) {
    // Monument order: History, Description, How Monument Was Dated
    // history comes from extra.history (monument-specific brief history of construction/restoration)
    if (tr.history)               sections.push({ heading: 'History', value: tr.history })
    if (tr.description)           sections.push({ heading: 'Description', value: tr.description })
  } else {
    // Object order: Description, History/Acquisition, How Object Was Dated, Provenance method
    if (tr.description)           sections.push({ heading: 'Description', value: tr.description })
    if (tr.obtention)             sections.push({ heading: 'History / Acquisition', value: tr.obtention })
  }

  if (tr.method_for_datation)
    sections.push({ heading: monument ? 'How Monument Was Dated' : 'How Object Was Dated', value: tr.method_for_datation })

  if (tr.method_for_provenance)
    sections.push({ heading: monument ? 'How Monument Provenance Was Determined' : 'How Object Provenance Was Determined', value: tr.method_for_provenance })

  if (tr.bibliography)
    sections.push({ heading: 'Bibliography', value: tr.bibliography })

  return sections
})

// Credits block: separated from content, matching legacy credits section labels
const credits = computed(() => {
  if (!selected.value) return []
  const tr = t(selected.value)
  const c = []
  if (tr.author)                   c.push({ label: 'Prepared by', value: tr.author })
  if (tr.copy_editor)              c.push({ label: 'Copyedited by', value: tr.copy_editor })
  if (tr.translator)               c.push({ label: 'Translation by', value: tr.translator })
  if (tr.translation_copy_editor)  c.push({ label: 'Translation copyedited by', value: tr.translation_copy_editor })
  return c
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
      <!-- ── Detail view ── -->
      <template v-if="selected">
        <a class="back" href="#" @click.prevent="back">← Back to list</a>

        <div class="detail">
          <div class="detail-type">{{ selected.type }}</div>
          <h1>{{ label(selected) }}</h1>

          <!-- Images -->
          <div v-if="selected.images?.length" class="images">
            <figure v-for="(img, i) in selected.images" :key="i">
              <img :src="img.url" :alt="img.captions?.[activeLang] ?? ''" loading="lazy" />
              <figcaption v-if="img.captions?.[activeLang] || img.photographer">
                <span v-if="img.captions?.[activeLang]">{{ img.captions[activeLang] }}</span>
                <span v-if="img.photographer" class="photo-credit">© {{ img.photographer }}</span>
              </figcaption>
            </figure>
          </div>

          <!-- Key facts table (metadata, type-conditional labels & order) -->
          <table v-if="keyFacts.length" class="key-facts">
            <tbody>
              <tr v-for="fact in keyFacts" :key="fact.label">
                <th>{{ fact.label }}</th>
                <td>{{ fact.value }}</td>
              </tr>
            </tbody>
          </table>

          <!-- Content sections (named sections with markdown body) -->
          <section
            v-for="section in contentSections"
            :key="section.heading"
            class="content-section"
          >
            <h2>{{ section.heading }}</h2>
            <div v-html="md(section.value)" class="prose"></div>
          </section>

          <!-- Credits block -->
          <div v-if="credits.length" class="credits">
            <h2 class="credits-heading">Credits</h2>
            <dl class="credits-list">
              <template v-for="c in credits" :key="c.label">
                <dt>{{ c.label }}</dt>
                <dd>{{ c.value }}</dd>
              </template>
            </dl>
            <p v-if="selected.mwnf_reference" class="mwnf-ref">
              MWNF Working Number: <strong>{{ selected.mwnf_reference }}</strong>
            </p>
          </div>

          <!-- Dynasty detail cards (historical context) -->
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

          <!-- Related items -->
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
        </div>
      </template>

      <!-- ── List view ── -->
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
  background: #2a2a4e; color: #fff;
  border: 1px solid #4a4a7e; border-radius: 4px;
  padding: .25rem .5rem; font-size: .8rem; cursor: pointer;
}

main { flex: 1; max-width: 900px; width: 100%; margin: 0 auto; padding: 1.5rem; }

/* ── List ── */
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

/* ── Detail ── */
.back {
  display: inline-block; margin-bottom: 1.25rem;
  color: #1a1a2e; font-size: .9rem; text-decoration: none;
}
.back:hover { text-decoration: underline; }

.detail { background: #fff; border-radius: 8px; padding: 2rem; }
.detail-type {
  display: inline-block;
  font-size: .7rem; text-transform: uppercase; letter-spacing: .1em;
  color: #fff; background: #1a1a2e;
  padding: .2rem .6rem; border-radius: 3px; margin-bottom: .75rem;
}
.detail h1 { font-size: 1.6rem; margin: 0 0 1.5rem; line-height: 1.3; }

/* Images */
.images {
  display: flex; gap: 1rem; flex-wrap: wrap;
  margin-bottom: 1.75rem;
}
.images figure { width: 180px; }
.images img { width: 100%; height: 130px; object-fit: cover; border-radius: 4px; }
.images figcaption { font-size: .72rem; color: #666; margin-top: .3rem; }
.photo-credit { display: block; }

/* Key facts table */
.key-facts {
  width: 100%; border-collapse: collapse;
  margin-bottom: 2rem;
  font-size: .9rem;
}
.key-facts th, .key-facts td {
  padding: .5rem .75rem;
  border-bottom: 1px solid #eee;
  text-align: left;
  vertical-align: top;
}
.key-facts th {
  width: 36%; font-weight: 600; color: #444;
  background: #f8f7f4;
  white-space: nowrap;
}
.key-facts td { color: #222; }
.key-facts tr:last-child th,
.key-facts tr:last-child td { border-bottom: none; }

/* Content sections */
.content-section {
  margin-bottom: 1.75rem;
  border-top: 1px solid #eee;
  padding-top: 1.5rem;
}
.content-section h2 {
  font-size: 1rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: .06em;
  color: #1a1a2e; margin: 0 0 .9rem;
}

/* Prose (markdown output) */
.prose { font-size: .93rem; line-height: 1.7; color: #222; }
.prose :deep(p) { margin: 0 0 .75em; }
.prose :deep(p:last-child) { margin-bottom: 0; }
.prose :deep(em) { font-style: italic; }
.prose :deep(strong) { font-weight: 700; }
.prose :deep(ul), .prose :deep(ol) { padding-left: 1.5em; margin: 0 0 .75em; }
.prose :deep(li) { margin-bottom: .25em; }
.prose :deep(a) { color: #1a1a2e; text-decoration: underline; }

/* Credits */
.credits {
  margin-top: 1.75rem; border-top: 2px solid #1a1a2e;
  padding-top: 1.25rem;
}
.credits-heading {
  font-size: .8rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: .08em;
  color: #1a1a2e; margin: 0 0 .75rem;
}
.credits-list {
  display: grid; grid-template-columns: max-content 1fr;
  gap: .3rem 1rem; font-size: .85rem; margin-bottom: .75rem;
}
.credits-list dt { font-weight: 600; color: #555; white-space: nowrap; }
.credits-list dd { color: #222; }

.mwnf-ref { font-size: .8rem; color: #888; margin-top: .5rem; }

/* Dynasty cards */
.section-title {
  font-size: .9rem; font-weight: 700; text-transform: uppercase;
  letter-spacing: .06em; color: #555; margin-bottom: .75rem;
}
.dynasties { margin-top: 1.75rem; border-top: 1px solid #eee; padding-top: 1.5rem; }
.dynasty-card {
  background: #f7f5ee; border-left: 3px solid #b5953a;
  border-radius: 4px; padding: .75rem 1rem; margin-bottom: .6rem;
}
.dynasty-header {
  display: flex; flex-wrap: wrap; align-items: baseline;
  gap: .4rem 1rem; margin-bottom: .4rem;
}
.dynasty-name { font-weight: 700; font-size: 1rem; }
.dynasty-aka { font-size: .82rem; color: #777; font-style: italic; }
.dynasty-dates { font-size: .82rem; color: #888; margin-left: auto; }
.dynasty-history { font-size: .88rem; line-height: 1.6; color: #333; margin: 0 0 .35rem; }
.dynasty-area { font-size: .8rem; color: #777; margin: 0; }

/* Related items */
.related { margin-top: 1.75rem; border-top: 1px solid #eee; padding-top: 1.5rem; }
.related-list { list-style: none; display: flex; flex-direction: column; gap: .4rem; }
.related-item {
  display: flex; align-items: center; gap: .75rem;
  padding: .5rem .75rem;
  background: #f7f7f9; border-radius: 6px;
  cursor: pointer; transition: background .15s;
}
.related-item:hover { background: #eeeef6; }
.related-thumb {
  width: 44px; height: 44px; flex-shrink: 0;
  border-radius: 4px; overflow: hidden; background: #ddd;
}
.related-thumb img { width: 100%; height: 100%; object-fit: cover; }
.related-thumb-placeholder { width: 100%; height: 100%; }
.related-info { display: flex; flex-direction: column; gap: .15rem; min-width: 0; }
.related-name { font-size: .88rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.related-type { font-size: .72rem; color: #888; text-transform: uppercase; letter-spacing: .05em; }
</style>
