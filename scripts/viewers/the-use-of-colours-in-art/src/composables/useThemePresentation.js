import {
  itemById, itemLabel, partnerLabel, countryLabel, tr, defaultLang,
  themePictures,
} from './useExhibitionData.js'

// Shared between the themes list, the theme page and the theme gallery.
//
// A theme's selections point at `picture` items, which are NOT members of the
// exhibition and therefore not in items.json — only their parents are. Every
// label a theme page shows (name, holding museum, location, country) is
// therefore read off the parent record, which is also what "see the full
// record" links to. `parent_in_package` says whether that parent is resolvable
// at all; the exporter reports one picture in this exhibition where it is not.

/** The parent record of a picture selection, or null when it is not a member. */
export function pictureParent(picture) {
  if (!picture?.parent_in_package) return null
  return itemById.value.get(picture.parent_item_id) ?? null
}

/** Legacy's `itemDetailString`: museum, location, country — blanks dropped. */
export function itemDetailString(item) {
  if (!item) return ''
  const sheet = tr('items', item.id, defaultLang)
  return [
    sheet.holder || partnerLabel(item.partner_id),
    sheet.location,
    countryLabel(item.country_id),
  ].filter(Boolean).join(', ')
}

/** The one-line caption legacy renders under a theme cover and on hover. */
export function pictureCaption(picture) {
  const parent = pictureParent(picture)
  if (!parent) return ''
  return [itemLabel(parent), itemDetailString(parent)].filter(Boolean).join(', ')
}

/** The cover picture of a theme, falling back to its first selection. */
export function themeCover(theme) {
  const pictures = themePictures(theme)
  if (!pictures.length) return null
  return (
    pictures.find(p => p.picture_item_id === theme?.cover_picture_item_id) ??
    pictures[0]
  )
}

/** Legacy's `truncate`: cut on the last space before `chars`, then ellipsis. */
export function truncate(chars, text) {
  if (!text) return ''
  if (text.length <= chars) return text
  return `${text.slice(0, text.lastIndexOf(' ', chars))}...`
}
