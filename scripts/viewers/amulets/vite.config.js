import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { createRequire } from 'module'
import { existsSync } from 'fs'
import { dirname, isAbsolute, resolve } from 'path'
import { fileURLToPath } from 'url'

const here = dirname(fileURLToPath(import.meta.url))

// The data package is resolved at build time, in this order:
//
//   1. DATA_PACKAGE — an npm package name *or* a directory path. Explicit wins.
//   2. @metanull/amulets-data — the published package, once it exists.
//   3. ../../exporters/amulets/output/amulets — a local exporter run.
//
// Step 3 is the one that carries this viewer today: `@metanull/amulets-data`
// has not been published yet (that call is not the viewer's to make), so the
// only source of truth is the exporter's own output directory, produced by
//
//   docker compose --profile jobs run --rm exporter amulets --force \
//       --base-url https://inventory.metanull.eu
//
// from the repository root. See README.md § "Where the data comes from" for
// the two-line change this file needs once the package is published.
function resolveDataPackage() {
  const explicit = process.env.DATA_PACKAGE
  const require = createRequire(import.meta.url)

  const asPackage = (name) => {
    try {
      return dirname(require.resolve(`${name}/package.json`))
    } catch {
      return null
    }
  }

  const asDirectory = (p) => {
    const abs = isAbsolute(p) ? p : resolve(here, p)
    return existsSync(resolve(abs, 'manifest.json')) ? abs : null
  }

  if (explicit) {
    const found = asPackage(explicit) ?? asDirectory(explicit)
    if (found) return found
    throw new Error(
      `DATA_PACKAGE="${explicit}" is neither an installed package nor a ` +
      `directory containing manifest.json.`
    )
  }

  const published = asPackage('@metanull/amulets-data')
  if (published) return published

  const local = asDirectory('../../exporters/amulets/output/amulets')
  if (local) return local

  throw new Error(
    'No Amulets data package found.\n' +
    '\n' +
    'This viewer reads @metanull/amulets-data when it is installed, and falls\n' +
    'back to the exporter output at scripts/exporters/amulets/output/amulets.\n' +
    'Neither is present. Produce the local export with:\n' +
    '\n' +
    '  docker compose --profile jobs run --rm exporter amulets --force \\\n' +
    '      --base-url https://inventory.metanull.eu\n' +
    '\n' +
    'or point DATA_PACKAGE at a package name or directory.'
  )
}

export default defineConfig(() => ({
  plugins: [vue()],
  resolve: {
    alias: { '@inventory-data': resolveDataPackage() },
  },
}))
