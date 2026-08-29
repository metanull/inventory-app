import { createApp } from 'vue'
import { router } from './router/index.js'
import { setUiLang } from './composables/useUiStrings.js'
import App from './App.vue'

setUiLang('en')

createApp(App).use(router).mount('#app')
