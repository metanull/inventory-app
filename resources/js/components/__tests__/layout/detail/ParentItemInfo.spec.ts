import { describe, it, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import ParentItemInfo from '../../../layout/detail/ParentItemInfo.vue'
import '../../test-utils/useColorsMock'

// Mock the imported components
vi.mock('@/components/format/Uuid.vue', () => ({
  default: {
    name: 'UuidDisplay',
    template: '<span class="mock-uuid-display">{{ uuid }}</span>',
    props: ['uuid', 'format'],
  },
}))

vi.mock('@/components/format/InternalName.vue', () => ({
  default: {
    name: 'InternalName',
    template: '<span class="mock-internal-name">{{ internalName }}</span>',
    props: ['internalName', 'backwardCompatibility', 'small'],
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

describe('ParentItemInfo', () => {
  const defaultProps = {
    itemId: '123e4567-e89b-12d3-a456-426614174000',
    itemInternalName: 'Test Item',
  }

  it('renders correctly with all props', () => {
    const wrapper = mount(ParentItemInfo, {
      props: defaultProps,
    })

    expect(wrapper.exists()).toBe(true)
    expect(wrapper.classes()).toContain('bg-white')
    expect(wrapper.classes()).toContain('shadow')
    expect(wrapper.classes()).toContain('overflow-hidden')
    expect(wrapper.classes()).toContain('sm:rounded-lg')
  })

  it('renders parent item title', () => {
    const wrapper = mount(ParentItemInfo, {
      props: defaultProps,
    })

    const title = wrapper.findComponent({ name: 'Title' })
    expect(title.exists()).toBe(true)
    expect(title.props('variant')).toBe('system')
    expect(title.props('description')).toBe('Parent item information that this detail belongs to.')
    expect(title.text()).toBe('Parent Item')
  })

  it('displays item ID with UuidDisplay component', () => {
    const wrapper = mount(ParentItemInfo, {
      props: defaultProps,
    })

    const uuidDisplay = wrapper.findComponent({ name: 'UuidDisplay' })
    expect(uuidDisplay.exists()).toBe(true)
    expect(uuidDisplay.props('uuid')).toBe(defaultProps.itemId)
    expect(uuidDisplay.props('format')).toBe('long')
  })

  it('displays item internal name with InternalName component', () => {
    const wrapper = mount(ParentItemInfo, {
      props: defaultProps,
    })

    const internalName = wrapper.findComponent({ name: 'InternalName' })
    expect(internalName.exists()).toBe(true)
    expect(internalName.text()).toBe(defaultProps.itemInternalName)
  })

  it('uses DescriptionList for layout', () => {
    const wrapper = mount(ParentItemInfo, {
      props: defaultProps,
    })

    const descriptionList = wrapper.findComponent({ name: 'DescriptionList' })
    expect(descriptionList.exists()).toBe(true)
  })

  it('renders two DescriptionRow components', () => {
    const wrapper = mount(ParentItemInfo, {
      props: defaultProps,
    })

    const descriptionRows = wrapper.findAllComponents({ name: 'DescriptionRow' })
    expect(descriptionRows.length).toBe(2)
  })

  it('renders DescriptionRows with correct variants and sizes', () => {
    const wrapper = mount(ParentItemInfo, {
      props: defaultProps,
    })

    const descriptionRows = wrapper.findAllComponents({ name: 'DescriptionRow' })

    // First row (Item ID) - gray variant
    expect(descriptionRows[0].props('variant')).toBe('gray')
    expect(descriptionRows[0].props('size')).toBe('small')

    // Second row (Internal Name) - white variant
    expect(descriptionRows[1].props('variant')).toBe('white')
    expect(descriptionRows[1].props('size')).toBe('small')
  })

  it('renders DescriptionTerms with correct content and variants', () => {
    const wrapper = mount(ParentItemInfo, {
      props: defaultProps,
    })

    const descriptionTerms = wrapper.findAllComponents({ name: 'DescriptionTerm' })
    expect(descriptionTerms.length).toBe(2)

    descriptionTerms.forEach(term => {
      expect(term.props('variant')).toBe('small-gray')
    })

    expect(descriptionTerms[0].text()).toBe('Item ID')
    expect(descriptionTerms[1].text()).toBe('Internal Name')
  })

  it('renders DescriptionDetails with correct variants', () => {
    const wrapper = mount(ParentItemInfo, {
      props: defaultProps,
    })

    const descriptionDetails = wrapper.findAllComponents({ name: 'DescriptionDetail' })
    expect(descriptionDetails.length).toBe(2)

    descriptionDetails.forEach(detail => {
      expect(detail.props('variant')).toBe('small-gray')
    })
  })

  it('has correct header structure', () => {
    const wrapper = mount(ParentItemInfo, {
      props: defaultProps,
    })

    const header = wrapper.find('.px-4.py-5.sm\\:px-6')
    expect(header.exists()).toBe(true)
  })

  it('has correct content structure with border', () => {
    const wrapper = mount(ParentItemInfo, {
      props: defaultProps,
    })

    const content = wrapper.find('.border-t.border-gray-200')
    expect(content.exists()).toBe(true)
  })

  it('handles item ID correctly', () => {
    const customProps = {
      itemId: 'abcd1234-5678-90ef-ghij-klmnopqrstuv',
      itemInternalName: 'Custom Item',
    }

    const wrapper = mount(ParentItemInfo, {
      props: customProps,
    })

    const uuidDisplay = wrapper.findComponent({ name: 'UuidDisplay' })
    expect(uuidDisplay.props('uuid')).toBe(customProps.itemId)
  })

  it('handles item internal name correctly', () => {
    const customProps = {
      itemId: '123e4567-e89b-12d3-a456-426614174000',
      itemInternalName: 'Custom Item Name',
    }

    const wrapper = mount(ParentItemInfo, {
      props: customProps,
    })

    const internalName = wrapper.findComponent({ name: 'InternalName' })
    expect(internalName.text()).toBe(customProps.itemInternalName)
  })
})