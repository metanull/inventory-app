<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useInventoryData } from '../composables/useInventoryData.js'

const route  = useRoute()
const router = useRouter()

const {
  items,
  availableLangs, defaultLang,
  translationsCache,
  loadLangTranslations,
  itemById,
  itemLabel, countryLabel, partnerLabel,
  exhibitionLinksForItem,
  md, mdInline,
} = useInventoryData()

// ── Active item & language ────────────────────────────────────────────

const item = computed(() => itemById.value[decodeURIComponent(route.params.id)] ?? null)

// Only languages this specific item actually has a translation for — not every
// site language (most items are translated into a handful of languages, not all).
const itemLangs = computed(() => {
  const langs = item.value?.languages
  return langs?.length ? langs : availableLangs.value
})

const activeLang = ref(itemLangs.value.includes(defaultLang) ? defaultLang : (itemLangs.value[0] ?? defaultLang))

watch(item, () => {
  if (!itemLangs.value.includes(activeLang.value)) {
    activeLang.value = itemLangs.value.includes(defaultLang) ? defaultLang : (itemLangs.value[0] ?? defaultLang)
  }
})

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

function labelText(it) {
  if (!it) return ''
  return itemLabel(it) // already mdStrip'd
}

function labelHtml(it) {
  if (!it) return ''
  return mdInlineGloss(t(it).name ?? it.internal_name ?? it.id)
}

// Item content (not the app's own English interface) follows the active
// language's natural direction — Arabic reads right-to-left.
const contentDir = computed(() => (activeLang.value === 'ar' ? 'rtl' : 'ltr'))

// ── Glossary term hyperlinking ────────────────────────────────────────
//
// Legacy wires known glossary words/phrases directly into item text (e.g.
// "thuluth" on database_item.php), opening a popup with the term's
// definition on click (glossary1.php). We do the same: wrap every spelling
// of this item's glossary_ids, in the active language, in a clickable span
// before markdown rendering — marked passes inline HTML through untouched —
// then handle clicks on those spans via event delegation (v-html content
// isn't part of Vue's own template, so it can't bind @click directly).

const glossaryTranslationsCache = ref({})

async function loadGlossaryTranslations(lang) {
  if (glossaryTranslationsCache.value[lang]) return
  try {
    const m = await import(`@inventory-data/translations/glossary.${lang}.json`)
    glossaryTranslationsCache.value = { ...glossaryTranslationsCache.value, [lang]: m.default }
  } catch {
    glossaryTranslationsCache.value = { ...glossaryTranslationsCache.value, [lang]: {} }
  }
}

onMounted(() => loadGlossaryTranslations(activeLang.value))
watch(activeLang, lang => loadGlossaryTranslations(lang))

// spelling (lowercased) -> { gid, spelling, definition } for this item's glossary
// terms in the active language, longest spelling first so multi-word phrases
// match before any single word they contain.
const glossaryEntries = computed(() => {
  const ids = item.value?.glossary_ids ?? []
  if (!ids.length) return []
  const byLang = glossaryTranslationsCache.value[activeLang.value] ?? {}
  const entries = []
  for (const gid of ids) {
    const entry = byLang[gid]
    if (!entry?.spellings?.length) continue
    for (const spelling of entry.spellings) {
      entries.push({ gid, spelling, definition: entry.definition ?? '' })
    }
  }
  return entries.sort((a, b) => b.spelling.length - a.spelling.length)
})

