<script setup>
import { ref, computed, onMounted } from 'vue'

const DATA_PATH = import.meta.env.VITE_DATA_PATH?.replace(/\/$/, '') ?? ''

const items = ref([])
const loading = ref(true)
const error = ref(null)
const selected = ref(null)

onMounted(async () => {
  try {
    const res = await fetch(`${DATA_PATH}/items.json`)
    if (!res.ok) throw new Error(`HTTP ${res.status}`)
    items.value = await res.json()
  } catch (e) {
    error.value = e.message
  } finally {
    loading.value = false
  }
})

function label(item) {
  return item.translations?.en?.name ?? item.internal_name ?? item.id
}

function selectItem(item) {
  selected.value = item
  window.scrollTo(0, 0)
}

function back() {
  selected.value = null
}

// Fields to show in detail view, in order
const DETAIL_FIELDS = [
  ['type', 'Type'],
  ['alternate_name', 'Alternate name'],
  ['description', 'Description'],
  ['dates', 'Dates'],
  ['location', 'Location'],
  ['holder', 'Holder'],
  ['owner', 'Owner'],
  ['initial_owner', 'Initial owner'],
  ['dimensions', 'Dimensions'],
  ['place_of_production', 'Place of production'],
  ['method_for_datation', 'Method for datation'],
  ['method_for_provenance', 'Method for provenance'],
  ['provenance', 'Provenance'],
  ['obtention', 'Obtention'],
  ['bibliography', 'Bibliography'],
]

const detailFields = computed(() => {
  if (!selected.value) return []
  const en = selected.value.translations?.en ?? {}
  return DETAIL_FIELDS
    .map(([key, label]) => ({ label, value: en[key] }))
    .filter(f => f.value)
})
</script>

<template>
  <div class="shell">
    <header>
      <span class="logo">Inventory Viewer</span>
      <span v-if="!loading && !error" class="count">{{ items.length }} items</span>
    </header>

    <main>
      <!-- Loading -->
      <div v-if="loading" class="state">Loading…</div>

      <!-- Error -->
      <div v-else-if="error" class="state error">
        Could not load items.json<br />
        <small>{{ error }}</small><br />
        <small>DATA_PATH: {{ DATA_PATH || '(not set)' }}</small>
      </div>

      <!-- Detail -->
      <template v-else-if="selected">
        <a class="back" href="#" @click.prevent="back">← Back to list</a>

        <div class="detail">
          <div class="detail-type">{{ selected.type }}</div>
          <h1>{{ label(selected) }}</h1>

          <div v-if="selected.images?.length" class="images">
            <figure v-for="(img, i) in selected.images" :key="i">
              <img :src="img.url" :alt="img.captions?.en ?? ''" loading="lazy" />
              <figcaption v-if="img.captions?.en || img.photographer">
                <span v-if="img.captions?.en">{{ img.captions.en }}</span>
                <span v-if="img.photographer" class="credit">© {{ img.photographer }}</span>
              </figcaption>
            </figure>
          </div>

          <dl v-if="detailFields.length">
            <template v-for="f in detailFields" :key="f.label">
              <dt>{{ f.label }}</dt>
              <dd>{{ f.value }}</dd>
            </template>
          </dl>

          <div class="meta">
            <span v-if="selected.country_id">Country: {{ selected.country_id }}</span>
            <span v-if="selected.start_date">
              Period: {{ selected.start_date }}{{ selected.end_date ? ' – ' + selected.end_date : '' }}
            </span>
            <span v-if="selected.mwnf_reference">Ref: {{ selected.mwnf_reference }}</span>
          </div>
        </div>
      </template>

      <!-- List -->
      <template v-else>
        <ul class="list">
          <li
            v-for="item in items"
            :key="item.id"
            class="list-item"
            @click="selectItem(item)"
          >
            <div class="item-thumb" v-if="item.images?.length">
              <img :src="item.images[0].url" :alt="label(item)" loading="lazy" />
            </div>
            <div class="item-thumb placeholder" v-else></div>
            <div class="item-info">
              <span class="item-name">{{ label(item) }}</span>
              <span class="item-type">{{ item.type }}</span>
            </div>
          </li>
        </ul>
      </template>
    </main>
  </div>
</template>

<style scoped>
.shell { min-height: 100vh; display: flex; flex-direction: column; }

header {
  position: sticky; top: 0; z-index: 10;
  display: flex; align-items: center; gap: 1rem;
  padding: .75rem 1.5rem;
  background: #1a1a2e; color: #fff;
}
.logo { font-weight: 700; font-size: 1rem; letter-spacing: .02em; }
.count { font-size: .8rem; opacity: .6; }

main { flex: 1; max-width: 900px; width: 100%; margin: 0 auto; padding: 1.5rem; }

.state { padding: 3rem; text-align: center; color: #666; }
.state.error { color: #c0392b; }

/* List */
.list { list-style: none; display: flex; flex-direction: column; gap: .5rem; }

.list-item {
  display: flex; align-items: center; gap: 1rem;
  padding: .75rem 1rem;
  background: #fff; border-radius: 8px;
  cursor: pointer; transition: box-shadow .15s;
}
.list-item:hover { box-shadow: 0 2px 12px rgba(0,0,0,.12); }

.item-thumb { width: 56px; height: 56px; flex-shrink: 0; border-radius: 4px; overflow: hidden; background: #eee; }
.item-thumb img { width: 100%; height: 100%; object-fit: cover; }
.item-thumb.placeholder { background: #e0e0e0; }

.item-info { display: flex; flex-direction: column; gap: .2rem; min-width: 0; }
.item-name { font-weight: 600; font-size: .95rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.item-type { font-size: .75rem; color: #888; text-transform: uppercase; letter-spacing: .05em; }

/* Detail */
.back {
  display: inline-block; margin-bottom: 1.25rem;
  color: #1a1a2e; font-size: .9rem; text-decoration: none;
}
.back:hover { text-decoration: underline; }

.detail { background: #fff; border-radius: 8px; padding: 1.5rem; }
.detail-type { font-size: .75rem; text-transform: uppercase; letter-spacing: .08em; color: #888; margin-bottom: .5rem; }
.detail h1 { font-size: 1.5rem; margin-bottom: 1.25rem; }

.images {
  display: flex; gap: 1rem; flex-wrap: wrap;
  margin-bottom: 1.5rem;
}
.images figure { width: 180px; }
.images img { width: 100%; height: 130px; object-fit: cover; border-radius: 4px; }
.images figcaption { font-size: .75rem; color: #666; margin-top: .3rem; }
.images .credit { display: block; }

dl { display: grid; grid-template-columns: max-content 1fr; gap: .4rem 1rem; margin-bottom: 1.25rem; }
dt { font-weight: 600; font-size: .85rem; color: #555; white-space: nowrap; }
dd { font-size: .9rem; line-height: 1.5; }

.meta { display: flex; flex-wrap: wrap; gap: .5rem 1.5rem; font-size: .8rem; color: #888; border-top: 1px solid #eee; padding-top: 1rem; }
</style>
