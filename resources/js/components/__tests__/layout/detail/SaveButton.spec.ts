import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import SaveButton from '../../../layout/detail/SaveButton.vue'

describe('SaveButton', () => {
  it('renders correctly with default props', () => {
    const wrapper = mount(SaveButton)

    expect(wrapper.exists()).toBe(true)
    expect(wrapper.element.tagName).toBe('BUTTON')
    expect(wrapper.text()).toBe('Save')
  })

  it('displays custom label when provided', () => {
    const wrapper = mount(SaveButton, {
      props: {
        label: 'Custom Save',
      },
    })

    expect(wrapper.text()).toBe('Custom Save')
  })

  it('applies correct green styling classes', () => {
    const wrapper = mount(SaveButton)

    expect(wrapper.classes()).toContain('inline-flex')
    expect(wrapper.classes()).toContain('items-center')
    expect(wrapper.classes()).toContain('px-4')
    expect(wrapper.classes()).toContain('py-2')
    expect(wrapper.classes()).toContain('border')
    expect(wrapper.classes()).toContain('border-transparent')
    expect(wrapper.classes()).toContain('text-sm')
    expect(wrapper.classes()).toContain('font-medium')
    expect(wrapper.classes()).toContain('rounded-md')
    expect(wrapper.classes()).toContain('text-white')
    expect(wrapper.classes()).toContain('bg-green-600')
  })

  it('applies hover and focus styles', () => {
    const wrapper = mount(SaveButton)

    expect(wrapper.classes()).toContain('hover:bg-green-700')
    expect(wrapper.classes()).toContain('focus:outline-none')
    expect(wrapper.classes()).toContain('focus:ring-2')
    expect(wrapper.classes()).toContain('focus:ring-offset-2')
    expect(wrapper.classes()).toContain('focus:ring-green-500')
  })

  it('is disabled when disabled prop is true', () => {
    const wrapper = mount(SaveButton, {
      props: {
        disabled: true,
      },
    })

    expect(wrapper.attributes('disabled')).toBeDefined()
    expect(wrapper.classes()).toContain('disabled:opacity-50')
    expect(wrapper.classes()).toContain('disabled:cursor-not-allowed')
  })

  it('is disabled when loading prop is true', () => {
    const wrapper = mount(SaveButton, {
      props: {
        loading: true,
      },
    })

    expect(wrapper.attributes('disabled')).toBeDefined()
  })

  it('is disabled when both disabled and loading are true', () => {
    const wrapper = mount(SaveButton, {
      props: {
        disabled: true,
        loading: true,
      },
    })

    expect(wrapper.attributes('disabled')).toBeDefined()
  })

  it('is not disabled when both disabled and loading are false', () => {
    const wrapper = mount(SaveButton, {
      props: {
        disabled: false,
        loading: false,
      },
    })

    expect(wrapper.attributes('disabled')).toBeUndefined()
  })

  it('emits click event when clicked and not disabled or loading', async () => {
    const wrapper = mount(SaveButton, {
      props: {
        disabled: false,
        loading: false,
      },
    })

    await wrapper.trigger('click')

    expect(wrapper.emitted().click).toBeTruthy()
    expect(wrapper.emitted().click).toHaveLength(1)
  })

  it('does not emit click event when disabled', async () => {
    const wrapper = mount(SaveButton, {
      props: {
        disabled: true,
      },
    })

    await wrapper.trigger('click')

    expect(wrapper.emitted().click).toBeFalsy()
  })

  it('does not emit click event when loading', async () => {
    const wrapper = mount(SaveButton, {
      props: {
        loading: true,
      },
    })

    await wrapper.trigger('click')

    expect(wrapper.emitted().click).toBeFalsy()
  })

  it('does not emit click event when both disabled and loading', async () => {
    const wrapper = mount(SaveButton, {
      props: {
        disabled: true,
        loading: true,
      },
    })

    await wrapper.trigger('click')

    expect(wrapper.emitted().click).toBeFalsy()
  })

  it('renders checkmark icon with correct attributes', () => {
    const wrapper = mount(SaveButton)

    const icon = wrapper.find('svg')
    expect(icon.exists()).toBe(true)
    expect(icon.classes()).toContain('h-4')
    expect(icon.classes()).toContain('w-4')
    expect(icon.classes()).toContain('mr-2')
    expect(icon.attributes('fill')).toBe('none')
    expect(icon.attributes('stroke')).toBe('currentColor')
    expect(icon.attributes('viewBox')).toBe('0 0 24 24')
  })

  it('has correct icon path for checkmark symbol', () => {
    const wrapper = mount(SaveButton)

    const path = wrapper.find('svg path')
    expect(path.exists()).toBe(true)
    expect(path.attributes('stroke-linecap')).toBe('round')
    expect(path.attributes('stroke-linejoin')).toBe('round')
    // HeroIcons may or may not have stroke-width attribute, so we don't test for specific value
    expect(path.attributes('d')).toBeTruthy() // Just ensure it has a path
  })

  it('handles all prop combinations correctly', async () => {
    const testCases = [
      { disabled: false, loading: false, shouldEmit: true },
      { disabled: true, loading: false, shouldEmit: false },
      { disabled: false, loading: true, shouldEmit: false },
      { disabled: true, loading: true, shouldEmit: false },
    ]

    for (const testCase of testCases) {
      const wrapper = mount(SaveButton, {
        props: {
          disabled: testCase.disabled,
          loading: testCase.loading,
        },
      })

      await wrapper.trigger('click')

      if (testCase.shouldEmit) {
        expect(wrapper.emitted().click).toBeTruthy()
      } else {
        expect(wrapper.emitted().click).toBeFalsy()
      }
    }
  })
})
