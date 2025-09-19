import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import DetailDetail from '../../DetailDetail.vue'
import type { DetailResource } from '@metanull/inventory-app-api-client'

// Mock components and dependencies following existing patterns
vi.mock('@heroicons/vue/24/outline', () => ({
  CubeIcon: { name: 'CubeIcon', render: () => null },
  ArrowLeftIcon: { name: 'ArrowLeftIcon', render: () => null },
}))

vi.mock('@/composables/useColors', () => ({
  useColors: vi.fn(() => ({
    value: {
      icon: 'text-teal-600',
      button: 'bg-teal-600',
      buttonHover: 'hover:bg-teal-700',
      focus: 'focus:ring-teal-500',
      border: 'border-teal-300',
      badge: 'bg-teal-100 text-teal-800',
    },
  })),
}))

// Mock layout and format components with shallow templates
vi.mock('@/components/layout/detail/DetailView.vue', () => ({
  default: {
    name: 'DetailView',
    template: `
      <div class="mock-detail-view">
        <div class="resource-icon"><slot name="resource-icon" /></div>
        <div class="information"><slot name="information" /></div>
      </div>
    `,
    props: [
      'storeLoading',
      'resource',
      'mode',
      'saveDisabled',
      'hasUnsavedChanges',
      'backLink',
      'createTitle',
      'createSubtitle',
      'informationTitle',
      'informationDescription',
      'fetchData',
    ],
    emits: ['edit', 'save', 'cancel', 'delete'],
  },
}))

vi.mock('@/components/layout/detail/ParentItemInfo.vue', () => ({
  default: {
    name: 'ParentItemInfo',
    template:
      '<div class="mock-parent-item-info" :data-item-id="itemId">{{ itemInternalName }}</div>',
    props: ['itemId', 'itemInternalName'],
  },
}))

// Mock format components
vi.mock('@/components/format/description/DescriptionList.vue', () => ({
  default: {
    name: 'DescriptionList',
    template: '<dl class="mock-description-list"><slot /></dl>',
  },
}))

vi.mock('@/components/format/description/DescriptionRow.vue', () => ({
  default: {
    name: 'DescriptionRow',
    template: '<div class="mock-description-row" :data-variant="variant"><slot /></div>',
    props: ['variant'],
  },
}))

vi.mock('@/components/format/description/DescriptionTerm.vue', () => ({
  default: {
    name: 'DescriptionTerm',
    template: '<dt class="mock-description-term"><slot /></dt>',
  },
}))

vi.mock('@/components/format/description/DescriptionDetail.vue', () => ({
  default: {
    name: 'DescriptionDetail',
    template: '<dd class="mock-description-detail"><slot /></dd>',
  },
}))

vi.mock('@/components/format/FormInput.vue', () => ({
  default: {
    name: 'FormInput',
    template:
      '<input class="mock-form-input" :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" />',
    props: ['modelValue', 'type', 'placeholder', 'required'],
    emits: ['update:modelValue'],
  },
}))

vi.mock('@/components/format/DisplayText.vue', () => ({
  default: {
    name: 'DisplayText',
    template: '<span class="mock-display-text"><slot /></span>',
  },
}))

// Mock stores with realistic behavior
const mockDetailStore = {
  loading: false,
  currentDetail: null as DetailResource | null,
  fetchDetail: vi.fn(),
  createDetail: vi.fn(),
  updateDetail: vi.fn(),
  deleteDetail: vi.fn(),
  clearCurrentDetail: vi.fn(),
}

const mockLoadingStore = {
  show: vi.fn(),
  hide: vi.fn(),
}

const mockErrorStore = {
  addMessage: vi.fn(),
}

const mockDeleteStore = {
  trigger: vi.fn(),
}

const mockCancelChangesStore = {
  addChange: vi.fn(),
  resetChanges: vi.fn(),
  trigger: vi.fn(),
}

vi.mock('@/stores/detail', () => ({
  useDetailStore: () => mockDetailStore,
}))

vi.mock('@/stores/loadingOverlay', () => ({
  useLoadingOverlayStore: () => mockLoadingStore,
}))

vi.mock('@/stores/errorDisplay', () => ({
  useErrorDisplayStore: () => mockErrorStore,
}))

vi.mock('@/stores/deleteConfirmation', () => ({
  useDeleteConfirmationStore: () => mockDeleteStore,
}))

vi.mock('@/stores/cancelChangesConfirmation', () => ({
  useCancelChangesConfirmationStore: () => mockCancelChangesStore,
}))

// Mock router
const mockRouter = {
  push: vi.fn(),
  replace: vi.fn(),
}

