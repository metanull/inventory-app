import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import AddButton from '../../../layout/list/AddButton.vue'

// Mock RouterLink component
const RouterLinkMock = {
  name: 'RouterLink',
  template: '<a class="mock-router-link"><slot /></a>',
  props: ['to'],
}

describe('AddButton', () => {
  const defaultMountOptions = {
    global: {
      components: {
        RouterLink: RouterLinkMock,
      },
    },
  }

  it('renders correctly with default props', () => {
    const wrapper = mount(AddButton, {
      props: {
        to: '/add',
      },
      ...defaultMountOptions,
    })

    expect(wrapper.exists()).toBe(true)
    expect(wrapper.text()).toContain('Add Item')
  })

  it('displays custom label when provided', () => {
    const wrapper = mount(AddButton, {
      props: {
        to: '/add',
        label: 'Add Custom Item',
      },
      ...defaultMountOptions,
    })

    expect(wrapper.text()).toContain('Add Custom Item')
  })

  it('uses RouterLink with correct props', () => {
    const wrapper = mount(AddButton, {
      props: {
        to: '/custom-add',
      },
      ...defaultMountOptions,
    })

    const routerLink = wrapper.findComponent({ name: 'RouterLink' })
    expect(routerLink.exists()).toBe(true)
    expect(routerLink.props('to')).toBe('/custom-add')
  })

  it('renders plus icon with correct attributes', () => {
    const wrapper = mount(AddButton, {
      props: {
        to: '/add',
      },
      ...defaultMountOptions,
    })

    const svg = wrapper.find('svg')
    expect(svg.exists()).toBe(true)
    expect(svg.classes()).toContain('-ml-1')
    expect(svg.classes()).toContain('mr-2')
    expect(svg.classes()).toContain('h-5')
    expect(svg.classes()).toContain('w-5')
  })

  it('has correct plus icon path', () => {
    const wrapper = mount(AddButton, {
      props: {
        to: '/add',
      },
      ...defaultMountOptions,
    })

    const svg = wrapper.find('svg')
    expect(svg.exists()).toBe(true)
    expect(svg.attributes('viewBox')).toBe('0 0 20 20')
  })

  it('renders RouterLink as root element', () => {
    const wrapper = mount(AddButton, {
      props: {
        to: '/add',
      },
      ...defaultMountOptions,
    })

    expect(wrapper.findComponent({ name: 'RouterLink' }).exists()).toBe(true)
  })

  it('passes through the to prop', () => {
    const wrapper = mount(AddButton, {
      props: {
        to: '/test-route',
      },
      ...defaultMountOptions,
    })

    const routerLink = wrapper.findComponent({ name: 'RouterLink' })
    expect(routerLink.props('to')).toBe('/test-route')
  })

  it('renders icon and text together', () => {
    const wrapper = mount(AddButton, {
      props: {
        to: '/add',
        label: 'Custom Label',
      },
      ...defaultMountOptions,
    })

    expect(wrapper.find('svg').exists()).toBe(true)
    expect(wrapper.text()).toContain('Custom Label')
  })

  it('uses default label when none provided', () => {
    const wrapper = mount(AddButton, {
      props: {
        to: '/add',
      },
      ...defaultMountOptions,
    })

    expect(wrapper.text()).toContain('Add Item')
  })

  it('has accessible structure', () => {
    const wrapper = mount(AddButton, {
      props: {
        to: '/add',
        label: 'Add New Item',
      },
      ...defaultMountOptions,
    })

    const routerLink = wrapper.findComponent({ name: 'RouterLink' })
    expect(routerLink.exists()).toBe(true)
    expect(wrapper.text()).toContain('Add New Item')
  })
})
