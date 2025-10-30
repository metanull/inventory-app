<template>
  <div class="mt-8 space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <h3 class="text-lg font-medium leading-6 text-gray-900">Item Translations</h3>
      <button
        type="button"
        :class="[
          'inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2',
          colorClasses.button,
          colorClasses.ring,
        ]"
        @click="handleAddTranslation"
      >
        <PlusIcon class="h-5 w-5 mr-2" />
        Add Translation
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
    </div>

    <!-- Empty State -->
    <div
      v-else-if="translations.length === 0"
      class="text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300"
    >
      <LanguageIcon class="mx-auto h-12 w-12 text-gray-400" />
      <h3 class="mt-2 text-sm font-medium text-gray-900">No translations</h3>
      <p class="mt-1 text-sm text-gray-500">Get started by adding a translation for this item.</p>
      <div class="mt-6">
        <button
          type="button"
          :class="[
            'inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2',
            colorClasses.button,
            colorClasses.ring,
          ]"
          @click="handleAddTranslation"
        >
          <PlusIcon class="h-5 w-5 mr-2" />
          Add Translation
        </button>
      </div>
    </div>

    <!-- Translations List -->
    <div v-else class="space-y-4">
      <div
        v-for="translation in translations"
        :key="translation.id"
        class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow"
      >
        <div class="p-4">
          <!-- Header with Language and Context -->
          <div class="flex items-start justify-between mb-3">
            <div class="flex items-center space-x-2">
              <LanguageIcon :class="['h-5 w-5', colorClasses.icon]" />
              <div>
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                >
                  {{ getLanguageName(translation.language_id) }}
                </span>
                <span
                  v-if="translation.context_id"
                  class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
                >
                  {{ getContextName(translation.context_id) }}
                </span>
              </div>
            </div>
            <div class="flex space-x-2">
              <button
                type="button"
                class="p-1.5 rounded hover:bg-gray-100 text-gray-600 hover:text-gray-900"
                title="View/Edit"
                @click="handleViewTranslation(translation.id)"
              >
                <PencilIcon class="h-5 w-5" />
              </button>
              <button
                type="button"
                class="p-1.5 rounded hover:bg-red-100 text-red-600 hover:text-red-700"
                title="Delete"
                @click="handleDelete(translation)"
              >
                <TrashIcon class="h-5 w-5" />
              </button>
            </div>
          </div>

          <!-- Translation Name -->
          <div class="mb-2">
            <h4 class="text-base font-semibold text-gray-900">{{ translation.name }}</h4>
            <p v-if="translation.alternate_name" class="text-sm text-gray-600">
              {{ translation.alternate_name }}
            </p>
          </div>

          <!-- Description Preview -->
          <div v-if="translation.description" class="text-sm text-gray-700 line-clamp-2 mb-3">
            {{ translation.description }}
          </div>

          <!-- Metadata -->
          <div class="flex items-center text-xs text-gray-500 space-x-4">
            <span v-if="translation.type">Type: {{ translation.type }}</span>
            <span v-if="translation.dates">Dates: {{ translation.dates }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { computed, onMounted, watch } from 'vue'
  import { useRouter } from 'vue-router'
  import { PlusIcon, LanguageIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline'
  import { useItemTranslationStore } from '@/stores/itemTranslation'
  import { useLanguageStore } from '@/stores/language'
  import { useContextStore } from '@/stores/context'
  import { useLoadingOverlayStore } from '@/stores/loadingOverlay'
  import { useErrorDisplayStore } from '@/stores/errorDisplay'
  import { useDeleteConfirmationStore } from '@/stores/deleteConfirmation'
  import { useColors, type ColorName } from '@/composables/useColors'
  import type { ItemTranslationResource } from '@metanull/inventory-app-api-client'

  interface Props {
    itemId: string
    color?: ColorName
  }

  const props = withDefaults(defineProps<Props>(), {
    color: 'blue',
  })

  const router = useRouter()
  const translationStore = useItemTranslationStore()
  const languageStore = useLanguageStore()
  const contextStore = useContextStore()
  const loadingStore = useLoadingOverlayStore()
  const errorStore = useErrorDisplayStore()
  const deleteStore = useDeleteConfirmationStore()

  const colorClasses = useColors(computed(() => props.color))

  const translations = computed(() => translationStore.itemTranslations)
  const loading = computed(() => translationStore.loading)

  // Load translations for this item
  const loadTranslations = async () => {
    if (props.itemId) {
      try {
        await translationStore.fetchItemTranslations({
          filters: { item_id: props.itemId },
        })
      } catch (error) {
        console.error('[ItemTranslationManager] Failed to load translations:', error)
        errorStore.addMessage('error', 'Failed to load translations')
      }
    }
  }

  // Get language name from ID
  const getLanguageName = (languageId: string): string => {
    const language = languageStore.languages.find(l => l.id === languageId)
    return language?.internal_name || languageId
  }

  // Get context name from ID
  const getContextName = (contextId: string): string => {
    const context = contextStore.contexts.find(c => c.id === contextId)
    return context?.internal_name || contextId
  }

  // Handle add translation
  const handleAddTranslation = () => {
    router.push({
      name: 'item-translation-new',
      query: { item_id: props.itemId },
    })
  }

  // Handle view/edit translation
  const handleViewTranslation = (translationId: string) => {
    router.push({
      name: 'item-translation-detail',
      params: { id: translationId },
    })
  }

  // Delete translation
  const handleDelete = async (translation: ItemTranslationResource) => {
    const translationName = translation.name || translation.alternate_name || 'this translation'
    const result = await deleteStore.trigger(
      'Delete Translation',
      `Are you sure you want to permanently delete "${translationName}"? This action cannot be undone.`
    )

    if (result === 'delete') {
      try {
        loadingStore.show('Deleting translation...')
        await translationStore.deleteItemTranslation(translation.id)
        errorStore.addMessage('info', 'Translation deleted successfully')
        // Reload translations
        await loadTranslations()
      } catch {
        errorStore.addMessage('error', 'Failed to delete translation')
      } finally {
        loadingStore.hide()
      }
    }
  }

  // Load translations on mount
  onMounted(async () => {
    // Load languages and contexts for name display
    if (languageStore.languages.length === 0) {
      await languageStore.fetchLanguages({ page: 1, perPage: 100 })
    }
    if (contextStore.contexts.length === 0) {
      await contextStore.fetchContexts({ page: 1, perPage: 100 })
    }
    // Load translations
    await loadTranslations()
  })

  // Reload when itemId changes
  watch(
    () => props.itemId,
    () => {
      loadTranslations()
    }
  )
</script>
