import { describe, it, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import SystemProperties from '../../../layout/detail/SystemProperties.vue'

// Mock the imported components
vi.mock('@/components/format/Date.vue', () => ({
  default: {
    name: 'DateDisplay',
    template: '<span class="mock-date-display">{{ date }}</span>',
    props: {
      date: { type: String },
      format: { type: String },
      showTime: { type: Boolean, default: false },
    },
  },
}))

vi.mock('@/components/format/Uuid.vue', () => ({
  default: {
    name: 'UuidDisplay',
    template: '<span class="mock-uuid-display">{{ uuid }}</span>',
    props: ['uuid', 'format'],
  },
}))

vi.mock('@/components/format/description/DescriptionList.vue', () => ({
  default: {
    name: 'DescriptionList',
    template: '<dl class="mock-description-list"><slot /></dl>',
  },
}))

vi.mock('@/components/format/description/DescriptionRow.vue', () => ({
  default: {
    name: 'DescriptionRow',
    template: '<div class="mock-description-row"><slot /></div>',
    props: ['variant', 'size'],
  },
}))

vi.mock('@/components/format/description/DescriptionTerm.vue', () => ({
  default: {
    name: 'DescriptionTerm',
    template: '<dt class="mock-description-term"><slot /></dt>',
    props: ['variant'],
  },
}))

vi.mock('@/components/format/description/DescriptionDetail.vue', () => ({
  default: {
    name: 'DescriptionDetail',
    template: '<dd class="mock-description-detail"><slot /></dd>',
    props: ['variant'],
  },
}))

vi.mock('@/components/format/title/Title.vue', () => ({
  default: {
    name: 'Title',
    template: '<h3 class="mock-title"><slot /></h3>',
    props: ['variant', 'description'],
  },
}))

describe('SystemProperties', () => {
  const defaultProps = {
    id: '123e4567-e89b-12d3-a456-426614174000',
    createdAt: '2023-06-15T14:30:00Z',
    updatedAt: '2023-06-16T10:20:00Z',
  }

  it('renders correctly with all props', () => {
    const wrapper = mount(SystemProperties, {
      props: defaultProps,
    })

    expect(wrapper.exists()).toBe(true)
    expect(wrapper.classes()).toContain('bg-white')
    expect(wrapper.classes()).toContain('shadow')
    expect(wrapper.classes()).toContain('overflow-hidden')
    expect(wrapper.classes()).toContain('sm:rounded-lg')
  })

  it('renders system properties title', () => {
    const wrapper = mount(SystemProperties, {
      props: defaultProps,
    })

    const title = wrapper.findComponent({ name: 'Title' })
    expect(title.exists()).toBe(true)
    expect(title.props('variant')).toBe('system')
    expect(title.props('description')).toBe('Internal system data managed by the API.')
    expect(title.text()).toBe('System Properties')
  })

  it('displays ID with UuidDisplay component', () => {
    const wrapper = mount(SystemProperties, {
      props: defaultProps,
    })

    const uuidDisplay = wrapper.findComponent({ name: 'UuidDisplay' })
    expect(uuidDisplay.exists()).toBe(true)
    expect(uuidDisplay.props('uuid')).toBe(defaultProps.id)
    expect(uuidDisplay.props('format')).toBe('long')
  })

  it('displays created date with DateDisplay component', () => {
    const wrapper = mount(SystemProperties, {
      props: defaultProps,
    })

    const dateDisplays = wrapper.findAllComponents({ name: 'DateDisplay' })
    expect(dateDisplays.length).toBe(2)

    const createdDateDisplay = dateDisplays[0]
    expect(createdDateDisplay.props('date')).toBe(defaultProps.createdAt)
    expect(createdDateDisplay.props('format')).toBe('medium')
    expect(createdDateDisplay.props('showTime')).toBe(true)
  })

  it('displays updated date with DateDisplay component', () => {
    const wrapper = mount(SystemProperties, {
      props: defaultProps,
    })

    const dateDisplays = wrapper.findAllComponents({ name: 'DateDisplay' })
    const updatedDateDisplay = dateDisplays[1]
    expect(updatedDateDisplay.props('date')).toBe(defaultProps.updatedAt)
    expect(updatedDateDisplay.props('format')).toBe('medium')
    expect(updatedDateDisplay.props('showTime')).toBe(true)
  })

  it('uses DescriptionList for layout', () => {
    const wrapper = mount(SystemProperties, {
      props: defaultProps,
    })

    const descriptionList = wrapper.findComponent({ name: 'DescriptionList' })
    expect(descriptionList.exists()).toBe(true)
  })

  it('renders three DescriptionRow components', () => {
    const wrapper = mount(SystemProperties, {
      props: defaultProps,
    })

    const descriptionRows = wrapper.findAllComponents({ name: 'DescriptionRow' })
    expect(descriptionRows.length).toBe(3)
  })

  it('renders DescriptionRows with correct variants and sizes', () => {
    const wrapper = mount(SystemProperties, {
      props: defaultProps,
    })

    const descriptionRows = wrapper.findAllComponents({ name: 'DescriptionRow' })

    // First row (ID) - gray variant
    expect(descriptionRows[0].props('variant')).toBe('gray')
    expect(descriptionRows[0].props('size')).toBe('small')

    // Second row (Created) - white variant
    expect(descriptionRows[1].props('variant')).toBe('white')
    expect(descriptionRows[1].props('size')).toBe('small')

    // Third row (Updated) - gray variant
    expect(descriptionRows[2].props('variant')).toBe('gray')
    expect(descriptionRows[2].props('size')).toBe('small')
  })

  it('renders DescriptionTerms with correct variants', () => {
    const wrapper = mount(SystemProperties, {
      props: defaultProps,
    })

    const descriptionTerms = wrapper.findAllComponents({ name: 'DescriptionTerm' })
    expect(descriptionTerms.length).toBe(3)

    descriptionTerms.forEach(term => {
      expect(term.props('variant')).toBe('small-gray')
    })

    expect(descriptionTerms[0].text()).toBe('ID')
    expect(descriptionTerms[1].text()).toBe('Created')
    expect(descriptionTerms[2].text()).toBe('Last Updated')
  })

  it('renders DescriptionDetails with correct variants', () => {
    const wrapper = mount(SystemProperties, {
      props: defaultProps,
    })

    const descriptionDetails = wrapper.findAllComponents({ name: 'DescriptionDetail' })
    expect(descriptionDetails.length).toBe(3)

    descriptionDetails.forEach(detail => {
      expect(detail.props('variant')).toBe('small-gray')
    })
  })

  it('handles null created date', () => {
    const wrapper = mount(SystemProperties, {
      props: {
        ...defaultProps,
        createdAt: null,
      },
    })

    const dateDisplays = wrapper.findAllComponents({ name: 'DateDisplay' })
    const createdDateDisplay = dateDisplays[0]
    expect(createdDateDisplay.props('date')).toBe(null)
  })

  it('handles null updated date', () => {
    const wrapper = mount(SystemProperties, {
      props: {
        ...defaultProps,
        updatedAt: null,
      },
    })

    const dateDisplays = wrapper.findAllComponents({ name: 'DateDisplay' })
    const updatedDateDisplay = dateDisplays[1]
    expect(updatedDateDisplay.props('date')).toBe(null)
  })

  it('has correct header structure', () => {
    const wrapper = mount(SystemProperties, {
      props: defaultProps,
    })

    const header = wrapper.find('.px-4.py-5.sm\\:px-6')
    expect(header.exists()).toBe(true)
  })

  it('has correct content structure with border', () => {
    const wrapper = mount(SystemProperties, {
      props: defaultProps,
    })

    const content = wrapper.find('.border-t.border-gray-200')
    expect(content.exists()).toBe(true)
  })
})
