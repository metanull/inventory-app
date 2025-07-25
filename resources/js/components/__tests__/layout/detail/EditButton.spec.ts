import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import EditButton from '../../../layout/detail/EditButton.vue'

describe('EditButton', () => {
  it('renders correctly with default props', () => {
    const wrapper = mount(EditButton)

    expect(wrapper.exists()).toBe(true)
    expect(wrapper.element.tagName).toBe('BUTTON')
    expect(wrapper.text()).toBe('Edit')
  })

  it('displays custom label when provided', () => {
    const wrapper = mount(EditButton, {
      props: {
        label: 'Custom Edit',
      },
    })

    expect(wrapper.text()).toBe('Custom Edit')
  })

  it('applies correct styling classes', () => {
    const wrapper = mount(EditButton)

    expect(wrapper.classes()).toContain('inline-flex')
    expect(wrapper.classes()).toContain('items-center')
    expect(wrapper.classes()).toContain('px-4')
    expect(wrapper.classes()).toContain('py-2')
    expect(wrapper.classes()).toContain('border')
    expect(wrapper.classes()).toContain('border-gray-300')
    expect(wrapper.classes()).toContain('shadow-sm')
    expect(wrapper.classes()).toContain('text-sm')
    expect(wrapper.classes()).toContain('font-medium')
    expect(wrapper.classes()).toContain('rounded-md')
    expect(wrapper.classes()).toContain('text-gray-700')
    expect(wrapper.classes()).toContain('bg-white')
  })

  it('applies hover and focus styles', () => {
    const wrapper = mount(EditButton)

    expect(wrapper.classes()).toContain('hover:bg-gray-50')
    expect(wrapper.classes()).toContain('focus:outline-none')
    expect(wrapper.classes()).toContain('focus:ring-2')
    expect(wrapper.classes()).toContain('focus:ring-offset-2')
    expect(wrapper.classes()).toContain('focus:ring-indigo-500')
  })

  it('is disabled when disabled prop is true', () => {
    const wrapper = mount(EditButton, {
      props: {
        disabled: true,
      },
    })

    expect(wrapper.attributes('disabled')).toBeDefined()
    expect(wrapper.classes()).toContain('disabled:opacity-50')
    expect(wrapper.classes()).toContain('disabled:cursor-not-allowed')
  })

  it('is not disabled when disabled prop is false', () => {
    const wrapper = mount(EditButton, {
      props: {
        disabled: false,
      },
    })

    expect(wrapper.attributes('disabled')).toBeUndefined()
  })

  it('emits click event when clicked and not disabled', async () => {
    const wrapper = mount(EditButton)

    await wrapper.trigger('click')

    expect(wrapper.emitted().click).toBeTruthy()
    expect(wrapper.emitted().click).toHaveLength(1)
  })

  it('does not emit click event when disabled', async () => {
    const wrapper = mount(EditButton, {
      props: {
        disabled: true,
      },
    })

    await wrapper.trigger('click')

    expect(wrapper.emitted().click).toBeFalsy()
  })

  it('renders edit icon with correct attributes', () => {
    const wrapper = mount(EditButton)

    const icon = wrapper.find('svg')
    expect(icon.exists()).toBe(true)
    expect(icon.classes()).toContain('h-4')
    expect(icon.classes()).toContain('w-4')
    expect(icon.classes()).toContain('mr-2')
    expect(icon.attributes('fill')).toBe('none')
    expect(icon.attributes('stroke')).toBe('currentColor')
    expect(icon.attributes('viewBox')).toBe('0 0 24 24')
  })

  it('has correct icon path for edit symbol', () => {
    const wrapper = mount(EditButton)

    const path = wrapper.find('svg path')
    expect(path.exists()).toBe(true)
    expect(path.attributes('stroke-linecap')).toBe('round')
    expect(path.attributes('stroke-linejoin')).toBe('round')
    // HeroIcons may or may not have stroke-width attribute, so we don't test for specific value
    expect(path.attributes('d')).toBeTruthy() // Just ensure it has a path
  })

  it('handles click event properly when not disabled', async () => {
    const wrapper = mount(EditButton, {
      props: {
        disabled: false,
      },
    })

    await wrapper.trigger('click')

    expect(wrapper.emitted().click).toBeTruthy()
    expect(wrapper.emitted().click).toHaveLength(1)
  })
})
