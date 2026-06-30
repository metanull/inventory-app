<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useInventoryData } from '../composables/useInventoryData.js'

const route  = useRoute()
const router = useRouter()

const {
  items, dynasties,
  availableLangs, defaultLang,
  translationsCache,
  loadLangTranslations,
  itemById,
  itemLabel, countryLabel, partnerLabel,
  md,
} = useInventoryData()

// ── Active item & language ────────────────────────────────────────────

const item = computed(() => itemById.value[decodeURIComponent(route.params.id)] ?? null)

const activeLang = ref(defaultLang)

onMounted(() => {
  loadLangTranslations(activeLang.value)
})

watch(activeLang, lang => {
  loadLangTranslations(lang)
})

// ── Translation helpers ───────────────────────────────────────────────

function t(it) {
  if (!it) return {}
  return translationsCache.value[activeLang.value]?.[it.id] ?? {}
}

function label(it) {
  return t(it).name ?? it.internal_name ?? it.id
}

// ── Dynasties for this item ───────────────────────────────────────────

const dynastyTranslationsCache = ref({})

async function loadDynastyTranslations(lang) {
  if (dynastyTranslationsCache.value[lang]) return
  try {
    const m = await import(`@inventory-data/translations/dynasties.${lang}.json`)
    dynastyTranslationsCache.value = { ...dynastyTranslationsCache.value, [lang]: m.default }
  } catch {}
}

onMounted(() => loadDynastyTranslations(activeLang.value))
watch(activeLang, lang => loadDynastyTranslations(lang))

const dynastyById = computed(() => {
  const m = {}
  for (const d of dynasties.value) m[d.id] = d
  return m
})

function tDynasty(dynastyId) {
  return dynastyTranslationsCache.value[activeLang.value]?.[dynastyId] ?? {}
}

const selectedDynasties = computed(() => {
  if (!item.value?.dynasty_ids?.length) return []
  return item.value.dynasty_ids
    .map(id => {
      const d = dynastyById.value[id]
      if (!d) return null
      const tr = tDynasty(id)
      return { ...d, ...tr }
    })
    .filter(Boolean)
})

// ── Related items ─────────────────────────────────────────────────────

const relatedItems = computed(() => {
  const links = item.value?.related_items ?? []
  if (!links.length) return []
  const seen = new Set()
  return links
    .map(link => {
      const it = itemById.value[link.id]
      if (!it || seen.has(it.id)) return null
      seen.add(it.id)
      return { item: it, justifications: link.justifications ?? {} }
    })
    .filter(Boolean)
})

// ── Key facts (metadata table), type-conditional field order ─────────

const isMonument = computed(() => item.value?.type === 'monument')

const keyFacts = computed(() => {
  if (!item.value) return []
  const it = item.value
  const tr = t(it)
  const dynastyNames = selectedDynasties.value.map(d => d.name).filter(Boolean).join(', ')
  const facts = []

  if (isMonument.value) {
    if (tr.alternate_name)  facts.push({ label: 'Also known as',    value: tr.alternate_name })
    if (tr.location)        facts.push({ label: 'Location',         value: tr.location })
    if (tr.dates)           facts.push({ label: 'Date of Monument', value: tr.dates })
    if (dynastyNames)       facts.push({ label: 'Period / Dynasty', value: dynastyNames })
    const patronValue = tr.patrons ?? tr.initial_owner
    if (patronValue)        facts.push({ label: 'Patron(s)',        value: patronValue })
  } else {
    if (tr.location)            facts.push({ label: 'Location',                  value: tr.location })
    if (tr.holder)              facts.push({ label: 'Holding Museum',             value: tr.holder })
    if (tr.dates)               facts.push({ label: 'Date of Object',             value: tr.dates })
    if (it.owner_reference)     facts.push({ label: 'Museum Inventory Number',    value: it.owner_reference })
    if (tr.type)                facts.push({ label: 'Material(s) / Technique(s)', value: tr.type })
    if (tr.dimensions)          facts.push({ label: 'Dimensions',                 value: tr.dimensions })
    if (dynastyNames)           facts.push({ label: 'Period / Dynasty',           value: dynastyNames })
    if (tr.provenance)          facts.push({ label: 'Provenance',                 value: tr.provenance })
    if (tr.owner)               facts.push({ label: 'Owner',                      value: tr.owner })
    if (tr.initial_owner)       facts.push({ label: 'Initial Owner',              value: tr.initial_owner })
    if (tr.place_of_production) facts.push({ label: 'Place of Production',        value: tr.place_of_production })
  }

  return facts
})

// ── Content sections ──────────────────────────────────────────────────

