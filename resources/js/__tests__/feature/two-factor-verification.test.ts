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
    available_methods: ['totp'],
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

  it('auto-selects method when only one available', async () => {
    const wrapper = mount(TwoFactorVerification, {
      props: {
        challenge: mockChallenge,
      },
    })

    // Wait for component to mount and auto-select method
    await wrapper.vm.$nextTick()

    expect(wrapper.vm.selectedMethod).toBe('totp')
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
        challenge: mockChallenge,
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
