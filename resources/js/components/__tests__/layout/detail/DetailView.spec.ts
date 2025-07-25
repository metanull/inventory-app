import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { nextTick } from 'vue'
import { createTestingPinia } from '@pinia/testing'
import DetailView from '../../../layout/detail/DetailView.vue'
import type { Component } from 'vue'

// Mock router functionality
const mockRouter = {
  push: vi.fn(),
  replace: vi.fn(),
}

const mockRoute = {
  params: { id: '123' },
  query: {},
  name: 'test-detail',
  path: '/test/123',
}

// Mock the imported components
vi.mock('@/components/layout/detail/EditButton.vue', () => ({
  default: {
    name: 'EditButton',
    template: '<button class="mock-edit-button" @click="$emit(\'click\')">Edit</button>',
  },
}))

vi.mock('@/components/layout/detail/DeleteButton.vue', () => ({
  default: {
    name: 'DeleteButton',
    template: '<button class="mock-delete-button" @click="$emit(\'click\')">Delete</button>',
  },
}))

vi.mock('@/components/layout/detail/SaveButton.vue', () => ({
  default: {
    name: 'SaveButton',
    template:
      '<button class="mock-save-button" :disabled="disabled" @click="$emit(\'click\')">Save</button>',
    props: ['disabled'],
  },
}))

vi.mock('@/components/layout/detail/CancelButton.vue', () => ({
  default: {
    name: 'CancelButton',
    template: '<button class="mock-cancel-button" @click="$emit(\'click\')">Cancel</button>',
  },
}))

vi.mock('@/components/format/card/StatusCard.vue', () => ({
  default: {
    name: 'StatusCard',
    template: `
      <div class="mock-status-card" @click="$emit('toggle')">
        <div class="title">{{ title }}</div>
        <div class="status">{{ isActive ? 'Active' : 'Inactive' }}</div>
      </div>
    `,
    props: [
      'title',
      'description',
      'mainColor',
      'statusText',
      'toggleTitle',
      'isActive',
      'loading',
      'disabled',
      'activeIconBackgroundClass',
      'inactiveIconBackgroundClass',
      'activeIconClass',
      'inactiveIconClass',
      'activeIconComponent',
      'inactiveIconComponent',
    ],
  },
}))

vi.mock('@/components/layout/detail/SystemProperties.vue', () => ({
  default: {
    name: 'SystemProperties',
    template: `
      <div class="mock-system-properties">
        <div>ID: {{ id }}</div>
        <div>Created: {{ createdAt }}</div>
        <div>Updated: {{ updatedAt }}</div>
      </div>
    `,
    props: ['id', 'createdAt', 'updatedAt'],
  },
}))

vi.mock('@/components/format/title/Title.vue', () => ({
  default: {
    name: 'Title',
    template: '<h2 class="mock-title"><slot /></h2>',
    props: ['variant', 'description'],
  },
}))

// Mock icons
const createMockIcon = (name: string) => ({
  name,
  template: `<div class="mock-icon-${name.toLowerCase()}">Icon</div>`,
})

const MockIcon = createMockIcon('MockIcon')

interface MockResource {
  id: string
  internal_name: string
  backward_compatibility?: string | null
  created_at: string | null
  updated_at: string | null
}

interface MockStatusCard {
  title: string
  description: string
  mainColor: string
  statusText: string
  toggleTitle: string
  isActive: boolean
  loading: boolean
  disabled?: boolean
  activeIconBackgroundClass: string
  inactiveIconBackgroundClass: string
  activeIconClass: string
  inactiveIconClass: string
  activeIconComponent: Component
  inactiveIconComponent: Component
}

interface InformationSlotProps {
  mode: 'view' | 'edit' | 'create'
  onFieldChange: () => void
}

