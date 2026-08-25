/**
 * HTML to Markdown conversion.
 *
 * The legacy UI strings are HTML fragments — the legacy client renders them with
 * `v-html`. Markup is not an accepted content format anywhere in this project:
 * the importer converts every legacy string to Markdown before it reaches the
 * database, and the strings this tool extracts follow the same rule so that a
 * scaffolded site renders Markdown like every other MWNF surface.
 *
 * The Turndown options are deliberately identical to
 * `scripts/importer/src/utils/html-to-markdown.ts`, so the same legacy fragment
 * produces the same Markdown whichever pipeline it travels through.
 */
import TurndownService from 'turndown'

const turndownService = new TurndownService({
  headingStyle: 'atx',
  hr: '---',
  bulletListMarker: '-',
  codeBlockStyle: 'fenced',
  emDelimiter: '*',
  strongDelimiter: '**',
})

/** True when the string carries markup that conversion would rewrite. */
export function containsMarkup(value: string): boolean {
  return value.includes('<')
}

/**
 * Convert an HTML string to Markdown.
 *
 * Strings with no markup are returned trimmed and otherwise untouched, which
 * makes the conversion idempotent and safe to apply to every value.
 */
export function convertHtmlToMarkdown(html: string | null | undefined): string {
  if (!html || typeof html !== 'string') {
    return ''
  }

  const trimmed = html.trim()

  if (!containsMarkup(trimmed)) {
    return trimmed
  }

  try {
    return turndownService.turndown(trimmed).trim()
  } catch (error) {
    throw new Error(
      `Failed to convert HTML to Markdown: ${error instanceof Error ? error.message : String(error)}`,
      { cause: error }
    )
  }
}
