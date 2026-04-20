export interface ParsedExplorePreparedBy {
  authorName: string | null;
  textCopyEditorName: string | null;
  translatorName: string | null;
  translationCopyEditorName: string | null;
  preserveRawInExtra: boolean;
}

const STRUCTURED_PATTERN =
  /<\s*br\s*\/?\s*>|author\s*:|copy-?editor|copyeditor|translat(?:or|ion)\s*:|sulla base di materiale fornito da|©|museum with no frontiers|museo senza frontiere/i;
const LINE_BREAK_PATTERN = /<\s*br\s*\/?\s*>/gi;
const INLINE_ROLE_PATTERN = /\s+(?=(Author|Copy-?editor|Copyeditor|Translat(?:or|ion))\s*:)/gi;
const AUTHOR_PATTERN = /^author\s*:\s*(.+)$/i;
const COPY_EDITOR_PATTERN = /^copy-?editor(?:\s*\(([^)]+)\))?\s*:\s*(.+)$/i;
const TRANSLATOR_PATTERN = /^translat(?:or|ion)\s*:\s*(.+)$/i;
const ATTRIBUTION_PATTERN = /sulla base di materiale fornito da\s*:\s*(.+)?$/i;
const COPYRIGHT_PATTERN = /(©|museum with no frontiers|mwnf|museo senza frontiere)/i;

function normalizePreparedBy(value: string): string {
  return value
    .replace(/\r/g, '')
    .replace(LINE_BREAK_PATTERN, '\n')
    .replace(INLINE_ROLE_PATTERN, '\n')
    .replace(/\n{2,}/g, '\n')
    .trim();
}

function stripCopyright(value: string): string {
  return value.replace(/\s*©.*$/i, '').trim();
}

function cleanNameCandidate(value: string, splitOnComma: boolean): string | null {
  const withoutCopyright = stripCopyright(value);
  const firstSegment = withoutCopyright.split(';')[0]?.trim() ?? '';
  const primarySegment = splitOnComma ? (firstSegment.split(',')[0]?.trim() ?? '') : firstSegment;
  const normalized = primarySegment.replace(/\s+/g, ' ').trim();
  const cleaned = normalized.replace(/[;:,.]+$/g, '').trim();

  return cleaned !== '' ? cleaned : null;
}

function isEnglishCopyEditor(roleHint: string | undefined): boolean {
  return roleHint ? /\benglish\b/i.test(roleHint) : false;
}

export function parseExplorePreparedBy(
  rawPreparedBy: string | null | undefined
): ParsedExplorePreparedBy | null {
  if (!rawPreparedBy) {
    return null;
  }

  const trimmed = rawPreparedBy.trim();
  if (trimmed === '' || !STRUCTURED_PATTERN.test(trimmed)) {
    return null;
  }

  const parsed: ParsedExplorePreparedBy = {
    authorName: null,
    textCopyEditorName: null,
    translatorName: null,
    translationCopyEditorName: null,
    preserveRawInExtra: true,
  };

  const lines = normalizePreparedBy(trimmed)
    .split('\n')
    .map((line) => line.trim())
    .filter((line) => line !== '');

  for (const line of lines) {
    const authorMatch = line.match(AUTHOR_PATTERN);
    if (authorMatch?.[1] && !parsed.authorName) {
      parsed.authorName = cleanNameCandidate(authorMatch[1], false);
      continue;
    }

    const copyEditorMatch = line.match(COPY_EDITOR_PATTERN);
    if (copyEditorMatch?.[2]) {
      const candidate = cleanNameCandidate(copyEditorMatch[2], false);
      if (!candidate) {
        continue;
      }

      if (isEnglishCopyEditor(copyEditorMatch[1])) {
        parsed.translationCopyEditorName ??= candidate;
      } else {
        parsed.textCopyEditorName ??= candidate;
      }
      continue;
    }

    const translatorMatch = line.match(TRANSLATOR_PATTERN);
    if (translatorMatch?.[1] && !parsed.translatorName) {
      parsed.translatorName = cleanNameCandidate(translatorMatch[1], false);
      continue;
    }

    const attributionMatch = line.match(ATTRIBUTION_PATTERN);
    if (attributionMatch && !parsed.authorName) {
      const inlineCandidate = attributionMatch[1]
        ? cleanNameCandidate(attributionMatch[1], true)
        : null;

      if (inlineCandidate) {
        parsed.authorName = inlineCandidate;
      }
      continue;
    }

    if (COPYRIGHT_PATTERN.test(line)) {
      const candidate = cleanNameCandidate(line, true);
      if (!parsed.authorName && candidate) {
        parsed.authorName = candidate;
      }
      continue;
    }

    if (!parsed.authorName) {
      parsed.authorName = cleanNameCandidate(line, false);
    }
  }

  const hasResolvedRole =
    parsed.authorName ||
    parsed.textCopyEditorName ||
    parsed.translatorName ||
    parsed.translationCopyEditorName;

  return hasResolvedRole ? parsed : null;
}
