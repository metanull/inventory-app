<script setup>
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import { exhibitionTitle, exhibitionSubtitle } from '../composables/useExhibitionData.js'
import { uiLang, t } from '../composables/useUiStrings.js'

// Legacy's BottomBanner: the exhibition's identity on the left, and the two
// ways into it — the introduction and the theme tour — on the right. It sits
// under every page including Home, in a contrast-coloured band.
const title = computed(() => exhibitionTitle(uiLang.value))
const subtitle = computed(() => exhibitionSubtitle(uiLang.value))
</script>

<template>
  <div class="bottom-banner-container">
    <div class="bottom-banner-wrapper">
      <div class="bottom-banner-content">
        <RouterLink to="/" class="bottom-banner-link bottom-banner-exhibition">
          <div class="bottom-banner-title">{{ title }}</div>
          <div class="bottom-banner-subtitle">{{ subtitle }}</div>
        </RouterLink>
        <div class="bottom-banner-responsive-wrapper">
          <RouterLink to="/about" class="bottom-banner-link">
            <div class="bold">ABOUT</div>
            <div>{{ t('bottomIntrExh') }}</div>
          </RouterLink>
          <RouterLink to="/themes" class="bottom-banner-link">
            <div class="bold">THEMES</div>
            <div>{{ t('bottomExhCont') }}</div>
          </RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.bold { font-weight: 700; }

.bottom-banner-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 100%;
  font-size: 18px;
  color: var(--contrast-text-color);
  background: var(--contrast-color);
}
.bottom-banner-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  padding: 50px 30px;
}
.bottom-banner-content {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
  align-items: start;
  width: 100%;
}
.bottom-banner-responsive-wrapper {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 25px;
  grid-column: span 2;
  width: 100%;
}
.bottom-banner-link {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;
  color: var(--contrast-text-color);
  text-align: center;
  text-decoration: none;
}
.bottom-banner-link:hover { text-decoration: underline; }
.bottom-banner-title { font-weight: 700; }
.bottom-banner-subtitle { font-size: 85%; }

@media only screen and (max-width: 849px) {
  .bottom-banner-content { display: flex; flex-direction: column; align-items: center; gap: 25px; }
  .bottom-banner-responsive-wrapper { display: flex; flex-direction: column; }
}
</style>
