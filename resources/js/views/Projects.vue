<template>
  <ListView
    title="Projects"
    description="Manage projects in your inventory system. Projects can be enabled/disabled and launched/not launched."
    add-button-route="/projects/new"
    add-button-label="Add Project"
    color="orange"
    :is-empty="filteredProjects.length === 0"
    empty-title="No projects found"
    :empty-message="
      filterMode === 'all'
        ? 'Get started by creating a new project.'
        : filterMode === 'visible'
          ? 'No visible projects found. Projects are visible when they are enabled, launched, and the launch date has passed.'
          : `No ${filterMode} projects found.`
    "
    :show-empty-add-button="filterMode === 'all'"
    empty-add-button-label="New Project"
    @retry="fetchProjects"
  >
    <!-- Icon -->
    <template #icon>
      <ProjectIcon />
    </template>
    <!-- Filter Buttons -->
    <template #filters>
      <FilterButton
        label="All Projects"
        :is-active="filterMode === 'all'"
        :count="projects.length"
        variant="primary"
        @click="filterMode = 'all'"
      />
      <FilterButton
        label="Enabled"
        :is-active="filterMode === 'enabled'"
        :count="enabledProjects.length"
        variant="info"
        @click="filterMode = 'enabled'"
      />
      <FilterButton
        label="Launched"
        :is-active="filterMode === 'launched'"
        :count="launchedProjects.length"
        variant="info"
        @click="filterMode = 'launched'"
      />
      <FilterButton
        label="Visible"
        :is-active="filterMode === 'visible'"
        :count="visibleProjects.length"
        variant="success"
        @click="filterMode = 'visible'"
      />
    </template>

    <!-- Search Slot -->
    <template #search>
      <SearchControl v-model="searchQuery" placeholder="Search projects..." />
    </template>

    <!-- Projects Table -->
    <!-- Projects Table -->
    <template #headers>
      <TableRow>
        <TableHeader
          sortable
          :sort-direction="sortKey === 'internal_name' ? sortDirection : null"
          @sort="handleSort('internal_name')"
        >
          Project
        </TableHeader>
        <TableHeader
          class="hidden md:table-cell"
          sortable
          :sort-direction="sortKey === 'is_enabled' ? sortDirection : null"
          @sort="handleSort('is_enabled')"
        >
          Enabled
        </TableHeader>
        <TableHeader
          class="hidden md:table-cell"
          sortable
          :sort-direction="sortKey === 'is_launched' ? sortDirection : null"
          @sort="handleSort('is_launched')"
        >
          Launched
        </TableHeader>
        <TableHeader
          class="hidden lg:table-cell"
          sortable
          :sort-direction="sortKey === 'launch_date' ? sortDirection : null"
          @sort="handleSort('launch_date')"
        >
          Launch Date
        </TableHeader>
        <TableHeader
          class="hidden lg:table-cell"
          sortable
          :sort-direction="sortKey === 'created_at' ? sortDirection : null"
          @sort="handleSort('created_at')"
        >
          Created
        </TableHeader>
        <TableHeader class="hidden sm:table-cell" variant="actions">
          <span class="sr-only">Actions</span>
        </TableHeader>
      </TableRow>
    </template>

    <template #rows>
      <TableRow
        v-for="project in filteredProjects"
        :key="project.id"
        class="cursor-pointer hover:bg-orange-50 transition"
        @click="openProjectDetail(project.id)"
      >
        <TableCell>
          <InternalName
            small
            :internal-name="project.internal_name"
            :backward-compatibility="project.backward_compatibility"
          >
            <template #icon>
              <ProjectIcon class="h-5 w-5 text-orange-600" />
            </template>
          </InternalName>
        </TableCell>
        <TableCell class="hidden md:table-cell">
          <div @click.stop>
            <Toggle
              small
              title="Enabled"
              :status-text="project.is_enabled ? 'Enabled' : 'Disabled'"
              :is-active="project.is_enabled"
              @toggle="updateProjectStatus(project, 'is_enabled', !project.is_enabled)"
            />
          </div>
        </TableCell>
        <TableCell class="hidden md:table-cell">
          <div @click.stop>
            <Toggle
              small
              title="Launched"
              :status-text="project.is_launched ? 'Launched' : 'Not launched'"
              :is-active="project.is_launched"
              @toggle="updateProjectStatus(project, 'is_launched', !project.is_launched)"
            />
          </div>
        </TableCell>
        <TableCell class="hidden lg:table-cell">
          <DateDisplay
            v-if="project.launch_date"
            :date="project.launch_date"
            format="short"
            variant="small-dark"
          />
          <DisplayText v-else variant="gray">Not scheduled</DisplayText>
        </TableCell>
        <TableCell class="hidden lg:table-cell">
          <DateDisplay :date="project.created_at" format="short" variant="small-dark" />
        </TableCell>
        <TableCell class="hidden sm:table-cell">
          <div class="flex space-x-2" @click.stop>
            <ViewButton @click="router.push(`/projects/${project.id}`)" />
            <EditButton @click="router.push(`/projects/${project.id}?edit=true`)" />
            <DeleteButton @click="handleDeleteProject(project)" />
          </div>
        </TableCell>
      </TableRow>
    </template>
  </ListView>
