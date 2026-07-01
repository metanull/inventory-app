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
import Dynasties from '../views/Dynasties.vue'
import DynastyDetail from '../views/DynastyDetail.vue'
import ItemDetail from '../views/ItemDetail.vue'

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
  { path: '/dynasties', component: Dynasties },
  { path: '/dynasty/:id', component: DynastyDetail },
  { path: '/item/:id', component: ItemDetail },
]

export const router = createRouter({
  history: createWebHashHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 }
  },
})