const contentSections = computed(() => {
  if (!item.value) return []
  const tr = t(item.value)
  const monument = isMonument.value
  const sections = []

  if (monument) {
    if (tr.history)               sections.push({ heading: 'History',                        value: tr.history })
    if (tr.description)           sections.push({ heading: 'Description',                    value: tr.description })
    if (tr.method_for_datation)   sections.push({ heading: 'How Monument was dated',         value: tr.method_for_datation })
    if (tr.method_for_provenance) sections.push({ heading: 'How provenance was established', value: tr.method_for_provenance })
    if (tr.bibliography)          sections.push({ heading: 'Selected bibliography',          value: tr.bibliography })
  } else {
    if (tr.description)           sections.push({ heading: 'Description',                          value: tr.description })
    if (tr.method_for_datation)   sections.push({ heading: 'How date and origin were established', value: tr.method_for_datation })
    if (tr.obtention)             sections.push({ heading: 'How Object was obtained',              value: tr.obtention })
    if (tr.method_for_provenance) sections.push({ heading: 'How provenance was established',       value: tr.method_for_provenance })
    if (tr.bibliography)          sections.push({ heading: 'Selected bibliography',                value: tr.bibliography })
  }

  return sections
})

// ── Credits ───────────────────────────────────────────────────────────

const credits = computed(() => {
  if (!item.value) return []
  const tr = t(item.value)
  const c = []
  if (tr.author)                   c.push({ label: 'Prepared by',                value: tr.author })
  if (tr.copy_editor)              c.push({ label: 'Copyedited by',              value: tr.copy_editor })
  if (tr.translator)               c.push({ label: 'Translation by',             value: tr.translator })
  if (tr.translation_copy_editor)  c.push({ label: 'Translation copyedited by',  value: tr.translation_copy_editor })
  return c
})

// ── Navigation ─────────────────────────────────────────────────────────

function back() {
  if (window.history.length > 2) {
    router.back()
  } else {
    router.push('/')
  }
}
</script>

<template>
  <div v-if="!item" class="content-box not-found">
    <p>Item not found.</p>
    <router-link to="/">← Return home</router-link>
  </div>

  <div v-else class="detail-wrap">
    <!-- Breadcrumb / back -->
    <a class="back-link" href="#" @click.prevent="back">← Back to results</a>

    <!-- Language selector for item content -->
    <div v-if="availableLangs.length > 1" class="lang-selector">
      <label class="lang-label">Item language:</label>
      <select v-model="activeLang" class="lang-select">
        <option v-for="lang in availableLangs" :key="lang" :value="lang">{{ lang.toUpperCase() }}</option>
      </select>
    </div>

    <div class="detail content-box">
      <!-- Type badge -->
      <div class="detail-type-badge">{{ item.type }}</div>

      <!-- Title -->
      <h1 class="detail-title">{{ label(item) }}</h1>

      <!-- Images -->
      <div v-if="item.images?.length" class="images">
        <figure v-for="(img, i) in item.images" :key="i">
          <img :src="img.url" :alt="img.captions?.[activeLang] ?? ''" loading="lazy" class="detail-img" />
          <figcaption v-if="img.captions?.[activeLang] || img.photographer">
            <span v-if="img.captions?.[activeLang]">{{ img.captions[activeLang] }}</span>
            <span v-if="img.photographer" class="photo-credit">© {{ img.photographer }}</span>
          </figcaption>
        </figure>
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

      <!-- Content sections (markdown) -->
      <section
        v-for="section in contentSections"
        :key="section.heading"
        class="content-section"
      >
        <h2 class="content-section-heading">{{ section.heading }}</h2>
        <div v-html="md(section.value)" class="prose" />
      </section>

      <!-- Credits -->
      <div v-if="credits.length" class="credits">
        <h2 class="credits-heading">Credits</h2>
        <dl class="credits-list">
          <template v-for="c in credits" :key="c.label">
            <dt>{{ c.label }}</dt>
            <dd>{{ c.value }}</dd>
          </template>
        </dl>
        <p v-if="item.mwnf_reference" class="mwnf-ref">
          MWNF Working Number: <strong>{{ item.mwnf_reference }}</strong>
        </p>
      </div>

      <!-- Dynasty cards -->
      <div v-if="selectedDynasties.length" class="dynasties">
        <h2 class="sub-section-title">{{ selectedDynasties.length === 1 ? 'Dynasty' : 'Dynasties' }}</h2>
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
        <h2 class="sub-section-title">Related Items</h2>
        <ul class="related-list item-list">
          <li
            v-for="{ item: rel, justifications } in relatedItems"
            :key="rel.id"
            class="item-list-row"
            @click="$router.push(`/item/${encodeURIComponent(rel.id)}`)"
          >
            <div class="item-thumb">
              <img v-if="rel.images?.length" :src="rel.images[0].url" :alt="itemLabel(rel)" loading="lazy" />
              <div v-else class="item-thumb-placeholder" />
            </div>
            <div class="item-list-info">
              <div class="item-list-name">{{ itemLabel(rel) }}</div>
              <div
                v-if="justifications[activeLang]"
                class="item-list-justification"
              >{{ justifications[activeLang] }}</div>
              <div class="item-list-meta">
                <span class="item-type-badge">{{ rel.type }}</span>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<style scoped>
.not-found { color: var(--muted); font-family: Arial, sans-serif; font-size: 13px; }

.detail-wrap { display: flex; flex-direction: column; gap: 10px; }

