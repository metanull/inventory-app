<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()

function browse() {
  router.push({ path: '/partners/results' })
}
</script>

<template>
  <div>
    <h1 class="section-heading">Partners</h1>

    <div class="content-box">
      <p class="intro-text">
        Sharing History — Arab World – Europe is a collaborative project
        bringing together museums, archives, universities, libraries and
        cultural heritage authorities from the Arab world and Europe. Browse
        the partners — and their associated partners — grouped by country.
      </p>

      <table class="form-table filter-table">
        <tbody>
          <tr>
            <th><label>Partners</label></th>
            <td>
              <button class="btn" @click="browse()">Browse Partners →</button>
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
