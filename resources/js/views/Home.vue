<template>
  <div>
    <div class="mb-8">
      <Title variant="page" description="Welcome to the Inventory Management System">
        Dashboard
      </Title>
    </div>

    <!-- Inventory Section -->
    <div class="mb-8">
      <h2
        :class="[
          'text-xl font-semibold mb-4 pb-2 border-b',
          getThemeClass('modalTitle'),
          getThemeClass('dropdownBorder'),
        ]"
      >
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
      <h2
        :class="[
          'text-xl font-semibold mb-4 pb-2 border-b',
          getThemeClass('modalTitle'),
          getThemeClass('dropdownBorder'),
        ]"
      >
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
      <h2
        :class="[
          'text-xl font-semibold mb-4 pb-2 border-b',
          getThemeClass('modalTitle'),
          getThemeClass('dropdownBorder'),
        ]"
      >
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
      </div>
    </div>

    <!-- Tools Section -->
    <div class="mb-8">
      <h2
        :class="[
          'text-xl font-semibold mb-4 pb-2 border-b',
          getThemeClass('modalTitle'),
          getThemeClass('dropdownBorder'),
        ]"
      >
        Tools
      </h2>
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
          :active-icon-background-class="statusColors.badgeBackground"
          :inactive-icon-background-class="statusColors.inactiveBackground"
          :active-icon-class="statusColors.icon"
          :inactive-icon-class="statusColors.inactiveIcon"
          :active-icon-component="CheckCircleIcon"
          :inactive-icon-component="XCircleIcon"
          @toggle="checkApiStatus"
        >
          <template #icon>
            <SystemIcon />
          </template>
        </StatusCard>

        <!-- Clear Cache debug card (reuses the AppHeader clear cache action) -->
        <NavigationCard
          title="Clear Cache"
          description="Wipe local application data (stores) and reload. Debug-only action."
          main-color="gray"
          button-text="Clear Cache"
          :button-action="handleClearCache"
        >
          <template #icon>
            <FeaturesIcon />
          </template>
          <template #button-icon>
            <WrenchScrewdriverIcon class="h-4 w-4 mr-2 text-current" />
          </template>
        </NavigationCard>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { computed } from 'vue'
  import { useColors, getThemeClass } from '@/composables/useColors'
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
  import { WrenchScrewdriverIcon } from '@heroicons/vue/24/solid'
  import { useApiStatus } from '@/composables/useApiStatus'
  import { clearCacheAndReload } from '@/utils/storeUtils'

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

  // Color classes for status card icons (derived from apiStatusColor)
  const statusColors = useColors(apiStatusColor)

  // Clear cache handler (reuses helper)
  const handleClearCache = async () => {
    await clearCacheAndReload()
  }
</script>
