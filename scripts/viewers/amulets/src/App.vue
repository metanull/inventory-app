<script setup>
import { ref, computed } from 'vue'
import { RouterView, RouterLink, useRoute, useRouter } from 'vue-router'
import { gallery, siteLanguages, languageByCode } from './composables/useGalleryData.js'
import { uiLang, setUiLang, t } from './composables/useUiStrings.js'
import Banner from './components/Banner.vue'
import SubBanner from './components/SubBanner.vue'

const route = useRoute()
const router = useRouter()

const PORTAL = 'https://www.museumwnf.org'
const GALLERIES = 'https://galleries.museumwnf.org'

const galleryName = computed(() => gallery.names?.[uiLang.value] ?? gallery.names?.en ?? 'Gallery')
const isHome = computed(() => route.name === 'home')
const currentYear = new Date().getFullYear()

const searchInput = ref('')
function submitSearch() {
  router.push({ name: 'search-results', query: { q: searchInput.value || 'all-objects' } })
  searchInput.value = ''
}

// Legacy's menu: five site sections plus the portal's My Collection.
const navItems = ['about', 'collection', 'partners', 'timeline', 'credits']

const menuOpen = ref(false)

function languageName(code) {
  return languageByCode.value.get(code)?.names?.[code] ?? code.toUpperCase()
}
</script>

<template>
  <div id="page-container">
    <header id="header-container">
      <div id="logo-container">
        <a :href="`${PORTAL}/`" target="_blank" rel="noopener">
          <span class="logo-mark">MWNF</span>
        </a>
      </div>

      <div id="title-container" v-if="!isHome">
        <RouterLink to="/">
          <span id="galleries-alt-header">MWNF Galleries</span>
          <span id="title">{{ galleryName.toUpperCase() }}</span>
        </RouterLink>
      </div>

      <div id="portals-search-container">
        <div id="portal-links">
          <RouterLink to="/">Home</RouterLink>
          <span> | </span>
          <a :href="`${GALLERIES}/list/1`" target="_blank" rel="noopener">All MWNF Galleries</a>
        </div>
        <div id="search-container">
          <form @submit.prevent="submitSearch">
            <input id="search-input" type="search" v-model="searchInput" placeholder='ex. "fragment"' />
            <button id="search-submit" type="submit" aria-label="Search">⌕</button>
          </form>
        </div>
        <!-- The gallery's four UI languages (thg_gallery_lang). Legacy pinned
             its chrome to English and switched only on record pages; here the
             switcher drives both, and Arabic flips the page to RTL. -->
        <div id="language-switch" v-if="siteLanguages.length > 1">
          <button
            v-for="code in siteLanguages"
            :key="code"
            :class="{ 'lang-active': code === uiLang }"
            @click="setUiLang(code)"
          >{{ languageName(code) }}</button>
        </div>
      </div>
    </header>

    <!-- Legacy's App.vue stacks the views in this order: banner (home only),
         navigation, sub-banner (everywhere else). -->
    <Banner v-if="isHome" />

    <nav id="navigation-container">
      <button id="hamburger" @click="menuOpen = !menuOpen" aria-label="Menu">☰</button>
      <ul :class="{ open: menuOpen }">
        <li v-for="item in navItems" :key="item" :class="`menu-${item}`">
          <RouterLink :to="`/${item}`" @click="menuOpen = false">{{ item.toUpperCase() }}</RouterLink>
        </li>
        <li class="menu-my-collection">
          <a :href="`${PORTAL}/mycollection/index.php`" target="_blank" rel="noopener">MY COLLECTION</a>
        </li>
      </ul>
      <!-- The standing notice legacy shipped under the menu, verbatim. It only
           ever existed in English, so it is pinned LTR. -->
      <div id="database-announcement" dir="ltr">
        <span>Tip:</span>
        The Database tool has been replaced with the Search bar in the header. Please enter your
        search term(s) into the bar to search the {{ galleryName }} database.
      </div>
    </nav>

    <SubBanner v-if="!isHome" />

    <main id="main-container">
      <RouterView />
    </main>

    <footer id="footer-container">
      <div id="footer-links">
        <a :href="`${PORTAL}/about`" target="_blank" rel="noopener">About MWNF</a> |
        <a :href="`${PORTAL}/about/contact`" target="_blank" rel="noopener">Contact</a> |
        <a :href="`${PORTAL}/about/legal-notice`" target="_blank" rel="noopener">Important Legal Notice</a> |
        <a :href="`${PORTAL}/about/credits`" target="_blank" rel="noopener">Credits</a> |
        <a :href="`${PORTAL}/about/cookies`" target="_blank" rel="noopener">Cookies</a> |
        <span>© Museum With No Frontiers (MWNF) 2004–{{ currentYear }}</span>
      </div>
    </footer>
  </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Roboto+Condensed:ital,wght@0,300;0,400;0,700;1,400&family=Roboto:ital,wght@0,300;0,400;0,500;0,700;1,400&display=swap');

