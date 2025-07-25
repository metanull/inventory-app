/**
 * Unit Tests for ProjectDetail Component Business Logic
 *
 * These tests focus on the core functionality and business logic
 * of the ProjectDetail component without dealing with complex UI rendering.
 *
 * Tests cover:
 * - Mode management (view, edit, create)
 * - Form data handling and validation
 * - Unsaved changes detection
 * - Save operations (create and update)
 * - Status toggle operations (enable/disable, launch/unlaunch) - Project-specific
 * - Delete operations
 * - Navigation guards for unsaved changes
 * - Data fetching and error handling
 */

import { beforeEach, describe, expect, it, vi, beforeAll, afterAll } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { useProjectStore } from '@/stores/project'
import { useContextStore } from '@/stores/context'
import { useLanguageStore } from '@/stores/language'
import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
import { useErrorDisplayStore } from '@/stores/errorDisplay'
import { useDeleteConfirmationStore } from '@/stores/deleteConfirmation'
import { useCancelChangesConfirmationStore } from '@/stores/cancelChangesConfirmation'
import { createMockProject } from '@/__tests__/test-utils'
import type {
  ProjectResource,
  ContextResource,
  LanguageResource,
} from '@metanull/inventory-app-api-client'

// Mock console.error to suppress error output during tests
vi.mock('console', () => ({
  error: vi.fn(),
  warn: vi.fn(),
  log: vi.fn(),
}))

// Store original console methods for cleanup
// eslint-disable-next-line @typescript-eslint/no-explicit-any
let originalConsole: any

beforeAll(() => {
  originalConsole = { ...console }
  console.error = vi.fn()
  console.warn = vi.fn()
  console.log = vi.fn()
})

afterAll(() => {
  Object.assign(console, originalConsole)
})

// Mock the stores
vi.mock('@/stores/project')
vi.mock('@/stores/context')
vi.mock('@/stores/language')
vi.mock('@/stores/loadingOverlay')
vi.mock('@/stores/errorDisplay')
vi.mock('@/stores/deleteConfirmation')
vi.mock('@/stores/cancelChangesConfirmation')

// Test data
const mockProject: ProjectResource = createMockProject({
  id: '123',
  internal_name: 'Test Project',
  backward_compatibility: 'test-project',
  launch_date: '2023-12-01T00:00:00Z',
  is_enabled: true,
  is_launched: false,
  created_at: '2023-01-01T00:00:00Z',
  updated_at: '2023-02-01T00:00:00Z',
  context: {
    id: 'ctx-1',
    internal_name: 'Test Context',
  } as ContextResource,
  language: {
    id: 'lang-1',
    internal_name: 'English',
  } as LanguageResource,
})

const mockContexts: ContextResource[] = [
  {
    id: 'ctx-1',
    internal_name: 'Test Context',
    created_at: '2023-01-01T00:00:00Z',
    updated_at: '2023-01-01T00:00:00Z',
  },
  {
    id: 'ctx-2',
    internal_name: 'Another Context',
    created_at: '2023-01-01T00:00:00Z',
    updated_at: '2023-01-01T00:00:00Z',
  },
]

const mockLanguages: LanguageResource[] = [
  {
    id: 'lang-1',
    internal_name: 'English',
    created_at: '2023-01-01T00:00:00Z',
    updated_at: '2023-01-01T00:00:00Z',
  },
  {
    id: 'lang-2',
    internal_name: 'Spanish',
    created_at: '2023-01-01T00:00:00Z',
    updated_at: '2023-01-01T00:00:00Z',
  },
]

// Form data interface matching component
interface ProjectFormData {
  internal_name: string
  backward_compatibility: string
  launch_date: string
  context_id: string
  language_id: string
}

// Business logic class simulating the component behavior
class ProjectDetailLogic {
  public mode: 'view' | 'edit' | 'create' = 'view'
  public editForm: ProjectFormData = {
    internal_name: '',
    backward_compatibility: '',
    launch_date: '',
    context_id: '',
    language_id: '',
  }

  constructor(
    private projectStore: ReturnType<typeof useProjectStore>,
    private contextStore: ReturnType<typeof useContextStore>,
    private languageStore: ReturnType<typeof useLanguageStore>,
    private loadingStore: ReturnType<typeof useLoadingOverlayStore>,
    private errorStore: ReturnType<typeof useErrorDisplayStore>,
    private deleteStore: ReturnType<typeof useDeleteConfirmationStore>,
    private cancelChangesStore: ReturnType<typeof useCancelChangesConfirmationStore>
  ) {}

