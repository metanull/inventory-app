import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import InternalName from '../../format/InternalName.vue'

// Create a mock Title component for testing
const MockTitle = {
  name: 'Title',
  template:
    '<h2 class="mock-title" :data-description="description">{{ $slots.default()[0].children }}</h2>',
  props: ['variant', 'description'],
}

describe('InternalName', () => {
  it('renders correctly', () => {
    const wrapper = mount(InternalName, {
      props: {
        internalName: 'Test Name',
      },
    })

    expect(wrapper.exists()).toBe(true)
  })

  it('displays internal name', () => {
    const wrapper = mount(InternalName, {
      props: {
        internalName: 'My Internal Name',
      },
    })

    expect(wrapper.text()).toContain('My Internal Name')
  })

  it('applies correct CSS classes to internal name', () => {
    const wrapper = mount(InternalName, {
      props: {
        internalName: 'Test Name',
      },
      global: {
        stubs: {
          Title: MockTitle,
        },
      },
    })

    // Should use Title component by default (when small is false)
    const titleElement = wrapper.findComponent({ name: 'Title' })
    expect(titleElement.exists()).toBe(true)
    expect(wrapper.text()).toContain('Test Name')
  })

  it('shows backward compatibility when provided', () => {
    const wrapper = mount(InternalName, {
      props: {
        internalName: 'Test Name',
        backwardCompatibility: 'legacy-123',
      },
    })

    expect(wrapper.text()).toContain('Legacy ID: legacy-123')
  })

  it('applies correct CSS classes to backward compatibility', () => {
    const wrapper = mount(InternalName, {
      props: {
        internalName: 'Test Name',
        backwardCompatibility: 'legacy-123',
      },
      global: {
        stubs: {
          Title: MockTitle,
        },
      },
    })

    // Should pass legacy info as description to Title component
    const titleElement = wrapper.findComponent({ name: 'Title' })
    expect(titleElement.exists()).toBe(true)
    expect(titleElement.props('description')).toBe('Legacy ID: legacy-123')
  })

  it('hides backward compatibility when null', () => {
    const wrapper = mount(InternalName, {
      props: {
        internalName: 'Test Name',
        backwardCompatibility: null,
      },
    })

    expect(wrapper.text()).not.toContain('Legacy ID:')
  })

  it('hides backward compatibility when undefined', () => {
    const wrapper = mount(InternalName, {
      props: {
        internalName: 'Test Name',
      },
    })

    expect(wrapper.text()).not.toContain('Legacy ID:')
  })

  it('hides backward compatibility when empty string', () => {
    const wrapper = mount(InternalName, {
      props: {
        internalName: 'Test Name',
        backwardCompatibility: '',
      },
    })

    expect(wrapper.text()).not.toContain('Legacy ID:')
  })

  // Small variant tests
  it('renders small variant correctly', () => {
    const wrapper = mount(InternalName, {
      props: {
        internalName: 'Test Name',
        small: true,
      },
    })

    expect(wrapper.exists()).toBe(true)
    // Should use direct text elements in small mode, not Title component
    expect(wrapper.findComponent({ name: 'Title' }).exists()).toBe(false)
    expect(wrapper.find('.text-sm.font-medium.text-gray-900').exists()).toBe(true)
  })

  it('shows responsive icon container in small mode', () => {
    const wrapper = mount(InternalName, {
      props: {
        internalName: 'Test Name',
        small: true,
      },
    })

    const iconContainer = wrapper.find('.flex-shrink-0')
    expect(iconContainer.exists()).toBe(true)
    // In small mode, margin should be mr-2
    expect(iconContainer.classes()).toContain('mr-2')
  })

  it('displays legacy text differently in small mode', () => {
    const wrapper = mount(InternalName, {
      props: {
        internalName: 'Test Name',
        backwardCompatibility: 'legacy-123',
        small: true,
      },
    })

    expect(wrapper.text()).toContain('Legacy: legacy-123')
    expect(wrapper.find('.text-xs.text-gray-500').exists()).toBe(true)
  })

  it('renders slot content for icon in small mode', () => {
    const wrapper = mount(InternalName, {
      props: {
        internalName: 'Test Name',
        small: true,
      },
      slots: {
        icon: '<div class="custom-icon">Custom Icon</div>',
      },
    })

    expect(wrapper.html()).toContain('Custom Icon')
  })

  it('uses default icon when no slot provided in small mode', () => {
    const wrapper = mount(InternalName, {
      props: {
        internalName: 'Test Name',
        small: true,
      },
    })

    // Should contain the default RectangleGroupIcon
    expect(wrapper.find('.h-5.w-5.text-gray-600').exists()).toBe(true)
  })
})