/* ── Palette ────────────────────────────────────────────────────────────────
   Ported verbatim from the legacy gallery's own SCSS variables,
   .legacy-code/dxa-client/src/sites/amu/_variables.scss, plus the shared
   values in src/styles/_variables.scss. Every colour below is one of those. */
:root {
  --theme-dark:        #8e2e15;  /* banner, header, footer */
  --theme-medium-dark: #b53615;  /* dark menu items */
  --theme-medium:      #d46e59;  /* text background, search button */
  --theme-light:       #eb9681;  /* light menu items */
  --background-color:  #ffe7e0;  /* page background */

  --light-text:   #ffffff;
  --shadow-grey:  #707070;
  --link-blue:    #0000ff;

  /* Source-project accents — legacy's #info-citation-link colouring */
  --dia-yellow:  #ffcc00;  /* Discover Islamic Art (ISL / EPM) */
  --dba-blue:    #001d66;  /* Discover Baroque Art (DBA / BAR) */
  --sh-red:      #900000;  /* Sharing History (AWE) */
  --dca-tan:     #6b612b;  /* Discover Carpet Art (DCA) */
  --g-grey:      #263238;  /* Galleries */
  --exhibitions: #7f01d4;  /* Exhibitions */
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
  font-family: 'Roboto Condensed', 'Roboto', Arial, sans-serif;
  background: var(--background-color);
  color: #1a1a1a;
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
  background: var(--background-color);
  box-shadow: 0 2px 8px 2px var(--shadow-grey);
}

/* ── Header ─────────────────────────────────────────────────────────────── */
#header-container {
  position: sticky;
  top: 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  min-height: 80px;
  width: 100%;
  color: var(--light-text);
  background: var(--theme-dark);
  z-index: 90;
}
#logo-container { padding: 0 14px; z-index: 8; }
#logo-container a { text-decoration: none; }
.logo-mark {
  display: inline-block;
  border: 2px solid var(--light-text);
  padding: 4px 8px;
  font-weight: 700;
  letter-spacing: 0.12em;
  font-size: 18px;
}
/* Legacy centred the title with position:absolute + max-width:45% and then
   walked the font size down through five breakpoints. Laying it out as the
   flex row's middle cell gets the same centred result and cannot overlap the
   logo or the portal links at any width. */
#title-container {
  flex: 1;
  min-width: 0;
  text-align: center;
  padding: 6px 8px;
  z-index: 10;
}
#title-container a {
  display: flex;
  flex-direction: column;
  color: var(--light-text);
  text-decoration: none;
}
#galleries-alt-header { font-size: 14px; }
#title {
  font-size: clamp(18px, 2.4vw, 30px);
  line-height: 1.05;
  margin-top: 4px;
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
#portal-links a { color: var(--light-text); text-decoration: none; padding: 0 8px; }
#portal-links a:hover { text-decoration: underline; }
#search-container { padding-top: 8px; }
#search-input {
  border: 2px solid var(--light-text);
  border-radius: 4px;
  padding: 4px 6px;
  margin-right: 5px;
  font-family: inherit;
}
#search-submit {
  border: 2px solid var(--theme-medium);
  background: var(--theme-medium);
  border-radius: 4px;
  color: var(--light-text);
  padding: 4px 10px;
  cursor: pointer;
  font-size: 15px;
}
#language-switch { display: flex; gap: 4px; padding-top: 6px; }
#language-switch button {
  background: none;
  border: 1px solid rgba(255,255,255,0.5);
  color: var(--light-text);
  font-family: inherit;
  font-size: 12px;
  padding: 2px 7px;
  cursor: pointer;
  border-radius: 3px;
}
#language-switch button.lang-active { background: var(--theme-medium); border-color: var(--theme-medium); }

