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
@import url('https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400&family=Roboto+Condensed:ital,wght@0,300;0,400;0,700;1,400&display=swap');

/* ── Reset & base ───────────────────────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  /* Header / nav / footer backgrounds */
  --header-bg:   #EEC201;   /* amber gold — legacy header bar */
  --nav-bg:      #FFD600;   /* bright yellow — legacy nav bar */
  --nav-alt-bg:  #E0B700;   /* alternating nav item */
  --footer-bg:   #E0B700;   /* footer bar */

  /* Text on those backgrounds */
  --header-fg:   #4C420E;   /* dark brown on amber header */
  --nav-fg:      #4C4214;   /* dark brown on yellow nav */
  --nav-active:  #990000;   /* hover / active nav link */
  --footer-fg:   #4C4214;   /* footer text */

  /* Heading & body text */
  --heading:     #4C420E;   /* section headings */
  --text:        #222222;   /* body text */
  --muted:       #4c4000;   /* secondary / label text */
  --link:        #222222;
  --link-hover:  #990000;

  /* Gold accent palette */
  --gold:        #FFD600;
  --gold-amber:  #EEC201;
  --gold-dark:   #E0B700;
  --gold-light:  #F5DA66;
  --gold-pale:   #fdfbed;

  /* Surfaces */
  --page-bg:     #ffffff;
  --content-bg:  #ffffff;
  --section-bg:  #fffff0;   /* ivory for info sections */

  /* Borders */
  --border:      #a19869;   /* muted gold-brown */
}

body {
  font-family: 'Roboto', sans-serif;
  font-size: 14px;
  font-weight: 400;
  color: var(--text);
  background: var(--page-bg);
  min-height: 100vh;
  -webkit-font-smoothing: antialiased;
}

a { color: var(--link); text-decoration: none; }
a:hover { color: var(--link-hover); text-decoration: underline; }

.container { max-width: 960px; margin: 0 auto; padding: 0 16px; }

/* ── Header ─────────────────────────────────────────────────────────────── */
.site-header {
  background: var(--header-bg);
  padding: 12px 0;
  border-bottom: 3px solid var(--nav-alt-bg);
}

.site-logo {
  display: flex;
  flex-direction: column;
  gap: 1px;
  text-decoration: none !important;
}
.site-logo-org {
  font-size: 11px;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--header-fg);
  font-family: 'Roboto', sans-serif;
  font-weight: 400;
  opacity: 0.8;
}
.site-logo-title {
  font-size: 28px;
  font-weight: 400;
  color: var(--header-fg);
  letter-spacing: 0.02em;
  font-family: 'Roboto', sans-serif;
  text-transform: uppercase;
  text-shadow: 2px 2px 4px var(--gold-light);
}

/* ── Navigation ─────────────────────────────────────────────────────────── */
.site-nav {
  background: var(--nav-bg);
  border-bottom: 1px solid var(--nav-alt-bg);
}
.nav-inner {
  display: flex;
  gap: 0;
}
.site-nav a {
  display: inline-block;
  padding: 8px 20px;
  color: var(--nav-fg);
  font-family: 'Roboto', sans-serif;
  font-size: 15px;
  font-weight: 500;
  text-decoration: none !important;
  transition: color 0.15s, background 0.15s;
}
.site-nav a:nth-child(even) {
  background: var(--nav-alt-bg);
}
.site-nav a:hover,
.site-nav a.nav-active {
  color: var(--nav-active);
  background: rgba(0,0,0,0.08);
}

/* ── Main content ────────────────────────────────────────────────────────── */
.site-main { padding: 20px 0 40px; }

/* ── Footer ─────────────────────────────────────────────────────────────── */
.site-footer {
  background: var(--footer-bg);
  border-top: 1px solid var(--nav-alt-bg);
  padding: 10px 0;
  font-family: 'Roboto', sans-serif;
  font-size: 12px;
  font-weight: 500;
  color: var(--footer-fg);
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
  font-size: 18px;
  font-weight: 400;
  color: var(--heading);
  border-bottom: 2px solid var(--gold-dark);
  padding-bottom: 4px;
  margin-bottom: 14px;
  font-family: 'Roboto', sans-serif;
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
.item-list-row:hover .item-list-name { color: var(--nav-active); }

.item-thumb {
  flex-shrink: 0;
  width: 64px;
  height: 64px;
  overflow: hidden;
  border: 1px solid var(--border);
  background: #f0ebdc;
}
.item-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
.item-thumb-placeholder { width: 100%; height: 100%; }

.item-list-info { flex: 1; min-width: 0; }
.item-list-name {
  font-weight: 500;
  font-size: 14px;
  color: var(--text);
  margin-bottom: 3px;
  font-family: 'Roboto', sans-serif;
}
.item-list-meta {
  font-size: 12px;
  color: var(--muted);
  font-family: 'Roboto', sans-serif;
}
.item-list-meta span + span::before { content: ' · '; }

/* ── Pagination ─────────────────────────────────────────────────────────── */
.pagination {
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 14px 0 0;
  flex-wrap: wrap;
  font-family: 'Roboto', sans-serif;
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
  color: var(--text);
  cursor: pointer;
  font-size: 12px;
  font-family: 'Roboto', sans-serif;
}
.page-btn:hover { background: var(--gold-pale); }
.page-btn.active {
  background: var(--nav-bg);
  color: var(--nav-fg);
  border-color: var(--nav-alt-bg);
  font-weight: 700;
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
  font-weight: 500;
  font-size: 12px;
  color: var(--muted);
  width: 160px;
  vertical-align: middle;
  font-family: 'Roboto', sans-serif;
}
.form-table td { padding: 6px 0; vertical-align: middle; }

select, input[type="text"], input[type="number"] {
  font-family: 'Roboto', sans-serif;
  font-size: 13px;
  padding: 4px 8px;
  border: 1px solid var(--border);
  background: var(--gold-pale);
  color: var(--text);
  border-radius: 0;
  width: auto;
}
select:focus, input:focus { outline: 1px solid var(--gold-dark); }

.btn {
  display: inline-block;
  padding: 6px 18px;
  background: var(--nav-bg);
  color: var(--nav-fg);
  font-family: 'Roboto', sans-serif;
  font-size: 13px;
  font-weight: 500;
  border: none;
  cursor: pointer;
  letter-spacing: 0.03em;
}
.btn:hover { background: var(--nav-alt-bg); }
.btn-secondary {
  background: var(--border);
  color: var(--text);
}
.btn-secondary:hover { background: #8a7d52; color: #fff; }

/* ── Back link ──────────────────────────────────────────────────────────── */
.back-link {
  display: inline-block;
  margin-bottom: 12px;
  font-family: 'Roboto', sans-serif;
  font-size: 13px;
  color: var(--muted);
}
.back-link:hover { color: var(--nav-active); }
</style>
