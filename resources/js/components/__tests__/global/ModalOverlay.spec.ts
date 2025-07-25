import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import ModalOverlay from '../../global/ModalOverlay.vue'

describe('ModalOverlay', () => {
  it('renders correctly when visible is true', () => {
    const wrapper = mount(ModalOverlay, {
      props: {
        visible: true,
      },
    })

    expect(wrapper.exists()).toBe(true)
    expect(wrapper.find('.fixed.inset-0').exists()).toBe(true)
    expect(wrapper.attributes('aria-modal')).toBe('true')
    expect(wrapper.attributes('role')).toBe('dialog')
    expect(wrapper.attributes('aria-labelledby')).toBe('modal-title')
  })

  it('does not render when visible is false', () => {
    const wrapper = mount(ModalOverlay, {
      props: {
        visible: false,
      },
    })

    expect(wrapper.find('.fixed.inset-0').exists()).toBe(false)
  })

  it('renders dialog variant with correct structure', () => {
    const wrapper = mount(ModalOverlay, {
      props: {
        visible: true,
        variant: 'dialog',
        title: 'Test Title',
        description: 'Test Description',
        iconBgClass: 'bg-red-100',
      },
      slots: {
        icon: '<div data-testid="icon-slot">Icon</div>',
        actions: '<div data-testid="actions-slot">Actions</div>',
      },
    })

    // Check header section
    const headerSection = wrapper.find('.bg-white.px-4.pb-4.pt-5')
    expect(headerSection.exists()).toBe(true)

    // Check title
    const title = wrapper.find('#modal-title')
    expect(title.exists()).toBe(true)
    expect(title.text()).toBe('Test Title')
    expect(title.classes()).toContain('text-base')
    expect(title.classes()).toContain('font-semibold')

    // Check description
    const description = wrapper.find('.text-sm.text-gray-500')
    expect(description.exists()).toBe(true)
    expect(description.text()).toBe('Test Description')

    // Check icon container
    const iconContainer = wrapper.find('.rounded-full')
    expect(iconContainer.exists()).toBe(true)
    expect(iconContainer.classes()).toContain('bg-red-100')

    // Check icon slot
    const iconSlot = wrapper.find('[data-testid="icon-slot"]')
    expect(iconSlot.exists()).toBe(true)

    // Check actions section
    const actionsSection = wrapper.find('.bg-gray-50.px-4.py-3')
    expect(actionsSection.exists()).toBe(true)

    // Check actions slot
    const actionsSlot = wrapper.find('[data-testid="actions-slot"]')
    expect(actionsSlot.exists()).toBe(true)
  })

  it('renders content variant with correct structure', () => {
    const wrapper = mount(ModalOverlay, {
      props: {
        visible: true,
        variant: 'content',
      },
      slots: {
        default: '<div data-testid="content-slot">Content</div>',
      },
    })

    // Should not have dialog structure
    expect(wrapper.find('.bg-white.px-4.pb-4.pt-5').exists()).toBe(false)
    expect(wrapper.find('.bg-gray-50.px-4.py-3').exists()).toBe(false)

    // Should have content structure
    const contentContainer = wrapper.find('.flex.flex-col.items-center')
    expect(contentContainer.exists()).toBe(true)

    const contentSlot = wrapper.find('[data-testid="content-slot"]')
    expect(contentSlot.exists()).toBe(true)
  })

  it('applies custom overlay class', () => {
    const wrapper = mount(ModalOverlay, {
      props: {
        visible: true,
        overlayClass: 'custom-overlay-class',
      },
    })

    const overlay = wrapper.find('.fixed.inset-0.bg-gray-500')
    expect(overlay.exists()).toBe(true)
    expect(overlay.classes()).toContain('custom-overlay-class')
  })

  it('applies custom content class', () => {
    const wrapper = mount(ModalOverlay, {
      props: {
        visible: true,
        contentClass: 'custom-content-class',
      },
    })

    const content = wrapper.find('.relative.transform')
    expect(content.exists()).toBe(true)
    expect(content.classes()).toContain('custom-content-class')
  })

  it('emits backgroundClick when overlay is clicked', async () => {
    const wrapper = mount(ModalOverlay, {
      props: {
        visible: true,
      },
    })

    const overlay = wrapper.find('.fixed.inset-0.bg-gray-500')
    await overlay.trigger('click')

    expect(wrapper.emitted().backgroundClick).toBeTruthy()
    expect(wrapper.emitted().backgroundClick).toHaveLength(1)
  })

  it('renders with default variant when variant prop is not provided', () => {
    const wrapper = mount(ModalOverlay, {
      props: {
        visible: true,
      },
      slots: {
        default: '<div>Default content</div>',
      },
    })

    // Should render content variant by default (not dialog)
    const contentContainer = wrapper.find('.flex.flex-col.items-center')
    expect(contentContainer.exists()).toBe(true)
  })

  it('has correct z-index and positioning classes', () => {
    const wrapper = mount(ModalOverlay, {
      props: {
        visible: true,
      },
    })

    const container = wrapper.find('.fixed.inset-0')
    expect(container.classes()).toContain('z-50')
    expect(container.classes()).toContain('overflow-y-auto')

    const flexContainer = wrapper.find('.flex.min-h-screen')
    expect(flexContainer.exists()).toBe(true)
    expect(flexContainer.classes()).toContain('items-center')
    expect(flexContainer.classes()).toContain('justify-center')
  })

  it('renders without title and description when not provided', () => {
    const wrapper = mount(ModalOverlay, {
      props: {
        visible: true,
        variant: 'dialog',
      },
    })

    const title = wrapper.find('#modal-title')
    expect(title.text()).toBe('')

    const description = wrapper.find('.text-sm.text-gray-500')
    expect(description.text()).toBe('')
  })

  it('uses default icon background class when not provided', () => {
    const wrapper = mount(ModalOverlay, {
      props: {
        visible: true,
        variant: 'dialog',
      },
    })

    const iconContainer = wrapper.find('.rounded-full')
    expect(iconContainer.exists()).toBe(true)
    // Should not have any additional background class
    expect(iconContainer.classes()).not.toContain('bg-red-100')
  })
})
