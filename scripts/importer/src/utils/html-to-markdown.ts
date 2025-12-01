/**
 * HTML to Markdown Converter
 *
 * Uses Turndown library for robust HTML to Markdown conversion.
 * This is shared business logic used by all importers.
 */
import TurndownService from 'turndown';

// Create a singleton instance of TurndownService
const turndownService = new TurndownService({
  headingStyle: 'atx',
  hr: '---',
  bulletListMarker: '-',
  codeBlockStyle: 'fenced',
  emDelimiter: '*',
  strongDelimiter: '**',
});

/**
 * Convert HTML string to Markdown using Turndown
 * Robust parser that handles malformed HTML gracefully
 */
export function convertHtmlToMarkdown(html: string | null | undefined): string {
  if (!html || typeof html !== 'string') {
    return '';
  }

  const trimmed = html.trim();

  // Return early if no HTML tags detected
  if (!trimmed.includes('<')) {
    return trimmed;
  }

  try {
    // Use Turndown to convert HTML to Markdown
    const markdown = turndownService.turndown(trimmed);
    return markdown.trim();
  } catch (error) {
    // Re-throw with more context instead of falling back to unsafe operations
    throw new Error(
      `Failed to convert HTML to Markdown: ${error instanceof Error ? error.message : String(error)}`
    );
  }
}

/**
 * Convert HTML fields in an object to Markdown
 * @param obj Object containing fields that may have HTML
 * @param htmlFields Array of field names that contain HTML
 * @returns New object with HTML fields converted to Markdown
 */
export function convertHtmlFieldsToMarkdown<T extends Record<string, unknown>>(
  obj: T,
  htmlFields: string[]
): T {
  const result: Record<string, unknown> = { ...obj };

  htmlFields.forEach((field) => {
    if (field in result && typeof result[field] === 'string') {
      result[field] = convertHtmlToMarkdown(result[field] as string);
    }
  });

  return result as T;
}

/**
 * Strip all HTML tags and return plain text
 * Uses Turndown to convert HTML to markdown, then strips markdown formatting
 * This is more robust than regex-based HTML stripping
 */
export function stripHtml(html: string | null | undefined): string {
  if (!html || typeof html !== 'string') {
    return '';
  }

  // First convert HTML to markdown using Turndown (handles malformed HTML gracefully)
  const markdown = convertHtmlToMarkdown(html);

  // Strip markdown formatting to get plain text
  return markdown
    .replace(/^#+\s+/gm, '') // Remove heading markers
    .replace(/\*\*(.+?)\*\*/g, '$1') // Remove bold
    .replace(/\*(.+?)\*/g, '$1') // Remove italic
    .replace(/__(.+?)__/g, '$1') // Remove bold (underscore)
    .replace(/_(.+?)_/g, '$1') // Remove italic (underscore)
    .replace(/~~(.+?)~~/g, '$1') // Remove strikethrough
    .replace(/`(.+?)`/g, '$1') // Remove inline code
    .replace(/```[\s\S]*?```/g, '') // Remove code blocks
    .replace(/\[(.+?)\]\(.+?\)/g, '$1') // Convert links to text
    .replace(/!\[.*?\]\(.+?\)/g, '') // Remove images
    .replace(/^[*\-+]\s+/gm, '') // Remove list markers
    .replace(/^\d+\.\s+/gm, '') // Remove ordered list markers
    .replace(/^>\s+/gm, '') // Remove blockquotes
    .replace(/---/g, '') // Remove horizontal rules
    .replace(/\n{3,}/g, '\n\n') // Normalize multiple newlines
    .trim();
}
