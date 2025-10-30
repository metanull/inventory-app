import { describe, it, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import '../../test-utils/useColorsMock'
import AppFooter from '../../../layout/app/AppFooter.vue'

// Mock the package.json imports
vi.mock('../../../../package.json', () => ({
  default: {
    name: 'test-app',
    version: '1.0.0',
  },
}))

vi.mock('@metanull/inventory-app-api-client/package.json', () => ({
  default: {
    version: '1.1.21-dev+20250724.1804',
  },
}))

describe('AppFooter', () => {
  it('renders correctly with default values', () => {
    const wrapper = mount(AppFooter)

    expect(wrapper.exists()).toBe(true)
    expect(wrapper.find('footer').exists()).toBe(true)
    expect(wrapper.classes()).toContain('bg-white')
    expect(wrapper.classes()).toContain('border-t')
    expect(wrapper.classes()).toContain('border-gray-200')
  })

  it('displays app title from environment or package name', () => {
    const wrapper = mount(AppFooter)

    // Check if it displays either the env var or package name
    const titleElement = wrapper.find('.font-semibold')
    expect(titleElement.exists()).toBe(true)
    // Should display either VITE_APP_TITLE from env or 'test-app' from package.json
    expect(titleElement.text().length).toBeGreaterThan(0)
  })

  /*it('displays version from package.json', () => {
    const wrapper = mount(AppFooter)

    expect(wrapper.text()).toContain('Version: 1.0.0')
  })*/

  it('displays API client version', () => {
    const wrapper = mount(AppFooter)

    expect(wrapper.text()).toContain('API Client|Version 1.1.21-dev+20250724.1804')
  })

  it('has correct structure and layout classes', () => {
    const wrapper = mount(AppFooter)

    const container = wrapper.find('.max-w-7xl')
    expect(container.exists()).toBe(true)
    expect(container.classes()).toContain('mx-auto')
    expect(container.classes()).toContain('px-4')

    const gridContainer = wrapper.find('.grid.grid-cols-1.md\\:grid-cols-3')
    expect(gridContainer.exists()).toBe(true)
    expect(gridContainer.classes()).toContain('gap-4')
    expect(gridContainer.classes()).toContain('items-center')
    expect(gridContainer.classes()).toContain('text-sm')
  })

  it('has pipe separator between title and version', () => {
    const wrapper = mount(AppFooter)

    const separator = wrapper.find('.mx-1')
    expect(separator.exists()).toBe(true)
    expect(separator.text()).toBe('|')
  })

  it('applies responsive layout classes', () => {
    const wrapper = mount(AppFooter)

    const gridContainer = wrapper.find('.grid')
    expect(gridContainer.classes()).toContain('grid-cols-1')
    expect(gridContainer.classes()).toContain('md:grid-cols-3')
  })

  it('has accessibility-compliant structure', () => {
    const wrapper = mount(AppFooter)

    const footer = wrapper.find('footer')
    expect(footer.exists()).toBe(true)
    expect(footer.element.tagName.toLowerCase()).toBe('footer')
  })
})
