<script setup>
import { computed, ref, watch, onMounted, nextTick } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import {
  itemFromUidPath, itemRoute, itemLabel, partnerLabel, countryLabel, partnerById,
  partnerRoute, dynastyById, tr, loadTranslations, translations, defaultLang,
  languageByCode, md, mdInline, mdStrip, itemById, glossaryById,
} from '../composables/useGalleryData.js'
import { tIn, isRtl } from '../composables/useUiStrings.js'
import { termsForItem, linkGlossary, searchGlossary } from '../composables/useGlossary.js'
import { findEvents, eraLabel, roundOutward, timelineCountries, countryIdForCode } from '../composables/useTimeline.js'
import BackLink from '../components/BackLink.vue'

const route = useRoute()
const router = useRouter()

const PORTAL = 'https://www.museumwnf.org'
const ISLAMIC_ART = 'https://islamicart.museumwnf.org'
const BAROQUE_ART = 'https://baroqueart.museumwnf.org'
const SHARING_HISTORY = 'https://sharinghistory.museumwnf.org'

const PROJECT_NAMES = {
  ISL: 'Discover Islamic Art',
  EPM: 'Explore Islamic Art Collections',
  DBA: 'Discover Baroque Art',
  BAR: 'Discover Baroque Art',
  AWE: 'Sharing History',
  awe: 'Sharing History',
  DCA: 'Discover Carpet Art',
  DGA: 'Discover Glass Art',
}

const item = computed(() => itemFromUidPath(route.params.uid))
const lang = computed(() => route.params.language ?? defaultLang)
const rtl = computed(() => isRtl(lang.value))
const ready = ref(false)

// The sheet's language list is the *record's*, not the site's: a borrowed item
// may carry de/el/tr that the gallery UI never offers, and may be missing one
// of the gallery's four. Legacy built the same list from `i18nLinks`.
const recordLanguages = computed(() => item.value?.languages ?? [defaultLang])

function languageName(code) {
  return languageByCode.value.get(code)?.names?.[code] ?? code.toUpperCase()
}

// Attribution names are language-independent, but the importer files
// `author` / `copy_editor` for EPM records on the Arabic row only (a known
// gap, recorded in the exporter's README). Legacy printed them on every
// sheet, so when the selected language has neither, fall back to the first
// of the record's other languages that does. This is the one place the
// viewer reads across languages, and only for proper names.
const attribution = ref({})

async function resolveAttribution() {
  attribution.value = {}
  const own = tr('items', item.value.id, lang.value)
  if (own.author || own.copy_editor) return
  for (const code of recordLanguages.value) {
    if (code === lang.value) continue
    await loadTranslations('items', code)
    const row = translations('items', code)[item.value.id] ?? {}
    if (row.author || row.copy_editor) {
      attribution.value = { author: row.author, copy_editor: row.copy_editor, from: code }
      return
    }
  }
}

async function load() {
  ready.value = false
  if (!item.value) { router.replace({ name: 'error' }); return }
  await Promise.all([
    loadTranslations('items', lang.value),
    loadTranslations('glossary', lang.value),
    loadTranslations('dynasties', lang.value),
    loadTranslations('partners', lang.value),
  ])
  await resolveAttribution()
  ready.value = true
  await nextTick()
  bindGlossaryLinks()
}

onMounted(load)
watch(() => [route.params.uid, route.params.language].join('|'), load)

const sheet = computed(() => (item.value ? tr('items', item.value.id, lang.value) : {}))
const partner = computed(() => (item.value ? partnerById.value.get(item.value.partner_id) : null))

// ── Glossary ───────────────────────────────────────────────────────────────

const terms = computed(() => (item.value ? termsForItem(item.value, lang.value) : []))
const openTerm = ref(null)

const description = computed(() =>
  linkGlossary(md(sheet.value.description), terms.value, lang.value)
)
const shortDescription = computed(() =>
  linkGlossary(md(sheet.value.short_description), terms.value, lang.value)
)

// Legacy shows both descriptions when both exist, with the short one collapsed
// behind a toggle; when only one exists it is relabelled plain "Description:".
// EPM records are the common case of the latter — their short text is the only
// text there is.
const bothDescriptions = computed(() =>
  Boolean(sheet.value.description) && Boolean(sheet.value.short_description)
)
const showShort = ref(false)
watch(bothDescriptions, both => { showShort.value = !both }, { immediate: true })

