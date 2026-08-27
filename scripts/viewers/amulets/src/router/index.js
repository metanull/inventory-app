import { createRouter, createWebHashHistory } from 'vue-router'

import Home from '../views/Home.vue'
import CollectionSearch from '../views/CollectionSearch.vue'
import CollectionResults from '../views/CollectionResults.vue'
import ItemSheet from '../views/ItemSheet.vue'
import SearchResults from '../views/SearchResults.vue'
import SearchHowTo from '../views/SearchHowTo.vue'
import Partners from '../views/Partners.vue'
import PartnerProfile from '../views/PartnerProfile.vue'
import PartnerObjects from '../views/PartnerObjects.vue'
import Timeline from '../views/Timeline.vue'
import TimelineResults from '../views/TimelineResults.vue'
import TimelineGallery from '../views/TimelineGallery.vue'
import About from '../views/About.vue'
import Credits from '../views/Credits.vue'
import ErrorPage from '../views/ErrorPage.vue'

// Paths mirror the legacy client's routes one for one, including the item
// sheet's dbUid path (`/database-item/mwnf3/objects/EPM/uk/Mus21/41/en`), so a
// legacy URL can be pasted after the `#` and land on the same page.
//
// The one deliberate difference is the partner route: legacy carried a project
// id it resolved server-side (`/partner/ISL/dz/Mus01/en`) and the inventory
// model has no per-partner project, so the segment is dropped rather than
// invented.
const routes = [
  { path: '/', name: 'home', component: Home },
  { path: '/collection', name: 'collection', component: CollectionSearch },
  { path: '/collection-results', name: 'collection-results', component: CollectionResults },
  { path: '/database-item/:uid(.*)/:language', name: 'database-item', component: ItemSheet },
  { path: '/search', name: 'search-results', component: SearchResults },
  { path: '/how-to-search', name: 'search-how-to', component: SearchHowTo },
  { path: '/partners', name: 'partners', component: Partners },
  { path: '/partner/:country/:id/:language', name: 'partner', component: PartnerProfile },
  { path: '/partner-objects/:country/:id/:page', name: 'partner-objects', component: PartnerObjects },
  { path: '/timeline', name: 'timeline', component: Timeline },
  { path: '/timeline-results', name: 'timeline-results', component: TimelineResults },
  { path: '/timeline-gallery/:country/:start/:end/:page', name: 'timeline-gallery', component: TimelineGallery },
  { path: '/about', name: 'about', component: About },
  { path: '/credits', name: 'credits', component: Credits },
  { path: '/error', name: 'error', component: ErrorPage },
  { path: '/:pathMatch(.*)*', redirect: '/error' },
]

export const router = createRouter({
  history: createWebHashHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (to.hash) return { el: to.hash, behavior: 'smooth' }
    if (savedPosition) return savedPosition
    return { top: 0 }
  },
})
