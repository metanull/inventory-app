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
  { path: '/item/:id', component: ItemDetail },
]

export const router = createRouter({
  history: createWebHashHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 }
  },
})
