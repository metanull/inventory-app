<template>
  <div>
    <!-- Detail Content -->
    <div v-if="resource || mode === 'create'" class="relative space-y-6">
      <!-- Header -->
      <div
        class="bg-white shadow"
        :class="{ 'border-l-4 border-blue-500': mode === 'edit' || mode === 'create' }"
      >
        <div class="px-4 py-5 sm:px-6">
          <div class="flex items-center justify-between">
            <div>
              <!-- Back Navigation Link -->
              <div v-if="backLink" class="mb-2">
                <router-link
                  :to="backLink.route"
                  :class="backLinkClasses"
                  class="inline-flex items-center text-sm font-medium transition-colors hover:underline"
                >
                  <component :is="backLink.icon" class="w-4 h-4 mr-1" />
                  {{ backLink.title }}
                </router-link>
              </div>

              <!-- Title Section -->
              <div class="flex items-center gap-3">
                <InternalName
                  :internal-name="
                    mode === 'create' ? createTitle || 'New Record' : resource?.internal_name || ''
                  "
                  :backward-compatibility="
                    mode !== 'create' ? resource?.backward_compatibility : undefined
                  "
                >
                  <template #icon>
                    <slot name="resource-icon" />
                  </template>
                </InternalName>

                <!-- Mode Indicator Pills -->
                <div
                  v-if="mode === 'edit'"
                  class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-blue-100 text-blue-800 text-sm font-medium"
                >
                  <PencilIcon class="h-4 w-4" />
                  Editing
                </div>
                <div
                  v-else-if="mode === 'create'"
                  class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-green-100 text-green-800 text-sm font-medium"
                >
                  <PlusIcon class="h-4 w-4" />
                  Creating
                </div>
              </div>
            </div>
            <div class="flex space-x-3">
              <template v-if="mode === 'view'">
                <EditButton @click="$emit('edit')" />
                <DeleteButton @click="$emit('delete')" />
              </template>
              <template v-else>
                <SaveButton :disabled="isSaveDisabled" @click="$emit('save')" />
                <CancelButton @click="handleCancel" />
              </template>
            </div>
          </div>
        </div>
      </div>

      <!-- Status Cards (hidden during creation and editing) -->
      <div
        v-if="statusCards && statusCards.length > 0 && mode === 'view'"
        class="grid grid-cols-1 gap-4 sm:grid-cols-2"
      >
        <StatusCard
          v-for="(card, index) in statusCards"
          :key="index"
          :title="card.title"
          :description="card.description"
          :main-color="card.mainColor"
          :status-text="card.statusText"
          :toggle-title="card.toggleTitle"
          :is-active="card.isActive"
          :loading="card.loading"
          :disabled="card.disabled"
          :compact="true"
          :active-icon-background-class="card.activeIconBackgroundClass"
          :inactive-icon-background-class="card.inactiveIconBackgroundClass"
          :active-icon-class="card.activeIconClass"
          :inactive-icon-class="card.inactiveIconClass"
          :active-icon-component="card.activeIconComponent"
          :inactive-icon-component="card.inactiveIconComponent"
          @toggle="$emit('statusToggle', index)"
        />
      </div>

      <!-- Information Section (Slot) -->
      <div class="bg-white shadow overflow-hidden sm:rounded-lg" :class="{}">
        <div class="px-4 py-5 sm:px-6">
          <Title variant="section" :description="informationDescription">
            {{ informationTitle }}
          </Title>
        </div>
        <div class="border-t border-gray-200">
          <slot name="information" :mode="mode" :on-field-change="handleFieldChange" />
        </div>
      </div>

      <!-- System Properties (hidden during creation) -->
      <div v-if="mode !== 'create'">
        <SystemProperties
          :id="resource?.id || ''"
          :created-at="resource?.created_at || ''"
          :updated-at="resource?.updated_at || ''"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { ref, computed, onMounted, type Component } from 'vue'
  import EditButton from '@/components/layout/detail/EditButton.vue'
  import DeleteButton from '@/components/layout/detail/DeleteButton.vue'
  import SaveButton from '@/components/layout/detail/SaveButton.vue'
  import CancelButton from '@/components/layout/detail/CancelButton.vue'
  import StatusCard from '@/components/format/card/StatusCard.vue'
  import SystemProperties from '@/components/layout/detail/SystemProperties.vue'
  import Title from '@/components/format/title/Title.vue'
  import InternalName from '@/components/format/InternalName.vue'
  import { PencilIcon, PlusIcon } from '@heroicons/vue/24/solid'

  // Types
  type Mode = 'view' | 'edit' | 'create'

  interface StatusCardConfig {
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

  interface Resource {
    id: string
    internal_name: string
    backward_compatibility?: string | null
    created_at: string | null
    updated_at: string | null
  }

  const props = defineProps<{
    // Loading states
    storeLoading?: boolean // Store's internal loading state (optional)
    resource: Resource | null

    // Mode state - Single source of truth
    mode: Mode
    hasUnsavedChanges?: boolean // New prop to track unsaved changes

    // Creation mode customization
    createTitle?: string // Custom title for creation mode
    createSubtitle?: string // Custom subtitle for creation mode

    // Navigation
    backLink?: {
      title: string
      route: string
      icon: any
      color?: string
    }

    // Configuration
    statusCards?: StatusCardConfig[]
    informationTitle: string
    informationDescription: string

    // Fetch function for initial load
    fetchData: () => Promise<void>
  }>()

  const emit = defineEmits<{
    edit: []
    save: []
    cancel: []
    delete: []
    statusToggle: [index: number]
    unsavedChanges: [hasChanges: boolean] // New emit for parent to notify of changes
  }>()

  // Internal loading state for initial page load
  const initialLoading = ref(false)

  // Disable save button if no unsaved changes
  const isSaveDisabled = computed(() => !props.hasUnsavedChanges)

  // Back link classes for dynamic coloring
  const backLinkClasses = computed(() => {
    if (!props.backLink?.color) return 'text-gray-600 hover:text-gray-800'

    const colorMap: Record<string, string> = {
      blue: 'text-blue-600 hover:text-blue-800',
      green: 'text-green-600 hover:text-green-800',
      purple: 'text-purple-600 hover:text-purple-800',
      orange: 'text-orange-600 hover:text-orange-800',
      red: 'text-red-600 hover:text-red-800',
      gray: 'text-gray-600 hover:text-gray-800',
    }
    return colorMap[props.backLink.color] || 'text-gray-600 hover:text-gray-800'
  })

  // Handle field changes from child components
  const handleFieldChange = () => {
    emit('unsavedChanges', true)
  }

  // Handle cancel action
  const handleCancel = () => {
    emit('cancel')
  }

  // Fetch data on mount
  onMounted(async () => {
    if (!props.resource && props.mode !== 'create') {
      initialLoading.value = true
      try {
        await props.fetchData()
      } catch (error) {
        console.error('Failed to fetch data:', error)
      } finally {
        initialLoading.value = false
      }
    }
  })
</script>
