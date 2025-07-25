import { describe, it, expect, beforeEach, vi, afterEach } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useContextStore } from '../context'
import type { ContextResource } from '@metanull/inventory-app-api-client'

// Mock the ErrorHandler
vi.mock('@/utils/errorHandler', () => ({
  ErrorHandler: {
    handleError: vi.fn(),
  },
}))

// Mock the auth store
vi.mock('../auth', () => ({
  useAuthStore: vi.fn(() => ({
    token: 'mock-token',
  })),
}))

// Mock the API client
const mockContextApi = {
  contextIndex: vi.fn(),
  contextShow: vi.fn(),
  contextStore: vi.fn(),
  contextUpdate: vi.fn(),
  contextDestroy: vi.fn(),
  contextSetDefault: vi.fn(),
  contextGetDefault: vi.fn(),
}

vi.mock('@metanull/inventory-app-api-client', () => ({
  ContextApi: vi.fn().mockImplementation(() => mockContextApi),
  Configuration: vi.fn(),
}))

const mockContexts: ContextResource[] = [
  {
    id: '123e4567-e89b-12d3-a456-426614174000',
    internal_name: 'Main Context',
    backward_compatibility: 'main',
    is_default: true,
    created_at: '2024-01-01T00:00:00Z',
    updated_at: '2024-01-01T00:00:00Z',
  },
  {
    id: '123e4567-e89b-12d3-a456-426614174001',
    internal_name: 'Development Context',
    backward_compatibility: 'dev',
    is_default: false,
    created_at: '2024-01-01T00:00:00Z',
    updated_at: '2024-01-01T00:00:00Z',
  },
]

