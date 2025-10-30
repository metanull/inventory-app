<template>
  <div class="space-y-4">
    <!-- 2FA Challenge Message -->
    <div v-if="challenge" class="text-center">
      <h3 :class="['text-lg font-medium', getThemeClass('modalTitle')]">
        Two-Factor Authentication Required
      </h3>
      <p :class="['mt-2 text-sm', getThemeClass('placeholderText')]">
        {{ challenge.message }}
      </p>
    </div>

    <!-- Method Selection (if multiple methods available) -->
    <div v-if="availableMethods.length > 1" class="space-y-2">
      <label :class="['block text-sm font-medium', getThemeClass('modalTitle')]">
        Choose verification method:
      </label>
      <div class="grid grid-cols-1 gap-2">
        <button
          v-for="method in availableMethods"
          :key="method"
          type="button"
          :class="[
            'px-4 py-2 rounded-md text-sm font-medium transition-colors',
            selectedMethod === method
              ? getThemeClass('primaryButton')
              : getThemeClass('secondaryButton'),
          ]"
          @click="selectMethod(method)"
        >
          {{ getMethodLabel(method) }}
        </button>
      </div>
    </div>

    <!-- Code Input -->
    <div v-if="showCodeInput" class="space-y-4">
      <div>
        <label for="twoFactorCode" class="sr-only">
          {{ getMethodLabel(selectedMethod) }} Code
        </label>
        <input
          id="twoFactorCode"
          v-model="code"
          type="text"
          :maxlength="codeMaxLength"
          :placeholder="getCodePlaceholder(selectedMethod)"
          :class="[
            'block w-full px-3 py-2 text-center font-mono text-lg',
            getThemeClass('inputText'),
            'rounded-md',
            getThemeClass('formBorder'),
            getThemeClass('inputFocus'),
          ]"
          @input="formatCode"
          @keydown.enter="verifyCode"
        />
      </div>

      <div class="flex space-x-2">
        <button
          type="button"
          :disabled="!code || code.length < codeMinLength || loading"
          :class="[
            'flex-1 px-4 py-2 rounded-md text-sm font-medium',
            getThemeClass('primaryButton'),
            { 'opacity-50 cursor-not-allowed': !code || code.length < codeMinLength || loading },
          ]"
          @click="verifyCode"
        >
          <span v-if="loading">Verifying...</span>
          <span v-else>Verify Code</span>
        </button>

        <button
          type="button"
          :class="['px-4 py-2 rounded-md text-sm font-medium', getThemeClass('secondaryButton')]"
          @click="cancel"
        >
          Cancel
        </button>
      </div>
    </div>

    <!-- Error Display -->
    <div v-if="error" :class="['rounded-md p-4', getThemeClass('messageError')]">
      <div :class="['text-sm', getThemeClass('messageErrorText')]">
        {{ error }}
      </div>
    </div>

    <!-- Help Text -->
    <div v-if="selectedMethod" :class="['text-xs text-center', getThemeClass('placeholderText')]">
      {{ getHelpText(selectedMethod) }}
    </div>
  </div>
</template>

<script setup lang="ts">
  import { ref, computed, onMounted } from 'vue'
  import { useAuthStore, type TwoFactorChallenge } from '@/stores/auth'
  import { getThemeClass } from '@/composables/useColors'

  interface Props {
    challenge: TwoFactorChallenge
  }

  const props = defineProps<Props>()
  const emit = defineEmits<{
    verified: []
    cancelled: []
  }>()

  const authStore = useAuthStore()

  const selectedMethod = ref<string>('')
  const code = ref('')
  const loading = ref(false)
  const error = ref<string | null>(null)

  const availableMethods = computed(() => props.challenge.available_methods)
  const showCodeInput = computed(() => {
    return selectedMethod.value === 'totp'
  })

  const codeMaxLength = computed(() => {
    return selectedMethod.value === 'totp' ? 6 : 6
  })

  const codeMinLength = computed(() => {
    return selectedMethod.value === 'totp' ? 6 : 6
  })

  onMounted(() => {
    // Auto-select method if only one is available
    if (availableMethods.value.length === 1 && availableMethods.value[0]) {
      selectedMethod.value = availableMethods.value[0]
    } else if (props.challenge.primary_method) {
      selectedMethod.value = props.challenge.primary_method
    }
  })

  const selectMethod = (method: string) => {
    selectedMethod.value = method
    code.value = ''
    error.value = null
  }

  const getMethodLabel = (method: string): string => {
    switch (method) {
      case 'totp':
        return 'Authenticator App'
      default:
        return method
    }
  }

  const getCodePlaceholder = (method: string): string => {
    switch (method) {
      case 'totp':
        return '000000'
      default:
        return 'Enter code'
    }
  }

  const getHelpText = (method: string): string => {
    switch (method) {
      case 'totp':
        return 'Enter the 6-digit code from your authenticator app'
      default:
        return ''
    }
  }

  const formatCode = (event: Event) => {
    const input = event.target as HTMLInputElement
    // Remove non-digits and limit length
    let value = input.value.replace(/\D/g, '').slice(0, codeMaxLength.value)
    code.value = value
  }

  const verifyCode = async () => {
    if (!code.value || code.value.length < codeMinLength.value) return

    loading.value = true
    error.value = null

    try {
      await authStore.verifyTwoFactor(code.value, selectedMethod.value as 'totp' | 'email')
      emit('verified')
    } catch (err) {
      error.value = (err as Error).message
      code.value = '' // Clear invalid code
    } finally {
      loading.value = false
    }
  }

  const cancel = () => {
    authStore.cancel2FA()
    emit('cancelled')
  }
</script>