function bindGlossaryLinks() {
  for (const el of document.querySelectorAll('.glossary-link')) {
    el.onclick = (event) => {
      event.preventDefault()
      openTerm.value = terms.value.find(t => t.id === el.dataset.term) ?? null
      closeOthers('glossary-word')
    }
  }
}
watch([description, shortDescription, showShort], () => nextTick(bindGlossaryLinks))

// ── Field list ─────────────────────────────────────────────────────────────
//
// Order and labels are the legacy sheet's, field for field
// (DatabaseItem.vue's `objectData`). Empty values are dropped, as legacy's
// `filterData` did.
//
// The three notice fields (`notice`, `notice_b`, `notice_c`) are the only
// legacy sheet fields with nothing behind them: they were never imported, so
// they are absent rather than faked. `scriber`, `binding_desc` and `workshop`
// are imported and exported like every other field — an earlier version of this
// comment said they had no counterpart in the inventory model, and that claim
// is what kept the three rows off the sheet and propagated into the websites
// built from it.
//
// They are sparse by nature. `scriber` and `binding_desc` exist only on
// manuscript records, so a dataset with no manuscripts carries none and the
// rows never appear — the same empty-value filtering every other row gets, not
// a reason to leave them out.
const L = (key) => tIn(lang.value, key)

const fields = computed(() => {
  const s = sheet.value
  const it = item.value
  if (!it) return []

  const dynastyNames = (it.dynasty_ids ?? [])
    .map(id => translations('dynasties', lang.value)[id]?.name
      ?? translations('dynasties', defaultLang)[id]?.name
      ?? '')
    .filter(Boolean)
    .join(', ')

  const rows = [
    ['name', L('objName'), mdInline(s.name)],
    ['aka', L('objAKA'), mdInline(s.alternate_name)],
    ['location', L('objLocation'), [s.location, countryLabel(it.country_id)].filter(Boolean).join(', ')],
    ['museum', L('objHoldingMuseum'), null],           // rendered as a profile link
    ['originalOwner', L('objOriginalOwner'), mdInline(s.initial_owner)],
    ['currentOwner', L('objCurrentOwner'), mdInline(s.owner)],
    ['date', L('objDate'), mdInline(s.dates)],
    ['artist', L('objArtist'), (it.artist_names ?? []).join(', ')],
    ['scribe', L('objScribe'), mdInline(s.scriber)],
    ['workshop', L('objWorkshop'), mdInline(s.workshop)],
    ['type', L('objType'), mdInline(s.type)],
    ['inventoryNumber', L('objInventoryNumber'), it.owner_reference],
    ['materials', L('objMaterials'), (s.materials ?? []).join('; ')],
    ['dimensions', L('objDimensions'), mdInline(s.dimensions)],
    ['dynasty', L('objDynasty'), dynastyNames],
    ['production', L('objPlaceOfProduction'), mdInline(s.place_of_production)],
    ['provenance', L('objProvenance'), mdInline(s.provenance)],
    ['binding', L('objBinding'), mdInline(s.binding_desc)],
    ['description', L('objDescription'), description.value],
    ['shortDescription', bothDescriptions.value ? L('objShortDesc_show') : L('objDescription'), shortDescription.value],
    ['catalogue', L('objDigitLink'), s.linkcatalogs],
    ['obtention', L('objObtentionMethod'), mdInline(s.obtention)],
    ['datation', L('objDatationMethod'), mdInline(s.method_for_datation)],
    ['provenanceMethod', L('objProvenanceMethod'), mdInline(s.method_for_provenance)],
    ['bibliography', L('objBibliography'), md(s.bibliography)],
  ]
  return rows.filter(([key, , value]) => key === 'museum' ? Boolean(partner.value) : Boolean(value))
})

// The citation block. Author attribution comes from the record's translation;
// EPM items file `author`/`copy_editor` only on the Arabic row (a known
// importer-side gap), so an English EPM sheet can legitimately show neither.
const preparedBy = computed(() => sheet.value.author ?? attribution.value.author ?? '')
const copyEditedBy = computed(() => sheet.value.copy_editor ?? attribution.value.copy_editor ?? '')

