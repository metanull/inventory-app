import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import Uuid from '../../format/Uuid.vue'

describe('Uuid', () => {
  const testUuid = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890'

  it('renders correctly', () => {
    const wrapper = mount(Uuid, {
      props: { uuid: testUuid },
    })

    expect(wrapper.exists()).toBe(true)
    expect(wrapper.element.tagName).toBe('SPAN')
  })

  it('displays short format by default', () => {
    const wrapper = mount(Uuid, {
      props: { uuid: testUuid },
    })

    expect(wrapper.text()).toBe('a1b2c3d4')
  })

  it('displays full UUID in long format', () => {
    const wrapper = mount(Uuid, {
      props: {
        uuid: testUuid,
        format: 'long',
      },
    })

    expect(wrapper.text()).toBe(testUuid)
  })

  it('respects custom length in short format', () => {
    const wrapper = mount(Uuid, {
      props: {
        uuid: testUuid,
        format: 'short',
        length: 12,
      },
    })

    expect(wrapper.text()).toBe('a1b2c3d4-e5f')
  })

  it('uses custom length even without explicit format', () => {
    const wrapper = mount(Uuid, {
      props: {
        uuid: testUuid,
        length: 6,
      },
    })

    expect(wrapper.text()).toBe('a1b2c3')
  })

  it('displays N/A for null uuid', () => {
    const wrapper = mount(Uuid, {
      props: { uuid: null },
    })

    expect(wrapper.text()).toBe('N/A')
  })

  it('displays N/A for undefined uuid', () => {
    const wrapper = mount(Uuid, {
      props: { uuid: undefined },
    })

    expect(wrapper.text()).toBe('N/A')
  })

  it('displays N/A for empty string uuid', () => {
    const wrapper = mount(Uuid, {
      props: { uuid: '' },
    })

    expect(wrapper.text()).toBe('N/A')
  })

  it('displays N/A for whitespace-only uuid', () => {
    const wrapper = mount(Uuid, {
      props: { uuid: '   ' },
    })

    expect(wrapper.text()).toBe('N/A')
  })

  it('applies custom className when provided', () => {
    const wrapper = mount(Uuid, {
      props: {
        uuid: testUuid,
        className: 'custom-class',
      },
    })

    expect(wrapper.classes()).toContain('custom-class')
  })

  it('has title attribute with full uuid', () => {
    const wrapper = mount(Uuid, {
      props: { uuid: testUuid },
    })

    expect(wrapper.attributes('title')).toBe(testUuid)
  })

  it('has appropriate title for null uuid', () => {
    const wrapper = mount(Uuid, {
      props: { uuid: null },
    })

    expect(wrapper.attributes('title')).toBe('No UUID available')
  })

  it('has appropriate title for empty uuid', () => {
    const wrapper = mount(Uuid, {
      props: { uuid: '' },
    })

    expect(wrapper.attributes('title')).toBe('No UUID available')
  })

  it('trims whitespace from uuid', () => {
    const wrapper = mount(Uuid, {
      props: { uuid: `  ${testUuid}  ` },
    })

    expect(wrapper.text()).toBe('a1b2c3d4')
    expect(wrapper.attributes('title')).toBe(testUuid)
  })

  it('handles uuid shorter than requested length', () => {
    const shortUuid = 'abc123'
    const wrapper = mount(Uuid, {
      props: {
        uuid: shortUuid,
        length: 10,
      },
    })

    expect(wrapper.text()).toBe(shortUuid)
  })
})
