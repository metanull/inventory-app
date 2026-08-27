/**
 * Human-readable report of an extraction run.
 *
 * Scaffolding a site is a one-shot operation whose output nobody re-derives, so
 * the run has to say what it did: which strings came from the site's own group,
 * which the legacy API was discarding, how thin the non-English coverage is, and
 * which sites carry registry damage that a human has to resolve.
 */
import type { ExtractedSite, SiteRegistryEntry } from './core/types.js'

const describeSite = (site: SiteRegistryEntry): string =>
  `${site.name} (gallery ${site.galleryId}, ${site.kind}, ${site.mwnf3ProjectId ?? 'no project'})`

export function buildReport(
  sites: ExtractedSite[],
  generatedAt: string,
  layout: string = 'flat'
): string {
  const lines: string[] = []

  lines.push('# Site i18n extraction report')
  lines.push('')
  lines.push(`Generated: ${generatedAt}`)
  lines.push('')
  lines.push(
    'Source: legacy `mwnf3.translation`, merged per site as common group + site group.',
    'Values are Markdown — the legacy strings are HTML fragments and are converted on the way out.'
  )
  lines.push('')
  lines.push(`Layout: \`${layout}\`.`)
  if (layout === 'layered') {
    lines.push('')
    lines.push(
      'Each site directory holds only the messages that site overrides or adds. The common',
      'group is written once under `_common/<groupId>/`. A site’s effective catalogue is',
      'that shared layer with the site’s own files overlaid, key by key within a locale.'
    )
  }
  lines.push('')

  lines.push('## Sites')
  lines.push('')
  lines.push('| Gallery | Slug | Kind | Project | Group | Common | Locales | Keys (en) |')
  lines.push('| --- | --- | --- | --- | --- | --- | --- | --- |')
  for (const { site, stats } of sites) {
    lines.push(
      `| ${site.galleryId} | ${site.slug ?? '—'} | ${site.kind} | ${site.mwnf3ProjectId ?? '—'} ` +
        `| ${site.i18nGroupId ?? '—'} | ${site.i18nCommonGroupId ?? '—'} ` +
        `| ${stats.locales.length} | ${stats.keysPerLocale['en'] ?? 0} |`
    )
  }
  lines.push('')

  const layered = sites.filter((s) => s.layers !== undefined)
  if (layered.length > 0) {
    lines.push('## Shared vs. own')
    lines.push('')
    lines.push(
      'What each site actually customises. A site with no own keys is served entirely by the',
      'common group — for an active gallery that is usually registry damage rather than a',
      'choice, so check the warnings below before scaffolding it.'
    )
    lines.push('')
    lines.push('| Site | Own keys | Locales with own keys | Keys owned |')
    lines.push('| --- | --- | --- | --- |')
    for (const { site, layers } of layered) {
      const own = layers!.ownKeysPerLocale
      const total = Object.values(own).reduce((sum, n) => sum + n, 0)
      const keys = [...new Set(Object.values(layers!.own).flatMap((m) => Object.keys(m)))].sort()
      lines.push(
        `| ${site.name} (${site.galleryId}) | ${total} ` +
          `| ${Object.keys(own).sort().join(' ') || '—'} ` +
          `| ${keys.map((k) => `\`${k}\``).join(', ') || '—'} |`
      )
    }
    lines.push('')

    const noOps = layered.filter((s) => s.stats.overriddenNoOp.length > 0)
    if (noOps.length > 0) {
      lines.push('### Overrides that change nothing')
      lines.push('')
      lines.push(
        'The site group restates these pairs with a value identical to the common group’s once',
        'converted. They are kept out of the site’s own files — a site that copies a shared',
        'message verbatim is not customising it — and listed here because a redundant row in',
        'the legacy data is worth knowing about.'
      )
      lines.push('')
      for (const { site, stats } of noOps) {
        lines.push(
          `- ${describeSite(site)}: ${stats.overriddenNoOp.length} — ` +
            stats.overriddenNoOp.map((label) => `\`${label}\``).join(', ')
        )
      }
      lines.push('')
    }
  }

  const withWarnings = sites.filter((s) => s.warnings.length > 0)
  if (withWarnings.length > 0) {
    lines.push('## Warnings')
    lines.push('')
    for (const { site, warnings } of withWarnings) {
      lines.push(`### ${describeSite(site)}`)
      lines.push('')
      for (const warning of warnings) {
        lines.push(`- ${warning}`)
      }
      lines.push('')
    }
  }

  lines.push('## Per-site detail')
  lines.push('')
  for (const { site, stats } of sites) {
    lines.push(`### ${describeSite(site)}`)
    lines.push('')
    lines.push(`- Host: ${site.host ?? '—'}`)
    lines.push(
      `- Rows read: ${stats.commonRows} common (group ${site.i18nCommonGroupId ?? '—'}), ` +
        `${stats.siteRows} site (group ${site.i18nGroupId ?? '—'})`
    )
    lines.push(
      `- Site group overrode ${stats.overridden.length} message(s)` +
        (stats.overriddenNoOp.length > 0
          ? ` (${stats.overriddenNoOp.length} with an identical value)`
          : '') +
        ` and added ${stats.added.length}`
    )
    lines.push(`- Converted from HTML to Markdown: ${stats.markdownConverted} message(s)`)
    if (stats.emptyKeyRows > 0) {
      lines.push(`- Skipped ${stats.emptyKeyRows} row(s) with an empty \`word_id\``)
    }
    if (stats.emptyValueRows > 0) {
      lines.push(
        `- Skipped ${stats.emptyValueRows} row(s) with an empty value, so vue-i18n falls back`
      )
    }
    if (stats.keysWithDots.length > 0) {
      lines.push(
        `- Keys containing \`.\` (vue-i18n reads these as message paths): ` +
          stats.keysWithDots.map((k) => `\`${k}\``).join(', ')
      )
    }
    lines.push(
      `- Coverage: ` +
        stats.locales.map((l) => `${l} ${stats.keysPerLocale[l]}`).join(', ')
    )
    if (stats.droppedByLegacyRightJoin.length > 0) {
      lines.push('')
      lines.push(
        `**Recovered from the legacy RIGHT JOIN** — ${stats.droppedByLegacyRightJoin.length} ` +
          `message(s) the legacy DXA API discards because the common group has no row for that ` +
          `key in that language:`
      )
      lines.push('')
      for (const label of stats.droppedByLegacyRightJoin) {
        lines.push(`- \`${label}\``)
      }
    }
    lines.push('')
  }

  return lines.join('\n')
}
