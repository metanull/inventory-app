import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import ViewButton from '../../../layout/list/ViewButton.vue'

describe('ViewButton', () => {
  it('renders correctly with default props', () => {
    const wrapper = mount(ViewButton)

    expect(wrapper.exists()).toBe(true)
    expect(wrapper.element.tagName).toBe('BUTTON')
  })

  it('applies correct styling classes', () => {
    const wrapper = mount(ViewButton)

    expect(wrapper.classes()).toContain('text-blue-600')
    expect(wrapper.classes()).toContain('hover:text-blue-800')
  })

  it('has default tooltip', () => {
    const wrapper = mount(ViewButton)

    expect(wrapper.attributes('title')).toBe('View')
  })

  it('uses custom tooltip when provided', () => {
    const wrapper = mount(ViewButton, {
      props: {
        tooltip: 'Custom tooltip text',
      },
    })

    expect(wrapper.attributes('title')).toBe('Custom tooltip text')
  })

  it('is not disabled by default', () => {
    const wrapper = mount(ViewButton)

    expect(wrapper.attributes('disabled')).toBeUndefined()
  })

  it('is disabled when disabled prop is true', () => {
    const wrapper = mount(ViewButton, {
      props: {
        disabled: true,
      },
    })

    expect(wrapper.attributes('disabled')).toBeDefined()
  })

  it('applies disabled styling when disabled', () => {
    const wrapper = mount(ViewButton, {
      props: {
        disabled: true,
      },
    })

    expect(wrapper.classes()).toContain('opacity-50')
    expect(wrapper.classes()).toContain('cursor-not-allowed')
  })

  it('applies hover styling when not disabled', () => {
    const wrapper = mount(ViewButton, {
      props: {
        disabled: false,
      },
    })

    expect(wrapper.classes()).toContain('hover:text-blue-800')
  })

  it('emits click event when clicked and not disabled', async () => {
    const wrapper = mount(ViewButton)

    await wrapper.trigger('click')

    expect(wrapper.emitted().click).toBeTruthy()
    expect(wrapper.emitted().click).toHaveLength(1)
  })

  it('does not emit click event when disabled', async () => {
    const wrapper = mount(ViewButton, {
      props: {
        disabled: true,
      },
    })

    await wrapper.trigger('click')

    expect(wrapper.emitted().click).toBeFalsy()
  })

  it('renders eye icon with correct attributes', () => {
    const wrapper = mount(ViewButton)

    const icon = wrapper.find('svg')
    expect(icon.exists()).toBe(true)
    expect(icon.classes()).toContain('h-5')
    expect(icon.classes()).toContain('w-5')
    expect(icon.attributes('fill')).toBe('none')
    expect(icon.attributes('stroke')).toBe('currentColor')
    expect(icon.attributes('viewBox')).toBe('0 0 24 24')
  })

  it('has correct eye icon paths', () => {
    const wrapper = mount(ViewButton)

    const paths = wrapper.findAll('svg path')
    expect(paths.length).toBeGreaterThanOrEqual(1)

    // Verify it has the expected icon structure
    expect(paths[0].attributes('stroke-linecap')).toBe('round')
    expect(paths[0].attributes('stroke-linejoin')).toBe('round')
    // HeroIcons may or may not have stroke-width attribute, so we don't test for specific value
  })

  it('handles click event properly when enabled', async () => {
    const wrapper = mount(ViewButton, {
      props: {
        disabled: false,
      },
    })

    await wrapper.trigger('click')

    expect(wrapper.emitted().click).toBeTruthy()
    expect(wrapper.emitted().click).toHaveLength(1)
  })

  it('handles disabled state correctly', () => {
    const wrapper = mount(ViewButton, {
      props: {
        disabled: true,
        tooltip: 'Cannot view this item',
      },
    })

    expect(wrapper.attributes('disabled')).toBeDefined()
    expect(wrapper.attributes('title')).toBe('Cannot view this item')
    expect(wrapper.classes()).toContain('opacity-50')
    expect(wrapper.classes()).toContain('cursor-not-allowed')
  })
})
