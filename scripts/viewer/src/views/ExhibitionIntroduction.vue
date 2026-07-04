<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useInventoryData } from '../composables/useInventoryData.js'

const route = useRoute()
const router = useRouter()
const {
  itemById, dynastyLabel, partnerLabel,
  availableLangs, defaultLang,
  translationsCache, loadLangTranslations,
  exhibitionById,
  md, mdInline,
} = useInventoryData()

const exhibition = computed(() => exhibitionById(decodeURIComponent(route.params.exhibitionId)) ?? null)

// ── Language selector (exhibition text + item captions loaded on demand) ──

const activeLang = ref(defaultLang)
const collectionLangCache = ref({})

async function loadCollectionLangTranslations(lang) {
  if (collectionLangCache.value[lang]) return
  try {
    const m = await import(`@inventory-data/translations/collections.${lang}.json`)
    collectionLangCache.value = { ...collectionLangCache.value, [lang]: m.default }
  } catch {
    collectionLangCache.value = { ...collectionLangCache.value, [lang]: {} }
  }
}

onMounted(() => {
  loadCollectionLangTranslations(activeLang.value)
  loadLangTranslations(activeLang.value)
})
watch(activeLang, lang => {
  loadCollectionLangTranslations(lang)
  loadLangTranslations(lang)
})

const text = computed(() => {
  const e = exhibition.value
  if (!e) return {}
  return collectionLangCache.value[activeLang.value]?.[e.id] ?? {}
})

// ── Introduction items: attached directly to the exhibition collection ────

const introItems = computed(() => {
  const e = exhibition.value
  if (!e) return []
  return (e.items ?? [])
    .map(entry => ({ entry, item: itemById.value[entry.id] }))
    .filter(({ item }) => item)
    .sort((a, b) => (a.entry.display_order ?? 9999) - (b.entry.display_order ?? 9999))
    .map(({ entry, item }) => {
      const caption = entry.caption?.[activeLang.value] ?? {}
      const t = translationsCache.value[activeLang.value]?.[item.id] ?? {}
      return {
        item,
        image: item.images?.[0]?.url ?? null,
        name: caption.name ?? t.name ?? item.internal_name ?? item.id,
        date: caption.date ?? t.dates ?? '',
        dynasty: caption.dynasty ?? (item.dynasty_ids?.[0] ? dynastyLabel(item.dynasty_ids[0]) : ''),
        location: caption.location ?? t.location ?? '',
        museum: caption.museum ?? (item.partner_id ? partnerLabel(item.partner_id) : ''),
      }
    })
})

function back() {
  if (window.history.length > 2) {
    router.back()
  } else if (exhibition.value) {
    router.push(`/exhibitions/${exhibition.value.id}`)
  } else {
    router.push('/exhibitions')
  }
}
</script>

<template>
  <div v-if="!exhibition" class="content-box not-found">
    <p>Exhibition not found.</p>
    <router-link to="/exhibitions">← Return to Exhibitions</router-link>
  </div>

  <div v-else class="intro-wrap">
    <a class="back-link" href="#" @click.prevent="back">← Back to {{ text.title ?? exhibition.internal_name }}</a>

    <div v-if="availableLangs.length > 1" class="lang-selector">
      <label class="lang-label">Text language:</label>
      <select v-model="activeLang" class="lang-select">
        <option v-for="lang in availableLangs" :key="lang" :value="lang">{{ lang.toUpperCase() }}</option>
      </select>
    </div>

    <div class="content-box">
      <h1 class="intro-title" v-html="mdInline(text.extra?.intro_header ?? 'Introduction')" />

      <div class="intro-grid">
        <div class="intro-text-col">
          <div v-if="text.extra?.intro_text" class="prose" v-html="md(text.extra.intro_text)" />
        </div>

        <div v-if="introItems.length" class="intro-items-col">
          <div v-for="i in introItems" :key="i.item.id" class="intro-item-card" @click="$router.push(`/item/${encodeURIComponent(i.item.id)}`)">
            <div class="intro-item-img-wrap">
              <img v-if="i.image" :src="i.image" :alt="i.name" class="intro-item-img" />
              <div v-else class="intro-item-img-placeholder" />
            </div>
            <h3 class="intro-item-name" v-html="mdInline(i.name)" />
            <p v-if="i.dynasty" class="intro-item-meta">{{ i.dynasty }}</p>
            <p v-if="i.date" class="intro-item-meta">{{ i.date }}</p>
            <p v-if="i.location" class="intro-item-meta">{{ i.location }}</p>
            <p v-if="i.museum" class="intro-item-meta">{{ i.museum }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.not-found { color: var(--muted); font-family: 'Roboto', sans-serif; font-size: 13px; }

.intro-wrap { display: flex; flex-direction: column; gap: 10px; }

.lang-selector {
  display: flex;
  align-items: center;
  gap: 8px;
  font-family: 'Roboto', sans-serif;
  font-size: 12px;
  color: var(--muted);
  justify-content: flex-end;
}
.lang-select { font-size: 12px; padding: 3px 6px; }

.intro-title {
  font-size: 20px;
  font-weight: 400;
  color: var(--heading);
  margin-bottom: 16px;
  line-height: 1.3;
  font-family: 'Roboto', sans-serif;
}

.intro-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 24px;
}
@media (max-width: 700px) { .intro-grid { grid-template-columns: 1fr; } }

.intro-text-col { min-width: 0; }
.prose { font-size: 14px; line-height: 1.7; color: var(--text); font-family: 'Roboto', sans-serif; }
.prose :deep(p) { margin: 0 0 .75em; }
.prose :deep(p:last-child) { margin-bottom: 0; }

.intro-items-col { display: flex; flex-direction: column; gap: 14px; }
.intro-item-card {
  border: 1px solid var(--border);
  background: var(--section-bg);
  padding: 12px;
  cursor: pointer;
}
.intro-item-card:hover .intro-item-name { color: var(--nav-active); }

.intro-item-img-wrap {
  width: 100%;
  aspect-ratio: 4 / 3;
  overflow: hidden;
  border: 1px solid var(--border);
  background: #f0ebdc;
  margin-bottom: 8px;
}
.intro-item-img { width: 100%; height: 100%; object-fit: cover; display: block; }
.intro-item-img-placeholder { width: 100%; height: 100%; }

.intro-item-name {
  font-size: 14px;
  font-weight: 500;
  color: var(--heading);
  margin-bottom: 4px;
  line-height: 1.3;
  font-family: 'Roboto', sans-serif;
}
.intro-item-meta {
  font-size: 12px;
  color: var(--muted);
  font-family: 'Roboto', sans-serif;
  margin-bottom: 2px;
}
</style>
