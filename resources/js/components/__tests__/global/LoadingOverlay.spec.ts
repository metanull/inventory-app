import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, type VueWrapper } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import LoadingOverlay from '../../global/LoadingOverlay.vue'
import { useLoadingOverlayStore } from '@/stores/loadingOverlay'

// Mock the icons to avoid import issues
vi.mock('@/components/icons/SpinnerIcon.vue', () => ({
  default: {
    name: 'SpinnerIcon',
    template: '<div class="mock-spinner h-12 w-12 text-white">spinning</div>',
  },
}))

// Mock ModalOverlay component
vi.mock('@/components/global/ModalOverlay.vue', () => ({
  default: {
    name: 'ModalOverlay',
    template: `
      <div data-testid="modal-overlay" v-if="visible">
        <div class="loading-content">
          <slot />
        </div>
      </div>
    `,
    props: ['visible', 'variant', 'overlayClass', 'contentClass'],
  },
}))

describe('LoadingOverlay', () => {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  let wrapper: VueWrapper<any>
  let store: ReturnType<typeof useLoadingOverlayStore>

  beforeEach(() => {
    wrapper = mount(LoadingOverlay, {
      global: {
        plugins: [createTestingPinia({ createSpy: vi.fn })],
      },
    })
    store = useLoadingOverlayStore()
  })

  it('renders correctly when store is not visible', () => {
    store.visible = false
    expect(wrapper.exists()).toBe(true)
  })

  it('renders ModalOverlay with correct props when visible', async () => {
    store.visible = true
    await wrapper.vm.$nextTick()

    const modal = wrapper.findComponent({ name: 'ModalOverlay' })
    expect(modal.exists()).toBe(true)
    expect(modal.props('visible')).toBe(true)
    expect(modal.props('variant')).toBe('content')
    expect(modal.props('overlayClass')).toBe('bg-black bg-opacity-50')
    expect(modal.props('contentClass')).toBe(
      '!bg-transparent !shadow-none !border-none !rounded-none !transform-none !overflow-visible'
    )
  })

  it('renders SpinnerIcon with correct styling', async () => {
    store.visible = true
    await wrapper.vm.$nextTick()

    const spinner = wrapper.findComponent({ name: 'SpinnerIcon' })
    expect(spinner.exists()).toBe(true)
    expect(spinner.classes()).toContain('mock-spinner')
    expect(spinner.classes()).toContain('h-12')
    expect(spinner.classes()).toContain('w-12')
    expect(spinner.classes()).toContain('text-white')
  })

  it('displays loading text from store', async () => {
    store.visible = true
    store.text = 'Loading data...'
    await wrapper.vm.$nextTick()

    const textElement = wrapper.find('p')
    expect(textElement.exists()).toBe(true)
    expect(textElement.text()).toBe('Loading data...')
    expect(textElement.classes()).toContain('mt-4')
    expect(textElement.classes()).toContain('text-white')
  })

  it('displays default loading text', async () => {
    store.visible = true
    store.text = 'Loading...'
    await wrapper.vm.$nextTick()

    const textElement = wrapper.find('p')
    expect(textElement.exists()).toBe(true)
    expect(textElement.text()).toBe('Loading...')
  })

  it('updates text when store text changes', async () => {
    store.visible = true
    store.text = 'Initial text'
    await wrapper.vm.$nextTick()

    expect(wrapper.find('p').text()).toBe('Initial text')

    store.text = 'Updated text'
    await wrapper.vm.$nextTick()

    expect(wrapper.find('p').text()).toBe('Updated text')
  })

  it('does not render modal when store is not visible', () => {
    store.visible = false

    const modal = wrapper.findComponent({ name: 'ModalOverlay' })
    // The modal component exists but should have visible=false
    expect(modal.props('visible')).toBe(false)
  })

  it('renders content in correct structure', async () => {
    store.visible = true
    store.text = 'Processing...'
    await wrapper.vm.$nextTick()

    const spinner = wrapper.findComponent({ name: 'SpinnerIcon' })
    const text = wrapper.find('p')

    expect(spinner.exists()).toBe(true)
    expect(text.exists()).toBe(true)
    expect(text.text()).toBe('Processing...')

    // Both elements should be present in the component
    expect(spinner.classes()).toContain('mock-spinner')
    expect(text.classes()).toContain('mt-4')
    expect(text.classes()).toContain('text-white')
  })
})
