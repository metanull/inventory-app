import { describe, it, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import GenericDropdown from '../../format/GenericDropdown.vue'

// Mock the dropdown styles utility
vi.mock('@/utils/dropdownStyles', () => ({
  getDropdownOptionClasses: vi.fn(() => 'mocked-class'),
  getDropdownOptionLabel: vi.fn(() => ' (mocked)'),
  DROPDOWN_OPTION_LABELS: {
    current: ' (Current)',
    systemDefault: ' (System Default)',
  },
}))

describe('GenericDropdown', () => {
  const mockOptions = [
    { id: '1', internal_name: 'Option 1', is_default: false },
    { id: '2', internal_name: 'Option 2', is_default: true },
    { id: '3', internal_name: 'Option 3', is_default: false },
  ]

  it('renders correctly', () => {
    const wrapper = mount(GenericDropdown, {
      props: {
        modelValue: '1',
        options: mockOptions,
      },
    })

    expect(wrapper.exists()).toBe(true)
    expect(wrapper.element.tagName).toBe('SELECT')
  })

  it('applies correct CSS classes', () => {
    const wrapper = mount(GenericDropdown, {
      props: {
        modelValue: '1',
        options: mockOptions,
      },
    })

    expect(wrapper.classes()).toContain('block')
    expect(wrapper.classes()).toContain('w-full')
    expect(wrapper.classes()).toContain('px-3')
    expect(wrapper.classes()).toContain('py-2')
  })

  it('applies disabled styles when disabled', () => {
    const wrapper = mount(GenericDropdown, {
      props: {
        modelValue: '1',
        options: mockOptions,
        disabled: true,
      },
    })

    expect(wrapper.classes()).toContain('bg-gray-100')
    expect(wrapper.classes()).toContain('cursor-not-allowed')
    expect(wrapper.attributes('disabled')).toBeDefined()
  })

  it('renders all options', () => {
    const wrapper = mount(GenericDropdown, {
      props: {
        modelValue: '1',
        options: mockOptions,
      },
    })

    const options = wrapper.findAll('option')
    // Should include regular options + no default option + separator
    expect(options.length).toBeGreaterThan(mockOptions.length)
  })

  it('shows no default option by default', () => {
    const wrapper = mount(GenericDropdown, {
      props: {
        modelValue: '1',
        options: mockOptions,
      },
    })

    const options = wrapper.findAll('option')
    const noDefaultOption = options.find(option => option.text().includes('No default'))
    expect(noDefaultOption).toBeTruthy()
  })

  it('hides no default option when showNoDefaultOption is false', () => {
    const wrapper = mount(GenericDropdown, {
      props: {
        modelValue: '1',
        options: mockOptions,
        showNoDefaultOption: false,
      },
    })

    const options = wrapper.findAll('option')
    const noDefaultOption = options.find(option => option.text().includes('No default'))
    expect(noDefaultOption).toBeFalsy()
  })

  it('uses custom no default label', () => {
    const wrapper = mount(GenericDropdown, {
      props: {
        modelValue: '1',
        options: mockOptions,
        noDefaultLabel: 'Custom No Default',
      },
    })

    const options = wrapper.findAll('option')
    const noDefaultOption = options.find(option => option.text().includes('Custom No Default'))
    expect(noDefaultOption).toBeTruthy()
  })

  it('sets correct value from modelValue prop', () => {
    const wrapper = mount(GenericDropdown, {
      props: {
        modelValue: '2',
        options: mockOptions,
      },
    })

    expect(wrapper.element.value).toBe('2')
  })

  it('emits update:modelValue on change', async () => {
    const wrapper = mount(GenericDropdown, {
      props: {
        modelValue: '1',
        options: mockOptions,
      },
    })

    await wrapper.setValue('3')

    const emitted = wrapper.emitted('update:modelValue')
    expect(emitted).toBeTruthy()
    expect(emitted![0]).toEqual(['3'])
  })

  it('handles empty options array', () => {
    const wrapper = mount(GenericDropdown, {
      props: {
        modelValue: '',
        options: [],
      },
    })

    expect(wrapper.exists()).toBe(true)
    // Should still render no default option
    const options = wrapper.findAll('option')
    expect(options.length).toBeGreaterThan(0)
  })

  it('uses custom sort function when provided', () => {
    const sortFunction = vi.fn(options =>
      [...options].sort((a, b) => b.internal_name.localeCompare(a.internal_name))
    )

    mount(GenericDropdown, {
      props: {
        modelValue: '1',
        options: mockOptions,
        sortFunction,
      },
    })

    expect(sortFunction).toHaveBeenCalled()
  })
})
