#!/usr/bin/env node
/**
 * Extract a site's UI strings from the legacy database into vue-i18n message files.
 *
 * Run this once when a gallery or exhibition website is scaffolded. It reads the
 * legacy `mwnf3.translation` groups the site is registered against, merges them,
 * converts the values to Markdown, and writes a directory the new site's repo can
 * take as-is. Nothing is written to the inventory database — these strings are
 * editorial page content and UI labels, and the decision on #1517 was that they
 * do not belong in the inventory model.
 *
 * Usage:
 *   npm run extract -- carpets                 # by slug
 *   npm run extract -- 9 DCA amulets           # by gallery id, project code or slug
 *   npm run extract -- --all                   # every active site
 *   npm run extract -- --all --include-hidden  # ...including status 'H'
 *   npm run extract -- carpets --layout flat   # one self-contained catalogue
 *
 * Output, under --output-dir (default ./output):
 *   _common/<id>/common.json      the shared layer's provenance (layered only)
 *   _common/<id>/i18n/<lang>.json the common group, written once (layered only)
 *   <slug>/site.json              the gallery anchor: id, project, slug, host, groups
 *   <slug>/i18n/index.json        locales, default and fallback locale, key counts
 *   <slug>/i18n/<lang>.json       vue-i18n messages, Markdown values
 *   extraction-report.md          what the run did, across every site extracted
 *
 * ## Layouts
 *
 * Nearly everything a site carries belongs to the common group every other site
 * is registered against: measured across the 41 active sites, 0.5% of the
 * (locale, key) instances differ, and a typical gallery owns two messages out of
 * 453. `--layout layered` (the default) writes the common group once under
 * `_common/` and gives each site only what it overrides or adds. `--layout flat`
 * writes the merged catalogue per site, which is the form to diff against the
 * legacy API's own output when verifying an extraction.
 */

import dotenv from 'dotenv'
import { resolve, join } from 'path'
import { existsSync, mkdirSync, rmSync, writeFileSync } from 'fs'
import { Command } from 'commander'
import chalk from 'chalk'

import { LegacyDatabase } from '../core/database.js'
import { Logger } from '../core/logger.js'
import type {
  ExtractedSite,
  MessageCatalogue,
  SiteRegistryEntry,
  TranslationRow,
} from '../core/types.js'
import {
  buildLocaleIndex,
  findLayerRoundTripFailures,
  mergeTranslationGroups,
  splitLayers,
} from '../extract.js'
import { buildReport } from '../report.js'
import { collectWarnings, isHidden, outputName, selectSites } from '../registry.js'

/**
 * Directory holding the shared layers, one subdirectory per common group id.
 *
 * Keyed by group id rather than flat, so a run covering sites registered
 * against different common groups cannot have one group's messages overwrite
 * another's. In practice every site in the registry uses group 59 — but the one
 * site that did not was a data defect, and the layout should not depend on that
 * defect staying fixed.
 */
const SHARED_ROOT = '_common'

dotenv.config({ path: resolve(process.cwd(), '.env') })

const writeJson = (path: string, value: unknown): void => {
  writeFileSync(path, `${JSON.stringify(value, null, 2)}\n`, 'utf8')
}

const program = new Command()