const citation = computed(() => {
  const s = sheet.value
  return [
    [L('objPreparedBy'), preparedBy.value],
    [L('objCopyeditedBy'), copyEditedBy.value],
    [L('objTranslationBy'), s.translator],
    [L('objTransCopyeditedBy'), s.translation_copy_editor],
  ].filter(([, v]) => Boolean(v))
})

const projectName = computed(() => PROJECT_NAMES[item.value?.project_key] ?? item.value?.project_key ?? '')

// ── Photos ─────────────────────────────────────────────────────────────────

const photos = computed(() =>
  [...(item.value?.images ?? [])].sort((a, b) => (a.display_order ?? 0) - (b.display_order ?? 0))
)
const currentPhoto = ref(0)
watch(item, () => { currentPhoto.value = 0 })
const lightbox = ref(false)

function slide(direction) {
  if (!photos.value.length) return
  const n = photos.value.length
  currentPhoto.value = (currentPhoto.value + (direction === 'right' ? 1 : -1) + n) % n
}

const currentCaption = computed(() => {
  const p = photos.value[currentPhoto.value]
  if (!p) return ''
  return p.captions?.[lang.value] ?? p.captions?.[defaultLang] ?? ''
})

// ── Related content popups ─────────────────────────────────────────────────

const openPopup = ref(null) // 'timeline' | 'glossaryTool' | `dynasty:<id>` | null
function togglePopup(which) {
  openPopup.value = openPopup.value === which ? null : which
  openTerm.value = null
}
function closeOthers() { openPopup.value = null }

// Dynasties with actual history text, as legacy filtered them.
const dynastyEntries = computed(() =>
  (item.value?.dynasty_ids ?? [])
    .map(id => {
      const t = translations('dynasties', lang.value)[id] ?? translations('dynasties', defaultLang)[id] ?? {}
      return { id, record: dynastyById.value.get(id), ...t }
    })
    .filter(d => d.history)
)

// "Timeline for this item"
const timelineCountry = ref('')
watch(item, it => { timelineCountry.value = it ? (countryCodeOf(it.country_id) ?? 'all') : 'all' }, { immediate: true })

function countryCodeOf(countryId) {
  for (const [code] of timelineCountries.value) {
    if (countryIdForCode(code) === countryId) return code
  }
  return null
}

const itemRange = computed(() => roundOutward(item.value?.start_date, item.value?.end_date))
const itemEvents = computed(() => {
  const [from, to] = itemRange.value
  if (from == null) return []
  return findEvents({ countryCode: timelineCountry.value, start: from, end: to })
})

// Glossary tool
const glossaryInput = ref('')
const glossaryMatches = computed(() => searchGlossary(glossaryInput.value, lang.value))
const selectedGlossary = ref(null)

// ── Cross-references (decision Q3) ─────────────────────────────────────────
//
// None of these become a constructed URL. The package carries identity plus
// whatever metadata the import had; a reference resolves to a link only where
// the data itself supplies one:
//
//   - related items with `in_package: true` open locally;
//   - related items outside the package render as an identified reference;
//   - `gallery_references` link to the sibling site's *home page* when the
//     import carried a `legacy_host`, never to a deep item path this viewer
//     would have to invent;
//   - the source-database link legacy built from a hand-maintained table has
//     no counterpart at all, so the sheet names the source and its dbUid and
//     stops there.

const relatedInPackage = computed(() =>
  (item.value?.related_items ?? [])
    .filter(r => r.in_package && itemById.value.has(r.id))
    .map(r => ({ ...r, item: itemById.value.get(r.id) }))
)
const relatedOutside = computed(() =>
  (item.value?.related_items ?? []).filter(r => !r.in_package || !itemById.value.has(r.id))
)
const byName = (a, b) => (a.name ?? '').localeCompare(b.name ?? '')
const galleryRefs = computed(() =>
  (item.value?.gallery_references ?? []).filter(g => g.kind === 'gallery').sort(byName)
)
// Legacy suppressed every exhibition link whose host was
// exhibitions.museumwnf.org (`DatabaseItem.vue` skips them outright). Decision
// Q3 says outbound links must not be dropped, so they are shown here.
const exhibitionRefs = computed(() =>
  (item.value?.gallery_references ?? []).filter(g => g.kind === 'exhibition').sort(byName)
)

