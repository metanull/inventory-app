<script setup>
import { computed } from 'vue'

// ── Decision Q5: the partner-profile map ───────────────────────────────────
//
// Legacy embedded Google Maps with a per-deployment API key
// (`googleApiKey` in dxa-client's environment/config.sh). CLAUDE.md forbids
// hardcoded secrets, and a key baked into a static bundle is a published
// secret however it is injected, so this viewer uses the keyless alternative
// the epic recommended: OpenStreetMap's public embed iframe, centred on the
// partner's own coordinates at the zoom level the record carries, with a
// plain link out to the full map.
//
// Everything Google-specific is confined to this component. Swapping back to
// Google Maps — should Pascal decide the key belongs in a build secret after
// all — means replacing the `src` computed below and nothing else; no caller
// knows which provider is in use.
//
// This is the open decision the story asked to surface rather than settle.

const props = defineProps({
  latitude: { type: Number, default: null },
  longitude: { type: Number, default: null },
  zoom: { type: Number, default: 15 },
  label: { type: String, default: '' },
})

const hasLocation = computed(() =>
  Number.isFinite(props.latitude) && Number.isFinite(props.longitude)
)

// OSM's embed takes a bounding box rather than a zoom level; derive one whose
// span shrinks as the record's zoom grows, so a zoom-16 museum pin still reads
// as a street-level view.
const bbox = computed(() => {
  const span = 360 / Math.pow(2, Math.max(1, Math.min(19, props.zoom || 15)))
  const [lat, lon] = [props.latitude, props.longitude]
  return [lon - span, lat - span / 2, lon + span, lat + span / 2].join('%2C')
})

const src = computed(() =>
  `https://www.openstreetmap.org/export/embed.html?bbox=${bbox.value}&layer=mapnik&marker=${props.latitude}%2C${props.longitude}`
)

const fullMap = computed(() =>
  `https://www.openstreetmap.org/?mlat=${props.latitude}&mlon=${props.longitude}#map=${props.zoom || 15}/${props.latitude}/${props.longitude}`
)
</script>

<template>
  <div class="partner-map" v-if="hasLocation">
    <p class="map-label">Museum on the map</p>
    <iframe
      :src="src"
      :title="label ? `Map of ${label}` : 'Map'"
      loading="lazy"
      referrerpolicy="no-referrer"
    ></iframe>
    <p class="map-link">
      <a :href="fullMap" target="_blank" rel="noopener">↗ Open in OpenStreetMap</a>
    </p>
  </div>
</template>

<style scoped>
.partner-map { padding: 20px 0 0; }
.map-label { font-weight: 700; color: var(--theme-dark); margin-bottom: 6px; }
.partner-map iframe {
  width: 100%;
  height: 320px;
  border: 1px solid var(--theme-medium);
  display: block;
}
.map-link { padding-top: 6px; font-size: 13px; }
.map-link a { color: var(--link-blue); }
</style>
