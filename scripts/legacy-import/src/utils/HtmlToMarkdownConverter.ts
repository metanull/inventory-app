/**
 * HTML to Markdown Converter
 * Uses Turndown library for robust HTML to Markdown conversion
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
    // Fallback: if Turndown fails, return the original text with HTML tags stripped
    console.warn('HTML to Markdown conversion failed:', error);
    return trimmed.replace(/<[^>]+>/g, '').trim();
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
