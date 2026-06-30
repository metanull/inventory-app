<script setup>
import { RouterView, RouterLink } from 'vue-router'
</script>

<template>
  <div class="site">
    <header class="site-header">
      <div class="container">
        <RouterLink to="/" class="site-logo">
          <span class="site-logo-org">Museum With No Frontiers</span>
          <span class="site-logo-title">Islamic Art</span>
        </RouterLink>
      </div>
    </header>

    <nav class="site-nav">
      <div class="container nav-inner">
        <RouterLink to="/" active-class="nav-active" exact-active-class="nav-exact-active">Home</RouterLink>
        <RouterLink to="/permanent-collection" active-class="nav-active">Permanent Collection</RouterLink>
        <RouterLink to="/database" active-class="nav-active">Database</RouterLink>
      </div>
    </nav>

    <main class="site-main">
      <div class="container">
        <RouterView />
      </div>
    </main>

    <footer class="site-footer">
      <div class="container">
        <span>© Museum With No Frontiers — Islamic Art Digital Exhibition</span>
      </div>
    </footer>
  </div>
</template>

<style>
/* ── Reset & base ───────────────────────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --header-bg:  #3a2510;
  --header-fg:  #e8d5a3;
  --nav-bg:     #5a3a1a;
  --nav-fg:     #f5ead8;
  --nav-active: #f5c842;
  --gold:       #c5882e;
  --gold-light: #e8c97a;
  --page-bg:    #f0e9d8;
  --content-bg: #ffffff;
  --text:       #1a1a1a;
  --muted:      #5a5252;
  --border:     #d4b890;
  --link:       #5a3a1a;
  --link-hover: #c5882e;
}

body {
  font-family: Georgia, 'Times New Roman', serif;
  font-size: 14px;
  color: var(--text);
  background: var(--page-bg);
  min-height: 100vh;
}

a { color: var(--link); text-decoration: none; }
a:hover { color: var(--link-hover); text-decoration: underline; }

.container { max-width: 960px; margin: 0 auto; padding: 0 16px; }

/* ── Header ─────────────────────────────────────────────────────────────── */
.site-header {
  background: var(--header-bg);
  padding: 14px 0;
  border-bottom: 2px solid var(--gold);
}

.site-logo {
  display: flex;
  flex-direction: column;
  gap: 2px;
  text-decoration: none !important;
}
.site-logo-org {
  font-size: 11px;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--header-fg);
  opacity: 0.75;
  font-family: Arial, sans-serif;
}
.site-logo-title {
  font-size: 26px;
  font-weight: bold;
  color: var(--gold-light);
  letter-spacing: 0.04em;
  font-family: Georgia, serif;
}

/* ── Navigation ─────────────────────────────────────────────────────────── */
.site-nav {
  background: var(--nav-bg);
  border-bottom: 1px solid #3a2008;
}
.nav-inner {
  display: flex;
  gap: 0;
}
.site-nav a {
  display: inline-block;
  padding: 9px 18px;
  color: var(--nav-fg);
  font-family: Arial, Helvetica, sans-serif;
  font-size: 13px;
  text-decoration: none !important;
  border-right: 1px solid rgba(255,255,255,0.1);
  transition: background 0.15s, color 0.15s;
}
.site-nav a:hover,
.site-nav a.nav-active {
  background: rgba(0,0,0,0.25);
  color: var(--nav-active);
}

/* ── Main content ────────────────────────────────────────────────────────── */
.site-main { padding: 20px 0 40px; }

/* ── Footer ─────────────────────────────────────────────────────────────── */
.site-footer {
  background: var(--header-bg);
  border-top: 2px solid var(--gold);
  padding: 12px 0;
  font-family: Arial, sans-serif;
  font-size: 11px;
  color: var(--header-fg);
  opacity: 0.85;
  text-align: center;
}

/* ── Shared content-box ─────────────────────────────────────────────────── */
.content-box {
  background: var(--content-bg);
  border: 1px solid var(--border);
  padding: 20px;
  margin-bottom: 16px;
}

/* ── Section heading ─────────────────────────────────────────────────────── */
.section-heading {
  font-size: 16px;
  font-weight: bold;
  color: var(--header-bg);
  border-bottom: 2px solid var(--gold);
  padding-bottom: 6px;
  margin-bottom: 14px;
  font-family: Georgia, serif;
}

/* ── Shared item list & cards ───────────────────────────────────────────── */
.item-list { list-style: none; }
.item-list-row {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 10px 0;
  border-bottom: 1px solid #e8dcc8;
  cursor: pointer;
}
.item-list-row:last-child { border-bottom: none; }
.item-list-row:hover .item-list-name { color: var(--gold); }

.item-thumb {
  flex-shrink: 0;
  width: 64px;
  height: 64px;
  overflow: hidden;
  border: 1px solid var(--border);
  background: #e8dcc8;
}
.item-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.item-thumb-placeholder { width: 100%; height: 100%; }

.item-list-info { flex: 1; min-width: 0; }
.item-list-name {
  font-weight: bold;
  font-size: 13px;
  color: var(--link);
  margin-bottom: 3px;
}
.item-list-meta {
  font-size: 11px;
  color: var(--muted);
  font-family: Arial, sans-serif;
}
.item-list-meta span + span::before { content: ' · '; }

/* ── Pagination ─────────────────────────────────────────────────────────── */
.pagination {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 14px 0 0;
  flex-wrap: wrap;
  font-family: Arial, sans-serif;
  font-size: 12px;
}
.pagination-info {
  margin-right: auto;
  color: var(--muted);
}
.page-btn {
  display: inline-block;
  padding: 4px 10px;
  border: 1px solid var(--border);
  background: var(--content-bg);
  color: var(--link);
  cursor: pointer;
  font-size: 12px;
  font-family: Arial, sans-serif;
}
.page-btn:hover { background: #f0e0c0; }
.page-btn.active {
  background: var(--header-bg);
  color: var(--gold-light);
  border-color: var(--header-bg);
  font-weight: bold;
}
.page-btn:disabled, .page-btn[disabled] {
  opacity: 0.4;
  cursor: default;
}

/* ── Form elements ──────────────────────────────────────────────────────── */
.form-table { width: 100%; border-collapse: collapse; }
.form-table th {
  text-align: right;
  padding: 6px 12px 6px 0;
  font-weight: bold;
  font-size: 12px;
  color: var(--muted);
  width: 160px;
  vertical-align: middle;
  font-family: Arial, sans-serif;
}
.form-table td { padding: 6px 0; vertical-align: middle; }

select, input[type="text"], input[type="number"] {
  font-family: Arial, Helvetica, sans-serif;
  font-size: 13px;
  padding: 4px 8px;
  border: 1px solid var(--border);
  background: #faf6ef;
  color: var(--text);
  border-radius: 0;
  width: auto;
}
select:focus, input:focus { outline: 1px solid var(--gold); }

.btn {
  display: inline-block;
  padding: 6px 18px;
  background: var(--header-bg);
  color: var(--gold-light);
  font-family: Arial, sans-serif;
  font-size: 13px;
  font-weight: bold;
  border: none;
  cursor: pointer;
  letter-spacing: 0.03em;
}
.btn:hover { background: var(--nav-bg); }
.btn-secondary {
  background: var(--border);
  color: var(--text);
}
.btn-secondary:hover { background: #c0a070; }

/* ── Back link ──────────────────────────────────────────────────────────── */
.back-link {
  display: inline-block;
  margin-bottom: 12px;
  font-family: Arial, sans-serif;
  font-size: 12px;
  color: var(--muted);
}
.back-link:hover { color: var(--gold); }
</style>
