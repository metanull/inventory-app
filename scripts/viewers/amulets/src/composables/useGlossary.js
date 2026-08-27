import { glossaryById, translations } from './useGalleryData.js'

// The item sheet links glossary terms inside its own description text, which is
// why the package ships a spelling list per term per language rather than a
// single headword: legacy built one regex per spelling and rewrote the rendered
// HTML in place (DatabaseItem.vue), including a distinct Arabic variant, since
// `\b` does not fire between Arabic letters — it used the character-class
// negation `[^،-٩]` instead.

const escape = (s) => s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')

/**
 * Glossary terms reachable from one item, with their spellings in `lang`.
 * Falls back to the English spellings when the term has no row in `lang`.
 */
export function termsForItem(item, lang) {
  const out = []
  for (const id of item?.glossary_ids ?? []) {
    const entry = glossaryById.value.get(id)
    if (!entry) continue
    const t = translations('glossary', lang)[id] ?? translations('glossary', 'en')[id] ?? {}
    const spellings = (t.spellings ?? []).map(s => String(s).trim()).filter(Boolean)
    out.push({
      id,
      word: entry.word,
      definition: t.definition ?? '',
      spellings: spellings.length ? spellings : [entry.word],
    })
  }
  return out
}

/**
 * Wrap every glossary spelling found in `html` with a clickable anchor.
 * Only text outside existing tags is touched, so markup already in the
 * description survives untouched.
 */
export function linkGlossary(html, terms, lang) {
  if (!html || !terms.length) return html ?? ''

  // One alternation for every spelling of every term, longest first so
  // "Ayat al-Kursi" wins over "Ayat". A single pass matters: replacing term by
  // term would let a later pattern match inside the anchor an earlier one had
  // just inserted.
  const byText = new Map()
  for (const term of terms) {
    for (const spelling of term.spellings) {
      const key = spelling.toLowerCase()
      if (!byText.has(key)) byText.set(key, term.id)
    }
  }
  const spellings = [...byText.keys()].sort((a, b) => b.length - a.length)
  if (!spellings.length) return html
  const alternation = spellings.map(escape).join('|')

  const arabic = lang === 'ar'
  const re = arabic
    ? new RegExp(`(^|[^،-٩])(${alternation})(?=[^،-٩]|$)`, 'gi')
    : new RegExp(`\\b(${alternation})(s?)\\b`, 'gi')

  const anchor = (core, suffix = '') => {
    const id = byText.get(core.toLowerCase())
    if (!id) return `${core}${suffix}`
    return `<a href="#" class="glossary-link" data-term="${id}">${core}${suffix}</a>`
  }

  // Split on tags so only text nodes are rewritten; markup already in the
  // description survives untouched.
  return html.split(/(<[^>]+>)/g).map(segment => {
    if (segment.startsWith('<')) return segment
    return arabic
      ? segment.replace(re, (_m, lead, core) => `${lead}${anchor(core)}`)
      : segment.replace(re, (_m, core, plural) => anchor(core, plural ?? ''))
  }).join('')
}

/** Every glossary term in the package, for the standalone glossary tool. */
export function searchGlossary(input, lang) {
  const needle = (input ?? '').trim().toLowerCase()
  if (!needle) return []
  const rows = translations('glossary', lang)
  const fallback = translations('glossary', 'en')
  const out = []
  for (const [id, entry] of glossaryById.value) {
    const t = rows[id] ?? fallback[id] ?? {}
    const spellings = t.spellings?.length ? t.spellings : [entry.word]
    for (const spelling of spellings) {
      if (String(spelling).toLowerCase().startsWith(needle)) {
        out.push({ id, spelling: String(spelling).trim(), definition: t.definition ?? '' })
        break
      }
    }
  }
  return out.sort((a, b) => a.spelling.localeCompare(b.spelling))
}