  get project() {
    return this.projectStore.currentProject
  }

  get contexts() {
    return this.contextStore.contexts
  }

  get languages() {
    return this.languageStore.languages
  }

  get defaultContext() {
    return this.contextStore.defaultContext
  }

  get defaultLanguage() {
    return this.languageStore.defaultLanguage
  }

  get hasUnsavedChanges(): boolean {
    if (this.mode === 'view') return false

    if (this.mode === 'create') {
      const defaultValues = this.getDefaultFormValues()
      return (
        this.editForm.internal_name !== defaultValues.internal_name ||
        this.editForm.backward_compatibility !== defaultValues.backward_compatibility ||
        this.editForm.launch_date !== defaultValues.launch_date ||
        this.editForm.context_id !== defaultValues.context_id ||
        this.editForm.language_id !== defaultValues.language_id
      )
    }

    if (!this.project) return false

    const originalValues = this.getFormValuesFromProject()
    return (
      this.editForm.internal_name !== originalValues.internal_name ||
      this.editForm.backward_compatibility !== originalValues.backward_compatibility ||
      this.editForm.launch_date !== originalValues.launch_date ||
      this.editForm.context_id !== originalValues.context_id ||
      this.editForm.language_id !== originalValues.language_id
    )
  }

  get statusCardsConfig() {
    if (!this.project) return []

    return [
      {
        title: 'Status',
        statusText: this.project.is_enabled ? 'Enabled' : 'Disabled',
        isActive: this.project.is_enabled,
        disabled: false,
      },
      {
        title: 'Launch Status',
        statusText: this.project.is_launched ? 'Launched' : 'Not Launched',
        isActive: this.project.is_launched,
        disabled: !this.project.is_enabled, // Launch is disabled when project is disabled
      },
    ]
  }

  private getDefaultFormValues(): ProjectFormData {
    return {
      internal_name: '',
      backward_compatibility: '',
      launch_date: '',
      context_id: this.defaultContext?.id || '',
      language_id: this.defaultLanguage?.id || '',
    }
  }

  private getFormValuesFromProject(): ProjectFormData {
    if (!this.project) return this.getDefaultFormValues()

    // Format launch_date for HTML date input (YYYY-MM-DD)
    let formattedLaunchDate = ''
    if (this.project.launch_date) {
      formattedLaunchDate = this.project.launch_date.split('T')[0]
    }

    return {
      internal_name: this.project.internal_name,
      backward_compatibility: this.project.backward_compatibility || '',
      launch_date: formattedLaunchDate,
      context_id: this.project.context?.id || '',
      language_id: this.project.language?.id || '',
    }
  }

  enterCreateMode() {
    this.mode = 'create'
    this.editForm = this.getDefaultFormValues()
  }

  enterEditMode() {
    if (!this.project) return
    this.mode = 'edit'
    this.editForm = this.getFormValuesFromProject()
  }

  enterViewMode() {
    this.mode = 'view'
    this.editForm = this.getDefaultFormValues()
  }

  async saveProject(): Promise<{ success: boolean; projectId?: string }> {
    try {
      this.loadingStore.show('Saving...')

      const projectData = {
        internal_name: this.editForm.internal_name,
        backward_compatibility: this.editForm.backward_compatibility || null,
        launch_date: this.editForm.launch_date || null,
        context_id: this.editForm.context_id || null,
        language_id: this.editForm.language_id || null,
      }

      if (this.mode === 'create') {
        const newProject = await this.projectStore.createProject(projectData)
        this.errorStore.addMessage('info', 'Project created successfully.')
        await this.projectStore.fetchProject(newProject.id)
        this.enterViewMode()
        return { success: true, projectId: newProject.id }
      } else if (this.mode === 'edit' && this.project) {
        await this.projectStore.updateProject(this.project.id, projectData)
        this.errorStore.addMessage('info', 'Project updated successfully.')
        this.enterViewMode()
        return { success: true, projectId: this.project.id }
      }

      return { success: false }
    } catch (error) {
      this.errorStore.addMessage(
        'error',
        'Failed to save project. Please check your input and try again.'
      )
      throw error
    } finally {
      this.loadingStore.hide()
    }
  }

