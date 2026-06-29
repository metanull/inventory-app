# Inventory Viewer

A minimal Vue 3 single-page application that renders items exported from the MWNF
Inventory API. Intended as a working sample for developers who want to consume the
`@metanull/islamicart-data` npm package.

## Structure

```
scripts/viewer/
├── index.html          # Shell HTML — mounts #app
├── src/
│   ├── main.js         # Creates and mounts the Vue app
│   └── App.vue         # Entire application: data loading, list, detail view
├── vite.config.js      # Resolves the data package path; sets @inventory-data alias
├── .npmrc              # Scopes @metanull to https://npm.pkg.github.com
└── package.json        # Dependencies: vue + the data package
```

The application is intentionally contained in a single component (`App.vue`) to keep
it easy to read top-to-bottom.

## How it uses `@metanull/islamicart-data`

`vite.config.js` resolves the installed package's directory at build time and registers
a Vite alias `@inventory-data` pointing to it:

```js
// vite.config.js
const dataPackageDir = dirname(require.resolve(`${dataPackage}/package.json`))
// alias: '@inventory-data' → '/abs/path/to/node_modules/@metanull/islamicart-data'
```

`App.vue` then imports data directly from that alias:

```js
// Static imports — bundled into the main chunk, available immediately
import manifestData from '@inventory-data/manifest.json'
import itemsData    from '@inventory-data/items.json'

// Dynamic import — code-split per language, loaded on demand
const module = await import(`@inventory-data/translations/items.${lang}.json`)
```

Vite bundles `manifest.json` and `items.json` into the main chunk. Each translation
file (`translations/items.en.json`, `translations/items.ar.json`, …) becomes a
separate lazy chunk, loaded only when the user selects that language.

The data package to use is configured by `DATA_PACKAGE` in `.env` (defaults to
`@metanull/islamicart-data`). Changing it to another compatible package requires only
updating `.env` and re-running `npm install`.

## Build and run

```bash
# Authentication — the @metanull scope is served from GitHub Package Registry.
# Make sure ~/.npmrc contains a valid token for npm.pkg.github.com.

npm install          # installs vue, vite, and the data package
npm run dev          # development server at http://localhost:5173
npm run build        # production build → dist/
npm run preview      # serve the production build locally
```
