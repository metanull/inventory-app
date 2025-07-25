import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import Toggle from '../../format/Toggle.vue'
import { markRaw } from 'vue'

// Simple mock components for icons
const MockIconActive = markRaw({
  name: 'MockIconActive',
  template: '<div class="mock-icon-active">Active Icon</div>',
})

const MockIconInactive = markRaw({
  name: 'MockIconInactive',
  template: '<div class="mock-icon-inactive">Inactive Icon</div>',
})

describe('Toggle', () => {
  const defaultProps = {
    title: 'Test Toggle',
    statusText: 'Enabled',
    isActive: true,
    activeIconBackgroundClass: 'bg-green-100',
    inactiveIconBackgroundClass: 'bg-gray-100',
    activeIconClass: 'text-green-600',
    inactiveIconClass: 'text-gray-400',
    activeIconComponent: MockIconActive,
    inactiveIconComponent: MockIconInactive,
  }

  it('renders correctly', () => {
    const wrapper = mount(Toggle, {
      props: defaultProps,
    })

    expect(wrapper.exists()).toBe(true)
  })

  it('displays title and status text', () => {
    const wrapper = mount(Toggle, {
      props: defaultProps,
    })

    expect(wrapper.text()).toContain('Test Toggle')
    expect(wrapper.text()).toContain('Enabled')
  })

  it('applies correct card styling', () => {
    const wrapper = mount(Toggle, {
      props: defaultProps,
    })

    const card = wrapper.find('.bg-white.overflow-hidden.shadow.rounded-lg')
    expect(card.exists()).toBe(true)
  })

  it('shows active icon when isActive is true', () => {
    const wrapper = mount(Toggle, {
      props: { ...defaultProps, isActive: true },
    })

    expect(wrapper.text()).toContain('Active Icon')
  })

  it('shows inactive icon when isActive is false', () => {
    const wrapper = mount(Toggle, {
      props: { ...defaultProps, isActive: false },
    })

    expect(wrapper.text()).toContain('Inactive Icon')
  })

  it('applies active icon background class when isActive is true', () => {
    const wrapper = mount(Toggle, {
      props: { ...defaultProps, isActive: true },
    })

    const iconContainer = wrapper.find('.w-8.h-8.rounded-full')
    expect(iconContainer.classes()).toContain('bg-green-100')
  })

  it('applies inactive icon background class when isActive is false', () => {
    const wrapper = mount(Toggle, {
      props: { ...defaultProps, isActive: false },
    })

    const iconContainer = wrapper.find('.w-8.h-8.rounded-full')
    expect(iconContainer.classes()).toContain('bg-gray-100')
  })

  it('renders toggle button with correct accessibility attributes', () => {
    const wrapper = mount(Toggle, {
      props: defaultProps,
    })

    const button = wrapper.find('button')
    expect(button.exists()).toBe(true)
    expect(button.attributes('aria-pressed')).toBe('true')
    expect(button.attributes('aria-label')).toBe('Toggle test toggle')
  })

  it('applies active toggle styling when isActive is true', () => {
    const wrapper = mount(Toggle, {
      props: { ...defaultProps, isActive: true },
    })

    const button = wrapper.find('button')
    expect(button.classes()).toContain('bg-indigo-600')

    const slider = wrapper.find('.translate-x-5')
    expect(slider.exists()).toBe(true)
  })

  it('applies inactive toggle styling when isActive is false', () => {
    const wrapper = mount(Toggle, {
      props: { ...defaultProps, isActive: false },
    })

    const button = wrapper.find('button')
    expect(button.classes()).toContain('bg-gray-200')

    const slider = wrapper.find('.translate-x-0')
    expect(slider.exists()).toBe(true)
  })

  it('emits toggle event on button click', async () => {
    const wrapper = mount(Toggle, {
      props: defaultProps,
    })

    const button = wrapper.find('button')
    await button.trigger('click')

    expect(wrapper.emitted('toggle')).toBeTruthy()
  })

  it('does not emit toggle event when disabled', async () => {
    const wrapper = mount(Toggle, {
      props: { ...defaultProps, disabled: true },
    })

    const button = wrapper.find('button')
    await button.trigger('click')

    expect(wrapper.emitted('toggle')).toBeFalsy()
  })

  it('does not emit toggle event when loading', async () => {
    const wrapper = mount(Toggle, {
      props: { ...defaultProps, loading: true },
    })

    const button = wrapper.find('button')
    await button.trigger('click')

    expect(wrapper.emitted('toggle')).toBeFalsy()
  })

  it('shows loading text when loading is true', () => {
    const wrapper = mount(Toggle, {
      props: { ...defaultProps, loading: true },
    })

    expect(wrapper.text()).toContain('Updating...')
  })

  it('applies disabled styling when disabled', () => {
    const wrapper = mount(Toggle, {
      props: { ...defaultProps, disabled: true },
    })

    const button = wrapper.find('button')
    expect(button.attributes('disabled')).toBeDefined()
    expect(button.classes()).toContain('disabled:opacity-50')
    expect(button.classes()).toContain('disabled:cursor-not-allowed')
  })

  it('hides toggle switch when hideSwitch is true', () => {
    const wrapper = mount(Toggle, {
      props: { ...defaultProps, hideSwitch: true },
    })

    const button = wrapper.find('button')
    expect(button.exists()).toBe(false)
  })

  it('shows toggle switch by default', () => {
    const wrapper = mount(Toggle, {
      props: defaultProps,
    })

    const button = wrapper.find('button')
    expect(button.exists()).toBe(true)
  })

  // Small variant tests
  describe('Small variant', () => {
    const smallProps = {
      title: 'Test Toggle',
      statusText: 'Enabled',
      isActive: true,
      small: true,
    }

    it('renders small variant correctly', () => {
      const wrapper = mount(Toggle, {
        props: smallProps,
      })

      expect(wrapper.exists()).toBe(true)
      // Should not have card styling in small mode
      expect(wrapper.find('.bg-white.overflow-hidden.shadow.rounded-lg').exists()).toBe(false)
      // Should have centered layout
      expect(wrapper.find('.flex.items-center.justify-center').exists()).toBe(true)
    })

    it('displays only toggle button in small mode', () => {
      const wrapper = mount(Toggle, {
        props: smallProps,
      })

      expect(wrapper.text()).not.toContain('Test Toggle')
      expect(wrapper.text()).not.toContain('Enabled')

      const button = wrapper.find('button')
      expect(button.exists()).toBe(true)
    })

    it('has tooltip with title and status in small mode', () => {
      const wrapper = mount(Toggle, {
        props: smallProps,
      })

      const button = wrapper.find('button')
      expect(button.attributes('title')).toBe('Test Toggle: Enabled')
      expect(button.attributes('aria-label')).toBe('Toggle test toggle')
    })

    it('emits toggle event when clicked in small mode', () => {
      const wrapper = mount(Toggle, {
        props: smallProps,
      })

      const button = wrapper.find('button')
      button.trigger('click')

      expect(wrapper.emitted().toggle).toHaveLength(1)
    })

    it('shows correct toggle state in small mode', () => {
      const wrapper = mount(Toggle, {
        props: { ...smallProps, isActive: true },
      })

      const button = wrapper.find('button')
      expect(button.classes()).toContain('bg-indigo-600')

      const wrapper2 = mount(Toggle, {
        props: { ...smallProps, isActive: false },
      })

      const button2 = wrapper2.find('button')
      expect(button2.classes()).toContain('bg-gray-200')
    })

    it('disables button when disabled prop is true in small mode', () => {
      const wrapper = mount(Toggle, {
        props: { ...smallProps, disabled: true },
      })

      const button = wrapper.find('button')
      expect(button.attributes('disabled')).toBeDefined()
      expect(button.classes()).toContain('disabled:opacity-50')
    })

    it('disables button when loading prop is true in small mode', () => {
      const wrapper = mount(Toggle, {
        props: { ...smallProps, loading: true },
      })

      const button = wrapper.find('button')
      expect(button.attributes('disabled')).toBeDefined()
    })
  })
})