const mockRoute = {
  params: { itemId: 'test-item-123', id: 'test-detail-456' },
  query: {},
  name: 'detail-detail',
  path: '/items/test-item-123/details/test-detail-456',
}

vi.mock('vue-router', () => ({
  useRouter: () => mockRouter,
  useRoute: () => mockRoute,
  onBeforeRouteLeave: vi.fn(),
}))

describe('DetailDetail Integration', () => {
  const mockDetail: DetailResource = {
    id: 'test-detail-456',
    internal_name: 'Test Detail Integration',
    backward_compatibility: 'test-detail-integration',
    created_at: '2023-01-01T00:00:00Z',
    updated_at: '2023-01-02T00:00:00Z',
    item: {
      id: 'test-item-123',
      internal_name: 'Test Item Integration',
      type: 'artifact',
      backward_compatibility: null,
      owner_reference: null,
      mwnf_reference: null,
      artists: [],
      workshops: [],
      created_at: '2023-01-01T00:00:00Z',
      updated_at: '2023-01-02T00:00:00Z',
    },
  }

  beforeEach(() => {
    vi.clearAllMocks()
    mockDetailStore.currentDetail = null
    mockDetailStore.loading = false
  })

  interface RouteOptions {
    params?: Record<string, string>
    name?: string
    path?: string
    [key: string]: unknown
  }

  const createWrapper = (routeOptions: RouteOptions = {}) => {
    // Update the mock route params, name, and path
    const { params = {}, name, path, ...otherOptions } = routeOptions
    Object.assign(mockRoute.params, params)
    if (name !== undefined) mockRoute.name = name
    if (path !== undefined) mockRoute.path = path
    Object.assign(mockRoute, otherOptions)

    return mount(DetailDetail, {
      props: { color: 'teal' },
      global: {
        stubs: { teleport: true },
      },
    })
  }

  describe('Complete Create Flow', () => {
    it('handles complete create workflow', async () => {
      // Simulate create route
      const wrapper = createWrapper({
        name: 'detail-new',
        path: '/items/test-item-123/details/new',
        params: { itemId: 'test-item-123' }, // Remove id param for create
      })

      // Wait for component to initialize
      await wrapper.vm.$nextTick()

      const component = wrapper.vm as unknown as {
        mode: string
        editForm: { internal_name: string; backward_compatibility: string | null }
        saveDetail: () => Promise<void>
      }

      // Should start in create mode when no detail id in route
      expect(component.mode).toBe('create')

      // Verify parent item info is not shown in create mode
      const parentItemInfo = wrapper.findComponent({ name: 'ParentItemInfo' })
      expect(parentItemInfo.exists()).toBe(false)

      // Set up form data
      component.editForm.internal_name = 'New Integration Detail'
      component.editForm.backward_compatibility = 'new-integration-detail'

      // Mock successful creation
      const createdDetail = {
        ...mockDetail,
        id: 'new-detail-id',
        internal_name: 'New Integration Detail',
      }
      mockDetailStore.createDetail.mockResolvedValue(createdDetail)

      // Trigger save
      await component.saveDetail()

      // Verify API call
      expect(mockDetailStore.createDetail).toHaveBeenCalledWith(
        expect.objectContaining({
          item_id: 'test-item-123',
          internal_name: 'New Integration Detail',
          backward_compatibility: 'new-integration-detail',
        }),
        { include: ['item'] }
      )

      // Verify navigation to new detail
      expect(mockRouter.push).toHaveBeenCalledWith('/items/test-item-123/details/new-detail-id')
    })
  })

  describe('Complete View/Edit Flow', () => {
    it('handles complete view and edit workflow', async () => {
      // Set up existing detail
      mockDetailStore.currentDetail = mockDetail

      const wrapper = createWrapper({
        name: 'detail-detail',
        path: '/items/test-item-123/details/test-detail-456',
        params: { itemId: 'test-item-123', id: 'test-detail-456' },
      })
      const component = wrapper.vm as unknown as {
        mode: string
        hasUnsavedChanges: boolean
        enterEditMode: () => void
        editForm: { internal_name: string }
        saveDetail: () => Promise<void>
      }

      // Should start in view mode
      expect(component.mode).toBe('view')

      // Verify parent item info is shown
      const parentItemInfo = wrapper.findComponent({ name: 'ParentItemInfo' })
      expect(parentItemInfo.exists()).toBe(true)
      expect(parentItemInfo.props('itemId')).toBe('test-item-123')
      expect(parentItemInfo.props('itemInternalName')).toBe('Test Item Integration')

      // Enter edit mode
      component.enterEditMode()
      await wrapper.vm.$nextTick()

      expect(component.mode).toBe('edit')
      expect(component.hasUnsavedChanges).toBe(false)

      // Make changes
      component.editForm.internal_name = 'Updated Integration Detail'
      expect(component.hasUnsavedChanges).toBe(true)

      // Mock successful update
      mockDetailStore.updateDetail.mockResolvedValue({
        ...mockDetail,
        internal_name: 'Updated Integration Detail',
      })

      // Save changes
      await component.saveDetail()

      // Verify API call
      expect(mockDetailStore.updateDetail).toHaveBeenCalledWith(
        'test-detail-456',
        expect.objectContaining({
          item_id: 'test-item-123',
          internal_name: 'Updated Integration Detail',
          backward_compatibility: 'test-detail-integration',
        }),
        { include: ['item'] }
      )

      // Should return to view mode
      expect(component.mode).toBe('view')
    })
  })

  describe('Complete Delete Flow', () => {
    it('handles complete delete workflow', async () => {
      mockDetailStore.currentDetail = mockDetail

      const wrapper = createWrapper()
      const component = wrapper.vm as unknown as {
        deleteDetail: () => Promise<void>
      }

      // Mock user confirming deletion
      mockDeleteStore.trigger.mockResolvedValue('delete')
      mockDetailStore.deleteDetail.mockResolvedValue(undefined)

      // Trigger delete
      await component.deleteDetail()

      // Verify confirmation dialog
      expect(mockDeleteStore.trigger).toHaveBeenCalledWith(
        'Delete Detail',
        'Are you sure you want to delete "Test Detail Integration"? This action cannot be undone.'
      )

      // Verify API call
      expect(mockDetailStore.deleteDetail).toHaveBeenCalledWith('test-detail-456')

      // Verify navigation back to item
      expect(mockRouter.push).toHaveBeenCalledWith('/items/test-item-123')
    })

    it('handles delete cancellation', async () => {
      mockDetailStore.currentDetail = mockDetail

      const wrapper = createWrapper()
      const component = wrapper.vm as unknown as {
        deleteDetail: () => Promise<void>
      }

      // Mock user canceling deletion
      mockDeleteStore.trigger.mockResolvedValue('cancel')

      // Trigger delete
      await component.deleteDetail()

      // Verify confirmation dialog was shown
      expect(mockDeleteStore.trigger).toHaveBeenCalled()

      // Verify API was not called
      expect(mockDetailStore.deleteDetail).not.toHaveBeenCalled()

      // Verify no navigation occurred
      expect(mockRouter.push).not.toHaveBeenCalled()
    })
  })

  describe('Navigation and Back Links', () => {
    it('provides correct back navigation', () => {
      const wrapper = createWrapper()
      const component = wrapper.vm as unknown as {
        backLink: { title: string; route: string }
      }

      // Verify back link structure
      expect(component.backLink.title).toBe('Back to Item')
      expect(component.backLink.route).toBe('/items/test-item-123')

      // Verify back link is passed to DetailView
      const detailView = wrapper.findComponent({ name: 'DetailView' })
      expect(detailView.props('backLink')).toEqual({
        title: 'Back to Item',
        route: '/items/test-item-123',
        icon: expect.any(Object), // ArrowLeftIcon mock
        color: 'teal',
      })
    })
  })

  describe('Error Handling Integration', () => {
    it('handles network errors gracefully', async () => {
      const wrapper = createWrapper({
        name: 'detail-new',
        path: '/items/test-item-123/details/new',
        params: { itemId: 'test-item-123' }, // Create mode
      })

      // Wait for component to initialize
      await wrapper.vm.$nextTick()

      const component = wrapper.vm as unknown as {
        editForm: { internal_name: string }
        saveDetail: () => Promise<void>
        mode: string
      }

      // Mock network error
      mockDetailStore.createDetail.mockRejectedValue(new Error('Network error'))

      component.editForm.internal_name = 'Test Detail'

      await component.saveDetail()

      // Verify error message
      expect(mockErrorStore.addMessage).toHaveBeenCalledWith(
        'error',
        'Failed to create detail. Please try again.'
      )

      // Should remain in create mode
      expect(component.mode).toBe('create')
    })
  })

  describe('Loading States', () => {
    it('handles loading states correctly', async () => {
      mockDetailStore.loading = true

      const wrapper = createWrapper()

      // Verify loading state is passed to DetailView
      const detailView = wrapper.findComponent({ name: 'DetailView' })
      expect(detailView.props('storeLoading')).toBe(true)
    })
  })
})
