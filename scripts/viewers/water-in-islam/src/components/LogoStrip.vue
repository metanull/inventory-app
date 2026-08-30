<script setup>
import { computed } from 'vue'
import { exhibition } from '../composables/useExhibitionData.js'
import { uiLang, t } from '../composables/useUiStrings.js'

// Legacy's LogosComponent: sponsor logos grouped by category, each group under
// its `footer_logo_section_<categoryId>` heading from the i18n catalogue, each
// logo linked to its sponsor and captioned on hover with its label.
//
// Three rules, all legacy's:
//   * category 0 is "Header" and belongs in the page header, not here —
//     App.vue renders it under `header_logo_section_1`.
//   * a logo with `visible: false` is dropped. The package ships hidden logos
//     rather than filtering them, because which of them to show is a property
//     of the page, not of the data.
//   * a category with nothing visible left in it renders no heading either.
const groups = computed(() => {
  const byCategory = new Map()
  for (const logo of exhibition.logos ?? []) {
    if (logo.visible === false) continue
    if (Number(logo.category_id) === 0) continue
    const key = logo.category_id ?? 0
    const bucket = byCategory.get(key)
    if (bucket) bucket.push(logo)
    else byCategory.set(key, [logo])
  }
  return [...byCategory.entries()]
    .sort((a, b) => a[0] - b[0])
    .map(([categoryId, logos]) => ({
      categoryId,
      // The catalogue key is authoritative; `category` is the legacy category
      // name and stands in when the catalogue has no row for that slot.
      heading: headingFor(categoryId, logos[0]),
      logos: [...logos].sort((a, b) => (a.display_order ?? 0) - (b.display_order ?? 0)),
    }))
})

function headingFor(categoryId, logo) {
  const key = `footer_logo_section_${categoryId}`
  const label = t(key)
  return label === key ? (logo.category ?? '') : label
}

function caption(logo) {
  return logo.labels?.[uiLang.value] ?? logo.labels?.en ?? ''
}

function altFor(logo) {
  return logo.alt_texts?.[uiLang.value] ?? logo.alt_texts?.en ?? logo.alt_text ?? caption(logo)
}
</script>

<template>
  <div id="logos-container" v-if="groups.length">
    <div class="logos-categories" v-for="group in groups" :key="group.categoryId">
      <div class="logo-category-header" v-if="group.heading">{{ group.heading }}</div>
      <div class="logos">
        <div class="logo-wrapper" v-for="logo in group.logos" :key="logo.id ?? logo.image_url">
          <a v-if="logo.url" :href="logo.url" target="_blank" rel="noopener" class="tooltip">
            <img :src="logo.image_url" :alt="altFor(logo)" />
            <span class="tooltip-text" v-if="caption(logo)">{{ caption(logo) }}</span>
          </a>
          <span v-else class="tooltip">
            <img :src="logo.image_url" :alt="altFor(logo)" />
            <span class="tooltip-text" v-if="caption(logo)">{{ caption(logo) }}</span>
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
#logos-container {
  width: 100%;
  padding: 20px 0 25px;
  color: var(--secondary-text-color);
  background: var(--secondary-color);
  border-bottom: 5px solid var(--secondary-color);
}
.logos-categories { padding: 10px 20px; text-align: center; }
.logo-category-header { font-weight: 700; margin-bottom: 10px; }
.logos {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: center;
  gap: 30px;
}
.logo-wrapper img { max-height: 70px; max-width: 240px; object-fit: contain; }

.tooltip { position: relative; display: inline-block; }
.tooltip-text {
  visibility: hidden;
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  z-index: 10;
  width: max-content;
  max-width: 260px;
  padding: 6px 8px;
  font-size: 13px;
  color: var(--main-text-color);
  background: var(--main-color);
}
.tooltip:hover .tooltip-text { visibility: visible; }
</style>
