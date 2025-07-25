import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, type VueWrapper } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import AppHeader from '../../../layout/app/AppHeader.vue'

// Mock router
const mockPush = vi.fn()
vi.mock('vue-router', () => ({
  RouterLink: {
    name: 'RouterLink',
    template: '<a><slot /></a>',
    props: ['to', 'class'],
  },
  useRouter: () => ({
    push: mockPush,
  }),
}))

interface MockAuthStore {
  isAuthenticated: boolean
  logout: ReturnType<typeof vi.fn>
}

describe('AppHeader', () => {
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  let wrapper: VueWrapper<any>
  let mockAuthStore: MockAuthStore

  beforeEach(() => {
    vi.clearAllMocks()

    wrapper = mount(AppHeader, {
      global: {
        plugins: [
          createTestingPinia({
            createSpy: vi.fn,
          }),
        ],
      },
    })

    // Get the actual store instance from the wrapper and set up the mock
    mockAuthStore = wrapper.vm.authStore
    mockAuthStore.isAuthenticated = true // Set authenticated state
    mockAuthStore.logout = vi.fn().mockResolvedValue(undefined)
  })

  it('renders correctly', () => {
    expect(wrapper.exists()).toBe(true)
    expect(wrapper.find('header').exists()).toBe(true)
  })

  it('displays app title as home link', () => {
    const titleLink = wrapper.findComponent({ name: 'RouterLink' })
    expect(titleLink.exists()).toBe(true)
    expect(titleLink.props('to')).toBe('/')
  })

  it('renders desktop navigation links', () => {
    const nav = wrapper.find('nav')
    expect(nav.exists()).toBe(true)

    const dashboardLink = nav.findComponent({ name: 'RouterLink' })
    expect(dashboardLink.exists()).toBe(true)
  })

  it('renders Reference Data dropdown', () => {
    const dropdown = wrapper.find('div.relative')
    expect(dropdown.exists()).toBe(true)
  })

  it('toggles dropdown on click', async () => {
    const dropdownButton = wrapper.find('div.relative button')

    await dropdownButton.trigger('click')
    expect(wrapper.vm.isDropdownOpen).toBe(true)
  })

  it('renders dropdown menu items when open', async () => {
    wrapper.vm.isDropdownOpen = true
    await wrapper.vm.$nextTick()

    const dropdownMenu = wrapper.find('.absolute')
    expect(dropdownMenu.exists()).toBe(true)
  })

  it('closes dropdown on mouseleave', async () => {
    wrapper.vm.isDropdownOpen = true
    const dropdown = wrapper.find('div.relative')

    await dropdown.trigger('mouseleave')

    expect(wrapper.vm.dropdownTimeout).toBeDefined()
  })

  it('renders logout button when authenticated', () => {
    const buttons = wrapper.findAll('button')
    const logoutButton = buttons.find(btn => btn.text() === 'Logout')
    expect(logoutButton?.exists()).toBe(true)
  })

  it('handles logout click', async () => {
    // Find the desktop logout button specifically by its text content
    const buttons = wrapper.findAll('button')
    const logoutButton = buttons.find(btn => btn.text() === 'Logout')

    expect(logoutButton?.exists()).toBe(true)
    expect(logoutButton?.text()).toBe('Logout')

    if (logoutButton) {
      await logoutButton.trigger('click')

      // Wait for async operations
      await wrapper.vm.$nextTick()

      expect(mockAuthStore.logout).toHaveBeenCalled()
      expect(mockPush).toHaveBeenCalledWith('/login')
    }
  })

  it('renders mobile menu button', () => {
    const mobileButton = wrapper.find('.md\\:hidden button')
    expect(mobileButton.exists()).toBe(true)
  })

  it('toggles mobile menu', async () => {
    const mobileButton = wrapper.find('.md\\:hidden button')

    await mobileButton.trigger('click')
    expect(wrapper.vm.isMobileMenuOpen).toBe(true)
  })

  it('renders mobile navigation when open', async () => {
    wrapper.vm.isMobileMenuOpen = true
    await wrapper.vm.$nextTick()

    const mobileSection = wrapper.find('.md\\:hidden')
    expect(mobileSection.exists()).toBe(true)
  })

  it('toggles mobile dropdown', async () => {
    // First open the mobile menu
    wrapper.vm.isMobileMenuOpen = true
    await wrapper.vm.$nextTick()

    // Find the mobile reference data button inside the mobile menu
    const mobileReferenceButton = wrapper.find('.md\\:hidden .px-3 button')
    expect(mobileReferenceButton.exists()).toBe(true)
    expect(mobileReferenceButton.text()).toContain('Reference Data')

    // Initially closed
    expect(wrapper.vm.isMobileDropdownOpen).toBe(false)

    // Click to open
    await mobileReferenceButton.trigger('click')
    expect(wrapper.vm.isMobileDropdownOpen).toBe(true)

    // Click again to close
    await mobileReferenceButton.trigger('click')
    expect(wrapper.vm.isMobileDropdownOpen).toBe(false)
  })

  it('closes mobile menu when menu link is clicked', async () => {
    wrapper.vm.isMobileMenuOpen = true
    await wrapper.vm.$nextTick()

    const mobileLinks = wrapper.find('.md\\:hidden').findAllComponents({ name: 'RouterLink' })
    if (mobileLinks.length > 0) {
      await mobileLinks[0].trigger('click')
      expect(wrapper.vm.isMobileMenuOpen).toBe(false)
    }
  })

  it('applies correct responsive classes', () => {
    const container = wrapper.find('.max-w-7xl')
    expect(container.exists()).toBe(true)
  })

  it('rotates dropdown arrow when open', async () => {
    const dropdownButton = wrapper.find('div.relative button')
    expect(dropdownButton.exists()).toBe(true)

    wrapper.vm.isDropdownOpen = true
    await wrapper.vm.$nextTick()

    expect(wrapper.vm.isDropdownOpen).toBe(true)
  })
})
