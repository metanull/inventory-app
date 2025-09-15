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
