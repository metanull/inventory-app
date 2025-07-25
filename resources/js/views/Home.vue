<template>
  <div>
    <div class="mb-8">
      <Title variant="page" description="Welcome to the Inventory Management System">
        Dashboard
      </Title>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <!-- Languages Management Card -->
      <NavigationCard
        title="Languages"
        description="Manage system languages and localization settings"
        main-color="purple"
        button-text="Manage Languages"
        button-route="/languages"
      >
        <template #icon>
          <LanguageIcon />
        </template>
      </NavigationCard>

      <!-- Countries Management Card -->
      <NavigationCard
        title="Countries"
        description="Manage countries and geographic regions"
        main-color="blue"
        button-text="Manage Countries"
        button-route="/countries"
      >
        <template #icon>
          <CountryIcon />
        </template>
      </NavigationCard>

      <!-- Contexts Management Card -->
      <NavigationCard
        title="Contexts"
        description="Manage system contexts and operational environments"
        main-color="green"
        button-text="Manage Contexts"
        button-route="/contexts"
      >
        <template #icon>
          <ContextIcon />
        </template>
      </NavigationCard>

      <!-- Projects Management Card -->
      <NavigationCard
        title="Projects"
        description="Manage projects, collections with launch dates and status"
        main-color="orange"
        button-text="Manage Projects"
        button-route="/projects"
      >
        <template #icon>
          <ProjectIcon />
        </template>
      </NavigationCard>

      <!-- System Status Card -->
      <StatusCard
        title="System Status"
        :description="apiStatusDescription"
        :main-color="apiStatusColor"
        :status-text="apiStatusText"
        toggle-title="API Server"
        :is-active="isApiUp"
        :loading="apiLoading"
        :disabled="true"
        active-icon-background-class="bg-green-100"
        inactive-icon-background-class="bg-red-100"
        active-icon-class="text-green-600"
        inactive-icon-class="text-red-600"
        :active-icon-component="CheckCircleIcon"
        :inactive-icon-component="XCircleIcon"
        @toggle="checkApiStatus"
      >
        <template #icon>
          <SystemIcon />
        </template>
      </StatusCard>

      <!-- More Features Coming Soon Card -->
      <InformationCard
        title="Additional Features"
        description="More inventory management features like items, addresses, and details will be implemented in future iterations."
        main-color="gray"
        pill-text="Coming Soon"
      >
        <template #icon>
          <FeaturesIcon />
        </template>
      </InformationCard>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { computed } from 'vue'
  import NavigationCard from '@/components/format/card/NavigationCard.vue'
  import StatusCard from '@/components/format/card/StatusCard.vue'
  import InformationCard from '@/components/format/card/InformationCard.vue'
  import Title from '@/components/format/title/Title.vue'
  import {
    CheckCircleIcon,
    XCircleIcon,
    LanguageIcon,
    GlobeAltIcon as CountryIcon,
    CogIcon as ContextIcon,
    FolderIcon as ProjectIcon,
    CpuChipIcon as SystemIcon,
    AdjustmentsHorizontalIcon as FeaturesIcon,
  } from '@heroicons/vue/24/solid'
  import { useApiStatus } from '@/composables/useApiStatus'

  const { isApiUp, loading: apiLoading, checkApiStatus } = useApiStatus()

  const apiStatusText = computed(() => {
    if (apiLoading.value) return 'Checking...'
    return isApiUp.value ? 'API Online' : 'API Offline'
  })

  const apiStatusDescription = computed(() => {
    if (apiLoading.value) return 'Checking API server connection...'

    if (isApiUp.value) {
      return 'Connected to the API server'
    }

    return 'Unable to connect to the API server. Please check your connection.'
  })

  const apiStatusColor = computed(() => {
    return isApiUp.value ? 'green' : 'red'
  })
</script>
