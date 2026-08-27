<script setup>
import FeaturedPartners from '../components/FeaturedPartners.vue'
import SiblingGalleries from '../components/SiblingGalleries.vue'

// The banner is rendered by App.vue (legacy put it in a named router-view of
// its own, above the nav). What is left is what legacy stacked below: the
// featured-partners carousel, a logos slot, and the sibling-galleries strip.
//
// The logos slot is deliberately absent: it was fed by `thg_gallery_logos`,
// a table with one row in total across every DXA site, so it renders nothing
// on the live amulets instance either and the package carries no logos.
</script>

<template>
  <div>
    <FeaturedPartners />
    <SiblingGalleries />
  </div>
</template>
