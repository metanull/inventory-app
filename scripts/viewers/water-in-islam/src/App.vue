<script setup>
import { ref, computed } from 'vue'
import { RouterView, RouterLink, useRoute, useRouter } from 'vue-router'
import { exhibition, exhibitionTitle } from './composables/useExhibitionData.js'
import { hasTimeline } from './composables/useTimeline.js'
import { uiLang, t } from './composables/useUiStrings.js'
import { ALL_OBJECTS_SENTINEL } from './views/SearchResults.vue'
import Banner from './components/Banner.vue'
import SubBanner from './components/SubBanner.vue'
import BottomBanner from './components/BottomBanner.vue'
import LogoStrip from './components/LogoStrip.vue'
import PopupLogo from './components/PopupLogo.vue'

const route = useRoute()
const router = useRouter()

const PORTAL = 'https://www.museumwnf.org'

const title = computed(() => exhibitionTitle(uiLang.value))
const isHome = computed(() => route.name === 'home')
const currentYear = new Date().getFullYear()

const searchInput = ref('')
function submitSearch() {
  router.push({ name: 'search-results', query: { q: searchInput.value || ALL_OBJECTS_SENTINEL } })
  searchInput.value = ''
}

// Legacy's NavigationComponent, one for one: the label is the value uppercased
// and the path is the value, with the single rename "related content" → /related.
// TIMELINE is dropped when the exhibition reports neither chronology — both
// flags gate the nav entry, not the data.
const NAV = [
  { path: 'about', label: 'ABOUT' },
  { path: 'themes', label: 'THEMES' },
  { path: 'collection', label: 'COLLECTION' },
  { path: 'partners', label: 'PARTNERS' },
  { path: 'timeline', label: 'TIMELINE' },
  { path: 'related', label: 'RELATED CONTENT' },
  { path: 'credits', label: 'CREDITS' },
]
const navItems = computed(() => NAV.filter(i => i.path !== 'timeline' || hasTimeline.value))

const menuOpen = ref(false)

// Legacy renders category 0 — "Header" — beside the MWNF mark, under the
// `header_logo_section_1` heading, and leaves categories 1–4 to the footer
// strip. This exhibition has one logo and it is category 1, the UNAOC mark
// under "Under the patronage of", so the header block stays empty here too;
// the code is kept because the split is the data's, not this exhibition's.
const headerLogos = computed(() =>
  (exhibition.logos ?? [])
    .filter(logo => Number(logo.category_id) === 0 && logo.visible !== false)
    .sort((a, b) => (a.display_order ?? 0) - (b.display_order ?? 0))
)

function logoCaption(logo) {
  return logo.labels?.[uiLang.value] ?? logo.labels?.en ?? logo.alt_text ?? ''
}
</script>