function justification(r) {
  return r.justifications?.[lang.value] ?? r.justifications?.[defaultLang] ?? ''
}

// The "Explore Islamic Art Collections" notice legacy printed on EPM records.
const showEiacNotice = computed(() => item.value?.project_key === 'EPM')
const languageNameList = computed(() => recordLanguages.value.map(languageName).join(', '))

function setLanguage(code) {
  if (code === lang.value) return
  router.push(itemRoute(item.value, code))
}

// Legacy's "As PDF (including images)" was the browser's own print dialog.
function printSheet() {
  window.print()
}
</script>

<template>
  <div id="database-page-wrapper" v-if="item">
    <div id="languages">
      <button
        v-for="code in recordLanguages"
        :key="code"
        class="languages-button"
        :class="{ 'language-selected': code === lang }"
        @click="setLanguage(code)"
      >{{ languageName(code) }}</button>
    </div>

    <BackLink />

    <div v-if="!ready" class="loader">Loading…</div>

    <div v-else id="database-object-wrapper" :dir="rtl ? 'rtl' : 'ltr'">
      <div id="photo-info-wrapper">
        <!-- Photos -->
        <div id="photo-container">
          <div class="photo" v-if="photos.length" @click="lightbox = true">
            <img :src="photos[currentPhoto].url" :alt="itemLabel(item)" />
          </div>
          <div class="photo photo-empty" v-else></div>

          <div id="thumbnail-container" v-if="photos.length > 1">
            <div
              v-for="(pic, index) in photos"
              :key="pic.url"
              class="thumbnail"
              :class="{ active: index === currentPhoto }"
              @click="currentPhoto = index"
            >
              <img :src="pic.url" :alt="`${itemLabel(item)} — ${index + 1}`" />
              <div class="tooltip-text" v-if="pic.photographer || pic.copyright">
                <div v-if="pic.photographer">{{ tIn(lang, 'photograph') }}: {{ pic.photographer }}</div>
                <div v-if="pic.copyright">© {{ pic.copyright }}</div>
              </div>
            </div>
          </div>
          <div class="current-caption" v-if="currentCaption">{{ currentCaption }}</div>

          <div id="links-container">
            <!-- Decision Q3: legacy's `remote-object` URL came from a
                 hand-maintained table with no counterpart in the new model, so
                 the source is named, not linked. -->
            <p id="source-reference">
              <span class="project-chip" :class="`project-${item.project_key}`">{{ item.project_key }}</span>
              {{ tIn(lang, 'sourceDatabase') }}: {{ projectName }}
            </p>
            <p id="source-uid"><code>{{ item.backward_compatibility }}</code></p>
            <p id="add-collection-link">
              <a :href="`${PORTAL}/mycollection/index.php`" target="_blank" rel="noopener">
                ↗ {{ tIn(lang, 'addToCollection') }}
              </a>
            </p>
          </div>
        </div>

        <!-- Sheet -->
        <div id="info-container">
          <div id="info-eiac" v-if="showEiacNotice">
            {{ tIn(lang, 'note_EIAC') }} <strong><em>{{ languageNameList }}</em></strong>
          </div>

          <div v-for="[key, label, value] in fields" :key="key">
            <p
              class="info-label"
              :class="{ pointer: key === 'shortDescription' && bothDescriptions }"
              @click="key === 'shortDescription' && bothDescriptions ? (showShort = !showShort) : null"
            >{{ label }}</p>

            <p class="info info-link" v-if="key === 'museum'">
              <RouterLink :to="partnerRoute(partner, lang)">{{ partnerLabel(item.partner_id) }}</RouterLink>
            </p>
            <p class="info" v-else-if="key === 'catalogue'">
              <a :href="value" target="_blank" rel="noopener">{{ value }}</a>
            </p>
            <div
              class="info"
              v-else-if="key !== 'shortDescription' || showShort"
              v-html="value"
            ></div>
          </div>

          <div id="citation-block">
            <p class="info-label">{{ tIn(lang, 'citation') }}</p>
            <p id="info-citation">
              <span v-if="preparedBy">{{ preparedBy }} </span>
              "<span v-html="mdInline(sheet.name)"></span>" {{ tIn(lang, 'in') }}
              <span id="info-project-name">{{ projectName }}</span>,
              Museum With No Frontiers, {{ new Date().getFullYear() }}.
            </p>
            <div class="info-authors" v-for="[label, value] in citation" :key="label">
              <span class="info-authors-label">{{ label }} </span>{{ value }}
            </div>
            <p class="info-working-number" v-if="item.mwnf_reference">
              {{ tIn(lang, 'objWorkingNumber') }} {{ item.mwnf_reference }}
            </p>
          </div>
        </div>
      </div>

      <!-- ── Related content ────────────────────────────────────────────── -->
      <div id="related-content-container">
        <p class="related-header">{{ tIn(lang, 'relatedContent').toUpperCase() }}</p>
        <p id="related-description">{{ tIn(lang, 'relatedContentDescription') }}</p>

        <!-- Related items inside this gallery -->
        <div v-if="relatedInPackage.length">
          <p class="related-sub">{{ tIn(lang, 'relatedObjects') }}</p>
          <div id="related-objects-wrapper">
            <RouterLink
              v-for="r in relatedInPackage"
              :key="r.id"
              class="related-objects"
              :to="itemRoute(r.item, lang)"
            >
              <img v-if="r.item.images?.length" :src="r.item.images[0].url" :alt="itemLabel(r.item)" />
              <div v-else class="related-thumb-empty"></div>
              <div class="related-tooltip">
                <div>{{ itemLabel(r.item) }}, {{ countryLabel(r.item.country_id) }}</div>
                <div v-if="justification(r)">{{ mdStrip(justification(r)) }}</div>
              </div>
            </RouterLink>
          </div>
        </div>

        <!-- Related items this gallery does not ship. The link is not dropped —
             it is shown as the reference it is, awaiting a resolver. -->
        <div v-if="relatedOutside.length">
          <p class="related-sub">{{ tIn(lang, 'relatedItems') }}</p>
          <ul class="reference-list">
            <li v-for="r in relatedOutside" :key="r.id">
              <span class="project-chip" :class="`project-${r.project_key}`">{{ r.project_key }}</span>
              <code>{{ r.backward_compatibility }}</code>
              <span class="unresolved-note">not in this gallery</span>
            </li>
          </ul>
        </div>

        <!-- Artistic Introduction — an Islamic Art site feature legacy linked
             from ISL/EPM sheets. -->
        <div v-if="item.project_key === 'ISL' || item.project_key === 'EPM'">
          <p class="related-line">
            <a :href="`${ISLAMIC_ART}/gai/ISL/`" target="_blank" rel="noopener">↗ {{ tIn(lang, 'artisticIntroduction') }}</a>
          </p>
        </div>

        <!-- Timeline for this item -->
        <div v-if="itemEvents.length || itemRange[0] != null">
          <p class="related-line clickable" @click="togglePopup('timeline')">➤ {{ tIn(lang, 'timeline') }}</p>
          <div class="popout" v-if="openPopup === 'timeline'" dir="ltr">
            <div class="popout-close" @click="openPopup = null">✕</div>
            <div class="popout-title">Timeline</div>
            <div class="popout-option">
              <label>See events for:</label>
              <select v-model="timelineCountry">
                <option v-for="c in timelineCountries" :key="c[0]" :value="c[0]">{{ c[1] }}</option>
              </select>
              <RouterLink
                class="popout-full-link"
                :to="{ name: 'timeline-results', query: { c: timelineCountry, start: itemRange[0], end: itemRange[1] } }"
              >➤ Click here to begin a full Timeline search</RouterLink>
            </div>
            <div class="popout-scroll">
              <div class="popout-subheader">
                {{ timelineCountries.find(c => c[0] === timelineCountry)?.[1] }},
                {{ eraLabel(itemRange[0]) }} – {{ eraLabel(itemRange[1]) }}
              </div>
              <div v-if="!itemEvents.length" class="popout-empty">No events recorded for this period.</div>
              <div class="timeline-event" v-for="event in itemEvents" :key="event.id">
                <div class="timeline-date">{{ eraLabel(event.year_from) }}</div>
                <div v-html="md(event.text.description)"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Glossary tool -->
        <div>
          <p class="related-line clickable" @click="togglePopup('glossaryTool')">➤ {{ tIn(lang, 'glossary') }}</p>
          <div class="popout" v-if="openPopup === 'glossaryTool'">
            <div class="popout-close" @click="openPopup = null">✕</div>
            <div class="popout-title">{{ tIn(lang, 'glossary') }}</div>
            <div class="popout-instructions">{{ tIn(lang, 'glossaryInstructions') }}</div>
            <input class="glossary-input" type="text" v-model="glossaryInput" />
            <ul class="glossary-list" v-if="glossaryInput && !selectedGlossary">
              <li
                v-for="hit in glossaryMatches"
                :key="hit.id"
                @click="selectedGlossary = hit; glossaryInput = hit.spelling"
              >{{ hit.spelling }}</li>
            </ul>
            <div class="popout-scroll" v-if="selectedGlossary">
              <p class="info-label">{{ tIn(lang, 'definition') }}</p>
              <div v-html="md(selectedGlossary.definition)"></div>
            </div>
          </div>
        </div>

        <!-- Dynasties -->
        <div v-if="dynastyEntries.length">
          <p class="related-sub">{{ tIn(lang, 'islamicDynasties') }}</p>
          <div v-for="dynasty in dynastyEntries" :key="dynasty.id">
            <p class="related-line clickable" @click="togglePopup(`dynasty:${dynasty.id}`)">➤ {{ dynasty.name }}</p>
            <div class="popout" v-if="openPopup === `dynasty:${dynasty.id}`">
              <div class="popout-close" @click="openPopup = null">✕</div>
              <div class="popout-title">{{ tIn(lang, 'theDynasties') }}</div>
              <div class="popout-scroll">
                <div class="dynasty-name">{{ dynasty.name }}</div>
                <p v-if="dynasty.also_known_as">{{ dynasty.also_known_as }}</p>
                <p v-if="dynasty.area">{{ dynasty.area }}</p>
                <p v-if="dynasty.record?.from_ad != null">
                  AH {{ dynasty.record.from_ah }}–{{ dynasty.record.to_ah }} /
                  AD {{ dynasty.record.from_ad }}–{{ dynasty.record.to_ad }}
                </p>
                <div v-html="md(dynasty.history)"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Audio / video -->
        <div v-if="item.media?.length">
          <p class="related-sub">{{ tIn(lang, 'relatedAudioVideos') }}</p>
          <p class="related-line" v-for="file in item.media" :key="file.url">
            <a :href="file.url" target="_blank" rel="noopener">↗ {{ file.title ?? file.url }}</a>
          </p>
        </div>

        <!-- On display in -->
        <div v-if="galleryRefs.length || exhibitionRefs.length">
          <p class="related-header">{{ tIn(lang, 'onDisplayIn') }}</p>

          <div v-if="exhibitionRefs.length">
            <p class="related-sub">{{ tIn(lang, 'exhibitions') }}</p>
            <p class="related-line" v-for="ref in exhibitionRefs" :key="ref.id">
              <a v-if="ref.legacy_host" :href="ref.legacy_host" target="_blank" rel="noopener">↗ {{ ref.name }}</a>
              <span v-else>{{ ref.name }} <span class="unresolved-note">link pending</span></span>
            </p>
          </div>

          <div v-if="galleryRefs.length">
            <p class="related-sub">{{ tIn(lang, 'galleries') }}</p>
            <p class="related-line" v-for="ref in galleryRefs" :key="ref.id">
              <a v-if="ref.legacy_host" :href="ref.legacy_host" target="_blank" rel="noopener">↗ {{ ref.name }}</a>
              <span v-else>{{ ref.name }} <span class="unresolved-note">link pending</span></span>
            </p>
          </div>
        </div>

        <!-- Search related database -->
        <div>
          <p class="related-header">{{ tIn(lang, 'searchRelatedDatabase') }}</p>
          <p class="related-line" v-if="item.project_key === 'ISL' || item.project_key === 'EPM'">
            <a :href="`${ISLAMIC_ART}/database.php`" target="_blank" rel="noopener">↗ Discover Islamic Art</a>
          </p>
          <p class="related-line" v-if="item.project_key === 'DBA' || item.project_key === 'BAR'">
            <a :href="`${BAROQUE_ART}/database.php`" target="_blank" rel="noopener">↗ Discover Baroque Art</a>
          </p>
          <p class="related-line" v-if="item.project_key === 'AWE' || item.project_key === 'awe'">
            <a :href="`${SHARING_HISTORY}/database.php`" target="_blank" rel="noopener">↗ Sharing History</a>
          </p>

          <p class="related-header">{{ tIn(lang, 'goToFullSearch') }}</p>
          <p class="related-line">
            <a :href="`${PORTAL}/database_searchform.php`" target="_blank" rel="noopener">↗ {{ tIn(lang, 'overallDatabase') }}</a>
          </p>
        </div>

        <div>
          <p class="related-header">{{ tIn(lang, 'download') }}</p>
          <p class="related-line clickable" @click="printSheet()">➤ {{ tIn(lang, 'asPDF') }}</p>
        </div>
      </div>
    </div>

    <!-- Glossary word popup -->
    <div class="glossary-popup" v-if="openTerm" :dir="rtl ? 'rtl' : 'ltr'">
      <div class="popout-close" @click="openTerm = null">✕</div>
      <div class="popout-title">{{ tIn(lang, 'glossary') }}</div>
      <div class="popout-scroll">
        <div class="glossary-word">{{ openTerm.word }}</div>
        <div v-html="md(openTerm.definition)"></div>
      </div>
    </div>

    <!-- Lightbox -->
    <div id="lightbox-container" v-if="lightbox" @click="lightbox = false">
      <button class="lightbox-control left" v-if="photos.length > 1" @click.stop="slide('left')">‹</button>
      <img :src="photos[currentPhoto].url" :alt="itemLabel(item)" />
      <button class="lightbox-control right" v-if="photos.length > 1" @click.stop="slide('right')">›</button>
    </div>
  </div>
