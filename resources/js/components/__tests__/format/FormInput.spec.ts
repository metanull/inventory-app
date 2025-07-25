import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import FormInput from '../../format/FormInput.vue'

describe('FormInput', () => {
  it('renders correctly', () => {
    const wrapper = mount(FormInput)

    expect(wrapper.exists()).toBe(true)
    expect(wrapper.element.tagName).toBe('INPUT')
  })

  it('applies correct CSS classes', () => {
    const wrapper = mount(FormInput)

    expect(wrapper.classes()).toContain('block')
    expect(wrapper.classes()).toContain('w-full')
    expect(wrapper.classes()).toContain('px-3')
    expect(wrapper.classes()).toContain('py-2')
    expect(wrapper.classes()).toContain('border')
    expect(wrapper.classes()).toContain('border-gray-300')
  })

  it('sets type attribute', () => {
    const wrapper = mount(FormInput, {
      props: { type: 'email' },
    })

    expect(wrapper.attributes('type')).toBe('email')
  })

  it('defaults to text type when no type provided', () => {
    const wrapper = mount(FormInput)

    // When no type is provided, the attribute is not set but behavior defaults to text
    const typeAttr = wrapper.attributes('type')
    expect(typeAttr).toBeUndefined()
    // The actual element type will still be 'text' in the DOM
    expect(wrapper.element.type).toBe('text')
  })

  it('sets placeholder attribute', () => {
    const wrapper = mount(FormInput, {
      props: { placeholder: 'Enter your name' },
    })

    expect(wrapper.attributes('placeholder')).toBe('Enter your name')
  })

  it('sets required attribute', () => {
    const wrapper = mount(FormInput, {
      props: { required: true },
    })

    expect(wrapper.attributes('required')).toBeDefined()
  })

  it('sets disabled attribute', () => {
    const wrapper = mount(FormInput, {
      props: { disabled: true },
    })

    expect(wrapper.attributes('disabled')).toBeDefined()
  })

  it('displays model value', () => {
    const wrapper = mount(FormInput, {
      props: { modelValue: 'test value' },
    })

    expect(wrapper.element.value).toBe('test value')
  })

  it('emits update:modelValue on input', async () => {
    const wrapper = mount(FormInput)

    await wrapper.setValue('new value')

    const emitted = wrapper.emitted('update:modelValue')
    expect(emitted).toBeTruthy()
    expect(emitted![0]).toEqual(['new value'])
  })

  it('handles multiple input events', async () => {
    const wrapper = mount(FormInput)

    await wrapper.setValue('first')
    await wrapper.setValue('second')

    const emitted = wrapper.emitted('update:modelValue')
    expect(emitted).toHaveLength(2)
    expect(emitted![0]).toEqual(['first'])
    expect(emitted![1]).toEqual(['second'])
  })
})
