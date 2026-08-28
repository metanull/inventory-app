import { ref, computed } from 'vue'
import { md, mdInline } from './useGalleryData.js'

// UI strings come from the site-i18n catalogues vendored into src/i18n — never
// from the data package (decision G3). Each file is the shared MWNF Galleries
// layer (group 59) with the Carpets site's own keys overlaid; regenerate them
// with `npm run sync-i18n`.
//
// Only the messages legacy actually has in a language are present: the files
// are deliberately not padded with English, so "missing" and "English on
// purpose" stay distinguishable. English is the fallback, as it was in the
// legacy client.
const catalogues = import.meta.glob('../i18n/*.json', { eager: true })

const messages = {}
for (const [path, mod] of Object.entries(catalogues)) {
  const lang = path.slice(path.lastIndexOf('/') + 1, -'.json'.length)
  messages[lang] = mod.default ?? mod
}

const FALLBACK = 'en'

// The chrome language. Legacy's dxa-client pinned this to English
// (`loadLocaleMessages("en")` in App.vue) and switched languages only on the
// item sheet and the partner profile, where the *record* carries the language.
// This viewer keeps that behaviour but lets the chrome follow along when the
// visitor picks one of the gallery's four UI languages.
export const uiLang = ref(FALLBACK)

export const RTL_LANGUAGES = new Set(['ar', 'he', 'fa', 'ur'])

export function isRtl(lang) {
  return RTL_LANGUAGES.has(lang)
}

export function setUiLang(lang) {
  uiLang.value = messages[lang] ? lang : FALLBACK
  const html = document.documentElement
  html.setAttribute('lang', uiLang.value)
  html.setAttribute('dir', isRtl(uiLang.value) ? 'rtl' : 'ltr')
}

/** Raw catalogue lookup for an explicit language (the item sheet's labels). */
export function tIn(lang, key) {
  return messages[lang]?.[key] ?? messages[FALLBACK]?.[key] ?? key
}

/** Catalogue lookup in the current chrome language. */
export function t(key) {
  return tIn(uiLang.value, key)
}

/** An editorial page (Markdown in the catalogue) rendered to HTML. */
export function tHtml(key) {
  return md(tIn(uiLang.value, key))
}

/**
 * Whether the current chrome language actually carries this message, or the
 * English fallback is standing in for it. Most of the Arabic, Spanish and
 * French catalogues are a single key deep — the long editorial pages exist in
 * English only — so a fallback is the normal case, not an error.
 */
export function isTranslated(key) {
  return Object.prototype.hasOwnProperty.call(messages[uiLang.value] ?? {}, key)
}

/**
 * Text direction for one message: the page may be RTL while the text actually
 * shown is the English fallback, and English set right-to-left reads badly
 * (trailing punctuation jumps to the front of the line).
 */
export function dirFor(key) {
  return isTranslated(key) && isRtl(uiLang.value) ? 'rtl' : 'ltr'
}

export function tInline(key) {
  return mdInline(tIn(uiLang.value, key))
}

export const rtl = computed(() => isRtl(uiLang.value))

export function useUiStrings() {
  return { t, tIn, tHtml, tInline, uiLang, setUiLang, rtl, isRtl, isTranslated, dirFor }
}