const glossaryRegex = computed(() => {
  const entries = glossaryEntries.value
  if (!entries.length) return null
  const escaped = entries.map(e => e.spelling.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'))
  return new RegExp(`\\b(${escaped.join('|')})\\b`, 'gi')
})

const spellingToEntry = computed(() => {
  const m = new Map()
  for (const e of glossaryEntries.value) m.set(e.spelling.toLowerCase(), e)
  return m
})

// Wraps every glossary spelling occurrence in `text` with a clickable span,
// safe to run on raw markdown/plain text before md()/mdInline() renders it.
function glossify(text) {
  if (!text || !glossaryRegex.value) return text
  return text.replace(glossaryRegex.value, match => {
    const entry = spellingToEntry.value.get(match.toLowerCase())
    if (!entry) return match
    return `<span class="gloss-term" data-gid="${entry.gid}">${match}</span>`
  })
}

function mdGloss(text) {
  return md(glossify(text))
}

function mdInlineGloss(text) {
  return mdInline(glossify(text))
}

// Plain-text fields (key facts) aren't markdown — glossify then render the
// resulting span(s) as-is, no further markdown parsing.
function glossifyPlain(text) {
  return glossify(text ?? '')
}

const activeGlossaryTerm = ref(null) // { spelling, definition } | null

function onDetailClick(event) {
  const el = event.target?.closest?.('.gloss-term')
  if (!el) return
  event.preventDefault()
  const entry = glossaryEntries.value.find(e => e.gid === el.dataset.gid)
  activeGlossaryTerm.value = {
    spelling: el.textContent,
    definition: entry?.definition ?? '',
  }
}

function closeGlossaryModal() {
  activeGlossaryTerm.value = null
}

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
  const facts = []

  if (isMonument.value) {
    if (tr.alternate_name)  facts.push({ label: 'Also known as',    value: tr.alternate_name })
    if (tr.location)        facts.push({ label: 'Location',         value: tr.location })
    if (tr.dates)           facts.push({ label: 'Date of Monument', value: tr.dates })
    if (tr.architects)      facts.push({ label: 'Architects',       value: tr.architects })
    const patronValue = tr.patrons ?? tr.initial_owner
    if (patronValue)        facts.push({ label: 'Patron(s)',        value: patronValue })
  } else {
    if (tr.alternate_name)      facts.push({ label: 'Also known as',              value: tr.alternate_name })
    if (tr.location)            facts.push({ label: 'Location',                  value: tr.location })
    if (tr.holder)              facts.push({ label: 'Holding Museum',             value: tr.holder })
    if (tr.dates)               facts.push({ label: 'Date of Object',             value: tr.dates })
    if (it.artist_names?.length) facts.push({ label: 'Artist',                    value: it.artist_names.join(', ') })
    if (tr.scriber)             facts.push({ label: 'Scribe',                     value: tr.scriber })
    if (it.owner_reference)     facts.push({ label: 'Museum Inventory Number',    value: it.owner_reference })
    if (tr.type)                facts.push({ label: 'Material(s) / Technique(s)', value: tr.type })
    if (tr.dimensions)          facts.push({ label: 'Dimensions',                 value: tr.dimensions })
    if (tr.provenance)          facts.push({ label: 'Provenance',                 value: tr.provenance })
    if (tr.workshop)            facts.push({ label: 'Workshop',                   value: tr.workshop })
    if (tr.binding_desc)        facts.push({ label: 'Binding',                    value: tr.binding_desc })
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
    if (tr.catalogue_holding_link) sections.push({ heading: 'Catalogue', value: `[${tr.catalogue_holding_link}](${tr.catalogue_holding_link})` })
  }

  return sections
})

// ── Short description — legacy's collapsible "view short description"
// toggle (pc_view_sdesc), shown below the main Description when the item
// also has a translation in a secondary context whose description is the
// shorter summary. See Epic 11 in the islamicart parity backlog. ──

const showShortDescription = ref(false)

const shortDescription = computed(() => {
  if (!item.value) return null
  return t(item.value).short_description ?? null
})

watch(() => item.value?.id, () => { showShortDescription.value = false })

// ── Monument "Special Features" — sub-details already imported as child
// items (type='detail', parent_id = this monument), just never surfaced ──

