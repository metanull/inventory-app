#!/usr/bin/env node
/**
 * Print the site registry.
 *
 * This is the "which group does my site use?" lookup that scaffolding starts
 * from, answered live from `thg_gallery` rather than from a checked-in copy that
 * would go stale. Read-only.
 *
 * Usage:
 *   npm run list
 *   npm run list -- --hidden        # include hidden (status 'H') sites
 *   npm run list -- --json
 */

import dotenv from 'dotenv'
import { resolve } from 'path'
import { Command } from 'commander'
import chalk from 'chalk'

import { LegacyDatabase } from '../core/database.js'
import { isHidden } from '../registry.js'

dotenv.config({ path: resolve(process.cwd(), '.env') })

const program = new Command()

program
  .name('site-i18n-list')
  .description('List the DXA gallery/exhibition registry from the legacy database')
  .allowExcessArguments(false)
  .option('--hidden', 'Include sites whose status is H (hidden)', false)
  .option('--json', 'Print the registry as JSON', false)
  .action(async (options: { hidden: boolean; json: boolean }) => {
    const db = new LegacyDatabase()

    try {
      await db.connect()
      const registry = await db.loadRegistry()
      const rows = options.hidden ? registry : registry.filter((site) => !isHidden(site))

      if (options.json) {
        console.log(JSON.stringify(rows, null, 2))
        return
      }

      const header = ['ID', 'SLUG', 'KIND', 'PROJECT', 'GROUP', 'COMMON', 'ST', 'HOST']
      const table = rows.map((site) => [
        String(site.galleryId),
        site.slug ?? '—',
        site.kind,
        site.mwnf3ProjectId ?? '—',
        site.i18nGroupId === null ? '—' : String(site.i18nGroupId),
        site.i18nCommonGroupId === null ? '—' : String(site.i18nCommonGroupId),
        site.status,
        site.host ?? '—',
      ])

      const widths = header.map((_, column) =>
        Math.max(header[column]!.length, ...table.map((row) => row[column]!.length))
      )
      const render = (row: string[]): string =>
        row.map((cell, column) => cell.padEnd(widths[column]!)).join('  ')

      console.log(chalk.bold(render(header)))
      for (const row of table) {
        console.log(render(row))
      }
      console.log('')
      console.log(
        chalk.dim(
          `${rows.length} site(s). Extract one with: npm run extract -- <id | slug | project>`
        )
      )
    } catch (error) {
      console.error(chalk.red(`Failed to read the registry: ${(error as Error).message}`))
      process.exitCode = 1
    } finally {
      await db.disconnect()
    }
  })

await program.parseAsync(process.argv)
