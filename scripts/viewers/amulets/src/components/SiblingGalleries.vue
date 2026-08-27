<script setup>
import { ref, onMounted } from 'vue'
import { pickSiblings, siblingUrl, legacyImage } from '../composables/useGalleryData.js'
import { uiLang } from '../composables/useUiStrings.js'

// The home page's "Visit MWNF Galleries" strip plus the other MWNF virtual
// museums, reproducing legacy's FeaturedGalleries.vue.
//
// Decision Q3 — these are *reference objects*, not resolved links. Legacy read
// its cross-site URLs from a hand-maintained table that has no counterpart in
// the new model. The exporter therefore ships identity plus whatever metadata
// the import carried, and this component renders a tile either way: an anchor
// when `legacy_host` came across, a plain, non-clickable tile when it did not.
// Nothing here constructs a URL.
const GALLERIES = 'https://galleries.museumwnf.org'
const MUSEUMS = [
  { name: 'Discover Islamic Art', url: 'https://islamicart.museumwnf.org/', accent: 'var(--dia-yellow)', fg: '#222' },
  { name: 'Discover Baroque Art', url: 'https://baroqueart.museumwnf.org/', accent: 'var(--dba-blue)', fg: '#fff' },
  { name: 'Sharing History', url: 'https://sharinghistory.museumwnf.org/', accent: 'var(--sh-red)', fg: '#fff' },
]

const siblings = ref([])
onMounted(() => { siblings.value = pickSiblings(4) })

function name(sibling) {
  return sibling.names?.[uiLang.value] ?? sibling.names?.en ?? sibling.slug
}
</script>

<template>
  <section id="galleries-container">
    <div id="featured-galleries">
      <div id="gallery-labels">
        <p>Visit MWNF Galleries</p>
        <a :href="`${GALLERIES}/list`" target="_blank" rel="noopener"><p>See more Galleries »</p></a>
      </div>
      <div id="gallery-links">
        <component
          v-for="sibling in siblings"
          :key="sibling.id"
          :is="siblingUrl(sibling) ? 'a' : 'div'"
          :href="siblingUrl(sibling) || undefined"
          :target="siblingUrl(sibling) ? '_blank' : undefined"
          rel="noopener"
          class="gallery-item"
          :class="{ unresolved: !siblingUrl(sibling) }"
        >
          <img
            v-if="sibling.image_path"
            class="gallery-image"
            :src="legacyImage(sibling.image_path, 'lo_res')"
            :alt="name(sibling)"
            loading="lazy"
          />
          <div v-else class="gallery-image gallery-image-empty"></div>
          <p class="gallery-name">{{ name(sibling).toUpperCase() }}</p>
        </component>
      </div>
    </div>

    <div id="mwnf-museums">
      <div id="museums-label"><p>Other Virtual Museums</p></div>
      <div id="museum-links">
        <a
          v-for="museum in MUSEUMS"
          :key="museum.name"
          class="museum-item"
          :href="museum.url"
          target="_blank"
          rel="noopener"
        >
          <div class="museum-image" :style="{ background: museum.accent, color: museum.fg }">
            {{ museum.name.split(' ').map(w => w[0]).join('') }}
          </div>
          <p class="museum-name">{{ museum.name }}</p>
        </a>
      </div>
    </div>
  </section>
</template>

<style scoped>
#galleries-container { width: 100%; }
#featured-galleries, #mwnf-museums { padding: 24px 40px; }
#featured-galleries { background: var(--background-color); }
#mwnf-museums { background: #fff; }

#gallery-labels, #museums-label {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  font-size: 22px;
  font-weight: 700;
  color: var(--theme-dark);
  margin-bottom: 14px;
}
#gallery-labels a { text-decoration: none; font-size: 15px; color: var(--theme-medium-dark); }
#gallery-labels a:hover { text-decoration: underline; }

#gallery-links, #museum-links { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
#museum-links { grid-template-columns: repeat(3, 1fr); }

.gallery-item, .museum-item {
  display: block;
  text-decoration: none;
  color: inherit;
  position: relative;
}
.gallery-item.unresolved { cursor: default; }
.gallery-image, .museum-image {
  width: 100%;
  height: 150px;
  object-fit: cover;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 34px;
  font-weight: 700;
  background: var(--theme-light);
}
.gallery-image-empty { background: repeating-linear-gradient(45deg, #f6ded7, #f6ded7 10px, #f0d0c7 10px, #f0d0c7 20px); }
.gallery-name, .museum-name {
  padding: 6px 4px;
  font-weight: 700;
  color: var(--theme-dark);
  font-size: 14px;
}
.museum-name { text-align: center; }

@media only screen and (max-width: 849px) {
  #gallery-links { grid-template-columns: repeat(2, 1fr); }
  #museum-links { grid-template-columns: 1fr; }
}
</style>