</template>

<script setup lang="ts">
  import { ref, computed, onMounted } from 'vue'
  import { useRouter } from 'vue-router'
  import { useProjectStore } from '@/stores/project'
  import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
  import { useErrorDisplayStore } from '@/stores/errorDisplay'
  import { useDeleteConfirmationStore } from '@/stores/deleteConfirmation'
  import type { ProjectResource } from '@metanull/inventory-app-api-client'
  import ViewButton from '@/components/layout/list/ViewButton.vue'
  import EditButton from '@/components/layout/list/EditButton.vue'
  import DeleteButton from '@/components/layout/list/DeleteButton.vue'
  import DateDisplay from '@/components/format/Date.vue'
  import DisplayText from '@/components/format/DisplayText.vue'
  import FilterButton from '@/components/layout/list/FilterButton.vue'
  import ListView from '@/components/layout/list/ListView.vue'
  import TableHeader from '@/components/format/table/TableHeader.vue'
  import TableRow from '@/components/format/table/TableRow.vue'
  import TableCell from '@/components/format/table/TableCell.vue'
  import Toggle from '@/components/format/Toggle.vue'
  import InternalName from '@/components/format/InternalName.vue'
  import { FolderIcon as ProjectIcon } from '@heroicons/vue/24/solid'
  import SearchControl from '@/components/layout/list/SearchControl.vue'

  const router = useRouter()

  const projectStore = useProjectStore()
  const loadingStore = useLoadingOverlayStore()
  const errorStore = useErrorDisplayStore()
  const deleteStore = useDeleteConfirmationStore()

  const projects = computed(() => projectStore.projects)
  const visibleProjects = computed(() => projectStore.visibleProjects)
  const enabledProjects = computed(() => projectStore.enabledProjects)
  const launchedProjects = computed(() => projectStore.launchedProjects)

  // Filter state - default to 'visible'
  const filterMode = ref<'visible' | 'all' | 'enabled' | 'launched'>('all')

  // Sorting state
  const sortKey = ref<string>('internal_name')
  const sortDirection = ref<'asc' | 'desc'>('asc')

  // Search state
  const searchQuery = ref<string>('')

  // Handle sort event from TableHeader
  function handleSort(key: string) {
    if (sortKey.value === key) {
      sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
    } else {
      sortKey.value = key
      sortDirection.value = 'asc'
    }
  }

  // Computed filtered and sorted projects
  const filteredProjects = computed(() => {
    let list: ProjectResource[]
    switch (filterMode.value) {
      case 'visible':
        list = visibleProjects.value
        break
      case 'enabled':
        list = enabledProjects.value
        break
      case 'launched':
        list = launchedProjects.value
        break
      default:
        list = projects.value
    }
    // Search filter
    const query = searchQuery.value.trim().toLowerCase()
    if (query.length > 0) {
      list = list.filter(project => {
        const name = project.internal_name?.toLowerCase() ?? ''
        const compat = project.backward_compatibility?.toLowerCase() ?? ''
        return name.includes(query) || compat.includes(query)
      })
    }
    // Simple sorting logic
    return [...list].sort((a, b) => {
      const key = sortKey.value
      let valA: unknown
      let valB: unknown
      if (key === 'internal_name') {
        valA = a.internal_name ?? ''
        valB = b.internal_name ?? ''
      } else {
        valA = (a as any)[key]
        valB = (b as any)[key]
      }
      if (valA == null && valB == null) return 0
      if (valA == null) return 1
      if (valB == null) return -1
      if (valA < valB) return sortDirection.value === 'asc' ? -1 : 1
      if (valA > valB) return sortDirection.value === 'asc' ? 1 : -1
      return 0
    })
  })

  // Fetch projects on mount
  onMounted(async () => {
    let usedCache = false
    // If cache exists, display immediately and refresh in background
    if (projects.value && projects.value.length > 0) {
      usedCache = true
    } else {
      loadingStore.show()
    }
    try {
      // Always refresh in background
      await projectStore.fetchProjects()
      await projectStore.fetchEnabledProjects()
      if (usedCache) {
        errorStore.addMessage('info', 'List refreshed')
      }
    } catch {
      errorStore.addMessage('error', 'Failed to fetch projects. Please try again.')
    } finally {
      if (!usedCache) {
        loadingStore.hide()
      }
    }
  })

  // Open ProjectDetail for clicked project
  function openProjectDetail(projectId: string | number) {
    router.push(`/projects/${projectId}`)
  }

  // Update project status
  const updateProjectStatus = async (project: ProjectResource, field: string, value: boolean) => {
    try {
      loadingStore.show('Updating...')
      if (field === 'is_enabled') {
        await projectStore.setProjectEnabled(project.id, value)
        errorStore.addMessage('info', `Project ${value ? 'enabled' : 'disabled'} successfully.`)
      } else if (field === 'is_launched') {
        await projectStore.setProjectLaunched(project.id, value)
        errorStore.addMessage('info', `Project ${value ? 'launched' : 'unlaunched'} successfully.`)
      }
    } catch {
      errorStore.addMessage('error', `Failed to update project status. Please try again.`)
    } finally {
      loadingStore.hide()
    }
  }

  // Fetch projects function for retry
  const fetchProjects = async () => {
    try {
      loadingStore.show()
      if (filterMode.value === 'visible') {
        await projectStore.fetchEnabledProjects()
      } else {
        await projectStore.fetchProjects()
      }
      errorStore.addMessage('info', 'Projects refreshed successfully.')
    } catch {
      errorStore.addMessage('error', 'Failed to refresh projects. Please try again.')
    } finally {
      loadingStore.hide()
    }
  }

  // Delete project with confirmation
  const handleDeleteProject = async (projectToDelete: ProjectResource) => {
    const result = await deleteStore.trigger(
      'Delete Project',
      `Are you sure you want to delete "${projectToDelete.internal_name}"? This action cannot be undone.`
    )

    if (result === 'delete') {
      try {
        loadingStore.show('Deleting...')
        await projectStore.deleteProject(projectToDelete.id)
        errorStore.addMessage('info', 'Project deleted successfully.')
      } catch {
        errorStore.addMessage('error', 'Failed to delete project. Please try again.')
      } finally {
        loadingStore.hide()
      }
    }
  }

  // Expose properties for testing
  defineExpose({
    projects,
    filteredProjects,
    visibleProjects,
    enabledProjects,
    launchedProjects,
    filterMode,
    searchQuery,
    sortKey,
    sortDirection,
    openProjectDetail,
    updateProjectStatus,
    handleDeleteProject,
    handleSort,
    fetchProjects,
  })
</script>