  async handleStatusToggle(index: number): Promise<void> {
    if (!this.project) return

    if (index === 0) {
      await this.toggleEnabled()
    } else if (index === 1) {
      await this.toggleLaunched()
    }
  }

  private async toggleEnabled(): Promise<void> {
    if (!this.project) return

    try {
      this.loadingStore.show('Updating...')
      const newStatus = !this.project.is_enabled
      await this.projectStore.setProjectEnabled(this.project.id, newStatus)
      this.errorStore.addMessage(
        'info',
        `Project ${newStatus ? 'enabled' : 'disabled'} successfully.`
      )
    } catch (error) {
      this.errorStore.addMessage('error', 'Failed to update project status. Please try again.')
      throw error
    } finally {
      this.loadingStore.hide()
    }
  }

  private async toggleLaunched(): Promise<void> {
    if (!this.project) return

    try {
      this.loadingStore.show('Updating...')
      const newStatus = !this.project.is_launched
      await this.projectStore.setProjectLaunched(this.project.id, newStatus)
      this.errorStore.addMessage(
        'info',
        `Project ${newStatus ? 'launched' : 'unlaunched'} successfully.`
      )
    } catch (error) {
      this.errorStore.addMessage(
        'error',
        'Failed to update project launch status. Please try again.'
      )
      throw error
    } finally {
      this.loadingStore.hide()
    }
  }

  async deleteProject(): Promise<{ navigateToList: boolean }> {
    if (!this.project?.id) return { navigateToList: false }

    const result = await this.deleteStore.trigger(
      'Delete Project',
      `Are you sure you want to delete "${this.project.internal_name}"? This action cannot be undone.`
    )

    if (result === 'delete') {
      try {
        this.loadingStore.show('Deleting...')
        await this.projectStore.deleteProject(this.project.id)
        this.errorStore.addMessage('info', 'Project deleted successfully.')
        return { navigateToList: true }
      } catch (error) {
        this.errorStore.addMessage('error', 'Failed to delete project. Please try again.')
        throw error
      } finally {
        this.loadingStore.hide()
      }
    }

    return { navigateToList: false }
  }

  async fetchProject(projectId: string): Promise<void> {
    try {
      this.loadingStore.show()
      await this.projectStore.fetchProject(projectId)
    } catch (error) {
      this.errorStore.addMessage('error', 'Failed to load project. Please try again.')
      throw error
    } finally {
      this.loadingStore.hide()
    }
  }

  async initializeComponent(params: {
    projectId?: string
    isCreateRoute: boolean
    editMode: boolean
  }): Promise<void> {
    const { projectId, isCreateRoute, editMode } = params

    if (isCreateRoute) {
      this.projectStore.clearCurrentProject()
      await Promise.all([this.contextStore.fetchContexts(), this.languageStore.fetchLanguages()])
      this.enterCreateMode()
    } else if (projectId) {
      await Promise.all([
        this.fetchProject(projectId),
        this.contextStore.fetchContexts(),
        this.languageStore.fetchLanguages(),
      ])

      if (editMode && this.project) {
        this.enterEditMode()
      } else {
        this.enterViewMode()
      }
    }
  }
}

