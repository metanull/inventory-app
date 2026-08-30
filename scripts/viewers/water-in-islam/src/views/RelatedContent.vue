<script setup>
import { computed } from 'vue'
import {
  relatedContent, legacyImage, countryLabelFromCode, mdInline, md,
} from '../composables/useExhibitionData.js'
import { uiLang } from '../composables/useUiStrings.js'
import BackLink from '../components/BackLink.vue'

// Legacy's RelatedContent: the exhibition's reading list, grouped by category
// and ordered inside each group.
//
// The four category NAMES are the one thing the package cannot supply. Legacy
// reads them from `mwnf3_thematic_gallery.related_content_category`, which the
// importer does not carry, so `related_content.json` ships `category_id` alone.
// The names below are that table's English rows, verbatim from the live API
// (`exhibitionRelatedContents[*].categoryName`) — the same class of ported
// legacy constant as the timeline's year-bucket algorithm, and recorded as a
// package gap in README.md rather than pretended away.
const CATEGORY_NAMES = {
  1: 'Further Reading',
  2: 'Related MWNF Content',
  3: 'Related Partner Content',
  4: 'Other Related Content',
}

// Legacy's own display order for the four groups, which is the order its API
// happened to answer in — not ascending id.
const CATEGORY_ORDER = [1, 2, 3, 4]

function text(map) {
  return map?.[uiLang.value] ?? map?.en ?? ''
}

function href(entry) {
  if (entry.document_path) return legacyImage(entry.document_path, 'hi_res')
  return entry.url ?? null
}

const groups = computed(() => {
  const byCategory = new Map()
  for (const entry of relatedContent.value) {
    const bucket = byCategory.get(entry.category_id)
    if (bucket) bucket.push(entry)
    else byCategory.set(entry.category_id, [entry])
  }
  const ids = [
    ...CATEGORY_ORDER.filter(id => byCategory.has(id)),
    ...[...byCategory.keys()].filter(id => !CATEGORY_ORDER.includes(id)).sort(),
  ]
  return ids.map(id => ({
    id,
    name: CATEGORY_NAMES[id] ?? `Category ${id}`,
    entries: [...byCategory.get(id)].sort(
      (a, b) => (a.display_order ?? 0) - (b.display_order ?? 0)
    ),
  }))
})
</script>

<template>
  <div id="related-content-wrapper">
    <BackLink />
    <div id="related-content-container">
      <!-- Kept as a guard, not as this exhibition's normal state. Its five
           entries are pure bibliographies with neither a link nor an uploaded
           document; the importer used to drop those on the floor because
           `collection_media` needs a URL, and now files them on the
           exhibition's `extra.further_readings` instead. An empty page would
           read as a rendering fault, so say it plainly if it ever happens. -->
      <p class="related-content-empty" v-if="!groups.length">
        The reading list for this exhibition is not yet available in the data
        package.
      </p>

      <div class="related-content-category" v-for="group in groups" :key="group.id">
        <div class="related-content-category-header">{{ group.name }}</div>

        <div class="related-content" v-for="entry in group.entries" :key="entry.legacy_id">
          <!-- kind: "text" — the entry IS the bibliography. No link, no title,
               no author; the importer converts the legacy HTML to markdown on
               the way in, so this goes through md() rather than v-html raw. -->
          <div class="further-reading prose" v-if="text(entry.texts)" v-html="md(text(entry.texts))"></div>

          <div class="further-reading prose" v-if="entry.further_reading" v-html="entry.further_reading"></div>

          <div v-if="entry.entity_location || entry.entity_country">
            <span v-if="entry.entity_location">{{ entry.entity_location }}</span>
            <span v-if="entry.entity_location && entry.entity_country">, </span>
            <span v-if="entry.entity_country">{{ countryLabelFromCode(entry.entity_country) }}</span>
          </div>

          <div v-if="text(entry.titles)">
            <a v-if="href(entry)" :href="href(entry)" target="_blank" rel="noopener"
               v-html="mdInline(text(entry.titles))"></a>
            <span v-else v-html="mdInline(text(entry.titles))"></span>
          </div>
          <div v-else-if="href(entry)">
            <a :href="href(entry)" target="_blank" rel="noopener">{{ href(entry) }}</a>
          </div>

          <div v-if="entry.authors || entry.type_resource">
            <span v-if="entry.authors">{{ entry.authors }}</span>
            <span v-if="entry.authors && entry.type_resource">, </span>
            <span v-if="entry.type_resource">{{ entry.type_resource }}</span>
          </div>

          <div class="prose" v-if="text(entry.descriptions)" v-html="md(text(entry.descriptions))"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
#related-content-wrapper {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 100%;
  min-height: 400px;
  color: var(--secondary-text-color);
  background: var(--secondary-color);
}
#related-content-container { width: 70%; padding: 20px 50px 50px; }
#related-content-container a { color: var(--main-color); text-decoration: underline; }
#related-content-container a:hover { background: var(--contrast-color); }

.related-content-empty { font-style: italic; line-height: 1.5; }
.related-content-category { margin-bottom: 30px; }
.related-content-category-header { font-weight: 700; margin-bottom: 10px; }
.related-content { margin-bottom: 14px; line-height: 1.5; }
.further-reading :deep(br) { display: block; content: ''; }

@media only screen and (max-width: 974px) {
  #related-content-container { width: 100%; padding: 20px 30px 50px; }
}
</style>
