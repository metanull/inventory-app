<template>
  <div>
    <div class="mb-8">
      <Title variant="page" description="Welcome to the Inventory Management System">
        Dashboard
      </Title>
    </div>

    <!-- Inventory Section -->
    <div class="mb-8">
      <h2 class="text-xl font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">
        Inventory
      </h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <!-- Items Management Card -->
        <NavigationCard
          title="Items"
          description="Manage inventory items including objects and monuments"
          main-color="teal"
          button-text="Manage Items"
          button-route="/items"
        >
          <template #icon>
            <ItemIcon />
          </template>
        </NavigationCard>

        <!-- Partners Management Card -->
        <NavigationCard
          title="Partners"
          description="Manage partners including museums, institutions, and individuals"
          main-color="yellow"
          button-text="Manage Partners"
          button-route="/partners"
        >
          <template #icon>
            <PartnerIcon />
          </template>
        </NavigationCard>
      </div>
    </div>

    <!-- Collections Section -->
    <div class="mb-8">
      <h2 class="text-xl font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">
        Collections
      </h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <!-- Collections Management Card -->
        <NavigationCard
          title="Collections"
          description="Manage collections of museum items with context and translation support"
          main-color="indigo"
          button-text="Manage Collections"
          button-route="/collections"
        >
          <template #icon>
            <CollectionIcon />
          </template>
        </NavigationCard>

        <!-- Galleries Coming Soon Card -->
        <InformationCard
          title="Galleries"
          description="Mixed content galleries containing Items and Details will be available in future updates."
          main-color="gray"
          pill-text="Coming Soon"
        >
          <template #icon>
            <GalleryIcon />
          </template>
        </InformationCard>

        <!-- Exhibitions Coming Soon Card -->
        <InformationCard
          title="Exhibitions"
          description="Theme-based exhibition collections with hierarchical organization will be available in future updates."
          main-color="gray"
          pill-text="Coming Soon"
        >
          <template #icon>
            <ExhibitionIcon />
          </template>
        </InformationCard>
      </div>
    </div>

    <!-- Reference Data Section -->
    <div class="mb-8">
      <h2 class="text-xl font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">
        Reference Data
      </h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
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
          description="Organize items and documentation by project or exhibition"
          main-color="purple"
          button-text="Manage Projects"
          button-route="/projects"
        >
          <template #icon>
            <ProjectIcon />
          </template>
        </NavigationCard>

        <!-- Collections Management Card -->
        <NavigationCard
          title="Collections"
          description="Organize and manage collections of artifacts and cultural items"
          main-color="green"
          button-text="Manage Collections"
          button-route="/collections"
        >
          <template #icon>
            <CollectionIcon />
          </template>
        </NavigationCard>
      </div>
    </div>

    <!-- Tools Section -->
    <div class="mb-8">
      <h2 class="text-xl font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">Tools</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
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
    ArchiveBoxIcon as ItemIcon,
    UserGroupIcon as PartnerIcon,
    RectangleStackIcon as CollectionIcon,
    PhotoIcon as GalleryIcon,
    PresentationChartLineIcon as ExhibitionIcon,
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