</template>

<style scoped>
#database-page-wrapper { background: #fff; width: 100%; min-height: 400px; }

#languages { display: flex; flex-wrap: wrap; gap: 2px; background: var(--background-color); padding: 6px 20px; }
.languages-button {
  background: none;
  border: 1px solid var(--theme-medium);
  color: var(--theme-dark);
  font-family: inherit;
  font-size: 13px;
  padding: 3px 10px;
  cursor: pointer;
}
.languages-button.language-selected { background: var(--theme-medium-dark); color: #fff; border-color: var(--theme-medium-dark); }

#database-object-wrapper { padding: 0 20px 30px; }
#photo-info-wrapper { display: flex; gap: 26px; align-items: flex-start; }
#photo-container { flex: 0 0 42%; max-width: 42%; }
.photo { cursor: zoom-in; background: var(--background-color); }
.photo img { width: 100%; display: block; }
.photo-empty { height: 320px; background: repeating-linear-gradient(45deg, #f6ded7, #f6ded7 10px, #f0d0c7 10px, #f0d0c7 20px); }
#thumbnail-container { display: flex; flex-wrap: wrap; gap: 6px; padding-top: 8px; }
.thumbnail { position: relative; width: 64px; height: 64px; cursor: pointer; border: 2px solid transparent; }
.thumbnail.active { border-color: var(--theme-medium-dark); }
.thumbnail img { width: 100%; height: 100%; object-fit: cover; display: block; }
.tooltip-text {
  display: none;
  position: absolute;
  bottom: 100%;
  inset-inline-start: 0;
  background: rgba(0,0,0,0.8);
  color: #fff;
  font-size: 11px;
  padding: 6px;
  width: 200px;
  z-index: 20;
}
.thumbnail:hover .tooltip-text { display: block; }
.current-caption { font-size: 13px; font-style: italic; color: #555; padding-top: 6px; }

#links-container { padding-top: 14px; font-size: 14px; }
#links-container p { margin-bottom: 6px; }
#source-uid code { font-size: 12px; color: #666; word-break: break-all; }
#add-collection-link a { color: var(--link-blue); }

#info-container { flex: 1; min-width: 0; padding-top: 14px; }
#info-eiac {
  background: var(--background-color);
  padding: 10px 12px;
  margin-bottom: 14px;
  font-size: 13px;
}
.info-label { font-weight: 700; color: var(--theme-dark); margin-top: 12px; }
.info-label.pointer { cursor: pointer; text-decoration: underline; }
.info { line-height: 1.5; }
.info :deep(p) { margin-bottom: 8px; }
.info :deep(em) { font-style: italic; }
.info :deep(.glossary-link) { color: var(--theme-medium-dark); text-decoration: underline dotted; cursor: pointer; }
.info-link a { color: var(--link-blue); }

#citation-block { margin-top: 22px; padding-top: 12px; border-top: 1px solid var(--theme-light); font-size: 14px; }
.info-authors-label { font-weight: 700; }
.info-working-number { margin-top: 8px; color: #555; }

#related-content-container { margin-top: 30px; border-top: 3px solid var(--theme-medium); padding-top: 16px; }
.related-header {
  font-weight: 700;
  color: var(--theme-dark);
  font-size: 16px;
  margin-top: 18px;
  border-bottom: 1px solid var(--theme-light);
}
.related-sub { font-weight: 700; margin-top: 12px; }
#related-description { font-size: 13px; color: #555; margin-top: 6px; }
.related-line { margin-top: 6px; }
.related-line a { color: var(--link-blue); }
.related-line.clickable { color: var(--theme-medium-dark); cursor: pointer; }
.related-line.clickable:hover { text-decoration: underline; }

#related-objects-wrapper { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
.related-objects { position: relative; width: 110px; }
.related-objects img { width: 110px; height: 110px; object-fit: cover; display: block; }
.related-thumb-empty { width: 110px; height: 110px; background: var(--background-color); }
.related-tooltip {
  display: none;
  position: absolute;
  top: 100%;
  inset-inline-start: 0;
  width: 240px;
  background: rgba(0,0,0,0.85);
  color: #fff;
  font-size: 12px;
  padding: 8px;
  z-index: 30;
}
.related-objects:hover .related-tooltip { display: block; }

.reference-list { list-style: none; margin-top: 6px; }
.reference-list li { padding: 3px 0; font-size: 13px; display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
.reference-list code { color: #555; }
.unresolved-note { color: #8a8a8a; font-style: italic; font-size: 12px; }

.popout, .glossary-popup {
  position: relative;
  background: #fff;
  border: 2px solid var(--theme-medium-dark);
  margin: 8px 0 14px;
  max-width: 640px;
}
.glossary-popup { position: fixed; inset-inline-end: 24px; bottom: 24px; width: 380px; z-index: 200; box-shadow: 0 4px 16px rgba(0,0,0,0.35); }
.popout-close {
  position: absolute;
  top: 4px;
  inset-inline-end: 8px;
  cursor: pointer;
  color: #fff;
  font-size: 16px;
  z-index: 2;
}
.popout-title { background: var(--theme-dark); color: #fff; padding: 6px 12px; font-weight: 700; }
.popout-instructions { padding: 8px 12px; font-size: 13px; }
.popout-option { padding: 8px 12px; display: flex; flex-wrap: wrap; gap: 8px; align-items: center; font-size: 13px; }
.popout-option select { font-family: inherit; padding: 3px; }
.popout-full-link { color: var(--link-blue); }
.popout-scroll { max-height: 320px; overflow: auto; padding: 10px 12px; font-size: 14px; }
.popout-subheader { font-weight: 700; margin-bottom: 8px; }
.popout-empty { color: #777; font-style: italic; }
.timeline-event { display: flex; gap: 10px; padding: 5px 0; border-bottom: 1px solid #eee; }
.timeline-date { flex: 0 0 90px; font-weight: 700; }
.dynasty-name { font-weight: 700; font-size: 16px; margin-bottom: 6px; }
.glossary-word { font-weight: 700; font-size: 16px; margin-bottom: 6px; }
.glossary-input { width: calc(100% - 24px); margin: 0 12px 8px; padding: 5px; font-family: inherit; border: 1px solid var(--theme-medium); }
.glossary-list { list-style: none; margin: 0 12px 10px; max-height: 180px; overflow: auto; border: 1px solid var(--theme-light); }
.glossary-list li { padding: 4px 8px; cursor: pointer; }
.glossary-list li:hover { background: var(--background-color); }

#lightbox-container {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 300;
}
#lightbox-container img { max-width: 88vw; max-height: 88vh; }
.lightbox-control {
  background: none;
  border: none;
  color: #fff;
  font-size: 60px;
  cursor: pointer;
  padding: 0 20px;
}

@media only screen and (max-width: 849px) {
  #photo-info-wrapper { flex-direction: column; }
  #photo-container { max-width: 100%; flex: none; width: 100%; }
  .glossary-popup { inset-inline: 12px; width: auto; }
}
</style>