<template>
  <div id="page-container">
    <PopupLogo />

    <header id="header-container">
      <div id="logo-container">
        <a :href="`${PORTAL}/`" target="_blank" rel="noopener">
          <span class="logo-mark">MWNF</span>
        </a>
        <div class="header-logos-container" v-if="headerLogos.length">
          <div class="header-logos-header">{{ t('header_logo_section_1') }}</div>
          <div class="header-logos">
            <a
              v-for="logo in headerLogos"
              :key="logo.id ?? logo.image_url"
              class="header-logo"
              :href="logo.url || undefined"
              :title="logoCaption(logo)"
              target="_blank"
              rel="noopener"
            ><img :src="logo.image_url" :alt="logoCaption(logo)" /></a>
          </div>
        </div>
      </div>

      <!-- Legacy's centre cell is the platform label, linking to the About
           page. The exhibition's own title lives in the banner and the bottom
           banner, not here. -->
      <div id="title-container">
        <RouterLink to="/about">
          <span id="title">{{ t('headerOnlExh') }}</span>
        </RouterLink>
      </div>

      <div id="portals-search-container">
        <div id="portal-links">
          <RouterLink to="/">Home</RouterLink>
          <span> | </span>
          <a :href="`${PORTAL}/about`" target="_blank" rel="noopener">About MWNF</a>
        </div>
        <div id="search-container">
          <form @submit.prevent="submitSearch">
            <input id="search-input" type="search" v-model="searchInput" placeholder='ex. "fragment"' />
            <button id="search-submit" type="submit" aria-label="Search">⌕</button>
          </form>
        </div>
        <!-- No language switcher: `languages_enabled` holds English alone, and
             per decision Q2 an exhibition ships one build per enabled language.
             The switcher appears the day a second language is enabled. -->
      </div>
    </header>

    <!-- Legacy's App.vue stacks the named views in this order: banner (home
         only), navigation, sub-banner (everywhere else), the page, the bottom
         banner and the logo strip. -->
    <Banner v-if="isHome" />

    <nav id="navigation-container">
      <button id="hamburger" @click="menuOpen = !menuOpen" aria-label="Menu">☰</button>
      <ul :class="{ open: menuOpen }">
        <li v-for="item in navItems" :key="item.path" :class="`menu-${item.path}`">
          <RouterLink :to="`/${item.path}`" @click="menuOpen = false">{{ item.label }}</RouterLink>
        </li>
        <li class="menu-my-collection">
          <a :href="`${PORTAL}/mycollection/index.php`" target="_blank" rel="noopener">MY COLLECTION</a>
        </li>
      </ul>
    </nav>

    <SubBanner v-if="!isHome" />

    <main id="main-container">
      <RouterView />
    </main>

    <BottomBanner />
    <LogoStrip />

    <footer id="footer-container">
      <div id="footer-links">
        <a :href="`${PORTAL}/about`" target="_blank" rel="noopener">About MWNF</a> |
        <a :href="`${PORTAL}/about/contact`" target="_blank" rel="noopener">Contact</a> |
        <a :href="`${PORTAL}/about/legal-notice`" target="_blank" rel="noopener">Important Legal Notice</a> |
        <a :href="`${PORTAL}/about/credits`" target="_blank" rel="noopener">Credits</a> |
        <a :href="`${PORTAL}/about/cookies`" target="_blank" rel="noopener">Cookies</a> |
        <span>© Museum With No Frontiers (MWNF) {{ title ? '2004–' : '' }}{{ currentYear }}</span>
      </div>
    </footer>
  </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Roboto+Condensed:ital,wght@0,300;0,400;0,700;1,400&family=Roboto:ital,wght@0,300;0,400;0,500;0,700;1,400&display=swap');

/* ── Palette ────────────────────────────────────────────────────────────────
   The exhibitions client takes its six colours from per-deployment
   `VUE_APP_VO_*` variables rather than a checked-in SCSS file, so these were
   read back off the live instance's own compiled stylesheet
   (exhibitions.museumwnf.org/water_in_islam/en) rather than guessed:

     MAIN #000000 / MAIN_TEXT #ffffff    header rule, themes gutter, footer
     SECONDARY #ffffff / SECONDARY_TEXT #000000
     CONTRAST #a3d6d9 / CONTRAST_TEXT #000000   theme titles, bottom banner

   The contrast tone is the one thing that moves between sibling exhibitions,
   and it is why every rule below reads a variable instead of a literal: this
   deployment runs the platform defaults except for a pale water blue where
   Colours runs #64bfd9. It was read off `#sub-banner-overlay:before`, the
   loader ring and the pop-up close button, which is where the live stylesheet
   spends it — 109 of its occurrences against 7 for the next colour. */
