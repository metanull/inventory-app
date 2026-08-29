<script setup>
import PartnerProfile from './PartnerProfile.vue'

// Legacy's InstitutionProfile is PartnerProfile with three strings changed and
// a different sibling route. `partners.json` ships museums and institutions in
// one list — the package has no endpoints to split them across — so the
// difference is a `type`, and the difference in the page is a variant.
</script>

<template>
  <PartnerProfile variant="institution" />
</template>