describe('DetailView', () => {
  const mockResource: MockResource = {
    id: '123e4567-e89b-12d3-a456-426614174000',
    internal_name: 'Test Resource',
    backward_compatibility: 'test-resource',
    created_at: '2023-01-01T00:00:00Z',
    updated_at: '2023-01-02T00:00:00Z',
  }

  const mockStatusCard: MockStatusCard = {
    title: 'Status Card',
    description: 'Test status card',
    mainColor: 'green',
    statusText: 'Active',
    toggleTitle: 'Toggle Status',
    isActive: true,
    loading: false,
    disabled: false,
    activeIconBackgroundClass: 'bg-green-100',
    inactiveIconBackgroundClass: 'bg-gray-100',
    activeIconClass: 'text-green-600',
    inactiveIconClass: 'text-gray-600',
    activeIconComponent: MockIcon,
    inactiveIconComponent: MockIcon,
  }

  const defaultProps = {
    resource: mockResource,
    mode: 'view' as const,
    informationTitle: 'Test Information',
    informationDescription: 'Test information description',
    fetchData: vi.fn(),
  }

  const createWrapper = (props = {}, slots = {}) => {
    return mount(DetailView, {
      props: { ...defaultProps, ...props },
      slots,
      global: {
        plugins: [createTestingPinia({ createSpy: vi.fn })],
        stubs: {
          'router-link': {
            name: 'RouterLink',
            template: '<a :href="to" :class="class"><slot /></a>',
            props: ['to', 'class'],
          },
        },
        mocks: {
          $router: mockRouter,
          $route: mockRoute,
        },
      },
    })
  }

  beforeEach(() => {
    vi.clearAllMocks()
  })

  describe('View Mode', () => {
    it('renders correctly in view mode', () => {
      const wrapper = createWrapper({ mode: 'view' })

      expect(wrapper.exists()).toBe(true)
      expect(wrapper.find('.relative.space-y-6').exists()).toBe(true)
      expect(wrapper.find('.bg-white.shadow').exists()).toBe(true)
    })

    it('displays correct header title', () => {
      const wrapper = createWrapper({ mode: 'view' })

      const title = wrapper.findComponent({ name: 'Title' })
      expect(title.text()).toBe('Test Resource')
    })

    it('shows edit and delete buttons in view mode', () => {
      const wrapper = createWrapper({ mode: 'view' })

      expect(wrapper.findComponent({ name: 'EditButton' }).exists()).toBe(true)
      expect(wrapper.findComponent({ name: 'DeleteButton' }).exists()).toBe(true)
      expect(wrapper.findComponent({ name: 'SaveButton' }).exists()).toBe(false)
      expect(wrapper.findComponent({ name: 'CancelButton' }).exists()).toBe(false)
    })

    it('displays backward compatibility ID when available', () => {
      const wrapper = createWrapper({ mode: 'view' })

      const title = wrapper.findComponent({ name: 'Title' })
      expect(title.props('description')).toBe('Legacy ID: test-resource')
    })

    it('does not display backward compatibility when null', () => {
      const resourceWithoutBackwardComp = { ...mockResource, backward_compatibility: null }
      const wrapper = createWrapper({
        mode: 'view',
        resource: resourceWithoutBackwardComp,
      })

      const title = wrapper.findComponent({ name: 'Title' })
      expect(title.props('description')).toBeUndefined()
    })

    it('emits edit event when edit button is clicked', async () => {
      const wrapper = createWrapper({ mode: 'view' })

      await wrapper.findComponent({ name: 'EditButton' }).vm.$emit('click')
      expect(wrapper.emitted('edit')).toHaveLength(1)
    })

    it('emits delete event when delete button is clicked', async () => {
      const wrapper = createWrapper({ mode: 'view' })

      await wrapper.findComponent({ name: 'DeleteButton' }).vm.$emit('click')
      expect(wrapper.emitted('delete')).toHaveLength(1)
    })
  })

  describe('Edit Mode', () => {
    it('renders correctly in edit mode', () => {
      const wrapper = createWrapper({ mode: 'edit' })

      expect(wrapper.find('.border-l-4.border-blue-500').exists()).toBe(true)
    })

    it('displays edit mode indicator in header', () => {
      const wrapper = createWrapper({ mode: 'edit' })

      expect(wrapper.html()).toContain('Editing')
      expect(wrapper.find('.bg-blue-100.text-blue-800').exists()).toBe(true)
    })

    it('shows save and cancel buttons in edit mode', () => {
      const wrapper = createWrapper({ mode: 'edit' })

      expect(wrapper.findComponent({ name: 'SaveButton' }).exists()).toBe(true)
      expect(wrapper.findComponent({ name: 'CancelButton' }).exists()).toBe(true)
      expect(wrapper.findComponent({ name: 'EditButton' }).exists()).toBe(false)
      expect(wrapper.findComponent({ name: 'DeleteButton' }).exists()).toBe(false)
    })

    it('disables save button when hasUnsavedChanges is false', () => {
      const wrapper = createWrapper({
        mode: 'edit',
        hasUnsavedChanges: false,
      })

      const saveButton = wrapper.findComponent({ name: 'SaveButton' })
      expect(saveButton.props('disabled')).toBe(true)
    })

    it('enables save button when hasUnsavedChanges is true', () => {
      const wrapper = createWrapper({
        mode: 'edit',
        hasUnsavedChanges: true,
      })

      const saveButton = wrapper.findComponent({ name: 'SaveButton' })
      expect(saveButton.props('disabled')).toBe(false)
    })

    it('emits save event when save button is clicked', async () => {
      const wrapper = createWrapper({
        mode: 'edit',
        hasUnsavedChanges: true,
      })

      await wrapper.findComponent({ name: 'SaveButton' }).vm.$emit('click')
      expect(wrapper.emitted('save')).toHaveLength(1)
    })

    it('emits cancel event when cancel button is clicked', async () => {
      const wrapper = createWrapper({ mode: 'edit' })

      await wrapper.findComponent({ name: 'CancelButton' }).vm.$emit('click')
      expect(wrapper.emitted('cancel')).toHaveLength(1)
    })

    it('hides status cards in edit mode', () => {
      const wrapper = createWrapper({
        mode: 'edit',
        statusCards: [mockStatusCard],
      })

      // Status cards should be hidden in edit mode
      const statusCardsContainer = wrapper.find('.grid.grid-cols-1.gap-4.sm\\:grid-cols-2')
      expect(statusCardsContainer.exists()).toBe(false)
    })
  })

  describe('Create Mode', () => {
    it('renders correctly in create mode', () => {
      const wrapper = createWrapper({
        mode: 'create',
        resource: null,
      })

      expect(wrapper.find('.border-l-4.border-blue-500').exists()).toBe(true)
    })

    it('uses default create title when createTitle is not provided', () => {
      const wrapper = createWrapper({
        mode: 'create',
        resource: null,
      })

      // Title should just contain "New Record", not include (Creating)
      expect(wrapper.text()).toContain('New Record')
      // Creating indicator should be in a separate pill
      expect(wrapper.html()).toContain('Creating')
    })

    it('uses custom create title when provided', () => {
      const wrapper = createWrapper({
        mode: 'create',
        resource: null,
        createTitle: 'New Test Item',
      })

      // Title should just contain the custom title, not include (Creating)
      expect(wrapper.text()).toContain('New Test Item')
      // Creating indicator should be in a separate pill
      expect(wrapper.html()).toContain('Creating')
    })

    it('displays create mode indicator in header', () => {
      const wrapper = createWrapper({
        mode: 'create',
        resource: null,
      })

      expect(wrapper.html()).toContain('Creating')
      expect(wrapper.find('.bg-green-100.text-green-800').exists()).toBe(true)
    })

    it('hides status cards in create mode', () => {
      const wrapper = createWrapper({
        mode: 'create',
        resource: null,
        statusCards: [mockStatusCard],
      })

      expect(wrapper.findComponent({ name: 'StatusCard' }).exists()).toBe(false)
    })

    it('hides system properties in create mode', () => {
      const wrapper = createWrapper({
        mode: 'create',
        resource: null,
      })

      expect(wrapper.findComponent({ name: 'SystemProperties' }).exists()).toBe(false)
    })

    it('shows save and cancel buttons in create mode', () => {
      const wrapper = createWrapper({
        mode: 'create',
        resource: null,
      })

      expect(wrapper.findComponent({ name: 'SaveButton' }).exists()).toBe(true)
      expect(wrapper.findComponent({ name: 'CancelButton' }).exists()).toBe(true)
      expect(wrapper.findComponent({ name: 'EditButton' }).exists()).toBe(false)
      expect(wrapper.findComponent({ name: 'DeleteButton' }).exists()).toBe(false)
    })
  })

  describe('Status Cards', () => {
    it('renders status cards when provided', () => {
      const wrapper = createWrapper({
        mode: 'view',
        statusCards: [mockStatusCard],
      })

      const statusCard = wrapper.findComponent({ name: 'StatusCard' })
      expect(statusCard.exists()).toBe(true)
      expect(statusCard.props('title')).toBe('Status Card')
      expect(statusCard.props('isActive')).toBe(true)
    })

    it('renders multiple status cards', () => {
      const cards = [
        { ...mockStatusCard, title: 'Card 1' },
        { ...mockStatusCard, title: 'Card 2' },
      ]
      const wrapper = createWrapper({
        mode: 'view',
        statusCards: cards,
      })

      const statusCards = wrapper.findAllComponents({ name: 'StatusCard' })
      expect(statusCards).toHaveLength(2)
      expect(statusCards[0].props('title')).toBe('Card 1')
      expect(statusCards[1].props('title')).toBe('Card 2')
    })

    it('emits statusToggle event when status card is toggled', async () => {
      const wrapper = createWrapper({
        mode: 'view',
        statusCards: [mockStatusCard],
      })

      await wrapper.findComponent({ name: 'StatusCard' }).vm.$emit('toggle')
      expect(wrapper.emitted('statusToggle')).toHaveLength(1)
      expect(wrapper.emitted('statusToggle')?.[0]).toEqual([0])
    })

    it('does not render status cards container when no cards provided', () => {
      const wrapper = createWrapper({ mode: 'view' })

      expect(wrapper.find('.grid.grid-cols-1.gap-4.sm\\:grid-cols-2').exists()).toBe(false)
    })
  })

  describe('Information Section', () => {
    it('renders information section with correct title and description', () => {
      const wrapper = createWrapper()

      // Find all Title components and get the information section title (should be the second one)
      const titles = wrapper.findAllComponents({ name: 'Title' })
      expect(titles.length).toBeGreaterThanOrEqual(2)

      const infoTitle = titles[1] // Information section title
      expect(infoTitle.props('variant')).toBe('section')
      expect(infoTitle.props('description')).toBe('Test information description')
      expect(infoTitle.text()).toBe('Test Information')
    })

    it('passes correct props to information slot', () => {
      let slotProps: InformationSlotProps | undefined
      const wrapper = createWrapper(
        {},
        {
          information: (props: InformationSlotProps) => {
            slotProps = props
            return '<div class="test-info">Info content</div>'
          },
        }
      )

      expect(slotProps!.mode).toBe('view')
      expect(typeof slotProps!.onFieldChange).toBe('function')
      expect(wrapper.html()).toContain('test-info')
    })

    it('calls onFieldChange handler and emits unsavedChanges', async () => {
      let onFieldChangeHandler: () => void = vi.fn()
      const wrapper = createWrapper(
        {},
        {
          information: (props: InformationSlotProps) => {
            onFieldChangeHandler = props.onFieldChange
            return '<div class="test-info">Info content</div>'
          },
        }
      )

      // Call the onFieldChange handler
      onFieldChangeHandler()
      await nextTick()

      expect(wrapper.emitted('unsavedChanges')).toHaveLength(1)
      expect(wrapper.emitted('unsavedChanges')?.[0]).toEqual([true])
    })
  })

  describe('System Properties', () => {
    it('renders system properties with correct data', () => {
      const wrapper = createWrapper({ mode: 'view' })

      const systemProperties = wrapper.findComponent({ name: 'SystemProperties' })
      expect(systemProperties.exists()).toBe(true)
      expect(systemProperties.props('id')).toBe('123e4567-e89b-12d3-a456-426614174000')
      expect(systemProperties.props('createdAt')).toBe('2023-01-01T00:00:00Z')
      expect(systemProperties.props('updatedAt')).toBe('2023-01-02T00:00:00Z')
    })

    it('handles missing resource dates gracefully', () => {
      const resourceWithNullDates = {
        ...mockResource,
        created_at: null,
        updated_at: null,
      }
      const wrapper = createWrapper({
        mode: 'view',
        resource: resourceWithNullDates,
      })

      const systemProperties = wrapper.findComponent({ name: 'SystemProperties' })
      expect(systemProperties.props('createdAt')).toBe('')
      expect(systemProperties.props('updatedAt')).toBe('')
    })
  })

  describe('Data Fetching', () => {
    it('calls fetchData on mount when resource is null and not in create mode', async () => {
      const mockFetchData = vi.fn().mockResolvedValue(undefined)
      createWrapper({
        mode: 'view',
        resource: null,
        fetchData: mockFetchData,
      })

      await flushPromises()
      expect(mockFetchData).toHaveBeenCalledOnce()
    })

    it('does not call fetchData on mount when resource is provided', async () => {
      const mockFetchData = vi.fn()
      createWrapper({
        mode: 'view',
        resource: mockResource,
        fetchData: mockFetchData,
      })

      await flushPromises()
      expect(mockFetchData).not.toHaveBeenCalled()
    })

    it('does not call fetchData on mount in create mode', async () => {
      const mockFetchData = vi.fn()
      createWrapper({
        mode: 'create',
        resource: null,
        fetchData: mockFetchData,
      })

      await flushPromises()
      expect(mockFetchData).not.toHaveBeenCalled()
    })

    it('handles fetchData errors gracefully', async () => {
      const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {})
      const mockFetchData = vi.fn().mockRejectedValue(new Error('Fetch failed'))

      createWrapper({
        mode: 'view',
        resource: null,
        fetchData: mockFetchData,
      })

      await flushPromises()
      expect(consoleSpy).toHaveBeenCalledWith('Failed to fetch data:', expect.any(Error))

      consoleSpy.mockRestore()
    })
  })

  describe('Event Emission', () => {
    it('emits all action events correctly', async () => {
      const wrapper = createWrapper({ mode: 'view' })

      // Test edit event
      await wrapper.findComponent({ name: 'EditButton' }).vm.$emit('click')
      expect(wrapper.emitted('edit')).toHaveLength(1)

      // Test delete event
      await wrapper.findComponent({ name: 'DeleteButton' }).vm.$emit('click')
      expect(wrapper.emitted('delete')).toHaveLength(1)
    })

    it('emits edit mode events correctly', async () => {
      const wrapper = createWrapper({ mode: 'edit', hasUnsavedChanges: true })

      // Test save event
      await wrapper.findComponent({ name: 'SaveButton' }).vm.$emit('click')
      expect(wrapper.emitted('save')).toHaveLength(1)

      // Test cancel event
      await wrapper.findComponent({ name: 'CancelButton' }).vm.$emit('click')
      expect(wrapper.emitted('cancel')).toHaveLength(1)
    })

    it('emits statusToggle event with correct index', async () => {
      const cards = [
        { ...mockStatusCard, title: 'Card 1' },
        { ...mockStatusCard, title: 'Card 2' },
      ]
      const wrapper = createWrapper({
        mode: 'view',
        statusCards: cards,
      })

      const statusCards = wrapper.findAllComponents({ name: 'StatusCard' })

      // Toggle second card
      await statusCards[1].vm.$emit('toggle')
      expect(wrapper.emitted('statusToggle')).toHaveLength(1)
      expect(wrapper.emitted('statusToggle')?.[0]).toEqual([1])
    })

    it('emits unsavedChanges event from field change handler', async () => {
      let onFieldChangeHandler: () => void = vi.fn()
      const wrapper = createWrapper(
        {},
        {
          information: (props: InformationSlotProps) => {
            onFieldChangeHandler = props.onFieldChange
            return '<div>Info</div>'
          },
        }
      )

      onFieldChangeHandler()
      await nextTick()

      expect(wrapper.emitted('unsavedChanges')).toHaveLength(1)
      expect(wrapper.emitted('unsavedChanges')?.[0]).toEqual([true])
    })
  })

  describe('Edge Cases', () => {
    it('handles null resource gracefully', () => {
      const wrapper = createWrapper({
        mode: 'view',
        resource: null,
      })

      expect(wrapper.exists()).toBe(true)
      // Component should render but without resource-specific content
      expect(wrapper.findComponent({ name: 'SystemProperties' }).exists()).toBe(false)
    })

    it('handles missing optional props gracefully', () => {
      const wrapper = createWrapper({
        resource: mockResource,
        mode: 'view',
        informationTitle: 'Test',
        informationDescription: 'Test desc',
        fetchData: vi.fn(),
        // Missing optional props like statusCards, backLink, etc.
      })

      expect(wrapper.exists()).toBe(true)
      expect(wrapper.findComponent({ name: 'StatusCard' }).exists()).toBe(false)
    })

    it('handles empty statusCards array', () => {
      const wrapper = createWrapper({
        mode: 'view',
        statusCards: [],
      })

      expect(wrapper.find('.grid.grid-cols-1.gap-4.sm\\:grid-cols-2').exists()).toBe(false)
    })

    it('provides default save button state when hasUnsavedChanges is undefined', () => {
      const wrapper = createWrapper({
        mode: 'edit',
        // hasUnsavedChanges not provided
      })

      const saveButton = wrapper.findComponent({ name: 'SaveButton' })
      expect(saveButton.props('disabled')).toBe(true)
    })
  })
})
