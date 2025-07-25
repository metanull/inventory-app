import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import DisplayText from '../../format/DisplayText.vue'

describe('DisplayText', () => {
  it('renders correctly', () => {
    const wrapper = mount(DisplayText)

    expect(wrapper.exists()).toBe(true)
    expect(wrapper.element.tagName).toBe('SPAN')
  })

  it('renders slot content', () => {
    const wrapper = mount(DisplayText, {
      slots: {
        default: 'Test content',
      },
    })

    expect(wrapper.text()).toBe('Test content')
  })

  it('applies default variant (no additional classes)', () => {
    const wrapper = mount(DisplayText, {
      props: { variant: 'default' },
    })

    // Default variant should not add any classes
    expect(wrapper.classes()).toEqual([])
  })

  it('applies gray variant classes', () => {
    const wrapper = mount(DisplayText, {
      props: { variant: 'gray' },
    })

    expect(wrapper.classes()).toContain('text-gray-500')
  })

  it('applies muted variant classes', () => {
    const wrapper = mount(DisplayText, {
      props: { variant: 'muted' },
    })

    expect(wrapper.classes()).toContain('text-gray-500')
  })

  it('defaults to no additional classes when no variant is provided', () => {
    const wrapper = mount(DisplayText)

    expect(wrapper.classes()).toEqual([])
  })
})
