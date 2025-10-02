<template>
  <div
    :class="[
      'min-h-screen flex items-center justify-center py-6 px-4 sm:px-6 lg:py-12 lg:px-8',
      getThemeClass('modalActionsBg'),
    ]"
  >
    <div class="max-w-md w-full space-y-6">
      <div>
        <h2 :class="['text-center text-3xl font-extrabold', getThemeClass('modalTitle')]">
          Sign in to your account
        </h2>
      </div>
      <form class="mt-6 space-y-6" @submit.prevent="handleSubmit">
        <div v-if="authStore.error" :class="['rounded-md p-4', getThemeClass('messageError')]">
          <div :class="['text-sm', getThemeClass('messageErrorText')]">
            {{ authStore.error }}
          </div>
        </div>

        <div class="rounded-md shadow-sm -space-y-px">
          <div>
            <label for="email" class="sr-only">Email address</label>
            <input
              id="email"
              v-model="form.email"
              name="email"
              type="email"
              autocomplete="email"
              required
              :class="[
                'relative block w-full px-3 py-2',
                getThemeClass('placeholderText'),
                getThemeClass('inputText'),
                'rounded-t-md sm:text-sm',
                getThemeClass('formBorder'),
                getThemeClass('inputFocus'),
              ]"
              placeholder="Email address"
            />
          </div>
          <div>
            <label for="password" class="sr-only">Password</label>
            <input
              id="password"
              v-model="form.password"
              name="password"
              type="password"
              autocomplete="current-password"
              required
              :class="[
                'relative block w-full px-3 py-2',
                getThemeClass('placeholderText'),
                getThemeClass('inputText'),
                'rounded-b-md sm:text-sm',
                getThemeClass('formBorder'),
                getThemeClass('inputFocus'),
              ]"
              placeholder="Password"
            />
          </div>
        </div>

        <div>
          <button
            type="submit"
            :disabled="authStore.loading"
            :class="[
              getThemeClass('primaryButton'),
              'disabled:opacity-50 disabled:cursor-not-allowed',
            ]"
          >
            <ArrowRightOnRectangleIcon v-if="!authStore.loading" class="w-4 h-4" />
            <span v-if="authStore.loading">Signing in...</span>
            <span v-else>Sign in</span>
          </button>
        </div>
      </form>

      <!-- Two-Factor Authentication Component -->
      <div v-if="authStore.requires2FA && authStore.twoFactorChallenge">
        <TwoFactorVerification
          :challenge="authStore.twoFactorChallenge"
          @verified="handle2FAVerified"
          @cancelled="handle2FACancelled"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { reactive, onMounted, watch } from 'vue'
  import { useRouter } from 'vue-router'
  import { useAuthStore } from '@/stores/auth'
  import { ArrowRightOnRectangleIcon } from '@heroicons/vue/24/outline'
  import { getThemeClass } from '@/composables/useColors'
  import TwoFactorVerification from '@/components/TwoFactorVerification.vue'
  // safeRedirect no longer needed here as we only accept named redirects
  import type { RouteParamsRaw } from 'vue-router'

  const router = useRouter()
  const authStore = useAuthStore()

  const form = reactive({
    email: '',
    password: '',
  })

  const handleSubmit = async () => {
    authStore.clearError()
    try {
      await authStore.login(form.email, form.password)

      // Check if 2FA is required - if so, the component will show automatically
      if (authStore.requires2FA) {
        return // Stay on login page to show 2FA component
      }

      // No 2FA required, proceed with redirect
      await handleSuccessfulLogin()
    } catch {
      // Error is handled by the store
    }
  }

  const handle2FAVerified = async () => {
    await handleSuccessfulLogin()
  }

  const handle2FACancelled = () => {
    // Reset form or stay on login page
    form.email = ''
    form.password = ''
  }

  const handleSuccessfulLogin = async () => {
    const q = router.currentRoute.value.query
    const redirectName = q.redirectName as string | undefined
    const redirectParamsRaw = q.redirectParams as string | undefined
    let redirectParams: RouteParamsRaw | undefined
    if (redirectParamsRaw) {
      try {
        const parsed = JSON.parse(decodeURIComponent(redirectParamsRaw)) as unknown
        if (parsed && typeof parsed === 'object') {
          redirectParams = parsed as RouteParamsRaw
        }
      } catch {
        redirectParams = undefined
      }
    }
    if (redirectName) {
      await router.push({ name: redirectName, params: redirectParams })
    } else {
      // Redirect to home (dashboard) route by name, not path
      await router.push({ name: 'home' })
    }
  }

  onMounted(() => {
    authStore.clearError()
  })

  // Watch for authentication state changes and redirect if user becomes authenticated
  watch(
    () => authStore.isAuthenticated,
    isAuthenticated => {
      if (isAuthenticated && !authStore.requires2FA) {
        // Only redirect if we're not in the middle of a 2FA flow
        const query = router.currentRoute.value.query
        const hasRedirectIntent = typeof query?.redirectName === 'string'
        if (!hasRedirectIntent) {
          // No specific redirect intent, go to home
          router.push({ name: 'home' })
        }
      }
    }
  )
</script>
