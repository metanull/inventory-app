<template>
  <header class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center py-6">
        <div class="flex items-center">
          <RouterLink to="/" class="text-2xl font-bold text-gray-900">
            {{ appTitle }}
          </RouterLink>
        </div>

        <nav class="hidden md:flex space-x-8 items-center">
          <RouterLink
            to="/"
            class="text-gray-500 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium flex items-center gap-2"
          >
            <HomeIcon class="w-4 h-4" />
            Dashboard
          </RouterLink>

          <!-- Inventory Dropdown -->
          <div class="relative" @mouseleave="closeInventoryDropdown">
            <button
              class="text-gray-500 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium flex items-center gap-1"
              @mouseenter="openInventoryDropdown"
              @click="toggleInventoryDropdown"
            >
              Inventory
              <ChevronDownIcon
                class="w-4 h-4 transition-transform"
                :class="{ 'rotate-180': isInventoryDropdownOpen }"
              />
            </button>

            <div
              v-if="isInventoryDropdownOpen"
              class="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200"
              @mouseenter="keepInventoryDropdownOpen"
              @mouseleave="closeInventoryDropdown"
            >
              <RouterLink
                to="/items"
                class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2"
                @click="closeInventoryDropdown"
              >
                <ArchiveBoxIcon class="w-4 h-4 text-teal-600" />
                Items
              </RouterLink>
              <RouterLink
                to="/partners"
                class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2"
                @click="closeInventoryDropdown"
              >
                <UserGroupIcon class="w-4 h-4 text-yellow-600" />
                Partners
              </RouterLink>
            </div>
          </div>

          <!-- Collections Dropdown -->
          <div class="relative" @mouseleave="closeCollectionsDropdown">
            <button
              class="text-gray-500 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium flex items-center gap-1"
              @mouseenter="openCollectionsDropdown"
              @click="toggleCollectionsDropdown"
            >
              Collections
              <ChevronDownIcon
                class="w-4 h-4 transition-transform"
                :class="{ 'rotate-180': isCollectionsDropdownOpen }"
              />
            </button>

            <div
              v-if="isCollectionsDropdownOpen"
              class="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200"
              @mouseenter="keepCollectionsDropdownOpen"
              @mouseleave="closeCollectionsDropdown"
            >
              <RouterLink
                to="/collections"
                class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2"
                @click="closeCollectionsDropdown"
              >
                <RectangleStackIcon class="w-4 h-4 text-indigo-600" />
                Collections
              </RouterLink>
              <div class="px-4 py-2 text-sm text-gray-500 flex items-center gap-2">
                <PhotoIcon class="w-4 h-4 text-gray-400" />
                <div class="flex flex-col">
                  <span>Galleries</span>
                  <span class="text-xs text-gray-400">Coming Soon</span>
                </div>
              </div>
              <div class="px-4 py-2 text-sm text-gray-500 flex items-center gap-2">
                <PresentationChartLineIcon class="w-4 h-4 text-gray-400" />
                <div class="flex flex-col">
                  <span>Exhibitions</span>
                  <span class="text-xs text-gray-400">Coming Soon</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Reference Data Dropdown -->
          <div class="relative" @mouseleave="closeDropdown">
            <button
              class="text-gray-500 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium flex items-center gap-1"
              @mouseenter="openDropdown"
              @click="toggleDropdown"
            >
              Reference Data
              <ChevronDownIcon
                class="w-4 h-4 transition-transform"
                :class="{ 'rotate-180': isDropdownOpen }"
              />
            </button>

            <div
              v-if="isDropdownOpen"
              class="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200"
              @mouseenter="keepDropdownOpen"
              @mouseleave="closeDropdown"
            >
              <RouterLink
                to="/languages"
                class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2"
                @click="closeDropdown"
              >
                <LanguageIcon class="w-4 h-4 text-purple-600" />
                Languages
              </RouterLink>
              <RouterLink
                to="/countries"
                class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2"
                @click="closeDropdown"
              >
                <GlobeAltIcon class="w-4 h-4 text-blue-600" />
                Countries
              </RouterLink>
              <RouterLink
                to="/contexts"
                class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2"
                @click="closeDropdown"
              >
                <CogIcon class="w-4 h-4 text-green-600" />
                Contexts
              </RouterLink>
              <RouterLink
                to="/projects"
                class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2"
                @click="closeDropdown"
              >
                <FolderIcon class="w-4 h-4 text-orange-600" />
                Projects
              </RouterLink>
            </div>
          </div>

          <!-- Tools Dropdown -->
          <div class="relative" @mouseleave="closeToolsDropdown">
            <button
              class="text-gray-500 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium flex items-center gap-1"
              @mouseenter="openToolsDropdown"
              @click="toggleToolsDropdown"
            >
              Tools
              <ChevronDownIcon
                class="w-4 h-4 transition-transform"
                :class="{ 'rotate-180': isToolsDropdownOpen }"
              />
            </button>

            <div
              v-if="isToolsDropdownOpen"
              class="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200"
              @mouseenter="keepToolsDropdownOpen"
              @mouseleave="closeToolsDropdown"
            >
              <button
                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2"
                @click="handleClearCache"
              >
                <WrenchScrewdriverIcon class="w-4 h-4 text-red-600" />
                Clear cache
              </button>
            </div>
          </div>
        </nav>

        <!-- Desktop Actions -->
        <div class="hidden md:flex items-center space-x-4">
          <button
            v-if="authStore.isAuthenticated"
            class="text-gray-500 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium flex items-center gap-2"
            @click="handleLogout"
          >
            <ArrowRightOnRectangleIcon class="w-4 h-4" />
            Logout
          </button>
        </div>

        <!-- Mobile menu button -->
        <div class="md:hidden">
          <button
            class="text-gray-500 hover:text-gray-900 focus:outline-none focus:text-gray-900 p-2"
            @click="toggleMobileMenu"
          >
            <Bars3Icon v-if="!isMobileMenuOpen" class="w-6 h-6" />
            <XMarkIcon v-else class="w-6 h-6" />
          </button>
        </div>
      </div>

      <!-- Mobile Navigation -->
      <div v-if="isMobileMenuOpen" class="md:hidden border-t border-gray-200 py-4">
        <div class="flex flex-col space-y-2">
          <RouterLink
            to="/"
            class="text-gray-500 hover:text-gray-900 px-3 py-2 rounded-md text-base font-medium flex items-center gap-2"
            @click="closeMobileMenu"
          >
            <HomeIcon class="w-5 h-5" />
            Dashboard
          </RouterLink>

          <!-- Mobile Inventory Section -->
          <div class="px-3">
            <button
              class="text-gray-500 hover:text-gray-900 w-full text-left py-2 text-base font-medium flex items-center justify-between"
              @click="toggleMobileInventoryDropdown"
            >
              Inventory
              <ChevronDownIcon
                class="w-4 h-4 transition-transform"
                :class="{ 'rotate-180': isMobileInventoryDropdownOpen }"
              />
            </button>

            <div
              v-if="isMobileInventoryDropdownOpen"
              class="mt-2 space-y-2 pl-4 border-l-2 border-gray-200"
            >
              <RouterLink
                to="/items"
                class="text-gray-500 hover:text-gray-900 py-2 text-sm flex items-center gap-2"
                @click="closeMobileMenu"
              >
                <ArchiveBoxIcon class="w-4 h-4 text-teal-600" />
                Items
              </RouterLink>
              <RouterLink
                to="/partners"
                class="text-gray-500 hover:text-gray-900 py-2 text-sm flex items-center gap-2"
                @click="closeMobileMenu"
              >
                <UserGroupIcon class="w-4 h-4 text-yellow-600" />
                Partners
              </RouterLink>
            </div>
          </div>

          <!-- Mobile Collections Section -->
          <div class="px-3">
            <button
              class="text-gray-500 hover:text-gray-900 w-full text-left py-2 text-base font-medium flex items-center justify-between"
              @click="toggleMobileCollectionsDropdown"
            >
              Collections
              <ChevronDownIcon
                class="w-4 h-4 transition-transform"
                :class="{ 'rotate-180': isMobileCollectionsDropdownOpen }"
              />
            </button>

            <div
              v-if="isMobileCollectionsDropdownOpen"
              class="mt-2 space-y-2 pl-4 border-l-2 border-gray-200"
            >
              <RouterLink
                to="/collections"
                class="text-gray-500 hover:text-gray-900 py-2 text-sm flex items-center gap-2"
                @click="closeMobileMenu"
              >
                <RectangleStackIcon class="w-4 h-4 text-indigo-600" />
                Collections
              </RouterLink>
              <div class="text-gray-400 py-2 text-sm flex items-center gap-2">
                <PhotoIcon class="w-4 h-4" />
                <div class="flex flex-col">
                  <span>Galleries</span>
                  <span class="text-xs">Coming Soon</span>
                </div>
              </div>
              <div class="text-gray-400 py-2 text-sm flex items-center gap-2">
                <PresentationChartLineIcon class="w-4 h-4" />
                <div class="flex flex-col">
                  <span>Exhibitions</span>
                  <span class="text-xs">Coming Soon</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Mobile Reference Data Section -->
          <div class="px-3">
            <button
              class="text-gray-500 hover:text-gray-900 w-full text-left py-2 text-base font-medium flex items-center justify-between"
              @click="toggleMobileDropdown"
            >
              Reference Data
              <ChevronDownIcon
                class="w-4 h-4 transition-transform"
                :class="{ 'rotate-180': isMobileDropdownOpen }"
              />
            </button>

            <div v-if="isMobileDropdownOpen" class="mt-2 space-y-2 pl-4 border-l-2 border-gray-200">
              <RouterLink
                to="/languages"
                class="text-gray-500 hover:text-gray-900 py-2 text-sm flex items-center gap-2"
                @click="closeMobileMenu"
              >
                <LanguageIcon class="w-4 h-4 text-purple-600" />
                Languages
              </RouterLink>
              <RouterLink
                to="/countries"
                class="text-gray-500 hover:text-gray-900 py-2 text-sm flex items-center gap-2"
                @click="closeMobileMenu"
              >
                <GlobeAltIcon class="w-4 h-4 text-blue-600" />
                Countries
              </RouterLink>
              <RouterLink
                to="/contexts"
                class="text-gray-500 hover:text-gray-900 py-2 text-sm flex items-center gap-2"
                @click="closeMobileMenu"
              >
                <CogIcon class="w-4 h-4 text-green-600" />
                Contexts
              </RouterLink>
              <RouterLink
                to="/projects"
                class="text-gray-500 hover:text-gray-900 py-2 text-sm flex items-center gap-2"
                @click="closeMobileMenu"
              >
                <FolderIcon class="w-4 h-4 text-orange-600" />
                Projects
              </RouterLink>
            </div>
          </div>

          <!-- Mobile Tools Section -->
          <div class="px-3">
            <button
              class="text-gray-500 hover:text-gray-900 w-full text-left py-2 text-base font-medium flex items-center justify-between"
              @click="toggleMobileToolsDropdown"
            >
              Tools
              <ChevronDownIcon
                class="w-4 h-4 transition-transform"
                :class="{ 'rotate-180': isMobileToolsDropdownOpen }"
              />
            </button>

            <div
              v-if="isMobileToolsDropdownOpen"
              class="mt-2 space-y-2 pl-4 border-l-2 border-gray-200"
            >
              <button
                class="text-gray-500 hover:text-gray-900 w-full text-left py-2 text-sm flex items-center gap-2"
                @click="handleClearCache"
              >
                <WrenchScrewdriverIcon class="w-4 h-4 text-red-600" />
                Clear cache
              </button>
            </div>
          </div>

          <!-- Mobile Logout -->
          <button
            v-if="authStore.isAuthenticated"
            class="text-gray-500 hover:text-gray-900 text-left px-3 py-2 rounded-md text-base font-medium flex items-center gap-2"
            @click="handleLogout"
          >
            <ArrowRightOnRectangleIcon class="w-5 h-5" />
            Logout
          </button>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
  import { ref } from 'vue'
  import { RouterLink, useRouter } from 'vue-router'
  import { useAuthStore } from '@/stores/auth'
  import { clearCacheAndReload } from '@/utils/storeUtils'
  import {
    ChevronDownIcon,
    Bars3Icon,
    XMarkIcon,
    HomeIcon,
    LanguageIcon,
    GlobeAltIcon,
    CogIcon,
    FolderIcon,
    ArrowRightOnRectangleIcon,
    WrenchScrewdriverIcon,
    ArchiveBoxIcon,
    UserGroupIcon,
    RectangleStackIcon,
    PhotoIcon,
    PresentationChartLineIcon,
  } from '@heroicons/vue/24/outline'

  const router = useRouter()
  const authStore = useAuthStore()
  const appTitle = import.meta.env.VITE_APP_TITLE

  // Desktop dropdown state
  const isDropdownOpen = ref(false)
  const isInventoryDropdownOpen = ref(false)
  const isCollectionsDropdownOpen = ref(false)
  const isToolsDropdownOpen = ref(false)
  let dropdownTimeout: ReturnType<typeof setTimeout> | null = null
  let inventoryDropdownTimeout: ReturnType<typeof setTimeout> | null = null
  let toolsDropdownTimeout: ReturnType<typeof setTimeout> | null = null

  // Mobile menu state
  const isMobileMenuOpen = ref(false)
  const isMobileDropdownOpen = ref(false)
  const isMobileInventoryDropdownOpen = ref(false)
  const isMobileCollectionsDropdownOpen = ref(false)
  const isMobileToolsDropdownOpen = ref(false)

  // Desktop dropdown functions
  const openDropdown = () => {
    if (dropdownTimeout) {
      clearTimeout(dropdownTimeout)
      dropdownTimeout = null
    }
    isDropdownOpen.value = true
  }

  const closeDropdown = () => {
    dropdownTimeout = setTimeout(() => {
      isDropdownOpen.value = false
    }, 150) // Small delay to allow mouse movement between elements
  }

  const keepDropdownOpen = () => {
    if (dropdownTimeout) {
      clearTimeout(dropdownTimeout)
      dropdownTimeout = null
    }
  }

  const toggleDropdown = () => {
    isDropdownOpen.value = !isDropdownOpen.value
  }

  // Inventory dropdown functions
  const openInventoryDropdown = () => {
    if (inventoryDropdownTimeout) {
      clearTimeout(inventoryDropdownTimeout)
      inventoryDropdownTimeout = null
    }
    isInventoryDropdownOpen.value = true
  }

  const closeInventoryDropdown = () => {
    inventoryDropdownTimeout = setTimeout(() => {
      isInventoryDropdownOpen.value = false
    }, 150) // Small delay to allow mouse movement between elements
  }

  const keepInventoryDropdownOpen = () => {
    if (inventoryDropdownTimeout) {
      clearTimeout(inventoryDropdownTimeout)
      inventoryDropdownTimeout = null
    }
  }

  const toggleInventoryDropdown = () => {
    isInventoryDropdownOpen.value = !isInventoryDropdownOpen.value
  }

  // Collections dropdown functions
  let collectionsDropdownTimeout: ReturnType<typeof setTimeout> | null = null

  const openCollectionsDropdown = () => {
    if (collectionsDropdownTimeout) {
      clearTimeout(collectionsDropdownTimeout)
      collectionsDropdownTimeout = null
    }
    isCollectionsDropdownOpen.value = true
  }

  const closeCollectionsDropdown = () => {
    collectionsDropdownTimeout = setTimeout(() => {
      isCollectionsDropdownOpen.value = false
    }, 150)
  }

  const keepCollectionsDropdownOpen = () => {
    if (collectionsDropdownTimeout) {
      clearTimeout(collectionsDropdownTimeout)
      collectionsDropdownTimeout = null
    }
  }

  const toggleCollectionsDropdown = () => {
    isCollectionsDropdownOpen.value = !isCollectionsDropdownOpen.value
  }

  // Mobile menu functions
  const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value
    if (!isMobileMenuOpen.value) {
      isMobileDropdownOpen.value = false
      isMobileInventoryDropdownOpen.value = false
      isMobileCollectionsDropdownOpen.value = false
      isMobileToolsDropdownOpen.value = false
    }
  }

  const closeMobileMenu = () => {
    isMobileMenuOpen.value = false
    isMobileDropdownOpen.value = false
    isMobileInventoryDropdownOpen.value = false
    isMobileCollectionsDropdownOpen.value = false
    isMobileToolsDropdownOpen.value = false
  }

  const toggleMobileDropdown = () => {
    isMobileDropdownOpen.value = !isMobileDropdownOpen.value
  }

  const toggleMobileInventoryDropdown = () => {
    isMobileInventoryDropdownOpen.value = !isMobileInventoryDropdownOpen.value
  }

  const toggleMobileCollectionsDropdown = () => {
    isMobileCollectionsDropdownOpen.value = !isMobileCollectionsDropdownOpen.value
  }

  const toggleMobileToolsDropdown = () => {
    isMobileToolsDropdownOpen.value = !isMobileToolsDropdownOpen.value
  }

  // Tools dropdown functions
  const openToolsDropdown = () => {
    if (toolsDropdownTimeout) {
      clearTimeout(toolsDropdownTimeout)
      toolsDropdownTimeout = null
    }
    isToolsDropdownOpen.value = true
  }

  const closeToolsDropdown = () => {
    toolsDropdownTimeout = setTimeout(() => {
      isToolsDropdownOpen.value = false
    }, 150)
  }

  const keepToolsDropdownOpen = () => {
    if (toolsDropdownTimeout) {
      clearTimeout(toolsDropdownTimeout)
      toolsDropdownTimeout = null
    }
  }

  const toggleToolsDropdown = () => {
    isToolsDropdownOpen.value = !isToolsDropdownOpen.value
  }

  // Clear cache handler
  const handleClearCache = async () => {
    closeToolsDropdown()
    closeMobileMenu()
    await clearCacheAndReload()
  }

  const handleLogout = async () => {
    await authStore.logout()
    router.push('/login')
  }
</script>

<style scoped>
  .router-link-active {
    color: rgb(37 99 235);
    font-weight: 600;
  }
</style>