:root {
  --main-color:            #000000;
  --main-text-color:       #ffffff;
  --secondary-color:       #ffffff;
  --secondary-text-color:  #000000;
  --contrast-color:        #a3d6d9;
  --contrast-text-color:   #000000;

  --shadow-grey: #707070;
  --link-blue:   #0000ff;
  --rule-grey:   #e0e0e0;

  /* Source-project accents — legacy's #info-citation-link colouring, one tone
     per family, read off the live stylesheet's own `.AWE`, `.DBA`, … rules.
     Seven of the eight appear in this exhibition; DCA is kept because the
     table is the platform's rather than this deployment's. */
  --dia-yellow:    #ffcc00;  /* Discover Islamic Art (ISL / EPM) */
  --dba-blue:      #001d66;  /* Discover Baroque Art (DBA / BAR) */
  --sh-red:        #900000;  /* Sharing History (AWE) */
  --dca-tan:       #6b612b;  /* Discover Carpet Art (DCA) */
  --dga-blue:      #0059bf;  /* Discover Glass Art (DGA) */
  --explore-green: #006950;  /* Explore monuments database */
  --g-grey:        #263238;  /* Galleries */
  --exhibitions:   #7f01d4;  /* Exhibitions (this one, and The Table Is Set) */

  /* The database half of this site — collection search and results, the item
     sheet, partners, the timeline — is the gallery client's, and was forked
     from the carpets viewer along with several hundred CSS rules that address
     the palette by the gallery variable names. Binding those names here keeps
     one palette for the whole site instead of two spellings of it.

     The mapping is not one-to-one: the galleries have four tones of a single
     hue, this exhibition has a black/white base plus one accent. Every gallery
     tone that carries light text maps onto the main colour, so the contrast
     pairs stay legible; only the tone used behind dark text becomes the
     accent. */
  --theme-dark:        var(--main-color);
  --theme-medium-dark: var(--main-color);
  --theme-medium:      var(--main-color);
  --theme-light:       var(--contrast-color);
  --light-text:        var(--main-text-color);
  --background-color:  var(--secondary-color);
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
  font-family: 'Roboto Condensed', 'Roboto', Arial, sans-serif;
  background: var(--secondary-color);
  color: var(--secondary-text-color);
  display: flex;
  justify-content: center;
  align-items: flex-start;
  overflow-x: hidden;
}

html[dir='rtl'] body { direction: rtl; }

a { color: inherit; }

#page-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 1350px;
  max-width: 100vw;
  min-height: 100vh;
  background: var(--secondary-color);
  box-shadow: 0 2px 8px 2px var(--shadow-grey);
}

/* ── Header ─────────────────────────────────────────────────────────────── */
/* Legacy's header is white with a 5px contrast rule under it — not the solid
   dark bar the galleries use. */
#header-container {
  position: sticky;
  top: 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  min-height: 80px;
  width: 100%;
  color: var(--secondary-text-color);
  background: var(--secondary-color);
  border-bottom: 5px solid var(--contrast-color);
  z-index: 90;
}
#logo-container { display: flex; align-items: center; gap: 16px; padding: 0 14px; z-index: 8; }
#logo-container a { text-decoration: none; }
.header-logos-container { font-size: 12px; text-align: center; }
.header-logos-header { font-weight: 700; }
.header-logos { display: flex; align-items: center; gap: 10px; }
.header-logo img { max-height: 46px; max-width: 120px; object-fit: contain; }
.logo-mark {
  display: inline-block;
  border: 2px solid var(--secondary-text-color);
  padding: 4px 8px;
  font-weight: 700;
  letter-spacing: 0.12em;
  font-size: 18px;
}
#title-container {
  flex: 1;
  min-width: 0;
  text-align: center;
  padding: 6px 8px;
  z-index: 10;
}
#title-container a { color: inherit; text-decoration: none; }
#title {
  font-size: clamp(15px, 1.6vw, 22px);
  line-height: 1.1;
  overflow-wrap: break-word;
}

#portals-search-container {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  padding: 8px 12px;
  font-size: 14px;
  z-index: 10;
}
#portal-links a { text-decoration: none; padding: 0 8px; }
#portal-links a:hover { text-decoration: underline; }
#search-container { padding-top: 8px; }
#search-input {
  border: 2px solid var(--main-color);
  border-radius: 4px;
  padding: 4px 6px;
  margin-right: 5px;
  font-family: inherit;
}
#search-submit {
  border: 2px solid var(--contrast-color);
  background: var(--contrast-color);
  border-radius: 4px;
  color: var(--contrast-text-color);
  padding: 4px 10px;
  cursor: pointer;
  font-size: 15px;
}

/* ── Navigation ─────────────────────────────────────────────────────────── */
#navigation-container { width: 100%; z-index: 89; }
/* One equal column per entry, however many there are. A fixed `repeat(8, 1fr)`
   is right only for an exhibition that shows TIMELINE; this one does not, and
   the eighth column would sit empty at the end of the bar. Legacy sizes the
   items with flex-grow, which is the same behaviour. */