const monumentDetails = computed(() => {
  if (!item.value) return []
  return items.value
    .filter(i => i.parent_id === item.value.id && i.type === 'detail')
    .sort((a, b) => (a.display_order ?? 9999) - (b.display_order ?? 9999))
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

// ── Citation — mirrors legacy's auto-generated citation blurb + permalink ─

const citation = computed(() => {
  if (!item.value) return ''
  const tr = t(item.value)
  const name = labelText(item.value)
  if (!name) return ''
  const year = new Date().getFullYear()
  const permalink = `${window.location.origin}${window.location.pathname}#/item/${encodeURIComponent(item.value.id)}`
  const author = tr.author ? `${tr.author} ` : ''
  return `${author}"${name}" in Sharing History, ${year}. ${permalink}`
})

// ── "View on Timeline" — country + date-range proximity link. Legacy never
// stores a direct item↔HCR-event link either; it computes this live by
// country/date overlap, same as this link's target route already does. ──

const timelineLink = computed(() => {
  if (!item.value?.country_id) return null
  const begin = item.value.start_date
  const end = item.value.end_date
  if (begin == null && end == null) return null
  const query = { country: item.value.country_id }
  if (begin != null) query.begin = String(begin)
  if (end != null) query.end = String(end)
  return { path: '/timeline/results', query }
})

// ── Related video/audio (item_media) — prefer the active content language,
// fall back to any language the item actually has media in ────────────

const relatedMedia = computed(() => {
  const all = item.value?.media ?? []
  if (!all.length) return []
  const inLang = all.filter(m => m.language === activeLang.value)
  return inLang.length ? inLang : all
})

// ── "On display in" (Exhibitions) — which Virtual Exhibitions feature
// this item, derived client-side from collections.json (see Epic 12 in the
// islamicart parity backlog; distinct from the THG cross-links below). ──

const onDisplayInLinks = computed(() => {
  if (!item.value) return []
  return exhibitionLinksForItem(item.value.id).map(l => ({
    label: l.label,
    to: l.themeId
      ? { path: `/exhibitions/${encodeURIComponent(l.exhibitionId)}/theme/${encodeURIComponent(l.themeId)}` }
      : { path: `/exhibitions/${encodeURIComponent(l.exhibitionId)}/introduction` },
  }))
})

// ── THG (Thematic Gallery) cross-links — a separate legacy project's
// galleries also featuring this item. THG is due for a rewrite, so these
// are placeholder same-page anchors, not real external URLs, for now. ──

const thgGalleryLinks = computed(() => {
  return (item.value?.thg_galleries ?? []).map(g => ({
    name: g.name,
    href: `#ThematicGallery-${g.name}`,
  }))
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
    <router-link v-if="timelineLink" :to="timelineLink" class="timeline-link">View on Timeline →</router-link>

    <!-- Language selector for item content — only languages this item actually has -->
    <div v-if="itemLangs.length > 1" class="lang-selector">
      <label class="lang-label">Item language:</label>
      <select v-model="activeLang" class="lang-select">
        <option v-for="lang in itemLangs" :key="lang" :value="lang">{{ lang.toUpperCase() }}</option>
      </select>
    </div>

    <div class="detail content-box" @click="onDetailClick">
      <!-- Type badge -->
      <div class="detail-type-badge">{{ item.type }}</div>

      <!-- Title -->
      <h1 class="detail-title" :dir="contentDir" v-html="labelHtml(item)" />

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
            <td :dir="contentDir" v-html="glossifyPlain(fact.value)"></td>
          </tr>
        </tbody>
      </table>

      <!-- Content sections (markdown) -->
      <template v-for="section in contentSections" :key="section.heading">
        <section class="content-section">
          <h2 class="content-section-heading">{{ section.heading }}</h2>
          <div v-html="mdGloss(section.value)" class="prose" :dir="contentDir" />
        </section>

        <!-- Short description toggle (legacy: pc_view_sdesc), directly below Description -->
        <section
          v-if="section.heading === 'Description' && shortDescription"
          class="content-section short-description"
        >
          <button
            type="button"
            class="short-description-toggle"
            @click="showShortDescription = !showShortDescription"
          >
            {{ showShortDescription ? 'Hide short description' : 'View short description' }}
          </button>
          <div v-if="showShortDescription" v-html="mdGloss(shortDescription)" class="prose" :dir="contentDir" />
        </section>
      </template>

      <!-- Special Features (monument sub-details) -->
      <div v-if="monumentDetails.length" class="special-features">
        <h2 class="sub-section-title">Special Features</h2>
        <div v-for="d in monumentDetails" :key="d.id" class="special-feature" :dir="contentDir">
          <h3 class="special-feature-name" v-html="mdInline(t(d).name ?? d.internal_name ?? d.id)" />
          <p v-if="t(d).location" class="special-feature-meta">{{ t(d).location }}</p>
          <p v-if="t(d).dates" class="special-feature-meta">{{ t(d).dates }}</p>
          <p v-if="d.artist_names?.length" class="special-feature-meta">{{ d.artist_names.join(', ') }}</p>
          <div v-if="t(d).description" v-html="mdGloss(t(d).description)" class="prose" />
          <div v-if="d.images?.length" class="images">
            <figure v-for="(img, i) in d.images" :key="i">
              <img :src="img.url" :alt="img.captions?.[activeLang] ?? ''" loading="lazy" class="detail-img" />
            </figure>
          </div>
        </div>
      </div>

      <!-- Related Video -->
      <div v-if="relatedMedia.length" class="related-media">
        <h2 class="sub-section-title">Related Video</h2>
        <div v-for="(m, i) in relatedMedia" :key="i" class="media-entry">
          <a :href="m.url" target="_blank" rel="noopener" class="media-title">{{ m.title }}</a>
          <p v-if="m.description" class="media-description">{{ m.description }}</p>
        </div>
      </div>

      <!-- Credits -->
      <div v-if="credits.length" class="credits">
        <h2 class="credits-heading">Credits</h2>
        <dl class="credits-list">
          <template v-for="c in credits" :key="c.label">
            <dt>{{ c.label }}</dt>
            <dd :dir="contentDir">{{ c.value }}</dd>
          </template>
        </dl>
        <p v-if="item.mwnf_reference" class="mwnf-ref">
          MWNF Working Number: <strong>{{ item.mwnf_reference }}</strong>
        </p>
      </div>

      <!-- Citation -->
      <div v-if="citation" class="citation">
        <h2 class="credits-heading">Citation</h2>
        <p class="citation-text">{{ citation }}</p>
      </div>

      <!-- On display in (Virtual Exhibitions) -->
      <div v-if="onDisplayInLinks.length" class="on-display-in">
        <h2 class="sub-section-title">On display in</h2>
        <ul class="gallery-list">
          <li v-for="l in onDisplayInLinks" :key="l.to.path">
            <router-link :to="l.to" v-html="mdInline(l.label)" />
          </li>
        </ul>
      </div>

      <!-- Galleries (THG cross-links) -->
      <div v-if="thgGalleryLinks.length" class="galleries">
        <h2 class="sub-section-title">Galleries</h2>
        <ul class="gallery-list">
          <li v-for="g in thgGalleryLinks" :key="g.name">
            <a :href="g.href">{{ g.name }}</a>
          </li>
        </ul>
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
              <img v-if="rel.images?.length" :src="rel.images[0].url" :alt="labelText(rel)" loading="lazy" />
              <div v-else class="item-thumb-placeholder" />
            </div>
            <div class="item-list-info" :dir="contentDir">
              <div class="item-list-name" v-html="mdInline(t(rel).name ?? rel.internal_name ?? rel.id)" />
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

    <!-- Glossary term modal, mirrors legacy's glossary1.php popup -->
    <div v-if="activeGlossaryTerm" class="gloss-modal-overlay" @click.self="closeGlossaryModal">
      <div class="gloss-modal" :dir="contentDir">
        <button class="gloss-modal-close" @click="closeGlossaryModal" aria-label="Close">✕</button>
        <h2 class="gloss-modal-heading">Glossary &amp; Spelling</h2>
        <h3 class="gloss-modal-term">{{ activeGlossaryTerm.spelling }}</h3>
        <p class="gloss-modal-definition">{{ activeGlossaryTerm.definition }}</p>
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

.detail-type-badge {
  display: inline-block;
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: var(--heading);
  border: 1px solid var(--accent);
  padding: 2px 8px;
  margin-bottom: 10px;
  font-family: 'Roboto', sans-serif;
}

.detail-title {
  font-size: 24px;
  font-weight: 400;
  color: var(--heading);
  margin-bottom: 16px;
  line-height: 1.3;
  font-family: 'Roboto', sans-serif;
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
  font-family: 'Roboto', sans-serif;
}
.photo-credit { display: block; }

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
  background: var(--field-bg);
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
.content-section.short-description {
  border-top: none;
  padding-top: 0;
  margin-top: -8px;
}
.short-description-toggle {
  background: none;
  border: none;
  padding: 0;
  margin-bottom: 8px;
  font-size: 13px;
  font-style: italic;
  font-weight: 500;
  color: var(--link);
  text-decoration: underline;
  cursor: pointer;
  font-family: 'Roboto', sans-serif;
}

/* Prose */
.prose { font-size: 14px; line-height: 1.7; color: var(--text); font-family: 'Roboto', sans-serif; }
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
  border-top: 2px solid var(--heading);
}
.credits-heading {
  font-size: 11px;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--heading);
  margin-bottom: 10px;
  font-family: 'Roboto', sans-serif;
}
.credits-list {
  display: grid;
  grid-template-columns: max-content 1fr;
  gap: .3rem 1rem;
  font-size: 12px;
  margin-bottom: 8px;
  font-family: 'Roboto', sans-serif;
}
.credits-list dt { font-weight: 500; color: var(--muted); }
.credits-list dd { color: var(--text); }
.mwnf-ref { font-size: 11px; color: var(--muted); font-family: 'Roboto', sans-serif; }

/* Citation */
.citation { margin-top: 16px; }
.citation-text { font-size: 12px; line-height: 1.6; color: var(--muted); font-family: 'Roboto', sans-serif; }

/* Special Features (monument sub-details) */
.special-features { margin-top: 20px; border-top: 1px solid var(--border); padding-top: 16px; }
.special-feature { margin-bottom: 16px; }
.special-feature-name { font-size: 15px; font-weight: 500; color: var(--heading); margin-bottom: 4px; font-family: 'Roboto', sans-serif; }
.special-feature-meta { font-size: 12px; color: var(--muted); margin: 0 0 4px; font-family: 'Roboto', sans-serif; }

/* Related Video */
.related-media { margin-top: 20px; border-top: 1px solid var(--border); padding-top: 16px; }
.media-entry { margin-bottom: 10px; }
.media-title { font-size: 13px; font-weight: 500; color: var(--nav-active); font-family: 'Roboto', sans-serif; }
.media-description { font-size: 12px; color: var(--muted); margin: 2px 0 0; font-family: 'Roboto', sans-serif; }

/* Galleries (THG cross-links) */
.galleries { margin-top: 20px; border-top: 1px solid var(--border); padding-top: 16px; }
.gallery-list { list-style: none; font-size: 13px; font-family: 'Roboto', sans-serif; }
.gallery-list a { color: var(--nav-active); }

/* Sub-section titles */
.sub-section-title {
  font-size: 12px;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--muted);
  margin-bottom: 10px;
  font-family: 'Roboto', sans-serif;
}

/* Related */
.related { margin-top: 20px; border-top: 1px solid var(--border); padding-top: 16px; }
.related-list { list-style: none; }

.item-type-badge {
  text-transform: uppercase;
  letter-spacing: 0.06em;
  font-size: 10px;
  color: var(--muted);
  font-family: 'Roboto', sans-serif;
}
.item-list-justification {
  font-size: 13px;
  color: var(--muted);
  font-style: italic;
  margin: 2px 0 4px;
  font-family: 'Roboto', sans-serif;
  line-height: 1.4;
}

/* Glossary term links, injected as raw HTML into v-html content.
   Not using var(--link)/a plain border-bottom: --link resolves to the same
   value as --text (#222) site-wide, and a 1px dotted border-bottom renders
   as a barely-visible sub-pixel line at this font size — both left the term
   visually indistinguishable from surrounding text. Underline (which scales
   with font/DPI correctly, unlike border-bottom) plus a color actually
   distinct from body text gives real affordance. */
.detail :deep(.gloss-term) {
  color: var(--heading);
  text-decoration: underline dotted;
  text-decoration-color: var(--accent);
  text-underline-offset: 2px;
  cursor: pointer;
}
.detail :deep(.gloss-term:hover) {
  color: var(--accent);
  text-decoration-style: solid;
}

/* Glossary modal */
.gloss-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding: 40px 16px;
  z-index: 1000;
}
.gloss-modal {
  position: relative;
  background: var(--section-bg, #fff);
  border-top: 4px solid var(--accent);
  max-width: 480px;
  width: 100%;
  padding: 28px 24px;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
}
.gloss-modal-close {
  position: absolute;
  top: 10px;
  right: 12px;
  background: none;
  border: none;
  font-size: 16px;
  line-height: 1;
  cursor: pointer;
  color: var(--muted);
}
.gloss-modal-heading {
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--muted);
  margin-bottom: 12px;
  font-family: 'Roboto', sans-serif;
}
.gloss-modal-term {
  font-size: 20px;
  color: var(--heading);
  margin-bottom: 10px;
  font-family: 'Roboto', sans-serif;
}
.gloss-modal-definition {
  font-size: 14px;
  line-height: 1.7;
  color: var(--text);
  font-family: 'Roboto', sans-serif;
}
</style>
