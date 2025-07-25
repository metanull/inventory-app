import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import FilterButton from '../../../layout/list/FilterButton.vue'

describe('FilterButton', () => {
  it('renders correctly with required props', () => {
    const wrapper = mount(FilterButton, {
      props: {
        label: 'Test Filter',
        isActive: false,
      },
    })

    expect(wrapper.exists()).toBe(true)
    expect(wrapper.element.tagName).toBe('BUTTON')
    expect(wrapper.text()).toBe('Test Filter')
  })

  it('displays label text', () => {
    const wrapper = mount(FilterButton, {
      props: {
        label: 'Custom Filter Label',
        isActive: false,
      },
    })

    expect(wrapper.text()).toContain('Custom Filter Label')
  })

  it('applies base button classes', () => {
    const wrapper = mount(FilterButton, {
      props: {
        label: 'Test',
        isActive: false,
      },
    })

    expect(wrapper.classes()).toContain('px-3')
    expect(wrapper.classes()).toContain('py-2')
    expect(wrapper.classes()).toContain('font-medium')
    expect(wrapper.classes()).toContain('text-sm')
    expect(wrapper.classes()).toContain('rounded-md')
  })

  it('applies inactive classes when not active', () => {
    const wrapper = mount(FilterButton, {
      props: {
        label: 'Test',
        isActive: false,
      },
    })

    expect(wrapper.classes()).toContain('text-gray-500')
    expect(wrapper.classes()).toContain('hover:text-gray-700')
  })

  it('applies active primary classes by default', () => {
    const wrapper = mount(FilterButton, {
      props: {
        label: 'Test',
        isActive: true,
      },
    })

    expect(wrapper.classes()).toContain('bg-indigo-100')
    expect(wrapper.classes()).toContain('text-indigo-700')
  })

  it('applies active success classes with success variant', () => {
    const wrapper = mount(FilterButton, {
      props: {
        label: 'Test',
        isActive: true,
        variant: 'success',
      },
    })

    expect(wrapper.classes()).toContain('bg-green-100')
    expect(wrapper.classes()).toContain('text-green-700')
  })

  it('applies active info classes with info variant', () => {
    const wrapper = mount(FilterButton, {
      props: {
        label: 'Test',
        isActive: true,
        variant: 'info',
      },
    })

    expect(wrapper.classes()).toContain('bg-blue-100')
    expect(wrapper.classes()).toContain('text-blue-700')
  })

  it('emits click event when clicked', async () => {
    const wrapper = mount(FilterButton, {
      props: {
        label: 'Test',
        isActive: false,
      },
    })

    await wrapper.trigger('click')

    expect(wrapper.emitted().click).toBeTruthy()
    expect(wrapper.emitted().click).toHaveLength(1)
  })

  it('does not display count when count is undefined', () => {
    const wrapper = mount(FilterButton, {
      props: {
        label: 'Test',
        isActive: false,
      },
    })

    const countSpan = wrapper.find('span.ml-1')
    expect(countSpan.exists()).toBe(false)
  })

  it('displays count when count is provided', () => {
    const wrapper = mount(FilterButton, {
      props: {
        label: 'Test',
        isActive: false,
        count: 5,
      },
    })

    const countSpan = wrapper.find('span.ml-1')
    expect(countSpan.exists()).toBe(true)
    expect(countSpan.text()).toContain('(5)')
  })

  it('applies correct count styling classes', () => {
    const wrapper = mount(FilterButton, {
      props: {
        label: 'Test',
        isActive: false,
        count: 3,
      },
    })

    const countSpan = wrapper.find('span.ml-1')
    expect(countSpan.classes()).toContain('ml-1')
    expect(countSpan.classes()).toContain('hidden')
    expect(countSpan.classes()).toContain('sm:inline')
  })

  it('applies pill styling for counts on md+ screens', () => {
    const wrapper = mount(FilterButton, {
      props: {
        label: 'Test',
        isActive: false,
        count: 2,
      },
    })

    const countSpan = wrapper.find('span.ml-1')
    const classes = countSpan.classes()
    expect(classes).toContain('md:inline-flex')
    expect(classes).toContain('md:items-center')
    expect(classes).toContain('md:justify-center')
    expect(classes).toContain('md:px-2')
    expect(classes).toContain('md:py-0.5')
    expect(classes).toContain('md:text-xs')
    expect(classes).toContain('md:font-semibold')
    expect(classes).toContain('md:rounded-full')
  })

  it('applies orange styling for zero count', () => {
    const wrapper = mount(FilterButton, {
      props: {
        label: 'Test',
        isActive: false,
        count: 0,
      },
    })

    const countSpan = wrapper.find('span.ml-1')
    expect(countSpan.classes()).toContain('md:bg-orange-100')
    expect(countSpan.classes()).toContain('md:text-orange-700')
  })

  it('applies blue styling for non-zero count', () => {
    const wrapper = mount(FilterButton, {
      props: {
        label: 'Test',
        isActive: false,
        count: 5,
      },
    })

    const countSpan = wrapper.find('span.ml-1')
    expect(countSpan.classes()).toContain('md:bg-blue-100')
    expect(countSpan.classes()).toContain('md:text-blue-700')
  })

  it('renders responsive count display elements', () => {
    const wrapper = mount(FilterButton, {
      props: {
        label: 'Test',
        isActive: false,
        count: 7,
      },
    })

    // Text display for sm+ (hidden on md+)
    const textCount = wrapper.find('span.md\\:hidden')
    expect(textCount.exists()).toBe(true)
    expect(textCount.text()).toBe('(7)')

    // Pill display for md+
    const pillCount = wrapper.find('span.hidden.md\\:inline')
    expect(pillCount.exists()).toBe(true)
    expect(pillCount.text()).toBe('7')
  })

  it('handles different count values correctly', () => {
    const testCases = [0, 1, 10, 99, 100]

    testCases.forEach(count => {
      const wrapper = mount(FilterButton, {
        props: {
          label: 'Test',
          isActive: false,
          count,
        },
      })

      const textCount = wrapper.find('span.md\\:hidden')
      const pillCount = wrapper.find('span.hidden.md\\:inline')

      expect(textCount.text()).toBe(`(${count})`)
      expect(pillCount.text()).toBe(count.toString())
    })
  })
})
