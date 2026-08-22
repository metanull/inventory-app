import { createRouter, createWebHashHistory } from 'vue-router'
import Home from '../views/Home.vue'
import PcEntrance from '../views/PcEntrance.vue'
import PcList from '../views/PcList.vue'
import Database from '../views/Database.vue'
import DatabaseResults from '../views/DatabaseResults.vue'
import TimelineEntrance from '../views/TimelineEntrance.vue'
import TimelineResults from '../views/TimelineResults.vue'
import PartnersEntrance from '../views/PartnersEntrance.vue'
import PartnersResults from '../views/PartnersResults.vue'
import PartnerDetail from '../views/PartnerDetail.vue'
import ItemDetail from '../views/ItemDetail.vue'
import ExhibitionsEntrance from '../views/ExhibitionsEntrance.vue'
import ExhibitionSplash from '../views/ExhibitionSplash.vue'
import ExhibitionIntroduction from '../views/ExhibitionIntroduction.vue'
import ExhibitionTheme from '../views/ExhibitionTheme.vue'
import ExhibitionChapter from '../views/ExhibitionChapter.vue'
import HistoricalBackground from '../views/HistoricalBackground.vue'
import HistoricalProfiles from '../views/HistoricalProfiles.vue'
import HistoricalBackgroundCountry from '../views/HistoricalBackgroundCountry.vue'

const routes = [
  { path: '/', component: Home },
  { path: '/permanent-collection', component: PcEntrance },
  { path: '/permanent-collection/results', component: PcList },
  { path: '/database', component: Database },
  { path: '/database/results', component: DatabaseResults },
  { path: '/timeline', component: TimelineEntrance },
  { path: '/timeline/results', component: TimelineResults },
  { path: '/partners', component: PartnersEntrance },
  { path: '/partners/results', component: PartnersResults },
  { path: '/partner/:id', component: PartnerDetail },
  { path: '/exhibitions', component: ExhibitionsEntrance },
  { path: '/exhibitions/:exhibitionId', component: ExhibitionSplash },
  { path: '/exhibitions/:exhibitionId/introduction', component: ExhibitionIntroduction },
  { path: '/exhibitions/:exhibitionId/theme/:themeId', component: ExhibitionTheme },
  { path: '/exhibitions/:exhibitionId/theme/:themeId/chapter/:chapterId', component: ExhibitionChapter },
  { path: '/historical-background', component: HistoricalBackground },
  { path: '/historical-profiles', component: HistoricalProfiles },
  { path: '/historical-profiles/:recordId', component: HistoricalBackgroundCountry },
  // Pre-#1498 URLs pointed country profiles at /historical-background/:id.
  { path: '/historical-background/:recordId', redirect: to => `/historical-profiles/${to.params.recordId}` },
  { path: '/item/:id', component: ItemDetail },
]

export const router = createRouter({
  history: createWebHashHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 }
  },
})
