import { createRouter, createWebHashHistory } from 'vue-router'

import Home from '../views/Home.vue'
import About from '../views/About.vue'
import Themes from '../views/Themes.vue'
import Theme from '../views/Theme.vue'
import ThemeGallery from '../views/ThemeGallery.vue'
import CollectionSearch from '../views/CollectionSearch.vue'
import CollectionResults from '../views/CollectionResults.vue'
import ItemSheet from '../views/ItemSheet.vue'
import SearchResults from '../views/SearchResults.vue'
import SearchHowTo from '../views/SearchHowTo.vue'
import Partners from '../views/Partners.vue'
import PartnerProfile from '../views/PartnerProfile.vue'
import PartnerObjects from '../views/PartnerObjects.vue'
import InstitutionProfile from '../views/InstitutionProfile.vue'
import InstitutionMonuments from '../views/InstitutionMonuments.vue'
import RelatedContent from '../views/RelatedContent.vue'
import Timeline from '../views/Timeline.vue'
import TimelineResults from '../views/TimelineResults.vue'
import TimelineGallery from '../views/TimelineGallery.vue'
import Credits from '../views/Credits.vue'
import ErrorPage from '../views/ErrorPage.vue'

// Paths mirror the legacy client's routes one for one, including the item
// sheet's dbUid path (`/database-item/mwnf3/objects/EPM/uk/Mus21/41/en`), so a
// legacy URL can be pasted after the `#` and land on the same page.
//
// `/theme/:id` carries `display_order - 1`, exactly as legacy did — the About
// theme is display order 1, so the first listed theme is `/theme/1`. `:subtheme`
// is either the literal `overview` or a 1-based index into the theme's
// sub-themes; `:image` is a 1-based index into the theme's picture selections.
//
// Two deliberate differences from legacy, both because the inventory model has
// no counterpart for a segment legacy resolved server-side:
//
//   * the partner and institution routes drop the project-id segment
//     (`/partner/dz/Mus01/en`, not `/partner/ISL/dz/Mus01/en`), since
//     partners.project_id is null for every imported museum;
//   * `/institution/:country/:id/:language` takes the same shape as `/partner`
//     rather than legacy's catch-all `pathMatch`, because a package routes by
//     partner `type` instead of by which endpoint answered.
const routes = [
  { path: '/', name: 'home', component: Home },
  { path: '/about', name: 'about', component: About },
  { path: '/themes', name: 'themes', component: Themes },
  { path: '/theme/:id/:subtheme?/:image?', name: 'theme', component: Theme },
  { path: '/theme-gallery/:id/:subtheme?', name: 'theme-gallery', component: ThemeGallery },
  { path: '/collection', name: 'collection', component: CollectionSearch },
  { path: '/collection-results', name: 'collection-results', component: CollectionResults },
  { path: '/database-item/:uid(.*)/:language', name: 'database-item', component: ItemSheet },
  { path: '/search', name: 'search-results', component: SearchResults },
  { path: '/how-to-search', name: 'search-how-to', component: SearchHowTo },
  { path: '/partners', name: 'partners', component: Partners },
  { path: '/partner/:country/:id/:language', name: 'partner', component: PartnerProfile },
  { path: '/partner-objects/:country/:id/:page', name: 'partner-objects', component: PartnerObjects },
  { path: '/institution/:country/:id/:language', name: 'institution', component: InstitutionProfile },
  { path: '/institution-monuments/:country/:id/:page', name: 'institution-monuments', component: InstitutionMonuments },
  { path: '/related', name: 'related', component: RelatedContent },
  { path: '/timeline', name: 'timeline', component: Timeline },
  { path: '/timeline-results', name: 'timeline-results', component: TimelineResults },
  { path: '/timeline-gallery/:country/:start/:end/:page', name: 'timeline-gallery', component: TimelineGallery },
  { path: '/credits', name: 'credits', component: Credits },
  { path: '/error', name: 'error', component: ErrorPage },
  { path: '/:pathMatch(.*)*', redirect: '/error' },
]

export const router = createRouter({
  history: createWebHashHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    // Legacy stayed put when only the parameters changed — picking another
    // picture inside a theme must not scroll the page back to the top.
    if (to.name === from.name) return false
    if (to.hash) return { el: to.hash, behavior: 'smooth' }
    if (savedPosition) return savedPosition
    return { top: 0 }
  },
})
