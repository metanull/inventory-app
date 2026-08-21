import { existsSync, readFileSync } from 'node:fs'
import { dirname, join } from 'node:path'
import { fileURLToPath } from 'node:url'

import { describe, expect, it } from 'vitest'

import * as exporters from '../../src/exporters/index.js'

const testDir = dirname(fileURLToPath(import.meta.url))
const srcDir = join(testDir, '..', '..', 'src')

/**
 * The Baroque Art dataset has no dynasties (every legacy mwnf3.dynasties row
 * belongs to ISL). This fork must therefore ship no dynasty exporter — a BAR
 * package containing dynasties.json would leak the full Islamic Art dynasty
 * list.
 */
describe('baroqueart exporter run list', () => {
  it('exposes no DynastyExporter', () => {
    expect(Object.keys(exporters)).not.toContain('DynastyExporter')
  })

  it('has no dynasty-exporter source file', () => {
    expect(existsSync(join(srcDir, 'exporters', 'dynasty-exporter.ts'))).toBe(false)
  })

  it('does not reference DynastyExporter in the CLI run list', () => {
    const cliSource = readFileSync(join(srcDir, 'cli', 'export.ts'), 'utf-8')
    expect(cliSource).not.toMatch(/new DynastyExporter/)
  })

  it('exposes the exporters the BAR package is built from', () => {
    expect(Object.keys(exporters).sort()).toEqual(
      [
        'CollectionExporter',
        'CountryExporter',
        'GlossaryExporter',
        'ItemExporter',
        'LanguageExporter',
        'ManifestExporter',
        'PartnerExporter',
        'TimelineExporter',
      ].sort()
    )
  })
})
