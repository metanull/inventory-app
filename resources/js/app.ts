import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import { setRouter, setAuthStoreAccessor } from '@/utils/errorHandler'
import { useAuthStore } from '@/stores/auth'
import '../css/app.css'

const app = createApp(App)

app.use(createPinia())
app.use(router)

// Inject shared dependencies into the error handler to avoid dynamic imports
setRouter(router)
setAuthStoreAccessor(() => {
  try {
    const store = useAuthStore()
    return { token: store.token as unknown as { value: string | null } }
  } catch {
    return null
  }
})

app.mount('#app')

// Handle Vite HMR - when app rebuilds, check if user needs to re-authenticate
if (import.meta.hot) {
  import.meta.hot.on('vite:beforeFullReload', () => {
    // Before full reload, check if user is authenticated
    try {
      const authStore = useAuthStore()
      if (authStore.isAuthenticated) {
        // Store a flag that we need to revalidate session after reload
        sessionStorage.setItem('revalidate-session', 'true')
      }
    } catch {
      // Ignore errors during HMR
    }
  })
}

// On app start, check if we need to revalidate session after a rebuild
;(async () => {
  if (sessionStorage.getItem('revalidate-session') === 'true') {
    sessionStorage.removeItem('revalidate-session')
    
    try {
      const authStore = useAuthStore()
      if (authStore.token) {
        // Attempt to validate the session is still valid
        await authStore.validateSession()
      }
    } catch (error) {
      console.error('[App] Session validation failed after rebuild, logging out:', error)
      try {
        const authStore = useAuthStore()
        authStore.logout()
        // Navigate to login
        router.push({ name: 'login' })
      } catch {
        // If logout fails, force reload to login page
        window.location.href = '/cli/login'
      }
    }
  }
})()
