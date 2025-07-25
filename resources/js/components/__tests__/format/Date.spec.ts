import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import Date from '../../format/Date.vue'

describe('Date', () => {
  const validDate = '2023-06-15T14:30:00Z'

  it('renders correctly with valid date', () => {
    const wrapper = mount(Date, {
      props: { date: validDate },
    })

    expect(wrapper.exists()).toBe(true)
    expect(wrapper.element.tagName).toBe('SPAN')
  })

  it('displays N/A for null date', () => {
    const wrapper = mount(Date, {
      props: { date: null },
    })

    expect(wrapper.text()).toBe('N/A')
  })

  it('displays N/A for undefined date', () => {
    const wrapper = mount(Date, {
      props: { date: undefined },
    })

    expect(wrapper.text()).toBe('N/A')
  })

  it('displays Invalid Date for malformed date string', () => {
    const wrapper = mount(Date, {
      props: { date: 'invalid-date-string' },
    })

    expect(wrapper.text()).toBe('Invalid Date')
  })

  it('applies custom className when provided', () => {
    const wrapper = mount(Date, {
      props: {
        date: validDate,
        className: 'custom-class',
      },
    })

    expect(wrapper.classes()).toContain('custom-class')
  })

  it('applies small-dark variant classes', () => {
    const wrapper = mount(Date, {
      props: {
        date: validDate,
        variant: 'small-dark',
      },
    })

    expect(wrapper.classes()).toContain('text-sm')
    expect(wrapper.classes()).toContain('text-gray-900')
  })

  it('has title attribute with full date format', () => {
    const wrapper = mount(Date, {
      props: { date: validDate },
    })

    const title = wrapper.attributes('title')
    expect(title).toBeTruthy()
    expect(title).not.toBe('No date available')
  })

  it('has appropriate title for null date', () => {
    const wrapper = mount(Date, {
      props: { date: null },
    })

    expect(wrapper.attributes('title')).toBe('No date available')
  })

  it('has appropriate title for invalid date', () => {
    const wrapper = mount(Date, {
      props: { date: 'invalid' },
    })

    expect(wrapper.attributes('title')).toBe('Invalid date')
  })

  // Note: Testing specific date format outputs would be brittle due to locale differences
  // The component correctly uses Intl.DateTimeFormat which varies by browser/locale
  // We test the structure and error handling instead
})
