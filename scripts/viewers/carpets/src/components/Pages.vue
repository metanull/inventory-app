<script setup>
// Legacy's PagesComponent: first / previous / numbered window / next / last,
// plus a "go to page" box.
import { computed, ref, watch } from 'vue'

const props = defineProps({
  pageInfo: { type: Object, required: true },
})
const emit = defineEmits(['navigate'])

const jumpTo = ref(String(props.pageInfo.currentPage))
watch(() => props.pageInfo.currentPage, p => { jumpTo.value = String(p) })

const windowPages = computed(() => {
  const { currentPage, lastPage } = props.pageInfo
  const span = 5
  let from = Math.max(1, currentPage - Math.floor(span / 2))
  const to = Math.min(lastPage, from + span - 1)
  from = Math.max(1, to - span + 1)
  const out = []
  for (let p = from; p <= to; p++) out.push(p)
  return out
})

function go(page) {
  const p = Math.min(Math.max(1, Number(page) || 1), props.pageInfo.lastPage)
  emit('navigate', p)
}
</script>

<template>
  <div class="pages" v-if="pageInfo.lastPage > 1">
    <button class="page-btn" :disabled="pageInfo.currentPage === 1" @click="go(1)">« First</button>
    <button class="page-btn" :disabled="pageInfo.currentPage === 1" @click="go(pageInfo.currentPage - 1)">‹ Previous</button>
    <button
      v-for="p in windowPages"
      :key="p"
      class="page-btn"
      :class="{ active: p === pageInfo.currentPage }"
      @click="go(p)"
    >{{ p }}</button>
    <button class="page-btn" :disabled="pageInfo.currentPage === pageInfo.lastPage" @click="go(pageInfo.currentPage + 1)">Next ›</button>
    <button class="page-btn" :disabled="pageInfo.currentPage === pageInfo.lastPage" @click="go(pageInfo.lastPage)">Last »</button>
    <span class="pages-jump">
      Page
      <input type="number" min="1" :max="pageInfo.lastPage" v-model="jumpTo" @keyup.enter="go(jumpTo)" />
      of {{ pageInfo.lastPage }}
      <button class="page-btn" @click="go(jumpTo)">Go</button>
    </span>
  </div>
</template>

<style scoped>
.pages {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 4px;
  padding: 12px 20px;
  font-size: 13px;
}
.page-btn {
  border: 1px solid var(--theme-medium);
  background: #fff;
  color: var(--theme-dark);
  padding: 3px 9px;
  font-family: inherit;
  font-size: 13px;
  cursor: pointer;
}
.page-btn:hover:not(:disabled) { background: var(--background-color); }
.page-btn.active { background: var(--theme-medium-dark); color: #fff; border-color: var(--theme-medium-dark); font-weight: 700; }
.page-btn:disabled { opacity: 0.4; cursor: default; }
.pages-jump { margin-inline-start: auto; display: flex; align-items: center; gap: 4px; }
.pages-jump input { width: 60px; padding: 3px; font-family: inherit; border: 1px solid var(--theme-medium); }
</style>
