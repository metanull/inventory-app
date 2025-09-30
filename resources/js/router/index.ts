import {
  createRouter,
  createWebHistory,
  type RouteLocationNormalized,
  type NavigationGuardNext,
} from 'vue-router'
import Home from '@/views/Home.vue'
import Login from '@/views/Login.vue'
import Items from '@/views/Items.vue'
import ItemDetail from '@/views/ItemDetail.vue'

import Languages from '@/views/Languages.vue'
import LanguageDetail from '@/views/LanguageDetail.vue'
import Countries from '@/views/Countries.vue'
import CountryDetail from '@/views/CountryDetail.vue'
import Contexts from '@/views/Contexts.vue'
import ContextDetail from '@/views/ContextDetail.vue'
import Projects from '@/views/Projects.vue'
import ProjectDetail from '@/views/ProjectDetail.vue'
import Partners from '@/views/Partners.vue'
import PartnerDetail from '@/views/PartnerDetail.vue'
import Collections from '@/views/Collections.vue'
import CollectionDetail from '@/views/CollectionDetail.vue'
import ImageUpload from '@/views/ImageUpload.vue'
import AvailableImages from '@/views/AvailableImages.vue'
import AvailableImageDetail from '@/views/AvailableImageDetail.vue'
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
      path: '/items',
      name: 'items',
      component: Items,
      meta: { requiresAuth: true },
    },
    {
      path: '/items/new',
      name: 'item-new',
      component: ItemDetail,
      meta: { requiresAuth: true },
    },
    {
      path: '/items/:id',
      name: 'item-detail',
      component: ItemDetail,
      meta: { requiresAuth: true },
    },

    {
      path: '/partners',
      name: 'partners',
      component: Partners,
      meta: { requiresAuth: true },
    },
    {
      path: '/partners/new',
      name: 'partner-new',
      component: PartnerDetail,
      meta: { requiresAuth: true },
    },
    {
      path: '/partners/:id',
      name: 'partner-detail',
      component: PartnerDetail,
      meta: { requiresAuth: true },
    },
    {
      path: '/collections',
      name: 'collections',
      component: Collections,
      meta: { requiresAuth: true },
    },
    {
      path: '/collections/new',
      name: 'collection-new',
      component: CollectionDetail,
      meta: { requiresAuth: true },
    },
    {
      path: '/collections/:id',
      name: 'collection-detail',
      component: CollectionDetail,
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
      path: '/images/upload',
      name: 'image-upload',
      component: ImageUpload,
      meta: { requiresAuth: true },
    },
    {
      path: '/images',
      name: 'available-images',
      component: AvailableImages,
      meta: { requiresAuth: true },
    },
    {
      path: '/images/:id',
      name: 'available-image-detail',
      component: AvailableImageDetail,
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
      const query: Record<string, string> = {}
      if (to.name && typeof to.name === 'string') {
        query.redirectName = to.name
        const params = to.params && typeof to.params === 'object' ? to.params : undefined
        if (params && Object.keys(params).length > 0) {
          try {
            query.redirectParams = encodeURIComponent(JSON.stringify(params))
          } catch {
            // swallow non-serializable params
          }
        }
      }
      next({ name: 'login', query })
    } else if (to.name === 'login' && authStore.isAuthenticated) {
      // If arriving to login with a redirectName in query, allow it so the user can re-auth
      // Otherwise, send authenticated users away from login to home
      const hasIntended = typeof to.query?.redirectName === 'string'
      if (hasIntended) {
        next()
      } else {
        next('/')
      }
    } else {
      next()
    }
  }
)

export default router
