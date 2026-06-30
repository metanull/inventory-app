import { createRouter, createWebHashHistory } from 'vue-router'
import Home from '../views/Home.vue'
import PcEntrance from '../views/PcEntrance.vue'
import PcList from '../views/PcList.vue'
import Database from '../views/Database.vue'
import DatabaseResults from '../views/DatabaseResults.vue'
import ItemDetail from '../views/ItemDetail.vue'

const routes = [
  { path: '/', component: Home },
  { path: '/permanent-collection', component: PcEntrance },
  { path: '/permanent-collection/results', component: PcList },
  { path: '/database', component: Database },
  { path: '/database/results', component: DatabaseResults },
  { path: '/item/:id', component: ItemDetail },
]

export const router = createRouter({
  history: createWebHashHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 }
  },
})