describe('ProjectDetail Component Business Logic', () => {
  let mockProjectStore: ReturnType<typeof useProjectStore>
  let mockContextStore: ReturnType<typeof useContextStore>
  let mockLanguageStore: ReturnType<typeof useLanguageStore>
  let mockLoadingStore: ReturnType<typeof useLoadingOverlayStore>
  let mockErrorStore: ReturnType<typeof useErrorDisplayStore>
  let mockDeleteStore: ReturnType<typeof useDeleteConfirmationStore>
  let mockCancelChangesStore: ReturnType<typeof useCancelChangesConfirmationStore>
  let projectDetailLogic: ProjectDetailLogic

  beforeEach(() => {
    setActivePinia(createPinia())

    // Setup store mocks
    mockProjectStore = {
      currentProject: mockProject,
      loading: false,
      fetchProject: vi.fn().mockResolvedValue(mockProject),
      createProject: vi.fn(),
      updateProject: vi.fn(),
      deleteProject: vi.fn(),
      setProjectEnabled: vi.fn(),
      setProjectLaunched: vi.fn(),
      clearCurrentProject: vi.fn(),
    } as ReturnType<typeof useProjectStore>

    mockContextStore = {
      contexts: mockContexts,
      defaultContext: mockContexts[0],
      fetchContexts: vi.fn().mockResolvedValue(mockContexts),
    } as ReturnType<typeof useContextStore>

    mockLanguageStore = {
      languages: mockLanguages,
      defaultLanguage: mockLanguages[0],
      fetchLanguages: vi.fn().mockResolvedValue(mockLanguages),
    } as ReturnType<typeof useLanguageStore>

    mockLoadingStore = {
      show: vi.fn(),
      hide: vi.fn(),
    } as ReturnType<typeof useLoadingOverlayStore>

    mockErrorStore = {
      addMessage: vi.fn(),
    } as ReturnType<typeof useErrorDisplayStore>

    mockDeleteStore = {
      trigger: vi.fn().mockResolvedValue('cancel'),
    } as ReturnType<typeof useDeleteConfirmationStore>

    mockCancelChangesStore = {
      trigger: vi.fn().mockResolvedValue('stay'),
      addChange: vi.fn(),
      resetChanges: vi.fn(),
    } as ReturnType<typeof useCancelChangesConfirmationStore>

    // Mock store implementations
    vi.mocked(useProjectStore).mockReturnValue(mockProjectStore)
    vi.mocked(useContextStore).mockReturnValue(mockContextStore)
    vi.mocked(useLanguageStore).mockReturnValue(mockLanguageStore)
    vi.mocked(useLoadingOverlayStore).mockReturnValue(mockLoadingStore)
    vi.mocked(useErrorDisplayStore).mockReturnValue(mockErrorStore)
    vi.mocked(useDeleteConfirmationStore).mockReturnValue(mockDeleteStore)
    vi.mocked(useCancelChangesConfirmationStore).mockReturnValue(mockCancelChangesStore)

    projectDetailLogic = new ProjectDetailLogic(
      mockProjectStore,
      mockContextStore,
      mockLanguageStore,
      mockLoadingStore,
      mockErrorStore,
      mockDeleteStore,
      mockCancelChangesStore
    )

    vi.clearAllMocks()
  })

  describe('Mode Management', () => {
    it('should initialize in view mode', () => {
      expect(projectDetailLogic.mode).toBe('view')
    })

    it('should transition to create mode with default form values', () => {
      projectDetailLogic.enterCreateMode()

      expect(projectDetailLogic.mode).toBe('create')
      expect(projectDetailLogic.editForm.internal_name).toBe('')
      expect(projectDetailLogic.editForm.context_id).toBe('ctx-1') // Default context
      expect(projectDetailLogic.editForm.language_id).toBe('lang-1') // Default language
    })

    it('should transition to edit mode with project data', () => {
      projectDetailLogic.enterEditMode()

      expect(projectDetailLogic.mode).toBe('edit')
      expect(projectDetailLogic.editForm.internal_name).toBe('Test Project')
      expect(projectDetailLogic.editForm.backward_compatibility).toBe('test-project')
      expect(projectDetailLogic.editForm.launch_date).toBe('2023-12-01') // Formatted for HTML
      expect(projectDetailLogic.editForm.context_id).toBe('ctx-1')
      expect(projectDetailLogic.editForm.language_id).toBe('lang-1')
    })

    it('should transition to view mode and clear form', () => {
      projectDetailLogic.enterEditMode()
      projectDetailLogic.editForm.internal_name = 'Modified'

      projectDetailLogic.enterViewMode()

      expect(projectDetailLogic.mode).toBe('view')
      expect(projectDetailLogic.editForm.internal_name).toBe('')
    })

    it('should not enter edit mode without current project', () => {
      mockProjectStore.currentProject = null

      projectDetailLogic.enterEditMode()

      expect(projectDetailLogic.mode).toBe('view') // Should remain in view mode
    })
  })

  describe('Form Data Management', () => {
    it('should format launch date correctly for HTML date input', () => {
      projectDetailLogic.enterEditMode()

      expect(projectDetailLogic.editForm.launch_date).toBe('2023-12-01')
    })

    it('should handle null launch date', () => {
      const projectWithoutDate = { ...mockProject, launch_date: null }
      mockProjectStore.currentProject = projectWithoutDate

      projectDetailLogic.enterEditMode()

      expect(projectDetailLogic.editForm.launch_date).toBe('')
    })

    it('should handle null backward compatibility', () => {
      const projectWithoutCompat = { ...mockProject, backward_compatibility: null }
      mockProjectStore.currentProject = projectWithoutCompat

      projectDetailLogic.enterEditMode()

      expect(projectDetailLogic.editForm.backward_compatibility).toBe('')
    })

    it('should handle missing context and language relations', () => {
      const projectWithoutRelations = { ...mockProject, context: null, language: null }
      mockProjectStore.currentProject = projectWithoutRelations

      projectDetailLogic.enterEditMode()

      expect(projectDetailLogic.editForm.context_id).toBe('')
      expect(projectDetailLogic.editForm.language_id).toBe('')
    })
  })

  describe('Unsaved Changes Detection', () => {
    it('should not detect changes in view mode', () => {
      expect(projectDetailLogic.hasUnsavedChanges).toBe(false)
    })

    it('should detect changes in create mode', () => {
      projectDetailLogic.enterCreateMode()

      expect(projectDetailLogic.hasUnsavedChanges).toBe(false)

      projectDetailLogic.editForm.internal_name = 'New Project'

      expect(projectDetailLogic.hasUnsavedChanges).toBe(true)
    })

    it('should detect changes in edit mode', () => {
      projectDetailLogic.enterEditMode()

      expect(projectDetailLogic.hasUnsavedChanges).toBe(false)

      projectDetailLogic.editForm.internal_name = 'Modified Project'

      expect(projectDetailLogic.hasUnsavedChanges).toBe(true)
    })

    it('should detect all form field changes', () => {
      projectDetailLogic.enterEditMode()

      // Test each field individually
      projectDetailLogic.editForm.backward_compatibility = 'modified'
      expect(projectDetailLogic.hasUnsavedChanges).toBe(true)

      // Reset and test another field
      projectDetailLogic.enterEditMode()
      projectDetailLogic.editForm.launch_date = '2024-01-01'
      expect(projectDetailLogic.hasUnsavedChanges).toBe(true)

      // Reset and test context
      projectDetailLogic.enterEditMode()
      projectDetailLogic.editForm.context_id = 'ctx-2'
      expect(projectDetailLogic.hasUnsavedChanges).toBe(true)

      // Reset and test language
      projectDetailLogic.enterEditMode()
      projectDetailLogic.editForm.language_id = 'lang-2'
      expect(projectDetailLogic.hasUnsavedChanges).toBe(true)
    })

    it('should not detect changes when reverting to original values', () => {
      projectDetailLogic.enterEditMode()

      projectDetailLogic.editForm.internal_name = 'Modified'
      expect(projectDetailLogic.hasUnsavedChanges).toBe(true)

      projectDetailLogic.editForm.internal_name = 'Test Project' // Original value
      expect(projectDetailLogic.hasUnsavedChanges).toBe(false)
    })
  })

  describe('Save Operations', () => {
    describe('Create Project', () => {
      it('should create new project successfully', async () => {
        const newProject = { ...mockProject, id: 'new-123' }
        mockProjectStore.createProject = vi.fn().mockResolvedValue(newProject)
        mockProjectStore.fetchProject = vi.fn().mockResolvedValue(newProject)

        projectDetailLogic.enterCreateMode()
        projectDetailLogic.editForm.internal_name = 'New Project'
        projectDetailLogic.editForm.backward_compatibility = 'new-project'

        const result = await projectDetailLogic.saveProject()

        expect(result.success).toBe(true)
        expect(result.projectId).toBe('new-123')
        expect(mockLoadingStore.show).toHaveBeenCalledWith('Saving...')
        expect(mockProjectStore.createProject).toHaveBeenCalledWith({
          internal_name: 'New Project',
          backward_compatibility: 'new-project',
          launch_date: null,
          context_id: 'ctx-1',
          language_id: 'lang-1',
        })
        expect(mockProjectStore.fetchProject).toHaveBeenCalledWith('new-123')
        expect(projectDetailLogic.mode).toBe('view')
        expect(mockErrorStore.addMessage).toHaveBeenCalledWith(
          'info',
          'Project created successfully.'
        )
        expect(mockLoadingStore.hide).toHaveBeenCalled()
      })

      it('should handle create project error', async () => {
        mockProjectStore.createProject = vi.fn().mockRejectedValue(new Error('Creation failed'))

        projectDetailLogic.enterCreateMode()
        projectDetailLogic.editForm.internal_name = 'New Project'

        await expect(projectDetailLogic.saveProject()).rejects.toThrow('Creation failed')

        expect(mockErrorStore.addMessage).toHaveBeenCalledWith(
          'error',
          'Failed to save project. Please check your input and try again.'
        )
        expect(projectDetailLogic.mode).toBe('create') // Should stay in create mode
      })

      it('should convert empty strings to null for optional fields', async () => {
        const newProject = { ...mockProject, id: 'new-123' }
        mockProjectStore.createProject = vi.fn().mockResolvedValue(newProject)
        mockProjectStore.fetchProject = vi.fn().mockResolvedValue(newProject)

        projectDetailLogic.enterCreateMode()
        projectDetailLogic.editForm.internal_name = 'New Project'
        projectDetailLogic.editForm.backward_compatibility = ''
        projectDetailLogic.editForm.launch_date = ''
        projectDetailLogic.editForm.context_id = ''
        projectDetailLogic.editForm.language_id = ''

        await projectDetailLogic.saveProject()

        expect(mockProjectStore.createProject).toHaveBeenCalledWith({
          internal_name: 'New Project',
          backward_compatibility: null,
          launch_date: null,
          context_id: null,
          language_id: null,
        })
      })
    })

    describe('Update Project', () => {
      it('should update existing project successfully', async () => {
        const updatedProject = { ...mockProject, internal_name: 'Updated Project' }
        mockProjectStore.updateProject = vi.fn().mockResolvedValue(updatedProject)

        projectDetailLogic.enterEditMode()
        projectDetailLogic.editForm.internal_name = 'Updated Project'

        const result = await projectDetailLogic.saveProject()

        expect(result.success).toBe(true)
        expect(result.projectId).toBe('123')
        expect(mockLoadingStore.show).toHaveBeenCalledWith('Saving...')
        expect(mockProjectStore.updateProject).toHaveBeenCalledWith('123', {
          internal_name: 'Updated Project',
          backward_compatibility: 'test-project',
          launch_date: '2023-12-01',
          context_id: 'ctx-1',
          language_id: 'lang-1',
        })
        expect(projectDetailLogic.mode).toBe('view')
        expect(mockErrorStore.addMessage).toHaveBeenCalledWith(
          'info',
          'Project updated successfully.'
        )
      })

      it('should handle update project error', async () => {
        mockProjectStore.updateProject = vi.fn().mockRejectedValue(new Error('Update failed'))

        projectDetailLogic.enterEditMode()
        projectDetailLogic.editForm.internal_name = 'Updated Project'

        await expect(projectDetailLogic.saveProject()).rejects.toThrow('Update failed')

        expect(mockErrorStore.addMessage).toHaveBeenCalledWith(
          'error',
          'Failed to save project. Please check your input and try again.'
        )
        expect(projectDetailLogic.mode).toBe('edit') // Should stay in edit mode
      })

      it('should not save if not in edit mode and no project', async () => {
        projectDetailLogic.mode = 'edit'
        mockProjectStore.currentProject = null

        const result = await projectDetailLogic.saveProject()

        expect(result.success).toBe(false)
        expect(mockProjectStore.updateProject).not.toHaveBeenCalled()
      })
    })
  })

  describe('Status Cards Configuration (Project-specific)', () => {
    it('should configure status cards for enabled project', () => {
      const statusCards = projectDetailLogic.statusCardsConfig

      expect(statusCards).toHaveLength(2)

      // Enabled status card
      expect(statusCards[0].title).toBe('Status')
      expect(statusCards[0].statusText).toBe('Enabled')
      expect(statusCards[0].isActive).toBe(true)
      expect(statusCards[0].disabled).toBe(false)

      // Launch status card
      expect(statusCards[1].title).toBe('Launch Status')
      expect(statusCards[1].statusText).toBe('Not Launched')
      expect(statusCards[1].isActive).toBe(false)
      expect(statusCards[1].disabled).toBe(false) // Not disabled because project is enabled
    })

    it('should disable launch status when project is disabled', () => {
      const disabledProject = { ...mockProject, is_enabled: false }
      mockProjectStore.currentProject = disabledProject

      const statusCards = projectDetailLogic.statusCardsConfig

      expect(statusCards[0].statusText).toBe('Disabled')
      expect(statusCards[0].isActive).toBe(false)
      expect(statusCards[1].disabled).toBe(true) // Launch should be disabled
    })

    it('should show correct status text for launched project', () => {
      const launchedProject = { ...mockProject, is_launched: true }
      mockProjectStore.currentProject = launchedProject

      const statusCards = projectDetailLogic.statusCardsConfig

      expect(statusCards[1].statusText).toBe('Launched')
      expect(statusCards[1].isActive).toBe(true)
    })

    it('should return empty array when no project', () => {
      mockProjectStore.currentProject = null

      const statusCards = projectDetailLogic.statusCardsConfig

      expect(statusCards).toEqual([])
    })
  })

  describe('Status Toggle Operations (Project-specific)', () => {
    it('should toggle enabled status successfully', async () => {
      const updatedProject = { ...mockProject, is_enabled: false }
      mockProjectStore.setProjectEnabled = vi.fn().mockResolvedValue(updatedProject)

      await projectDetailLogic.handleStatusToggle(0) // First status card

      expect(mockLoadingStore.show).toHaveBeenCalledWith('Updating...')
      expect(mockProjectStore.setProjectEnabled).toHaveBeenCalledWith('123', false)
      expect(mockErrorStore.addMessage).toHaveBeenCalledWith(
        'info',
        'Project disabled successfully.'
      )
      expect(mockLoadingStore.hide).toHaveBeenCalled()
    })

    it('should toggle launched status successfully', async () => {
      const updatedProject = { ...mockProject, is_launched: true }
      mockProjectStore.setProjectLaunched = vi.fn().mockResolvedValue(updatedProject)

      await projectDetailLogic.handleStatusToggle(1) // Second status card

      expect(mockLoadingStore.show).toHaveBeenCalledWith('Updating...')
      expect(mockProjectStore.setProjectLaunched).toHaveBeenCalledWith('123', true)
      expect(mockErrorStore.addMessage).toHaveBeenCalledWith(
        'info',
        'Project launched successfully.'
      )
    })

    it('should handle toggle enabled error', async () => {
      mockProjectStore.setProjectEnabled = vi.fn().mockRejectedValue(new Error('Toggle failed'))

      await expect(projectDetailLogic.handleStatusToggle(0)).rejects.toThrow('Toggle failed')

      expect(mockErrorStore.addMessage).toHaveBeenCalledWith(
        'error',
        'Failed to update project status. Please try again.'
      )
    })

    it('should handle toggle launched error', async () => {
      mockProjectStore.setProjectLaunched = vi.fn().mockRejectedValue(new Error('Toggle failed'))

      await expect(projectDetailLogic.handleStatusToggle(1)).rejects.toThrow('Toggle failed')

      expect(mockErrorStore.addMessage).toHaveBeenCalledWith(
        'error',
        'Failed to update project launch status. Please try again.'
      )
    })

    it('should not toggle without current project', async () => {
      mockProjectStore.currentProject = null

      await projectDetailLogic.handleStatusToggle(0)

      expect(mockProjectStore.setProjectEnabled).not.toHaveBeenCalled()
      expect(mockProjectStore.setProjectLaunched).not.toHaveBeenCalled()
    })

    it('should handle invalid status card index gracefully', async () => {
      await projectDetailLogic.handleStatusToggle(2) // Invalid index

      expect(mockProjectStore.setProjectEnabled).not.toHaveBeenCalled()
      expect(mockProjectStore.setProjectLaunched).not.toHaveBeenCalled()
    })
  })

  describe('Delete Operations', () => {
    it('should show delete confirmation dialog', async () => {
      const result = await projectDetailLogic.deleteProject()

      expect(mockDeleteStore.trigger).toHaveBeenCalledWith(
        'Delete Project',
        'Are you sure you want to delete "Test Project"? This action cannot be undone.'
      )
      expect(result.navigateToList).toBe(false)
    })

    it('should delete project when confirmed', async () => {
      mockDeleteStore.trigger = vi.fn().mockResolvedValue('delete')

      const result = await projectDetailLogic.deleteProject()

      expect(mockLoadingStore.show).toHaveBeenCalledWith('Deleting...')
      expect(mockProjectStore.deleteProject).toHaveBeenCalledWith('123')
      expect(mockErrorStore.addMessage).toHaveBeenCalledWith(
        'info',
        'Project deleted successfully.'
      )
      expect(result.navigateToList).toBe(true)
    })

    it('should not delete when cancelled', async () => {
      mockDeleteStore.trigger = vi.fn().mockResolvedValue('cancel')

      const result = await projectDetailLogic.deleteProject()

      expect(mockProjectStore.deleteProject).not.toHaveBeenCalled()
      expect(result.navigateToList).toBe(false)
    })

    it('should handle delete error', async () => {
      mockDeleteStore.trigger = vi.fn().mockResolvedValue('delete')
      mockProjectStore.deleteProject = vi.fn().mockRejectedValue(new Error('Delete failed'))

      await expect(projectDetailLogic.deleteProject()).rejects.toThrow('Delete failed')

      expect(mockErrorStore.addMessage).toHaveBeenCalledWith(
        'error',
        'Failed to delete project. Please try again.'
      )
    })

    it('should not attempt delete without project', async () => {
      mockProjectStore.currentProject = null

      const result = await projectDetailLogic.deleteProject()

      expect(mockDeleteStore.trigger).not.toHaveBeenCalled()
      expect(result.navigateToList).toBe(false)
    })
  })

  describe('Data Fetching and Initialization', () => {
    it('should initialize for existing project in view mode', async () => {
      await projectDetailLogic.initializeComponent({
        projectId: '123',
        isCreateRoute: false,
        editMode: false,
      })

      expect(mockProjectStore.fetchProject).toHaveBeenCalledWith('123')
      expect(mockContextStore.fetchContexts).toHaveBeenCalled()
      expect(mockLanguageStore.fetchLanguages).toHaveBeenCalled()
      expect(projectDetailLogic.mode).toBe('view')
    })

    it('should initialize for existing project in edit mode', async () => {
      await projectDetailLogic.initializeComponent({
        projectId: '123',
        isCreateRoute: false,
        editMode: true,
      })

      expect(mockProjectStore.fetchProject).toHaveBeenCalledWith('123')
      expect(projectDetailLogic.mode).toBe('edit')
    })

    it('should initialize for create mode', async () => {
      await projectDetailLogic.initializeComponent({
        projectId: undefined,
        isCreateRoute: true,
        editMode: false,
      })

      expect(mockProjectStore.clearCurrentProject).toHaveBeenCalled()
      expect(mockContextStore.fetchContexts).toHaveBeenCalled()
      expect(mockLanguageStore.fetchLanguages).toHaveBeenCalled()
      expect(projectDetailLogic.mode).toBe('create')
    })

    it('should handle project fetch error', async () => {
      mockProjectStore.fetchProject = vi.fn().mockRejectedValue(new Error('Fetch failed'))

      await expect(projectDetailLogic.fetchProject('123')).rejects.toThrow('Fetch failed')

      expect(mockErrorStore.addMessage).toHaveBeenCalledWith(
        'error',
        'Failed to load project. Please try again.'
      )
    })

    it('should handle initialization errors', async () => {
      mockProjectStore.fetchProject = vi.fn().mockRejectedValue(new Error('Init failed'))

      await expect(
        projectDetailLogic.initializeComponent({
          projectId: '123',
          isCreateRoute: false,
          editMode: false,
        })
      ).rejects.toThrow('Init failed')
    })
  })

  describe('Form Validation Edge Cases', () => {
    it('should handle form with all empty optional fields', () => {
      projectDetailLogic.enterCreateMode()
      projectDetailLogic.editForm.internal_name = 'Test'
      projectDetailLogic.editForm.backward_compatibility = ''
      projectDetailLogic.editForm.launch_date = ''
      projectDetailLogic.editForm.context_id = ''
      projectDetailLogic.editForm.language_id = ''

      // Should still detect changes because internal_name is different
      expect(projectDetailLogic.hasUnsavedChanges).toBe(true)
    })

    it('should handle form with whitespace in fields', () => {
      projectDetailLogic.enterCreateMode()
      projectDetailLogic.editForm.internal_name = '   '

      expect(projectDetailLogic.hasUnsavedChanges).toBe(true)
    })
  })
})
