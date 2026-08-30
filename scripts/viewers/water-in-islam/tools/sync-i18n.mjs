#!/usr/bin/env node
//
// Vendor the Water in Islam UI catalogue into src/i18n/.
//
// UI strings are NOT data: they never enter the inventory model and never ride
// in the data package (decision G3, scripts/site-i18n/README.md). They are
// delivered to a website *at scaffold time*, which is what this script does —
// it reads scripts/site-i18n/output/, merges the shared layer named by the
// site's `extends` with the site's own overrides, and writes one flat catalogue
// per site language into src/i18n/.
//
// The result is committed, because scripts/site-i18n/output/ is gitignored and
// regenerating it needs a VPN tunnel to the legacy database. Re-run this only
// after a fresh site-i18n extraction:
//
//   node tools/sync-i18n.mjs
//
// Run it from scripts/viewers/water-in-islam. It is pure file shuffling — no
// network, no database, no npm dependencies.

import { readFileSync, writeFileSync, mkdirSync, existsSync, readdirSync } from 'fs'
import { dirname, resolve, basename } from 'path'
import { fileURLToPath } from 'url'

const here = dirname(fileURLToPath(import.meta.url))
// exhibition.json's `slug`, which is the underscore form and NOT the folder
// name — the folder and package use the kebab-cased slug fixed by decision Q4.
// site-i18n keys its output by the legacy slug, so this is spelled out rather
// than derived from the directory (see the exporter README, "Naming").
const SITE_SLUG = 'water_in_islam'
const i18nRoot = resolve(here, '../../../site-i18n/output')
const siteDir = resolve(i18nRoot, SITE_SLUG)
const outDir = resolve(here, '../src/i18n')

if (!existsSync(siteDir)) {
  console.error(
    `No site-i18n output at ${siteDir}.\n` +
    `Run the extractor first — see scripts/site-i18n/README.md.`
  )
  process.exit(1)
}

const readJson = (p) => JSON.parse(readFileSync(p, 'utf8'))

const site = readJson(resolve(siteDir, 'site.json'))
const commonDir = resolve(siteDir, site.i18n.extends, 'i18n')

// Only the languages the exhibition actually publishes. Here `thg_gallery_lang`
// and `exhibition_i18n.enabled` agree — English alone — so `languages` and
// `languages_enabled` are the same list and there is one build. (They differ on
// Colours, where German has full theme translations and is never published,
// which is why the package carries both fields rather than deriving one from
// the other.) Per decision Q2 the sites are per-language builds, and
// `languages_enabled` is the field that decides which builds exist.
const SITE_LANGUAGES = ['en']

mkdirSync(outDir, { recursive: true })

const written = []
for (const lang of SITE_LANGUAGES) {
  const commonFile = resolve(commonDir, `${lang}.json`)
  const ownFile = resolve(siteDir, 'i18n', `${lang}.json`)
  const common = existsSync(commonFile) ? readJson(commonFile) : {}
  const own = existsSync(ownFile) ? readJson(ownFile) : {}
  // Shared layer first, the site's own keys overlaid — the merge order
  // site.json's `extends` describes.
  //
  // The overlay is CASE-INSENSITIVE on the key, because the legacy lookup is:
  // the strings live in a MySQL table read with `WHERE key = ?` under a `_ci`
  // collation, so legacy never had to spell a key the same way twice. This
  // exhibition is where that bites. Its own layer carries
  // `Footer_logo_section_1` = "Under the patronage of" while the shared layer
  // carries `footer_logo_section_1` = "LEFT MOST FOOTER SECTION FOR LOGOS", a
  // placeholder no site is meant to display. A plain spread keeps both, and the
  // code reads the lowercase one — so the curator's heading would be dropped in
  // favour of the placeholder. The live instance renders "Under the patronage
  // of", which is what says the override is meant to win.
  //
  // The COMMON spelling is the one kept, because that is the spelling the
  // components call `t()` with; only the value comes from the site's layer. A
  // key the shared layer does not have keeps the site's own spelling.
  const merged = { ...common }
  const commonKeyByLower = new Map(Object.keys(common).map(k => [k.toLowerCase(), k]))
  for (const [key, value] of Object.entries(own)) {
    merged[commonKeyByLower.get(key.toLowerCase()) ?? key] = value
  }
  const sorted = Object.fromEntries(Object.keys(merged).sort().map(k => [k, merged[k]]))
  writeFileSync(resolve(outDir, `${lang}.json`), JSON.stringify(sorted, null, 2) + '\n', 'utf8')
  written.push(`${lang}.json (${Object.keys(sorted).length} keys, ${Object.keys(own).length} site-owned)`)
}

const stale = readdirSync(outDir)
  .filter(f => f.endsWith('.json'))
  .filter(f => !SITE_LANGUAGES.includes(basename(f, '.json')))
if (stale.length) console.warn(`Note: unmanaged files left in src/i18n: ${stale.join(', ')}`)

console.log(`Vendored ${SITE_SLUG} UI catalogue into src/i18n:`)
for (const line of written) console.log(`  ${line}`)
