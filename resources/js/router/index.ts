import {
  createRouter,
  createWebHistory,
  type RouteLocationNormalized,
  type NavigationGuardNext,
} from 'vue-router'
import Home from '@/views/Home.vue'
import Login from '@/views/Login.vue'
import Languages from '@/views/Languages.vue'
import LanguageDetail from '@/views/LanguageDetail.vue'
import Countries from '@/views/Countries.vue'
import CountryDetail from '@/views/CountryDetail.vue'
import Contexts from '@/views/Contexts.vue'
import ContextDetail from '@/views/ContextDetail.vue'
import Projects from '@/views/Projects.vue'
import ProjectDetail from '@/views/ProjectDetail.vue'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory('/cli/'),
  routes: [
    {
      path: '/',
      name: 'home',
      component: Home,
      meta: { requiresAuth: true },
    },
    {
      path: '/languages',
      name: 'languages',
      component: Languages,
      meta: { requiresAuth: true },
    },
    {
      path: '/languages/:id',
      name: 'language-detail',
      component: LanguageDetail,
      meta: { requiresAuth: true },
    },
    {
      path: '/countries',
      name: 'countries',
      component: Countries,
      meta: { requiresAuth: true },
    },
    {
      path: '/countries/:id',
      name: 'country-detail',
      component: CountryDetail,
      meta: { requiresAuth: true },
    },
    {
      path: '/contexts',
      name: 'contexts',
      component: Contexts,
      meta: { requiresAuth: true },
    },
    {
      path: '/contexts/new',
      name: 'context-new',
      component: ContextDetail,
      meta: { requiresAuth: true },
    },
    {
      path: '/contexts/:id',
      name: 'context-detail',
      component: ContextDetail,
      meta: { requiresAuth: true },
    },
    {
      path: '/projects',
      name: 'projects',
      component: Projects,
      meta: { requiresAuth: true },
    },
    {
      path: '/projects/new',
      name: 'project-new',
      component: ProjectDetail,
      meta: { requiresAuth: true },
    },
    {
      path: '/projects/:id',
      name: 'project-detail',
      component: ProjectDetail,
      meta: { requiresAuth: true },
    },
    {
      path: '/login',
      name: 'login',
      component: Login,
      meta: { requiresAuth: false },
    },
  ],
})

router.beforeEach(
  async (
    to: RouteLocationNormalized,
    _from: RouteLocationNormalized,
    next: NavigationGuardNext
  ) => {
    const authStore = useAuthStore()

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
      next('/login')
    } else if (to.name === 'login' && authStore.isAuthenticated) {
      next('/')
    } else {
      next()
    }
  }
)

export default router