describe('Context Store', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  afterEach(() => {
    vi.clearAllMocks()
  })

  it('should initialize with empty state', () => {
    const store = useContextStore()

    expect(store.contexts).toEqual([])
    expect(store.currentContext).toBeNull()
    expect(store.loading).toBe(false)
    expect(store.error).toBeNull()
  })

  it('should compute default context correctly', () => {
    const store = useContextStore()

    // Initially no default context
    expect(store.defaultContext).toBeUndefined()

    // Add contexts with one being default
    store.contexts = mockContexts
    expect(store.defaultContext?.id).toBe('123e4567-e89b-12d3-a456-426614174000')
  })

  it('should clear error', () => {
    const store = useContextStore()

    store.error = 'Some error'
    store.clearError()

    expect(store.error).toBeNull()
  })

  it('should clear current context', () => {
    const store = useContextStore()

    store.currentContext = mockContexts[0]
    store.clearCurrentContext()

    expect(store.currentContext).toBeNull()
  })

  it('should handle fetchContexts success', async () => {
    const store = useContextStore()

    mockContextApi.contextIndex.mockResolvedValue({
      data: { data: mockContexts },
    })

    await store.fetchContexts()

    expect(mockContextApi.contextIndex).toHaveBeenCalled()
    expect(store.contexts).toEqual(mockContexts)
    expect(store.loading).toBe(false)
    expect(store.error).toBeNull()
  })

  it('should handle fetchContexts error', async () => {
    const store = useContextStore()
    const error = new Error('Network error')

    mockContextApi.contextIndex.mockRejectedValue(error)

    await expect(store.fetchContexts()).rejects.toThrow('Network error')

    expect(store.loading).toBe(false)
    expect(store.error).toBe('Failed to fetch contexts')
  })

  it('should handle fetchContext success', async () => {
    const store = useContextStore()
    const context = mockContexts[0]

    mockContextApi.contextShow.mockResolvedValue({
      data: { data: context },
    })

    const result = await store.fetchContext('123e4567-e89b-12d3-a456-426614174000')

    expect(mockContextApi.contextShow).toHaveBeenCalledWith('123e4567-e89b-12d3-a456-426614174000')
    expect(store.currentContext).toEqual(context)
    expect(result).toEqual(context)
  })

  it('should handle createContext success', async () => {
    const store = useContextStore()
    const newContext = {
      id: '123e4567-e89b-12d3-a456-426614174002',
      internal_name: 'Test Context',
      backward_compatibility: 'test',
      is_default: false,
      created_at: '2024-01-01T00:00:00Z',
      updated_at: '2024-01-01T00:00:00Z',
    }
    const createData = {
      internal_name: 'Test Context',
      backward_compatibility: 'test',
    }

    mockContextApi.contextStore.mockResolvedValue({
      data: { data: newContext },
    })

    const result = await store.createContext(createData)

    expect(mockContextApi.contextStore).toHaveBeenCalledWith(createData)
    expect(store.contexts).toHaveLength(1)
    expect(store.contexts[0]).toEqual(newContext)
    expect(result).toEqual(newContext)
  })

  it('should handle updateContext success', async () => {
    const store = useContextStore()
    store.contexts = [...mockContexts]

    const updatedContext = { ...mockContexts[0], internal_name: 'Updated Main Context' }
    const updateData = {
      internal_name: 'Updated Main Context',
      backward_compatibility: 'main',
    }

    mockContextApi.contextUpdate.mockResolvedValue({
      data: { data: updatedContext },
    })

    const result = await store.updateContext('123e4567-e89b-12d3-a456-426614174000', updateData)

    expect(mockContextApi.contextUpdate).toHaveBeenCalledWith(
      '123e4567-e89b-12d3-a456-426614174000',
      updateData
    )
    expect(store.contexts[0]).toEqual(updatedContext)
    expect(result).toEqual(updatedContext)
  })

  it('should handle deleteContext success', async () => {
    const store = useContextStore()
    store.contexts = [...mockContexts]

    mockContextApi.contextDestroy.mockResolvedValue({})

    await store.deleteContext('123e4567-e89b-12d3-a456-426614174001')

    expect(mockContextApi.contextDestroy).toHaveBeenCalledWith(
      '123e4567-e89b-12d3-a456-426614174001'
    )
    expect(store.contexts).toHaveLength(1)
    expect(store.contexts[0].id).toBe('123e4567-e89b-12d3-a456-426614174000')
  })

  it('should handle setDefaultContext success', async () => {
    const store = useContextStore()
    store.contexts = [...mockContexts]
    store.currentContext = mockContexts[1] // Development context

    const updatedContext = { ...mockContexts[1], is_default: true }

    mockContextApi.contextSetDefault.mockResolvedValue({
      data: { data: updatedContext },
    })

    const result = await store.setDefaultContext('123e4567-e89b-12d3-a456-426614174001', true)

    expect(mockContextApi.contextSetDefault).toHaveBeenCalledWith(
      '123e4567-e89b-12d3-a456-426614174001',
      { is_default: true }
    )

    // Check that the target context is now default
    expect(
      store.contexts.find(ctx => ctx.id === '123e4567-e89b-12d3-a456-426614174001')?.is_default
    ).toBe(true)

    // Check that other contexts are no longer default
    expect(
      store.contexts.find(ctx => ctx.id === '123e4567-e89b-12d3-a456-426614174000')?.is_default
    ).toBe(false)

    // Check that current context is updated if it matches
    expect(store.currentContext?.is_default).toBe(true)

    expect(result).toEqual(updatedContext)
  })

  it('should handle setDefaultContext error', async () => {
    const store = useContextStore()
    const errorMessage = 'Failed to set default'

    mockContextApi.contextSetDefault.mockRejectedValue(new Error(errorMessage))

    await expect(
      store.setDefaultContext('123e4567-e89b-12d3-a456-426614174001', true)
    ).rejects.toThrow(errorMessage)
    expect(store.error).toBe('Failed to set default context')
  })

  it('should handle getDefaultContext success', async () => {
    const store = useContextStore()
    const defaultContext = mockContexts[0] // Main Context is the default

    mockContextApi.contextGetDefault.mockResolvedValue({
      data: { data: defaultContext },
    })

    // Initially empty contexts array
    store.contexts = []

    const result = await store.getDefaultContext()

    expect(mockContextApi.contextGetDefault).toHaveBeenCalled()
    expect(result).toEqual(defaultContext)
    expect(store.contexts).toHaveLength(1)
    expect(store.contexts[0]).toEqual(defaultContext)
    expect(store.loading).toBe(false)
    expect(store.error).toBeNull()
  })

  it('should handle getDefaultContext error', async () => {
    const store = useContextStore()
    const errorMessage = 'Failed to get default'

    mockContextApi.contextGetDefault.mockRejectedValue(new Error(errorMessage))

    await expect(store.getDefaultContext()).rejects.toThrow(errorMessage)
    expect(store.error).toBe('Failed to get default context')
  })
})
