#!/usr/bin/env node
//
// Vendor The Use of Colours in Art UI catalogue into src/i18n/.
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
// Run it from scripts/viewers/the-use-of-colours-in-art. It is pure file
// shuffling — no network, no database, no npm dependencies.

import { readFileSync, writeFileSync, mkdirSync, existsSync, readdirSync } from 'fs'
import { dirname, resolve, basename } from 'path'
import { fileURLToPath } from 'url'

const here = dirname(fileURLToPath(import.meta.url))
// exhibition.json's `slug`, which is the underscore form and NOT the folder
// name — the folder and package use the kebab-cased slug fixed by decision Q4.
// site-i18n keys its output by the legacy slug, so this is spelled out rather
// than derived from the directory (see the exporter README, "Naming").
const SITE_SLUG = 'the_use_of_colours_in_art'
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

// Only the languages the exhibition actually publishes. `thg_gallery_lang`
// lists de and en, but `exhibition_i18n.enabled` is 'N' for German and the
// live German instance is a shell — see the exporter README, "German is not a
// published language". Per decision Q2 the sites are per-language builds, and
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
  const merged = { ...common, ...own }
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
