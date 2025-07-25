import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, type VueWrapper } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import ErrorDisplay from '../../global/ErrorDisplay.vue'
import { useErrorDisplayStore } from '@/stores/errorDisplay'

// Mock the icons to avoid import issues
vi.mock('@heroicons/vue/24/solid', () => ({
  ExclamationTriangleIcon: {
    name: 'ExclamationTriangleIcon',
    template: '<div class="mock-exclamation-icon" />',
  },
  InformationCircleIcon: {
    name: 'InformationCircleIcon',
    template: '<div class="mock-info-icon" />',
  },
  XMarkIcon: { name: 'XMarkIcon', template: '<div class="mock-x-icon" />' },
}))

describe('ErrorDisplay', () => {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  let wrapper: VueWrapper<any>
  let store: ReturnType<typeof useErrorDisplayStore>

  beforeEach(() => {
    wrapper = mount(ErrorDisplay, {
      global: {
        plugins: [createTestingPinia({ createSpy: vi.fn })],
      },
    })
    store = useErrorDisplayStore()
  })

  it('renders correctly with no messages', () => {
    store.messages = []
    expect(wrapper.exists()).toBe(true)
    expect(wrapper.find('.fixed').exists()).toBe(true)
  })

  it('does not render messages when store is empty', () => {
    store.messages = []
    expect(wrapper.findAll('[data-testid="error-message"]').length).toBe(0)
  })

  it('renders error message with correct styling', async () => {
    const errorMessage = {
      id: '1',
      type: 'error' as const,
      text: 'Test error message',
      timestamp: Date.now(),
    }
    store.messages = [errorMessage]

    await wrapper.vm.$nextTick()

    const messageEl = wrapper.find('[class*="bg-red-50"]')
    expect(messageEl.exists()).toBe(true)
    expect(messageEl.classes()).toContain('bg-red-50')
    expect(messageEl.classes()).toContain('border-red-200')
    expect(messageEl.classes()).toContain('text-red-800')
    expect(messageEl.text()).toContain('Test error message')
  })

  it('renders warning message with correct styling', async () => {
    const warningMessage = {
      id: '2',
      type: 'warning' as const,
      text: 'Test warning message',
      timestamp: Date.now(),
    }
    store.messages = [warningMessage]

    await wrapper.vm.$nextTick()

    const messageEl = wrapper.find('[class*="bg-yellow-50"]')
    expect(messageEl.exists()).toBe(true)
    expect(messageEl.classes()).toContain('bg-yellow-50')
    expect(messageEl.classes()).toContain('border-yellow-200')
    expect(messageEl.classes()).toContain('text-yellow-800')
    expect(messageEl.text()).toContain('Test warning message')
  })

  it('renders info message with correct styling', async () => {
    const infoMessage = {
      id: '3',
      type: 'info' as const,
      text: 'Test info message',
      timestamp: Date.now(),
    }
    store.messages = [infoMessage]

    await wrapper.vm.$nextTick()

    const messageEl = wrapper.find('[class*="bg-blue-50"]')
    expect(messageEl.exists()).toBe(true)
    expect(messageEl.classes()).toContain('bg-blue-50')
    expect(messageEl.classes()).toContain('border-blue-200')
    expect(messageEl.classes()).toContain('text-blue-800')
    expect(messageEl.text()).toContain('Test info message')
  })

  it('renders ExclamationTriangleIcon for error messages', async () => {
    const errorMessage = {
      id: '1',
      type: 'error' as const,
      text: 'Test error',
      timestamp: Date.now(),
    }
    store.messages = [errorMessage]

    await wrapper.vm.$nextTick()

    const icons = wrapper.findAllComponents({ name: 'ExclamationTriangleIcon' })
    expect(icons.length).toBe(1)
    expect(icons[0].classes()).toContain('mock-exclamation-icon')
    expect(icons[0].attributes('aria-hidden')).toBe('true')
  })

  it('renders ExclamationTriangleIcon for warning messages', async () => {
    const warningMessage = {
      id: '1',
      type: 'warning' as const,
      text: 'Test warning',
      timestamp: Date.now(),
    }
    store.messages = [warningMessage]

    await wrapper.vm.$nextTick()

    const icons = wrapper.findAllComponents({ name: 'ExclamationTriangleIcon' })
    expect(icons.length).toBe(1)
    expect(icons[0].classes()).toContain('mock-exclamation-icon')
    expect(icons[0].attributes('aria-hidden')).toBe('true')
  })

  it('renders InformationCircleIcon for info messages', async () => {
    const infoMessage = {
      id: '1',
      type: 'info' as const,
      text: 'Test info',
      timestamp: Date.now(),
    }
    store.messages = [infoMessage]

    await wrapper.vm.$nextTick()

    const icon = wrapper.findComponent({ name: 'InformationCircleIcon' })
    expect(icon.exists()).toBe(true)
    expect(icon.classes()).toContain('mock-info-icon')
    expect(icon.attributes('aria-hidden')).toBe('true')
  })

  it('renders multiple messages correctly', async () => {
    const messages = [
      { id: '1', type: 'error' as const, text: 'Error 1', timestamp: Date.now() },
      { id: '2', type: 'warning' as const, text: 'Warning 1', timestamp: Date.now() },
      { id: '3', type: 'info' as const, text: 'Info 1', timestamp: Date.now() },
    ]
    store.messages = messages

    await wrapper.vm.$nextTick()

    expect(wrapper.text()).toContain('Error 1')
    expect(wrapper.text()).toContain('Warning 1')
    expect(wrapper.text()).toContain('Info 1')
  })

  it('renders dismiss button with correct styling for each message type', async () => {
    const errorMessage = {
      id: '1',
      type: 'error' as const,
      text: 'Test error',
      timestamp: Date.now(),
    }
    store.messages = [errorMessage]

    await wrapper.vm.$nextTick()

    const dismissButton = wrapper.find('button[class*="text-red-400"]')
    expect(dismissButton.exists()).toBe(true)
    expect(dismissButton.classes()).toContain('text-red-400')
    expect(dismissButton.classes()).toContain('hover:bg-red-100')
    expect(dismissButton.classes()).toContain('focus:ring-red-500')

    const xIcon = dismissButton.findComponent({ name: 'XMarkIcon' })
    expect(xIcon.exists()).toBe(true)
    expect(xIcon.classes()).toContain('mock-x-icon')
  })

  it('calls removeMessage when dismiss button is clicked', async () => {
    const message = {
      id: 'test-id',
      type: 'error' as const,
      text: 'Test error',
      timestamp: Date.now(),
    }
    store.messages = [message]

    await wrapper.vm.$nextTick()

    const dismissButton = wrapper.find('button')
    await dismissButton.trigger('click')

    expect(store.removeMessage).toHaveBeenCalledWith('test-id')
  })

  it('has correct accessibility attributes', async () => {
    const message = {
      id: '1',
      type: 'error' as const,
      text: 'Test error',
      timestamp: Date.now(),
    }
    store.messages = [message]

    await wrapper.vm.$nextTick()

    const dismissButton = wrapper.find('button')
    const srOnly = dismissButton.find('.sr-only')
    expect(srOnly.exists()).toBe(true)
    expect(srOnly.text()).toBe('Dismiss')
  })

  it('applies correct transition group properties', () => {
    const transitionGroup = wrapper.findComponent({ name: 'TransitionGroup' })
    expect(transitionGroup.exists()).toBe(true)
    expect(transitionGroup.props('name')).toBe('error-message')
    expect(transitionGroup.props('tag')).toBe('div')
    expect(transitionGroup.classes()).toContain('fixed')
    expect(transitionGroup.classes()).toContain('top-16')
    expect(transitionGroup.classes()).toContain('z-40')
  })
})