#navigation-container ul {
  list-style: none;
  display: grid;
  grid-auto-flow: column;
  grid-auto-columns: 1fr;
  width: 100%;
}
#navigation-container li {
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--contrast-color);
  border: 1px solid rgba(0, 0, 0, 0.05);
  line-height: 28px;
}
#navigation-container a {
  width: 100%;
  text-align: center;
  color: var(--contrast-text-color);
  font-weight: 700;
  text-decoration: none;
  padding: 2px 4px;
  font-size: 96%;
}
#navigation-container a.router-link-active { background: rgba(0, 0, 0, 0.14); }
#hamburger { display: none; }

/* ── Main ───────────────────────────────────────────────────────────────── */
#main-container { width: 100%; flex: 1; background: var(--secondary-color); }

/* ── Footer ─────────────────────────────────────────────────────────────── */
#footer-container {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 40px;
  width: 100%;
  font-size: 14px;
  color: var(--main-text-color);
  background: var(--main-color);
  text-align: center;
  padding: 8px 14px;
}
#footer-container a { color: var(--main-text-color); text-decoration: none; }
#footer-container a:hover { text-decoration: underline; }

/* ── Shared building blocks ─────────────────────────────────────────────── */
.white-panel { background: var(--secondary-color); width: 100%; }

.section-label { font-size: 125%; font-weight: 700; }

.legacy-select {
  height: 35px;
  width: 100%;
  max-width: 300px;
  margin-top: 10px;
  padding: 5px;
  font-family: inherit;
  font-size: 14px;
  border: 1px solid #999;
  background: #fff;
}

.legacy-button {
  background: var(--contrast-color);
  color: var(--contrast-text-color);
  border: none;
  padding: 6px 16px;
  font-family: inherit;
  font-size: 14px;
  cursor: pointer;
}
.legacy-button:hover { background: var(--main-color); color: var(--main-text-color); }

.link-blue { color: var(--link-blue); }
.link-blue:hover { text-decoration: underline; }

.prose a { color: var(--link-blue); }
.prose p { margin-bottom: 12px; }
.prose em { font-style: italic; }
.prose strong { font-weight: 700; }
.prose ul { margin: 0 0 12px 20px; }

.back-bar { padding: 12px 20px; }
.back-bar button {
  background: none;
  border: none;
  color: var(--secondary-text-color);
  font-family: inherit;
  font-size: 15px;
  cursor: pointer;
}
.back-bar button:hover { text-decoration: underline; }

.page-heading {
  font-size: 20px;
  font-weight: 700;
  color: var(--secondary-text-color);
  padding: 20px 20px 0;
}

/* Source-project colour chips. One class per legacy FAMILY rather than per
   project key — the mapping lives in useExhibitionData.js — so the class names
   below are legacy's own, and this block reads as the stylesheet it copies. */
.project-chip { color: #fff; padding: 1px 8px; font-size: 12px; display: inline-block; }
.project-ISLandEPM { background: var(--dia-yellow); color: #000; }
.project-DBA { background: var(--dba-blue); }
.project-AWE { background: var(--sh-red); }
.project-DCA { background: var(--dca-tan); }
.project-DGA { background: var(--dga-blue); }
.project-Explore { background: var(--explore-green); }
.project-Galleries { background: var(--g-grey); }
.project-EXH { background: var(--exhibitions); }

.loader { padding: 40px; text-align: center; color: var(--secondary-text-color); }

@media only screen and (max-width: 1199px) {
  #navigation-container ul { grid-auto-flow: row; grid-template-columns: repeat(4, 1fr); }
}

@media only screen and (max-width: 599px) {
  #header-container { min-height: 65px; flex-wrap: wrap; }
  #title-container { flex-basis: 100%; order: 3; }
  #navigation-container { position: static; }
  #navigation-container ul { display: none; }
  #navigation-container ul.open { display: flex; flex-direction: column; }
  #hamburger {
    display: block;
    background: var(--main-color);
    color: var(--main-text-color);
    border: none;
    width: 100%;
    padding: 6px;
    font-size: 20px;
    cursor: pointer;
  }
}
</style>