.lang-selector {
  display: flex;
  align-items: center;
  gap: 8px;
  font-family: Arial, sans-serif;
  font-size: 12px;
  color: var(--muted);
  justify-content: flex-end;
}
.lang-label { }
.lang-select { font-size: 12px; padding: 3px 6px; }

.detail { }

.detail-type-badge {
  display: inline-block;
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: var(--header-bg);
  border: 1px solid var(--gold);
  padding: 2px 8px;
  margin-bottom: 10px;
  font-family: Arial, sans-serif;
}

.detail-title {
  font-size: 22px;
  font-weight: bold;
  color: var(--header-bg);
  margin-bottom: 16px;
  line-height: 1.3;
  font-family: Georgia, serif;
}

/* Images */
.images {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  margin-bottom: 20px;
}
.images figure { flex-shrink: 0; }
.detail-img {
  width: 180px;
  height: 140px;
  object-fit: cover;
  border: 1px solid var(--border);
  display: block;
}
.images figcaption {
  font-size: 11px;
  color: var(--muted);
  margin-top: 4px;
  width: 180px;
  font-family: Arial, sans-serif;
}
.photo-credit { display: block; }

/* Key facts table */
.key-facts {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 20px;
  font-size: 13px;
}
.key-facts th,
.key-facts td {
  padding: 6px 10px;
  border: 1px solid var(--border);
  vertical-align: top;
  text-align: left;
}
.key-facts th {
  background: #f8f0e3;
  width: 36%;
  font-weight: bold;
  color: var(--header-bg);
  font-family: Arial, sans-serif;
  font-size: 12px;
  white-space: nowrap;
}
.key-facts td { color: var(--text); font-family: Arial, sans-serif; }

/* Content sections */
.content-section {
  margin-bottom: 20px;
  padding-top: 16px;
  border-top: 1px solid var(--border);
}
.content-section-heading {
  font-size: 13px;
  font-weight: bold;
  text-transform: uppercase;
  letter-spacing: 0.07em;
  color: var(--header-bg);
  margin-bottom: 10px;
  font-family: Arial, sans-serif;
}

/* Prose */
.prose { font-size: 13px; line-height: 1.7; color: var(--text); font-family: Arial, sans-serif; }
.prose :deep(p) { margin: 0 0 .75em; }
.prose :deep(p:last-child) { margin-bottom: 0; }
.prose :deep(em) { font-style: italic; }
.prose :deep(strong) { font-weight: 700; }
.prose :deep(ul), .prose :deep(ol) { padding-left: 1.5em; margin: 0 0 .75em; }
.prose :deep(li) { margin-bottom: .25em; }
.prose :deep(a) { color: var(--link); text-decoration: underline; }

/* Credits */
.credits {
  margin-top: 20px;
  padding-top: 14px;
  border-top: 2px solid var(--header-bg);
}
.credits-heading {
  font-size: 11px;
  font-weight: bold;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--header-bg);
  margin-bottom: 10px;
  font-family: Arial, sans-serif;
}
.credits-list {
  display: grid;
  grid-template-columns: max-content 1fr;
  gap: .3rem 1rem;
  font-size: 12px;
  margin-bottom: 8px;
  font-family: Arial, sans-serif;
}
.credits-list dt { font-weight: bold; color: var(--muted); }
.credits-list dd { color: var(--text); }
.mwnf-ref { font-size: 11px; color: var(--muted); font-family: Arial, sans-serif; }

/* Dynasty cards */
.sub-section-title {
  font-size: 12px;
  font-weight: bold;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--muted);
  margin-bottom: 10px;
  font-family: Arial, sans-serif;
}
.dynasties { margin-top: 20px; border-top: 1px solid var(--border); padding-top: 16px; }
.dynasty-card {
  background: #faf5ec;
  border-left: 3px solid var(--gold);
  padding: 10px 14px;
  margin-bottom: 8px;
}
.dynasty-header {
  display: flex;
  flex-wrap: wrap;
  align-items: baseline;
  gap: .4rem 1rem;
  margin-bottom: 4px;
}
.dynasty-name { font-weight: bold; font-size: 13px; }
.dynasty-aka  { font-size: 11px; color: var(--muted); font-style: italic; }
.dynasty-dates { font-size: 11px; color: var(--muted); margin-left: auto; }
.dynasty-history { font-size: 12px; line-height: 1.6; color: var(--text); margin: 0 0 4px; font-family: Arial, sans-serif; }
.dynasty-area { font-size: 11px; color: var(--muted); margin: 0; font-family: Arial, sans-serif; }

/* Related */
.related { margin-top: 20px; border-top: 1px solid var(--border); padding-top: 16px; }
.related-list { list-style: none; }

.item-type-badge {
  text-transform: uppercase;
  letter-spacing: 0.06em;
  font-size: 10px;
  color: #888;
}
.item-list-justification {
  font-size: 12px;
  color: var(--muted);
  font-style: italic;
  margin: 2px 0 4px;
  font-family: Arial, sans-serif;
  line-height: 1.4;
}
</style>
