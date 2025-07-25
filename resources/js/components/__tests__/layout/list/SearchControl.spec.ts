import { describe, it, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'

vi.mock('@heroicons/vue/24/outline', () => ({
  MagnifyingGlassIcon: {
    name: 'MagnifyingGlassIcon',
    template: '<div class="mock-search-icon h-5 w-5">🔍</div>',
  },
}))

import SearchControl from '../../../layout/list/SearchControl.vue'

describe('SearchControl', () => {
  it('renders correctly with default props', () => {
    const wrapper = mount(SearchControl)

    expect(wrapper.exists()).toBe(true)
    expect(wrapper.find('input').exists()).toBe(true)
    expect(wrapper.find('button').exists()).toBe(true)
  })

  it('applies correct container styling', () => {
    const wrapper = mount(SearchControl)

    const container = wrapper.find('.flex.items-center.space-x-2')
    expect(container.exists()).toBe(true)
  })

  it('renders input with default placeholder', () => {
    const wrapper = mount(SearchControl)

    const input = wrapper.find('input')
    expect(input.attributes('placeholder')).toBe('Search...')
    expect(input.attributes('type')).toBe('text')
  })

  it('renders input with custom placeholder', () => {
    const wrapper = mount(SearchControl, {
      props: {
        placeholder: 'Custom Search Placeholder',
      },
    })

    const input = wrapper.find('input')
    expect(input.attributes('placeholder')).toBe('Custom Search Placeholder')
  })

  it('applies correct input styling classes', () => {
    const wrapper = mount(SearchControl)

    const input = wrapper.find('input')
    expect(input.classes()).toContain('block')
    expect(input.classes()).toContain('w-full')
    expect(input.classes()).toContain('rounded-md')
    expect(input.classes()).toContain('border-gray-300')
    expect(input.classes()).toContain('shadow-sm')
    expect(input.classes()).toContain('focus:border-indigo-500')
    expect(input.classes()).toContain('focus:ring-indigo-500')
    expect(input.classes()).toContain('sm:text-sm')
    expect(input.classes()).toContain('pl-3')
    expect(input.classes()).toContain('pr-10')
    expect(input.classes()).toContain('py-2')
  })

  it('initializes with modelValue prop', () => {
    const wrapper = mount(SearchControl, {
      props: {
        modelValue: 'initial value',
      },
    })

    const input = wrapper.find('input')
    expect((input.element as HTMLInputElement).value).toBe('initial value')
  })

  it('emits update:modelValue on input', async () => {
    const wrapper = mount(SearchControl)

    const input = wrapper.find('input')
    await input.setValue('test search')

    expect(wrapper.emitted()['update:modelValue']).toBeTruthy()
    expect(wrapper.emitted()['update:modelValue'][0]).toEqual(['test search'])
  })

  it('emits search event on enter key', async () => {
    const wrapper = mount(SearchControl, {
      props: {
        modelValue: 'search term',
      },
    })

    const input = wrapper.find('input')
    await input.trigger('keydown.enter')

    expect(wrapper.emitted().search).toBeTruthy()
    expect(wrapper.emitted().search[0]).toEqual(['search term'])
  })

  it('emits search event on button click', async () => {
    const wrapper = mount(SearchControl)

    // Set a search value first
    const input = wrapper.find('input')
    await input.setValue('button search')

    const button = wrapper.find('button')
    await button.trigger('click')

    expect(wrapper.emitted().search).toBeTruthy()
    expect(wrapper.emitted().search[0]).toEqual(['button search'])
  })

  it('renders search button with correct styling', () => {
    const wrapper = mount(SearchControl)

    const button = wrapper.find('button')
    expect(button.attributes('type')).toBe('button')
    expect(button.classes()).toContain('inline-flex')
    expect(button.classes()).toContain('items-center')
    expect(button.classes()).toContain('rounded-md')
    expect(button.classes()).toContain('border')
    expect(button.classes()).toContain('border-transparent')
    expect(button.classes()).toContain('bg-indigo-600')
    expect(button.classes()).toContain('px-3')
    expect(button.classes()).toContain('py-2')
    expect(button.classes()).toContain('text-sm')
    expect(button.classes()).toContain('font-medium')
    expect(button.classes()).toContain('leading-4')
    expect(button.classes()).toContain('text-white')
    expect(button.classes()).toContain('shadow-sm')
    expect(button.classes()).toContain('hover:bg-indigo-700')
    expect(button.classes()).toContain('focus:outline-none')
    expect(button.classes()).toContain('focus:ring-2')
    expect(button.classes()).toContain('focus:ring-indigo-500')
    expect(button.classes()).toContain('focus:ring-offset-2')
  })

  it('renders MagnifyingGlassIcon component', () => {
    const wrapper = mount(SearchControl)

    const searchIcon = wrapper.findComponent({ name: 'MagnifyingGlassIcon' })
    expect(searchIcon.exists()).toBe(true)
  })

  it('updates input value correctly', async () => {
    const wrapper = mount(SearchControl)

    const input = wrapper.find('input')
    await input.setValue('new value')

    expect((input.element as HTMLInputElement).value).toBe('new value')
  })

  it('handles multiple input changes', async () => {
    const wrapper = mount(SearchControl)

    const input = wrapper.find('input')

    await input.setValue('first')
    expect(wrapper.emitted()['update:modelValue']).toHaveLength(1)
    expect(wrapper.emitted()['update:modelValue'][0]).toEqual(['first'])

    await input.setValue('second')
    expect(wrapper.emitted()['update:modelValue']).toHaveLength(2)
    expect(wrapper.emitted()['update:modelValue'][1]).toEqual(['second'])
  })

  it('handles search with empty value', async () => {
    const wrapper = mount(SearchControl)

    const button = wrapper.find('button')
    await button.trigger('click')

    expect(wrapper.emitted().search).toBeTruthy()
    expect(wrapper.emitted().search[0]).toEqual([''])
  })

  it('has relative positioning for input container', () => {
    const wrapper = mount(SearchControl)

    const inputContainer = wrapper.find('.relative')
    expect(inputContainer.exists()).toBe(true)
  })
})
