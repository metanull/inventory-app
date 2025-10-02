import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import TwoFactorVerification from '@/components/TwoFactorVerification.vue'
import { useAuthStore } from '@/stores/auth'

// Mock the useColors composable
vi.mock('@/composables/useColors', () => ({
  getThemeClass: (className: string) => `theme-${className}`,
}))

describe('TwoFactorVerification', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
  })

  const mockChallenge = {
    requires_two_factor: true,
    available_methods: ['totp', 'email'],
    primary_method: 'totp',
    message: 'Two-factor authentication required.',
  }

  it('renders 2FA challenge correctly', () => {
    const wrapper = mount(TwoFactorVerification, {
      props: {
        challenge: mockChallenge,
      },
    })

    expect(wrapper.text()).toContain('Two-Factor Authentication Required')
    expect(wrapper.text()).toContain(mockChallenge.message)
  })

  it('shows method selection when multiple methods available', () => {
    const wrapper = mount(TwoFactorVerification, {
      props: {
        challenge: mockChallenge,
      },
    })

    expect(wrapper.text()).toContain('Choose verification method')
    expect(wrapper.text()).toContain('Authenticator App')
    expect(wrapper.text()).toContain('Email Code')
  })

  it('auto-selects method when only one available', async () => {
    const singleMethodChallenge = {
      ...mockChallenge,
      available_methods: ['totp'],
    }

    const wrapper = mount(TwoFactorVerification, {
      props: {
        challenge: singleMethodChallenge,
      },
    })

    // Wait for component to mount and auto-select method
    await wrapper.vm.$nextTick()

    expect(wrapper.vm.selectedMethod).toBe('totp')
  })

  it('shows email code request for email method', async () => {
    const wrapper = mount(TwoFactorVerification, {
      props: {
        challenge: mockChallenge,
      },
    })

    // Select email method
    await wrapper.vm.selectMethod('email')

    expect(wrapper.text()).toContain('Send Email Code')
    expect(wrapper.vm.selectedMethod).toBe('email')
  })

  it('shows code input for TOTP method', async () => {
    const wrapper = mount(TwoFactorVerification, {
      props: {
        challenge: mockChallenge,
      },
    })

    // Select TOTP method
    await wrapper.vm.selectMethod('totp')

    expect(wrapper.find('input[type="text"]').exists()).toBe(true)
    expect(wrapper.find('input').attributes('placeholder')).toBe('000000')
  })

  it('formats code input correctly', async () => {
    const wrapper = mount(TwoFactorVerification, {
      props: {
        challenge: {
          ...mockChallenge,
          available_methods: ['totp'],
        },
      },
    })

    // Wait for component to mount and auto-select method
    await wrapper.vm.$nextTick()

    const input = wrapper.find('input[type="text"]')
    expect(input.exists()).toBe(true)

    // Test number formatting by directly calling the formatCode method
    const mockEvent = {
      target: { value: 'abc123def456' },
    } as Event & { target: { value: string } }

    wrapper.vm.formatCode(mockEvent)
    expect(wrapper.vm.code).toBe('123456')
  })

  it('handles email code request', async () => {
    const authStore = useAuthStore()
    const requestEmailCodeSpy = vi.spyOn(authStore, 'requestEmailCode').mockResolvedValue({
      message: 'Code sent',
      expires_in: 300,
    })

    const wrapper = mount(TwoFactorVerification, {
      props: {
        challenge: mockChallenge,
      },
    })

    // Select email method and request code
    await wrapper.vm.selectMethod('email')
    await wrapper.vm.sendEmailCode()

    expect(requestEmailCodeSpy).toHaveBeenCalled()
    expect(wrapper.vm.emailCodeSent).toBe(true)
  })

  it('handles 2FA verification', async () => {
    const authStore = useAuthStore()
    const verifyTwoFactorSpy = vi.spyOn(authStore, 'verifyTwoFactor').mockResolvedValue()

    const wrapper = mount(TwoFactorVerification, {
      props: {
        challenge: {
          ...mockChallenge,
          available_methods: ['totp'],
        },
      },
    })

    // Set code and verify
    wrapper.vm.code = '123456'
    await wrapper.vm.verifyCode()

    expect(verifyTwoFactorSpy).toHaveBeenCalledWith('123456', 'totp')
  })

  it('emits verified event on successful verification', async () => {
    const authStore = useAuthStore()
    vi.spyOn(authStore, 'verifyTwoFactor').mockResolvedValue()

    const wrapper = mount(TwoFactorVerification, {
      props: {
        challenge: {
          ...mockChallenge,
          available_methods: ['totp'],
        },
      },
    })

    wrapper.vm.code = '123456'
    await wrapper.vm.verifyCode()

    expect(wrapper.emitted().verified).toBeTruthy()
  })

  it('handles verification errors', async () => {
    const authStore = useAuthStore()
    const error = new Error('Invalid code')
    vi.spyOn(authStore, 'verifyTwoFactor').mockRejectedValue(error)

    const wrapper = mount(TwoFactorVerification, {
      props: {
        challenge: {
          ...mockChallenge,
          available_methods: ['totp'],
        },
      },
    })

    wrapper.vm.code = '123456'
    await wrapper.vm.verifyCode()

    expect(wrapper.vm.error).toBe('Invalid code')
    expect(wrapper.vm.code).toBe('') // Code should be cleared
  })

  it('handles cancellation', async () => {
    const authStore = useAuthStore()
    const cancel2FASpy = vi.spyOn(authStore, 'cancel2FA').mockImplementation(() => {})

    const wrapper = mount(TwoFactorVerification, {
      props: {
        challenge: mockChallenge,
      },
    })

    await wrapper.vm.cancel()

    expect(cancel2FASpy).toHaveBeenCalled()
    expect(wrapper.emitted().cancelled).toBeTruthy()
  })
})
