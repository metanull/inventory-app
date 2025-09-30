<template>
  <header class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center py-6">
        <div class="flex items-center">
          <RouterLink to="/" :class="['text-2xl font-bold', getThemeClass('appTitleColor')]">
            {{ appTitle }}
          </RouterLink>
        </div>

        <nav class="hidden md:flex space-x-8 items-center">
          <RouterLink
            to="/"
            :class="[
              getThemeClass('navLinkColor'),
              'px-3 py-2 rounded-md text-sm font-medium flex items-center gap-2',
            ]"
          >
            <HomeIcon class="w-4 h-4" />
            Dashboard
          </RouterLink>

          <!-- Inventory Dropdown -->
          <div class="relative" @mouseleave="closeInventoryDropdown">
            <button
              :class="[
                getThemeClass('navLinkColor'),
                'px-3 py-2 rounded-md text-sm font-medium flex items-center gap-1',
              ]"
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
              :class="[
                'absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border',
                getThemeClass('dropdownBorder'),
              ]"
              @mouseenter="keepInventoryDropdownOpen"
              @mouseleave="closeInventoryDropdown"
            >
              <RouterLink
                to="/items"
                :class="[
                  getThemeClass('dropdownItemColor'),
                  'px-4 py-2 text-sm flex items-center gap-2',
                ]"
                @click="closeInventoryDropdown"
              >
                <ArchiveBoxIcon class="w-4 h-4" :class="itemsColors.icon" />
                Items
              </RouterLink>
              <RouterLink
                to="/partners"
                :class="[
                  getThemeClass('dropdownItemColor'),
                  'px-4 py-2 text-sm flex items-center gap-2',
                ]"
                @click="closeInventoryDropdown"
              >
                <UserGroupIcon class="w-4 h-4" :class="partnersColors.icon" />
                Partners
              </RouterLink>
              <RouterLink
                to="/collections"
                :class="[
                  getThemeClass('dropdownItemColor'),
                  'px-4 py-2 text-sm flex items-center gap-2',
                ]"
                @click="closeInventoryDropdown"
              >
                <RectangleStackIcon class="w-4 h-4" :class="collectionsColors.icon" />
                Collections
              </RouterLink>
            </div>
          </div>



          <!-- Image Management Dropdown -->
          <div class="relative" @mouseleave="closeImagesDropdown">
            <button
              :class="[
                getThemeClass('navLinkColor'),
                'px-3 py-2 rounded-md text-sm font-medium flex items-center gap-1',
              ]"
              @mouseenter="openImagesDropdown"
              @click="toggleImagesDropdown"
            >
              Image Management
              <ChevronDownIcon
                class="w-4 h-4 transition-transform"
                :class="{ 'rotate-180': isImagesDropdownOpen }"
              />
            </button>

            <div
              v-if="isImagesDropdownOpen"
              :class="[
                'absolute left-0 mt-2 w-48 rounded-md shadow-lg py-1 z-50 border',
                getThemeClass('dropdownBorder'),
                'bg-white',
              ]"
              @mouseenter="keepImagesDropdownOpen"
              @mouseleave="closeImagesDropdown"
            >
              <RouterLink
                to="/images"
                :class="[
                  getThemeClass('dropdownItemColor'),
                  'px-4 py-2 text-sm flex items-center gap-2',
                ]"
                @click="closeImagesDropdown"
              >
                <PhotoIcon class="w-4 h-4" :class="imagesColors.icon" />
                Available Images
              </RouterLink>
              <RouterLink
                to="/images/upload"
                :class="[
                  getThemeClass('dropdownItemColor'),
                  'px-4 py-2 text-sm flex items-center gap-2',
                ]"
                @click="closeImagesDropdown"
              >
                <ArrowUpTrayIcon class="w-4 h-4" :class="imagesColors.icon" />
                Upload Images
              </RouterLink>
            </div>
          </div>

          <!-- Reference Data Dropdown -->
          <div class="relative" @mouseleave="closeDropdown">
            <button
              :class="[
                getThemeClass('navLinkColor'),
                'px-3 py-2 rounded-md text-sm font-medium flex items-center gap-1',
              ]"
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
              :class="[
                'absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border',
                getThemeClass('dropdownBorder'),
              ]"
              @mouseenter="keepDropdownOpen"
              @mouseleave="closeDropdown"
            >
              <RouterLink
                to="/languages"
                :class="[
                  getThemeClass('dropdownItemColor'),
                  'px-4 py-2 text-sm flex items-center gap-2',
                ]"
                @click="closeDropdown"
              >
                <LanguageIcon class="w-4 h-4" :class="languagesColors.icon" />
                Languages
              </RouterLink>
              <RouterLink
                to="/countries"
                :class="[
                  getThemeClass('dropdownItemColor'),
                  'px-4 py-2 text-sm flex items-center gap-2',
                ]"
                @click="closeDropdown"
              >
                <GlobeAltIcon class="w-4 h-4" :class="countriesColors.icon" />
                Countries
              </RouterLink>
              <RouterLink
                to="/contexts"
                :class="[
                  getThemeClass('dropdownItemColor'),
                  'px-4 py-2 text-sm flex items-center gap-2',
                ]"
                @click="closeDropdown"
              >
                <CogIcon class="w-4 h-4" :class="contextsColors.icon" />
                Contexts
              </RouterLink>
              <RouterLink
                to="/projects"
                :class="[
                  getThemeClass('dropdownItemColor'),
                  'px-4 py-2 text-sm flex items-center gap-2',
                ]"
                @click="closeDropdown"
              >
                <FolderIcon class="w-4 h-4" :class="projectsColors.icon" />
                Projects
              </RouterLink>
            </div>
          </div>

          <!-- Tools Dropdown -->
          <div class="relative" @mouseleave="closeToolsDropdown">
            <button
              :class="[
                getThemeClass('navLinkColor'),
                'px-3 py-2 rounded-md text-sm font-medium flex items-center gap-1',
              ]"
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
              :class="[
                'absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border',
                getThemeClass('dropdownBorder'),
              ]"
              @mouseenter="keepToolsDropdownOpen"
              @mouseleave="closeToolsDropdown"
            >
              <button
                :class="[
                  getThemeClass('dropdownItemColor'),
                  'w-full text-left px-4 py-2 text-sm flex items-center gap-2',
                ]"
                @click="handleClearCache"
              >
                <WrenchScrewdriverIcon class="w-4 h-4" :class="toolsColors.icon" />
                Clear cache
              </button>
            </div>
          </div>
        </nav>

        <!-- Desktop Actions -->
        <div class="hidden md:flex items-center space-x-4">
          <button
            v-if="authStore.isAuthenticated"
            :class="[
              getThemeClass('navLinkColor'),
              'px-3 py-2 rounded-md text-sm font-medium flex items-center gap-2',
            ]"
            @click="handleLogout"
          >
            <ArrowRightOnRectangleIcon class="w-4 h-4" />
            Logout
          </button>
        </div>

        <!-- Mobile menu button -->
        <div class="md:hidden">
          <button
            :class="[getThemeClass('navLinkColor'), 'focus:outline-none p-2']"
            @click="toggleMobileMenu"
          >
            <Bars3Icon v-if="!isMobileMenuOpen" class="w-6 h-6" />
            <XMarkIcon v-else class="w-6 h-6" />
          </button>
        </div>
      </div>

      <!-- Mobile Navigation -->
      <div
        v-if="isMobileMenuOpen"
        :class="['md:hidden border-t py-4', getThemeClass('mobileBorder')]"
      >
        <div class="flex flex-col space-y-2">
          <RouterLink
            to="/"
            :class="[
              getThemeClass('mobileNavLinkColor'),
              'px-3 py-2 rounded-md text-base font-medium flex items-center gap-2',
            ]"
            @click="closeMobileMenu"
          >
            <HomeIcon class="w-5 h-5" />
            Dashboard
          </RouterLink>

          <!-- Mobile Inventory Section -->
          <div class="px-3">
            <button
              :class="[
                getThemeClass('mobileNavLinkColor'),
                'w-full text-left py-2 text-base font-medium flex items-center justify-between',
              ]"
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
              :class="['mt-2 space-y-2 pl-4 border-l-2', getThemeClass('mobileBorder')]"
            >
              <RouterLink
                to="/items"
                :class="[
                  getThemeClass('mobileNavLinkColor'),
                  'py-2 text-sm flex items-center gap-2',
                ]"
                @click="closeMobileMenu"
              >
                <ArchiveBoxIcon class="w-4 h-4" :class="itemsColors.icon" />
                Items
              </RouterLink>
              <RouterLink
                to="/partners"
                :class="[
                  getThemeClass('mobileNavLinkColor'),
                  'py-2 text-sm flex items-center gap-2',
                ]"
                @click="closeMobileMenu"
              >
                <UserGroupIcon class="w-4 h-4" :class="partnersColors.icon" />
                Partners
              </RouterLink>
              <RouterLink
                to="/collections"
                :class="[
                  getThemeClass('mobileNavLinkColor'),
                  'py-2 text-sm flex items-center gap-2',
                ]"
                @click="closeMobileMenu"
              >
                <RectangleStackIcon class="w-4 h-4" :class="collectionsColors.icon" />
                Collections
              </RouterLink>
            </div>
          </div>



          <!-- Mobile Image Management Section -->
          <div class="px-3">
            <button
              :class="[
                getThemeClass('mobileNavLinkColor'),
                'w-full text-left py-2 text-base font-medium flex items-center justify-between',
              ]"
              @click="toggleMobileImagesDropdown"
            >
              Image Management
              <ChevronDownIcon
                class="w-4 h-4 transition-transform"
                :class="{ 'rotate-180': isMobileImagesDropdownOpen }"
              />
            </button>

            <div
              v-if="isMobileImagesDropdownOpen"
              :class="['mt-2 space-y-2 pl-4 border-l-2', getThemeClass('mobileBorder')]"
            >
              <RouterLink
                to="/images"
                :class="[
                  getThemeClass('mobileNavLinkColor'),
                  'py-2 text-sm flex items-center gap-2',
                ]"
                @click="closeMobileMenu"
              >
                <PhotoIcon class="w-4 h-4" :class="imagesColors.icon" />
                Available Images
              </RouterLink>
              <RouterLink
                to="/images/upload"
                :class="[
                  getThemeClass('mobileNavLinkColor'),
                  'py-2 text-sm flex items-center gap-2',
                ]"
                @click="closeMobileMenu"
              >
                <ArrowUpTrayIcon class="w-4 h-4" :class="imagesColors.icon" />
                Upload Images
              </RouterLink>
            </div>
          </div>

          <!-- Mobile Reference Data Section -->
          <div class="px-3">
            <button
              :class="[
                getThemeClass('mobileNavLinkColor'),
                'w-full text-left py-2 text-base font-medium flex items-center justify-between',
              ]"
              @click="toggleMobileDropdown"
            >
              Reference Data
              <ChevronDownIcon
                class="w-4 h-4 transition-transform"
                :class="{ 'rotate-180': isMobileDropdownOpen }"
              />
            </button>

            <div
              v-if="isMobileDropdownOpen"
              :class="['mt-2 space-y-2 pl-4 border-l-2', getThemeClass('mobileBorder')]"
            >
              <RouterLink
                to="/languages"
                :class="[
                  getThemeClass('mobileNavLinkColor'),
                  'py-2 text-sm flex items-center gap-2',
                ]"
                @click="closeMobileMenu"
              >
                <LanguageIcon class="w-4 h-4" :class="languagesColors.icon" />
                Languages
              </RouterLink>
              <RouterLink
                to="/countries"
                :class="[
                  getThemeClass('mobileNavLinkColor'),
                  'py-2 text-sm flex items-center gap-2',
                ]"
                @click="closeMobileMenu"
              >
                <GlobeAltIcon class="w-4 h-4" :class="countriesColors.icon" />
                Countries
              </RouterLink>
              <RouterLink
                to="/contexts"
                :class="[
                  getThemeClass('mobileNavLinkColor'),
                  'py-2 text-sm flex items-center gap-2',
                ]"
                @click="closeMobileMenu"
              >
                <CogIcon class="w-4 h-4" :class="contextsColors.icon" />
                Contexts
              </RouterLink>
              <RouterLink
                to="/projects"
                :class="[
                  getThemeClass('mobileNavLinkColor'),
                  'py-2 text-sm flex items-center gap-2',
                ]"
                @click="closeMobileMenu"
              >
                <FolderIcon class="w-4 h-4" :class="projectsColors.icon" />
                Projects
              </RouterLink>
            </div>
          </div>

          <!-- Mobile Tools Section -->
          <div class="px-3">
            <button
              :class="[
                getThemeClass('mobileNavLinkColor'),
                'w-full text-left py-2 text-base font-medium flex items-center justify-between',
              ]"
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
              :class="['mt-2 space-y-2 pl-4 border-l-2', getThemeClass('mobileBorder')]"
            >
              <button
                :class="[
                  getThemeClass('mobileNavLinkColor'),
                  'w-full text-left py-2 text-sm flex items-center gap-2',
                ]"
                @click="handleClearCache"
              >
                <WrenchScrewdriverIcon class="w-4 h-4" :class="toolsColors.icon" />
                Clear cache
              </button>
            </div>
          </div>

          <!-- Mobile Logout -->
          <button
            v-if="authStore.isAuthenticated"
            :class="[
              getThemeClass('mobileNavLinkColor'),
              'text-left px-3 py-2 rounded-md text-base font-medium flex items-center gap-2',
            ]"
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
  import { useThemeColors, getThemeClass } from '@/composables/useColors'
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

    ArrowUpTrayIcon,
  } from '@heroicons/vue/24/outline'

  const router = useRouter()
  const authStore = useAuthStore()
  const appTitle = import.meta.env.VITE_APP_TITLE

  // Theme colors for header icons (proof-of-concept)
  const itemsColors = useThemeColors('items')
  const partnersColors = useThemeColors('partners')
  const collectionsColors = useThemeColors('collections')
  const imagesColors = useThemeColors('tools') // Using red for images for now
  const languagesColors = useThemeColors('languages')
  const countriesColors = useThemeColors('countries')
  const contextsColors = useThemeColors('contexts')
  const projectsColors = useThemeColors('projects')
  const toolsColors = useThemeColors('tools')

  // Desktop dropdown state
  const isDropdownOpen = ref(false)
  const isInventoryDropdownOpen = ref(false)

  const isImagesDropdownOpen = ref(false)
  const isToolsDropdownOpen = ref(false)
  let dropdownTimeout: ReturnType<typeof setTimeout> | null = null
  let inventoryDropdownTimeout: ReturnType<typeof setTimeout> | null = null

  let imagesDropdownTimeout: ReturnType<typeof setTimeout> | null = null
  let toolsDropdownTimeout: ReturnType<typeof setTimeout> | null = null

  // Mobile menu state
  const isMobileMenuOpen = ref(false)
  const isMobileDropdownOpen = ref(false)
  const isMobileInventoryDropdownOpen = ref(false)

  const isMobileImagesDropdownOpen = ref(false)
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



  // Images dropdown functions
  const openImagesDropdown = () => {
    if (imagesDropdownTimeout) {
      clearTimeout(imagesDropdownTimeout)
      imagesDropdownTimeout = null
    }
    isImagesDropdownOpen.value = true
  }

  const closeImagesDropdown = () => {
    imagesDropdownTimeout = setTimeout(() => {
      isImagesDropdownOpen.value = false
    }, 150)
  }

  const keepImagesDropdownOpen = () => {
    if (imagesDropdownTimeout) {
      clearTimeout(imagesDropdownTimeout)
      imagesDropdownTimeout = null
    }
  }

  const toggleImagesDropdown = () => {
    isImagesDropdownOpen.value = !isImagesDropdownOpen.value
  }

  // Mobile menu functions
  const toggleMobileMenu = () => {
    isMobileMenuOpen.value = !isMobileMenuOpen.value
    if (!isMobileMenuOpen.value) {
      isMobileDropdownOpen.value = false
      isMobileInventoryDropdownOpen.value = false

      isMobileImagesDropdownOpen.value = false
      isMobileToolsDropdownOpen.value = false
    }
  }

  const closeMobileMenu = () => {
    isMobileMenuOpen.value = false
    isMobileDropdownOpen.value = false
    isMobileInventoryDropdownOpen.value = false

    isMobileToolsDropdownOpen.value = false
  }

  const toggleMobileDropdown = () => {
    isMobileDropdownOpen.value = !isMobileDropdownOpen.value
  }

  const toggleMobileInventoryDropdown = () => {
    isMobileInventoryDropdownOpen.value = !isMobileInventoryDropdownOpen.value
  }



  const toggleMobileImagesDropdown = () => {
    isMobileImagesDropdownOpen.value = !isMobileImagesDropdownOpen.value
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
    router.push({ name: 'login' })
  }
</script>
