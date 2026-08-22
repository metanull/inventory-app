<script setup>
import { computed } from 'vue'
import { useInventoryData } from '../composables/useInventoryData.js'

const { exhibitions, enCollectionTranslations, mdInline } = useInventoryData()

const exhibitionList = computed(() =>
  exhibitions.value.map(e => ({
    ...e,
    title: enCollectionTranslations.value[e.id]?.title ?? e.internal_name,
  }))
)
</script>

<template>
  <div v-if="!exhibitionList.length" class="content-box not-found">
    <p>No exhibitions available.</p>
  </div>

  <div v-else>
    <h1 class="section-heading">Exhibitions</h1>

    <div class="content-box">
      <p class="intro-text">
        Select an exhibition below to explore its themes, monuments, and objects.
      </p>
      <ul class="theme-list">
        <li
          v-for="e in exhibitionList"
          :key="e.id"
          class="theme-row"
          @click="$router.push(`/exhibitions/${encodeURIComponent(e.id)}`)"
        >
          <span class="theme-name" v-html="mdInline(e.title)" />
          <span class="theme-arrow">→</span>
        </li>
      </ul>
    </div>
  </div>
</template>

<style scoped>
.not-found { color: var(--muted); font-family: 'Roboto', sans-serif; font-size: 13px; }

.intro-text {
  font-size: 13px;
  line-height: 1.65;
  color: var(--muted);
  margin-bottom: 16px;
  font-family: 'Roboto', sans-serif;
}

.theme-list { list-style: none; }
.theme-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 4px;
  border-bottom: 1px solid var(--border-light);
  cursor: pointer;
}
.theme-row:last-child { border-bottom: none; }
.theme-row:hover .theme-name { color: var(--nav-active); }

.theme-name {
  font-size: 15px;
  font-weight: 500;
  color: var(--heading);
  font-family: 'Roboto', sans-serif;
}
.theme-arrow {
  color: var(--muted);
  font-size: 14px;
}
</style>
