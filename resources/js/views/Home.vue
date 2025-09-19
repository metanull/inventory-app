<template>
  <div>
    <!-- Horizontal Scrolling Groups Interface -->
    <div class="relative">
      <!-- Group Navigation -->
      <div class="hidden md:flex items-center justify-center mb-6 px-4">
        <div class="flex space-x-1 bg-gray-100 rounded-lg p-1 max-w-full">
          <!-- Desktop: Show all tabs -->
          <div class="flex space-x-1">
            <button
              v-for="(group, index) in groups"
              :key="group.id"
              :class="[
                'px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 whitespace-nowrap',
                currentGroup === index
                  ? 'bg-white text-gray-900 shadow-sm scale-105'
                  : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50',
              ]"
              @click="currentGroup = index"
            >
              {{ group.title }}
            </button>
          </div>
        </div>
      </div>

      <!-- Group Content with Smooth Transition -->
      <div class="relative overflow-hidden">
        <div
          class="flex transition-transform duration-300 ease-in-out"
          :style="{ transform: `translateX(-${currentGroup * 100}%)` }"
        >
          <div v-for="group in groups" :key="group.id" class="w-full flex-shrink-0">
            <div class="mb-6">
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <component
                  :is="card.component"
                  v-for="card in group.cards"
                  :key="card.title"
                  v-bind="card.props"
                >
                  <template #icon>
                    <component :is="card.icon" />
                  </template>
                </component>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Navigation Dots -->
      <div class="flex justify-center mt-6 space-x-2">
        <button
          v-for="(group, index) in groups"
          :key="index"
          :class="[
            'w-3 h-3 rounded-full transition-all duration-200 hover:scale-125',
            currentGroup === index ? 'bg-indigo-600 shadow-md' : 'bg-gray-300 hover:bg-gray-400',
          ]"
          :aria-label="`Go to ${group.title} section`"
          @click="currentGroup = index"
        ></button>
      </div>

      <!-- Navigation Arrows (Desktop only) -->
      <button
        v-if="currentGroup > 0"
        class="absolute left-2 top-1/2 -translate-y-1/2 hidden md:flex items-center justify-center w-12 h-12 bg-white rounded-full shadow-lg hover:shadow-xl hover:scale-110 hover:bg-gray-50 transition-all duration-200 z-10 group"
        :aria-label="`Go to previous section: ${groups[currentGroup - 1]?.title}`"
        @click="currentGroup--"
      >
        <svg
          class="w-6 h-6 text-gray-600 group-hover:text-gray-800 transition-colors duration-200"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M15 19l-7-7 7-7"
          />
        </svg>
      </button>

      <button
        v-if="currentGroup < groups.length - 1"
        class="absolute right-2 top-1/2 -translate-y-1/2 hidden md:flex items-center justify-center w-12 h-12 bg-white rounded-full shadow-lg hover:shadow-xl hover:scale-110 hover:bg-gray-50 transition-all duration-200 z-10 group"
        :aria-label="`Go to next section: ${groups[currentGroup + 1]?.title}`"
        @click="currentGroup++"
      >
        <svg
          class="w-6 h-6 text-gray-600 group-hover:text-gray-800 transition-colors duration-200"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { ref, onMounted, onUnmounted, type Component } from 'vue'
  import NavigationCard from '@/components/format/card/NavigationCard.vue'
  import InformationCard from '@/components/format/card/InformationCard.vue'
  import {
    LanguageIcon,
    GlobeAltIcon as CountryIcon,
    CogIcon as ContextIcon,
    FolderIcon as ProjectIcon,
    ArchiveBoxIcon as ItemIcon,
    UserGroupIcon as PartnerIcon,
    RectangleStackIcon as CollectionIcon,
    PhotoIcon as GalleryIcon,
    PresentationChartLineIcon as ExhibitionIcon,
    CloudArrowUpIcon,
  } from '@heroicons/vue/24/outline'

  // Current group index
  const currentGroup = ref(0)

  // Touch handling for mobile swipe gestures
  let touchStartX = 0
  let touchEndX = 0

  const handleTouchStart = (e: Event) => {
    const touches = (e as any).changedTouches
    if (touches && touches[0]) {
      touchStartX = touches[0].screenX
    }
  }

  const handleTouchEnd = (e: Event) => {
    const touches = (e as any).changedTouches
    if (touches && touches[0]) {
      touchEndX = touches[0].screenX
      handleSwipeGesture()
    }
  }

  const handleSwipeGesture = () => {
    const swipeThreshold = 50
    const difference = touchStartX - touchEndX

    if (Math.abs(difference) > swipeThreshold) {
      if (difference > 0 && currentGroup.value < groups.length - 1) {
        // Swipe left - next group
        currentGroup.value++
      } else if (difference < 0 && currentGroup.value > 0) {
        // Swipe right - previous group
        currentGroup.value--
      }
    }
  }

  // Keyboard navigation
  const handleKeyDown = (e: Event) => {
    const key = (e as any).key
    if (key === 'ArrowLeft' && currentGroup.value > 0) {
      currentGroup.value--
      e.preventDefault()
    } else if (key === 'ArrowRight' && currentGroup.value < groups.length - 1) {
      currentGroup.value++
      e.preventDefault()
    }
  }

  // Lifecycle hooks
  onMounted(() => {
    document.addEventListener('keydown', handleKeyDown)
    document.addEventListener('touchstart', handleTouchStart, { passive: true })
    document.addEventListener('touchend', handleTouchEnd, { passive: true })
  })

  onUnmounted(() => {
    document.removeEventListener('keydown', handleKeyDown)
    document.removeEventListener('touchstart', handleTouchStart)
    document.removeEventListener('touchend', handleTouchEnd)
  })

  // Group data structure
  interface CardData {
    title: string
    component: Component
    props: Record<string, any>
    icon: Component
  }

  interface GroupData {
    id: string
    title: string
    cards: CardData[]
  }

  // Define all groups
  const groups: GroupData[] = [
    {
      id: 'inventory',
      title: 'Inventory',
      cards: [
        {
          title: 'Items',
          component: NavigationCard,
          props: {
            title: 'Items',
            description: 'Manage inventory items including objects and monuments',
            mainColor: 'teal',
            buttonText: 'Manage Items',
            buttonRoute: '/items',
          },
          icon: ItemIcon,
        },
        {
          title: 'Partners',
          component: NavigationCard,
          props: {
            title: 'Partners',
            description: 'Manage partners including museums, institutions, and individuals',
            mainColor: 'yellow',
            buttonText: 'Manage Partners',
            buttonRoute: '/partners',
          },
          icon: PartnerIcon,
        },
      ],
    },
    {
      id: 'collections',
      title: 'Collections',
      cards: [
        {
          title: 'Collections',
          component: NavigationCard,
          props: {
            title: 'Collections',
            description: 'Manage collections of museum items with context and translation support',
            mainColor: 'indigo',
            buttonText: 'Manage Collections',
            buttonRoute: '/collections',
          },
          icon: CollectionIcon,
        },
        {
          title: 'Galleries',
          component: InformationCard,
          props: {
            title: 'Galleries',
            description:
              'Mixed content galleries containing Items and Details will be available in future updates.',
            mainColor: 'gray',
            pillText: 'Coming Soon',
          },
          icon: GalleryIcon,
        },
        {
          title: 'Exhibitions',
          component: InformationCard,
          props: {
            title: 'Exhibitions',
            description:
              'Theme-based exhibition collections with hierarchical organization will be available in future updates.',
            mainColor: 'gray',
            pillText: 'Coming Soon',
          },
          icon: ExhibitionIcon,
        },
      ],
    },
    {
      id: 'images',
      title: 'Image Management',
      cards: [
        {
          title: 'Upload Images',
          component: NavigationCard,
          props: {
            title: 'Upload Images',
            description: 'Upload images for validation and processing into the collection',
            mainColor: 'indigo',
            buttonText: 'Upload Images',
            buttonRoute: '/images/upload',
          },
          icon: CloudArrowUpIcon,
        },
        {
          title: 'Available Images',
          component: NavigationCard,
          props: {
            title: 'Available Images',
            description: 'Browse and manage validated images in your collection',
            mainColor: 'pink',
            buttonText: 'View Images',
            buttonRoute: '/images',
          },
          icon: GalleryIcon,
        },
      ],
    },
    {
      id: 'reference',
      title: 'Reference Data',
      cards: [
        {
          title: 'Languages',
          component: NavigationCard,
          props: {
            title: 'Languages',
            description: 'Manage system languages and localization settings',
            mainColor: 'purple',
            buttonText: 'Manage Languages',
            buttonRoute: '/languages',
          },
          icon: LanguageIcon,
        },
        {
          title: 'Countries',
          component: NavigationCard,
          props: {
            title: 'Countries',
            description: 'Manage countries and geographic regions',
            mainColor: 'blue',
            buttonText: 'Manage Countries',
            buttonRoute: '/countries',
          },
          icon: CountryIcon,
        },
        {
          title: 'Contexts',
          component: NavigationCard,
          props: {
            title: 'Contexts',
            description: 'Manage system contexts and operational environments',
            mainColor: 'green',
            buttonText: 'Manage Contexts',
            buttonRoute: '/contexts',
          },
          icon: ContextIcon,
        },
        {
          title: 'Projects',
          component: NavigationCard,
          props: {
            title: 'Projects',
            description: 'Organize items and documentation by project or exhibition',
            mainColor: 'purple',
            buttonText: 'Manage Projects',
            buttonRoute: '/projects',
          },
          icon: ProjectIcon,
        },
      ],
    },
  ]
</script>
