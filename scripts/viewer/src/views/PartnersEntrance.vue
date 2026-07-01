<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()

function browse(type) {
  router.push({ path: '/partners/results', query: { type } })
}
</script>

<template>
  <div>
    <h1 class="section-heading">Partners</h1>

    <div class="content-box">
      <p class="intro-text">
        Discover Islamic Art is a collaborative project bringing together museums and
        institutions from across the Islamic world and beyond. Browse the partner
        museums that lend and hold the objects in the collection, or the partner
        institutions responsible for the monuments and historic sites.
      </p>

      <table class="form-table filter-table">
        <tbody>
          <tr>
            <th><label>Partner Museums</label></th>
            <td>
              <button class="btn" @click="browse('museum')">Browse Museums →</button>
            </td>
          </tr>
          <tr>
            <th><label>Partner Institutions</label></th>
            <td>
              <button class="btn" @click="browse('institution')">Browse Institutions →</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style scoped>
.intro-text {
  font-size: 13px;
  line-height: 1.65;
  color: var(--muted);
  margin-bottom: 16px;
  font-family: 'Roboto', sans-serif;
}

.filter-table th {
  text-align: left;
  font-weight: normal;
  padding: 10px 16px 10px 0;
  font-family: 'Roboto', sans-serif;
  font-size: 13px;
  color: var(--text);
  vertical-align: middle;
  width: auto;
}
</style>