program
  .name('site-i18n-extract')
  .description('Extract legacy DXA UI strings into vue-i18n message files')
  .argument('[selectors...]', 'Sites to extract, by gallery id, slug or mwnf3 project code')
  .option('--all', 'Extract every site in the registry', false)
  .option('--include-hidden', "With --all, also extract sites whose status is 'H'", false)
  .option(
    '--group <id>',
    "Override the site's i18n group id (one site only; for sites whose registered group is stale)"
  )
  .option('--common-group <id>', 'Override the common i18n group id (one site only)')
  .option('--output-dir <path>', 'Output directory (relative to cwd or absolute)', 'output')
  .option('--force', 'Overwrite the output directory if it already exists', false)
  .option(
    '--layout <layout>',
    "'layered' (shared layer in _common/, sites carry only their own messages) or 'flat' (one self-contained catalogue per site)",
    'layered'
  )
  .action(
    async (
      selectors: string[],
      options: {
        all: boolean
        includeHidden: boolean
        group?: string
        commonGroup?: string
        outputDir: string
        force: boolean
        layout: string
      }
    ) => {
      const logger = new Logger('site-i18n')

      if (options.layout !== 'layered' && options.layout !== 'flat') {
        console.error(
          chalk.red(`Unknown layout "${options.layout}".\n`) +
            chalk.dim("Expected 'layered' (default) or 'flat'.")
        )
        process.exitCode = 1
        return
      }
      const layered = options.layout === 'layered'

      if (!options.all && selectors.length === 0) {
        console.error(
          chalk.red('Nothing to extract. Name at least one site, or pass --all.\n') +
            chalk.dim('List the registry with: npm run list')
        )
        process.exitCode = 1
        return
      }

      const outputRoot = resolve(process.cwd(), options.outputDir)
      if (existsSync(outputRoot)) {
        if (!options.force) {
          console.error(
            chalk.red(`Output directory already exists: ${outputRoot}\n`) +
              chalk.dim('Pass --force to replace it.')
          )
          process.exitCode = 1
          return
        }
        rmSync(outputRoot, { recursive: true, force: true })
      }

      const db = new LegacyDatabase()

      try {
        await db.connect()
        logger.info('Connected to the legacy database (read-only)')

        const registry = await db.loadRegistry()
        logger.info(`Registry: ${registry.length} galleries and exhibitions`)

        let targets: SiteRegistryEntry[]
        if (options.all) {
          targets = options.includeHidden ? registry : registry.filter((site) => !isHidden(site))
        } else {
          const { selected, unmatched } = selectSites(registry, selectors)
          if (unmatched.length > 0) {
            console.error(
              chalk.red(`No site matches: ${unmatched.join(', ')}\n`) +
                chalk.dim('List the registry with: npm run list')
            )
            process.exitCode = 1
            return
          }
          targets = selected
        }

        if (targets.length === 0) {
          logger.warning('No sites selected; nothing to do.')
          return
        }

        // Group overrides exist for sites whose registered group is wrong —
        // gallery 31 (Portraits) is registered against group 63, which has no
        // rows, while its live site is served group 45. Applying an override to
        // a batch would be meaningless, so it is a single-site operation.
        if ((options.group !== undefined || options.commonGroup !== undefined) && targets.length !== 1) {
          console.error(
            chalk.red('--group and --common-group apply to a single site; name exactly one.')
          )
          process.exitCode = 1
          return
        }
        const parseGroup = (value: string | undefined, flag: string): number | undefined => {
          if (value === undefined) {
            return undefined
          }
          const parsed = Number.parseInt(value, 10)
          if (!Number.isInteger(parsed)) {
            throw new Error(`${flag} expects an integer group id, got "${value}"`)
          }
          return parsed
        }
        const groupOverride = parseGroup(options.group, '--group')
        const commonGroupOverride = parseGroup(options.commonGroup, '--common-group')
        if (groupOverride !== undefined || commonGroupOverride !== undefined) {
          const target = targets[0]!
          targets = [
            {
              ...target,
              i18nGroupId: groupOverride ?? target.i18nGroupId,
              i18nCommonGroupId: commonGroupOverride ?? target.i18nCommonGroupId,
            },
          ]
          logger.warning(
            `Overriding registered i18n groups for ${outputName(targets[0]!)}: ` +
              `group ${targets[0]!.i18nGroupId}, common ${targets[0]!.i18nCommonGroupId}`
          )
        }

        // The common group is shared by nearly every site, so read each group once.
        const groupCache = new Map<number, TranslationRow[]>()
        const loadGroup = async (groupId: number | null): Promise<TranslationRow[]> => {
          if (groupId === null) {
            return []
          }
          const cached = groupCache.get(groupId)
          if (cached) {
            return cached
          }
          const rows = await db.loadTranslationGroup(groupId)
          groupCache.set(groupId, rows)
          return rows
        }

        mkdirSync(outputRoot, { recursive: true })

        // The shared layer is a property of the common group alone, so it is
        // built once per group id and reused — which is also what makes it come
        // out byte-identical whether the run covers one site or all of them.
        const sharedCache = new Map<number, MessageCatalogue>()
        const sharedLayerFor = (groupId: number, commonRows: TranslationRow[]): MessageCatalogue => {
          let shared = sharedCache.get(groupId)
          if (shared === undefined) {
            // Merging the common group over nothing, rather than a second code
            // path, so the shared layer and the merged catalogue cannot
            // disagree about how a given row converts.
            const { messages: sharedMessages, stats: sharedStats } = mergeTranslationGroups(
              commonRows,
              []
            )
            shared = sharedMessages
            sharedCache.set(groupId, shared)

            const sharedDir = join(outputRoot, SHARED_ROOT, String(groupId))
            const sharedI18nDir = join(sharedDir, 'i18n')
            mkdirSync(sharedI18nDir, { recursive: true })

            writeJson(join(sharedDir, 'common.json'), {
              groupId,
              source: 'mwnf3.translation',
              rows: sharedStats.commonRows,
              contentFormat: 'markdown',
            })
            writeJson(join(sharedI18nDir, 'index.json'), buildLocaleIndex(sharedStats))
            for (const locale of sharedStats.locales) {
              writeJson(join(sharedI18nDir, `${locale}.json`), shared[locale])
            }

            logger.info(
              `Shared layer, group ${groupId}: ${sharedStats.locales.length} locale(s), ` +
                `${sharedStats.keysPerLocale['en'] ?? 0} English key(s)`
            )
          }
          return shared
        }

        const extracted: ExtractedSite[] = []

        for (const site of targets) {
          const commonRows = await loadGroup(site.i18nCommonGroupId)
          const siteRows = await loadGroup(site.i18nGroupId)

          const { messages, stats } = mergeTranslationGroups(commonRows, siteRows)
          const warnings = collectWarnings(site, commonRows, siteRows)

          const name = outputName(site)
          const siteDir = join(outputRoot, name)
          const i18nDir = join(siteDir, 'i18n')
          mkdirSync(i18nDir, { recursive: true })

          // A site with no common group has nothing to share and owns the lot;
          // the legacy API serves it nothing at all, which `collectWarnings`
          // already flags.
          const shared =
            layered && site.i18nCommonGroupId !== null
              ? sharedLayerFor(site.i18nCommonGroupId, commonRows)
              : {}
          const layers = layered ? splitLayers(messages, shared) : undefined

          if (layers !== undefined) {
            const failures = findLayerRoundTripFailures(messages, layers, shared)
            if (failures.length > 0) {
              throw new Error(
                `${name}: layered output would not reproduce the merged catalogue for ` +
                  `${failures.length} message(s) — ${failures.slice(0, 5).join(', ')}` +
                  (failures.length > 5 ? ', …' : '') +
                  '. This happens when the site group blanks a common message, which the ' +
                  'layered layout cannot express. Extract this site with --layout flat.'
              )
            }
          }

          writeJson(join(siteDir, 'site.json'), {
            galleryId: site.galleryId,
            kind: site.kind,
            thgProjectId: site.thgProjectId,
            mwnf3ProjectId: site.mwnf3ProjectId,
            slug: site.slug,
            host: site.host,
            name: site.name,
            status: site.status,
            i18n: {
              groupId: site.i18nGroupId,
              commonGroupId: site.i18nCommonGroupId,
              // The merge contract lives in the data rather than in prose: the
              // scaffold reads the shared layer named here, then overlays this
              // directory's own files, key by key within a locale.
              ...(layered
                ? {
                    extends:
                      site.i18nCommonGroupId === null
                        ? null
                        : `../${SHARED_ROOT}/${site.i18nCommonGroupId}`,
                  }
                : {}),
            },
            contentFormat: 'markdown',
          })

          writeJson(join(i18nDir, 'index.json'), buildLocaleIndex(stats, layers))
          const written = layers === undefined ? messages : layers.own
          for (const locale of Object.keys(written).sort()) {
            writeJson(join(i18nDir, `${locale}.json`), written[locale])
          }

          extracted.push({ site, messages, stats, warnings, layers })

          const recovered = stats.droppedByLegacyRightJoin.length
          const ownKeys = layers
            ? Object.values(layers.ownKeysPerLocale).reduce((total, n) => total + n, 0)
            : null
          logger.success(
            `${name}: ${stats.locales.length} locale(s), ` +
              `${stats.keysPerLocale['en'] ?? 0} English key(s), ` +
              `${stats.markdownConverted} converted to Markdown` +
              (ownKeys === null ? '' : `, ${ownKeys} own`) +
              (recovered > 0 ? `, ${recovered} recovered from the legacy RIGHT JOIN` : '')
          )
          for (const warning of warnings) {
            logger.warning(`${name}: ${warning}`)
          }
        }

        const generatedAt = new Date().toISOString()
        writeFileSync(
          join(outputRoot, 'extraction-report.md'),
          buildReport(extracted, generatedAt, options.layout),
          'utf8'
        )

        console.log('')
        logger.success(
          `Extracted ${extracted.length} site(s) to ${outputRoot} (${options.layout} layout)`
        )
        if (layered) {
          logger.info(
            `Merge order: ${SHARED_ROOT}/<commonGroupId>/i18n/<lang>.json, then <site>/i18n/<lang>.json`
          )
        }
        logger.info('Report: extraction-report.md')
      } catch (error) {
        console.error(chalk.red(`Extraction failed: ${(error as Error).message}`))
        process.exitCode = 1
      } finally {
        await db.disconnect()
      }
    }
  )

await program.parseAsync(process.argv)
