import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import ListView from '../../../layout/list/ListView.vue'

// Mock the imported components
vi.mock('@/components/layout/list/AddButton.vue', () => ({
  default: {
    name: 'AddButton',
    template: '<button class="mock-add-button" :to="to">{{ label }}</button>',
    props: ['to', 'label', 'color'],
  },
}))

vi.mock('@/components/format/title/Title.vue', () => ({
  default: {
    name: 'Title',
    template: '<h1 class="mock-title"><slot /></h1>',
    props: ['variant', 'description'],
  },
}))

vi.mock('@/components/format/table/TableElement.vue', () => ({
  default: {
    name: 'TableElement',
    template: `
      <table class="mock-table">
        <thead><slot name="headers" /></thead>
        <tbody><slot name="rows" /></tbody>
      </table>
    `,
  },
}))

vi.mock('@heroicons/vue/24/outline', () => ({
  FolderIcon: {
    name: 'FolderIcon',
    template: '<div class="mock-generic-icon mx-auto h-12 w-12 text-gray-400">📁</div>',
  },
}))

describe('ListView', () => {
  const defaultProps = {
    title: 'Test List',
    isEmpty: false,
    emptyTitle: 'No items found',
    emptyMessage: 'Create your first item to get started',
  }

  const mockRouter = {
    push: vi.fn(),
    replace: vi.fn(),
  }

  const createWrapper = (props = {}, slots = {}) => {
    return mount(ListView, {
      props: { ...defaultProps, ...props },
      slots,
      global: {
        stubs: {
          'router-link': {
            name: 'RouterLink',
            template: '<a :href="to"><slot /></a>',
            props: ['to'],
          },
        },
        mocks: {
          $router: mockRouter,
        },
      },
    })
  }

  beforeEach(() => {
    vi.clearAllMocks()
  })

  describe('Header Section', () => {
    it('renders correctly with required props', () => {
      const wrapper = createWrapper()

      expect(wrapper.exists()).toBe(true)
      expect(wrapper.find('.sm\\:flex').exists()).toBe(true)
      expect(wrapper.findComponent({ name: 'Title' }).exists()).toBe(true)
    })

    it('displays the correct title', () => {
      const wrapper = createWrapper()

      const title = wrapper.findComponent({ name: 'Title' })
      expect(title.props('variant')).toBe('page')
      expect(title.text()).toBe('Test List')
    })

    it('displays the title with description when provided', () => {
      const wrapper = createWrapper({ description: 'Test description' })

      const title = wrapper.findComponent({ name: 'Title' })
      expect(title.props('description')).toBe('Test description')
    })

    it('renders icon slot when provided', () => {
      const wrapper = createWrapper(
        { color: 'blue' },
        {
          icon: '<div class="test-icon">Icon</div>',
        }
      )

      expect(wrapper.find('.h-8.w-8.mr-3').exists()).toBe(true)
      expect(wrapper.html()).toContain('test-icon')
    })

    it('applies correct icon color classes', () => {
      const wrapper = createWrapper(
        { color: 'blue' },
        {
          icon: '<div class="test-icon">Icon</div>',
        }
      )

      const iconContainer = wrapper.find('.h-8.w-8.mr-3')
      expect(iconContainer.classes()).toContain('text-blue-600')
    })

    it('defaults to gray icon color when no color is provided', () => {
      const wrapper = createWrapper(
        {},
        {
          icon: '<div class="test-icon">Icon</div>',
        }
      )

      const iconContainer = wrapper.find('.h-8.w-8.mr-3')
      expect(iconContainer.classes()).toContain('text-gray-600')
    })

    it('renders add button when addButtonRoute is provided', () => {
      const wrapper = createWrapper({
        addButtonRoute: '/test/new',
        addButtonLabel: 'Add Test',
        color: 'green',
      })

      const addButton = wrapper.findComponent({ name: 'AddButton' })
      expect(addButton.exists()).toBe(true)
      expect(addButton.props('to')).toBe('/test/new')
      expect(addButton.props('label')).toBe('Add Test')
      expect(addButton.props('color')).toBe('green')
    })

    it('does not render add button when addButtonRoute is not provided', () => {
      const wrapper = createWrapper()

      const addButton = wrapper.findComponent({ name: 'AddButton' })
      expect(addButton.exists()).toBe(false)
    })
  })

  describe('Filter Section', () => {
    it('renders filter section when filters slot is provided', () => {
      const wrapper = createWrapper(
        {},
        {
          filters: '<div class="test-filters">Filter content</div>',
        }
      )

      expect(wrapper.find('.mt-6.flex.flex-col.gap-2').exists()).toBe(true)
      expect(wrapper.html()).toContain('test-filters')
    })

    it('renders search slot when provided', () => {
      const wrapper = createWrapper(
        {},
        {
          search: '<div class="test-search">Search content</div>',
        }
      )

      expect(wrapper.find('.flex-shrink-0.w-full.sm\\:w-auto').exists()).toBe(true)
      expect(wrapper.html()).toContain('test-search')
    })

    it('renders filter section when filters prop is provided', () => {
      const filters = [
        { label: 'All', count: 10, isActive: true, variant: 'primary' },
        { label: 'Active', count: 5, isActive: false },
      ]
      const wrapper = createWrapper({ filters })

      expect(wrapper.find('.mt-6.flex.flex-col.gap-2').exists()).toBe(true)
    })

    it('does not render filter section when no filters or slots are provided', () => {
      const wrapper = createWrapper()

      expect(wrapper.find('.mt-6.flex.flex-col.gap-2').exists()).toBe(false)
    })
  })

  describe('Empty State', () => {
    it('displays empty state when isEmpty is true', () => {
      const wrapper = createWrapper({ isEmpty: true })

      expect(wrapper.find('.text-center.py-12').exists()).toBe(true)
      // Find the empty state title (second Title component)
      const titles = wrapper.findAllComponents({ name: 'Title' })
      expect(titles).toHaveLength(2) // Header title and empty state title
      expect(titles[1].props('variant')).toBe('empty')
      expect(titles[1].text()).toBe('No items found')
    })

    it('displays empty message correctly', () => {
      const wrapper = createWrapper({
        isEmpty: true,
        emptyMessage: 'Custom empty message',
      })

      // Find the empty state title (second Title component)
      const titles = wrapper.findAllComponents({ name: 'Title' })
      const emptyStateTitle = titles[1]
      expect(emptyStateTitle.props('description')).toBe('Custom empty message')
    })

    it('renders icon in empty state when provided', () => {
      const wrapper = createWrapper(
        { isEmpty: true, color: 'purple' },
        {
          icon: '<div class="test-icon">Icon</div>',
        }
      )

      const iconContainer = wrapper.find('.mx-auto.h-12.w-12.mb-4')
      expect(iconContainer.exists()).toBe(true)
      expect(iconContainer.classes()).toContain('text-purple-600')
      expect(wrapper.html()).toContain('test-icon')
    })

    it('renders generic icon in empty state when no icon slot is provided', () => {
      const wrapper = createWrapper({ isEmpty: true })

      expect(wrapper.findComponent({ name: 'FolderIcon' }).exists()).toBe(true)
    })

    it('does not display content table when isEmpty is true', () => {
      const wrapper = createWrapper({ isEmpty: true })

      expect(wrapper.find('.mt-8').exists()).toBe(false)
      expect(wrapper.findComponent({ name: 'TableElement' }).exists()).toBe(false)
    })
  })

  describe('Content Section', () => {
    it('displays content section when isEmpty is false', () => {
      const wrapper = createWrapper({ isEmpty: false })

      expect(wrapper.find('.mt-8').exists()).toBe(true)
      expect(wrapper.find('.flow-root').exists()).toBe(true)
      expect(wrapper.find('.overflow-x-auto').exists()).toBe(true)
    })

    it('renders TableElement with proper structure', () => {
      const wrapper = createWrapper({ isEmpty: false })

      const table = wrapper.findComponent({ name: 'TableElement' })
      expect(table.exists()).toBe(true)
    })

    it('passes headers slot to TableElement', () => {
      const wrapper = createWrapper(
        { isEmpty: false },
        {
          headers: '<th class="test-header">Header</th>',
        }
      )

      expect(wrapper.html()).toContain('test-header')
    })

    it('passes rows slot to TableElement', () => {
      const wrapper = createWrapper(
        { isEmpty: false },
        {
          rows: '<tr class="test-row"><td>Row</td></tr>',
        }
      )

      expect(wrapper.html()).toContain('test-row')
    })

    it('applies proper responsive classes', () => {
      const wrapper = createWrapper({ isEmpty: false })

      expect(wrapper.find('.-mx-4.-my-2.overflow-x-auto.sm\\:-mx-6.lg\\:-mx-8').exists()).toBe(true)
      expect(wrapper.find('.min-w-full.py-2.align-middle.sm\\:px-6.lg\\:px-8').exists()).toBe(true)
      expect(
        wrapper
          .find('.overflow-hidden.shadow.ring-1.ring-black.ring-opacity-5.md\\:rounded-lg')
          .exists()
      ).toBe(true)
    })
  })

  describe('Modal Section', () => {
    it('renders modals slot when provided', () => {
      const wrapper = createWrapper(
        {},
        {
          modals: '<div class="test-modal">Modal content</div>',
        }
      )

      expect(wrapper.html()).toContain('test-modal')
    })
  })

  describe('Color System', () => {
    const colorTests = [
      { color: 'blue', expected: 'text-blue-600' },
      { color: 'green', expected: 'text-green-600' },
      { color: 'purple', expected: 'text-purple-600' },
      { color: 'orange', expected: 'text-orange-600' },
      { color: 'red', expected: 'text-red-600' },
      { color: 'yellow', expected: 'text-yellow-600' },
      { color: 'indigo', expected: 'text-indigo-600' },
      { color: 'pink', expected: 'text-pink-600' },
      { color: 'gray', expected: 'text-gray-600' },
      { color: 'unknown', expected: 'text-gray-600' },
    ]

    colorTests.forEach(({ color, expected }) => {
      it(`applies correct color class for ${color}`, () => {
        const wrapper = createWrapper(
          { color },
          {
            icon: '<div class="test-icon">Icon</div>',
          }
        )

        const iconContainer = wrapper.find('.h-8.w-8.mr-3')
        expect(iconContainer.classes()).toContain(expected)
      })
    })
  })

  describe('Integration Scenarios', () => {
    it('handles complete list view scenario', () => {
      const wrapper = createWrapper(
        {
          title: 'Projects',
          description: 'Manage your projects',
          addButtonRoute: '/projects/new',
          addButtonLabel: 'New Project',
          color: 'orange',
          isEmpty: false,
          filters: [
            { label: 'All', count: 10, isActive: true, variant: 'primary' },
            { label: 'Active', count: 5, isActive: false },
          ],
        },
        {
          icon: '<div class="project-icon">Project</div>',
          filters: '<div class="filter-buttons">Filters</div>',
          search: '<div class="search-control">Search</div>',
          headers: '<th>Name</th><th>Status</th>',
          rows: '<tr><td>Project 1</td><td>Active</td></tr>',
          modals: '<div class="modal">Modal</div>',
        }
      )

      // Verify all sections are rendered
      expect(wrapper.findComponent({ name: 'Title' }).text()).toBe('Projects')
      expect(wrapper.findComponent({ name: 'AddButton' }).exists()).toBe(true)
      expect(wrapper.find('.h-8.w-8.mr-3').classes()).toContain('text-orange-600')
      expect(wrapper.html()).toContain('filter-buttons')
      expect(wrapper.html()).toContain('search-control')
      expect(wrapper.findComponent({ name: 'TableElement' }).exists()).toBe(true)
      expect(wrapper.html()).toContain('Project 1')
      expect(wrapper.html()).toContain('Modal')
    })

    it('handles empty state scenario', () => {
      const wrapper = createWrapper(
        {
          title: 'Projects',
          isEmpty: true,
          emptyTitle: 'No projects found',
          emptyMessage: 'Create your first project to get started',
          addButtonRoute: '/projects/new',
          addButtonLabel: 'New Project',
        },
        {
          icon: '<div class="project-icon">Project</div>',
        }
      )

      // Verify empty state is rendered correctly
      expect(wrapper.find('.text-center.py-12').exists()).toBe(true)
      // Find the empty state title (second Title component)
      const titles = wrapper.findAllComponents({ name: 'Title' })
      expect(titles[1].props('variant')).toBe('empty')
      expect(wrapper.findComponent({ name: 'TableElement' }).exists()).toBe(false)
      expect(wrapper.findComponent({ name: 'AddButton' }).exists()).toBe(true)
    })

    it('handles minimal configuration', () => {
      const wrapper = createWrapper({
        title: 'Simple List',
        isEmpty: false,
        emptyTitle: 'Empty',
        emptyMessage: 'No items',
      })

      expect(wrapper.findComponent({ name: 'Title' }).text()).toBe('Simple List')
      expect(wrapper.findComponent({ name: 'AddButton' }).exists()).toBe(false)
      expect(wrapper.find('.mt-6.flex.flex-col.gap-2').exists()).toBe(false)
      expect(wrapper.findComponent({ name: 'TableElement' }).exists()).toBe(true)
    })
  })

  describe('Edge Cases', () => {
    it('handles undefined description prop', () => {
      const wrapper = createWrapper({ description: undefined })

      const title = wrapper.findComponent({ name: 'Title' })
      expect(title.props('description')).toBeUndefined()
    })

    it('handles empty filters array', () => {
      const wrapper = createWrapper({ filters: [] })

      expect(wrapper.find('.mt-6.flex.flex-col.gap-2').exists()).toBe(false)
    })

    it('handles missing color prop', () => {
      const wrapper = createWrapper(
        {},
        {
          icon: '<div class="test-icon">Icon</div>',
        }
      )

      const iconContainer = wrapper.find('.h-8.w-8.mr-3')
      expect(iconContainer.classes()).toContain('text-gray-600')
    })

    it('handles boolean isEmpty prop correctly', () => {
      const wrapperEmpty = createWrapper({ isEmpty: true })
      const wrapperNotEmpty = createWrapper({ isEmpty: false })

      expect(wrapperEmpty.find('.text-center.py-12').exists()).toBe(true)
      expect(wrapperEmpty.findComponent({ name: 'TableElement' }).exists()).toBe(false)

      expect(wrapperNotEmpty.find('.text-center.py-12').exists()).toBe(false)
      expect(wrapperNotEmpty.findComponent({ name: 'TableElement' }).exists()).toBe(true)
    })

    it('gracefully handles missing required props', () => {
      const wrapper = mount(ListView, {
        props: {
          title: 'Test',
          isEmpty: false,
          emptyTitle: 'Empty',
          emptyMessage: 'No items',
          // Missing other optional props
        },
        global: {
          stubs: {
            'router-link': {
              name: 'RouterLink',
              template: '<a :href="to"><slot /></a>',
              props: ['to'],
            },
          },
        },
      })

      expect(wrapper.exists()).toBe(true)
      const titles = wrapper.findAllComponents({ name: 'Title' })
      expect(titles[0].text()).toBe('Test')
    })
  })

  describe('Accessibility', () => {
    it('has proper semantic structure', () => {
      const wrapper = createWrapper(
        { isEmpty: false },
        {
          headers: '<th>Header</th>',
          rows: '<tr><td>Cell</td></tr>',
        }
      )

      // Check main container structure
      expect(wrapper.find('.sm\\:flex.sm\\:items-center').exists()).toBe(true)
      expect(wrapper.find('.sm\\:flex-auto').exists()).toBe(true)
    })

    it('maintains proper heading hierarchy', () => {
      const wrapper = createWrapper()

      const title = wrapper.findComponent({ name: 'Title' })
      expect(title.props('variant')).toBe('page')
    })

    it('provides meaningful empty state content', () => {
      const wrapper = createWrapper({
        isEmpty: true,
        emptyTitle: 'Accessible Empty Title',
        emptyMessage: 'Descriptive message for screen readers',
      })

      // Find the empty state title (second Title component)
      const titles = wrapper.findAllComponents({ name: 'Title' })
      const emptyStateTitle = titles[1]
      expect(emptyStateTitle.text()).toBe('Accessible Empty Title')
      expect(emptyStateTitle.props('description')).toBe('Descriptive message for screen readers')
    })
  })
})
