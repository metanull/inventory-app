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
      `Failed to convert HTML to Markdown: ${error instanceof Error ? error.message : String(error)}`,
      { cause: error }
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
 * Fields holding a JSON document rather than a text.
 *
 * Converting one of these as a whole would destroy it — Turndown would be
 * handed braces and quotes and asked to read them as markup. They are instead
 * decoded and converted value by value, because the texts inside them are
 * legacy content like any other: the curator justifications in a collection
 * item's `extra` reached three websites with their `<i>` tags intact, for the
 * years this field was skipped outright.
 */
const JSON_FIELDS = new Set(['extra']);

/**
 * Convert every string inside a decoded JSON value, leaving the shape, the
 * keys and the non-string values exactly as they were.
 */
function convertJsonValue(value: unknown): unknown {
  if (typeof value === 'string') {
    return convertHtmlToMarkdown(value);
  }
  if (Array.isArray(value)) {
    return value.map(convertJsonValue);
  }
  if (value !== null && typeof value === 'object') {
    return Object.fromEntries(
      Object.entries(value as Record<string, unknown>).map(([key, item]) => [
        key,
        convertJsonValue(item),
      ])
    );
  }
  return value;
}

/**
 * Convert the texts inside a JSON field, returning it re-encoded.
 *
 * Anything that does not decode is returned untouched. A field that is not
 * JSON after all is a separate problem, and mangling it here would hide it.
 */
export function sanitizeJsonField(json: string): string {
  let decoded: unknown;
  try {
    decoded = JSON.parse(json);
  } catch {
    return json;
  }
  if (decoded === null || typeof decoded !== 'object') {
    return json;
  }
  return JSON.stringify(convertJsonValue(decoded));
}

/**
 * MySQL zero-date patterns produced by legacy Windows MySQL.
 * MySQL 8+ with STRICT_TRANS_TABLES + NO_ZERO_DATE rejects these on INSERT.
 */
const ZERO_DATE_PATTERN = /^0000-00-00/;

/**
 * Sanitize a date value from the legacy database.
 *
 * mysql2 returns DATETIME columns as Date objects by default.
 * Legacy MySQL on Windows stored '0000-00-00 00:00:00' as a default,
 * which mysql2 converts to an Invalid Date object.
 *
 * This function explicitly handles:
 * - null / undefined → null
 * - empty string → null
 * - Date object that is Invalid Date → null
 * - string matching '0000-00-00...' → null
 * - valid Date object → ISO string (YYYY-MM-DD HH:mm:ss)
 * - valid date string → returned as-is
 */
export function sanitizeDateValue(value: Date | string | null | undefined): string | null {
  if (value === null || value === undefined) {
    return null;
  }

  if (value instanceof Date) {
    if (isNaN(value.getTime())) {
      return null;
    }
    return value.toISOString().slice(0, 19).replace('T', ' ');
  }

  if (typeof value === 'string') {
    const trimmed = value.trim();
    if (trimmed === '' || ZERO_DATE_PATTERN.test(trimmed)) {
      return null;
    }
    return trimmed;
  }

  return null;
}

/**
 * Sanitize ALL string fields in an object by converting HTML to Markdown
 *
 * This function iterates over all properties of the object and converts
 * any string value from HTML to Markdown. This is safe because:
 * - convertHtmlToMarkdown returns strings unchanged if they contain no HTML tags
 * - The conversion is idempotent (calling it twice produces the same result)
 *
 * Use this as a catch-all sanitizer at the persistence layer to ensure
 * no HTML content reaches the database.
 *
 * Note: fields in JSON_FIELDS (like 'extra') hold a JSON document, so they are
 * decoded and converted value by value rather than as one string.
 *
 * @param data The data object to sanitize
 * @returns A new object with all string fields converted from HTML to Markdown
 */
export function sanitizeAllStrings<T extends object>(data: T): T {
  const result = { ...data };

  for (const key of Object.keys(result) as (keyof T)[]) {
    const value = result[key];
    if (typeof value !== 'string') {
      continue;
    }
    (result as Record<string, unknown>)[key as string] = JSON_FIELDS.has(key as string)
      ? sanitizeJsonField(value)
      : convertHtmlToMarkdown(value);
  }

  return result;
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