/* ── Navigation ─────────────────────────────────────────────────────────── */
/* Legacy pinned the nav under a fixed-height header; the language switcher
   makes this header's height variable, so the nav scrolls with the page. */
#navigation-container { width: 100%; z-index: 89; }
#navigation-container ul {
  list-style: none;
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  width: 100%;
}
#navigation-container li {
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--light-text);
  line-height: 28px;
}
#navigation-container li:nth-child(odd)  { background: var(--theme-medium-dark); }
#navigation-container li:nth-child(even) { background: var(--theme-light); }
#navigation-container a {
  width: 100%;
  text-align: center;
  color: var(--light-text);
  text-decoration: none;
  padding: 2px 4px;
}
#navigation-container a.router-link-active { background: rgba(0,0,0,0.18); }
#database-announcement {
  width: 100%;
  font-size: 90%;
  background: var(--theme-dark);
  color: var(--light-text);
  text-align: center;
  padding: 5px 15px;
}
#database-announcement span { font-weight: 700; }
#hamburger { display: none; }

/* ── Main ───────────────────────────────────────────────────────────────── */
#main-container { width: 100%; flex: 1; background: var(--background-color); }

/* ── Footer ─────────────────────────────────────────────────────────────── */
#footer-container {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 40px;
  width: 100%;
  font-size: 14px;
  color: var(--light-text);
  background: var(--theme-dark);
  text-align: center;
  padding: 8px 14px;
}
#footer-container a { color: var(--light-text); text-decoration: none; }
#footer-container a:hover { text-decoration: underline; }

/* ── Shared building blocks ─────────────────────────────────────────────── */
.white-panel { background: #fff; width: 100%; }

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
  background: var(--theme-medium-dark);
  color: var(--light-text);
  border: none;
  padding: 6px 16px;
  font-family: inherit;
  font-size: 14px;
  cursor: pointer;
}
.legacy-button:hover { background: var(--theme-dark); }

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
  color: var(--theme-dark);
  font-family: inherit;
  font-size: 15px;
  cursor: pointer;
}
.back-bar button:hover { text-decoration: underline; }

.page-heading {
  font-size: 20px;
  font-weight: 700;
  color: var(--theme-dark);
  padding: 20px 20px 0;
}

/* Source-project colour chips, mirroring legacy's backgroundColor() */
.project-chip { color: #fff; padding: 1px 8px; font-size: 12px; display: inline-block; }
.project-ISL, .project-EPM { background: var(--dia-yellow); color: #222; }
.project-DBA, .project-BAR { background: var(--dba-blue); }
.project-AWE, .project-awe { background: var(--sh-red); }
.project-DCA { background: var(--dca-tan); }
.project-GALLERIES { background: var(--g-grey); }

.loader { padding: 40px; text-align: center; color: var(--theme-dark); }

@media only screen and (max-width: 974px) {
  #navigation-container ul { grid-template-columns: repeat(3, 1fr); }
}

@media only screen and (max-width: 599px) {
  #header-container { min-height: 65px; flex-wrap: wrap; }
  #title-container { flex-basis: 100%; order: 3; }
  #navigation-container { position: static; }
  #navigation-container ul { display: none; }
  #navigation-container ul.open { display: flex; flex-direction: column; }
  #hamburger {
    display: block;
    background: var(--theme-dark);
    color: var(--light-text);
    border: none;
    width: 100%;
    padding: 6px;
    font-size: 20px;
    cursor: pointer;
  }
}
</style>
