/**
 * Links a curator wrote into a gallery's or an exhibition's own pages.
 *
 * Legacy texts point at the site they were written for by its absolute
 * address — `https://exhibitions.museumwnf.org/water_in_islam/en/themes`. On
 * the website that replaces it those addresses send a visitor off the site;
 * what they mean is a page of the same site, which a hash router reaches as
 * `#/themes`. The rewrite belongs here, where the data is made: a data package
 * holds Markdown whose links work, and no website rewrites a text.
 *
 * Only a link into the same gallery is touched — the host and, for an
 * exhibition served as a path segment under a shared host, the slug have to
 * match. Every other link, including the ones into museumwnf.org, is left
 * exactly as the curator wrote it. The language segment legacy carried in the
 * path is dropped: the website negotiates its language once and keeps it in
 * the query.
 */

export interface GalleryAddress {
  /** The canonical public host, e.g. `https://carpets.museumwnf.org`. */
  host: string | null;
  /** The legacy slug, e.g. `water_in_islam`, a path segment on a shared host. */
  slug: string | null;
  /**
   * Whether the host is shared with other sites (the exhibitions host), in
   * which case only the slug under it is this site — a link to another slug
   * on the same host is another site's, and stays as written.
   */
  shared?: boolean;
}

const LANGUAGE_SEGMENT = /^[a-z]{2}(?:\/|$)/i;

function escapeRegExp(text: string): string {
  return text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

/**
 * The prefixes a same-site link can start with, longest first: the host with
 * the slug under it, then the host alone (a gallery on its own host).
 */
function prefixesOf(address: GalleryAddress): string[] {
  const host = (address.host ?? '').trim().replace(/\/+$/, '');
  if (!host) return [];
  const slug = (address.slug ?? '').trim().replace(/^\/+|\/+$/g, '');
  const bare = host.replace(/^https?:\/\//i, '');
  const prefixes: string[] = [];
  if (slug) prefixes.push(`${bare}/${slug}`);
  if (!address.shared || !slug) prefixes.push(bare);
  return prefixes;
}

/**
 * Rewrite every same-site absolute link in `text` (HTML or Markdown) into a
 * hash route: `https://host/slug/en/theme/3` → `#/theme/3`. Whitespace a
 * curator typed inside the address is consumed with it — a fragment does not
 * survive a leading space the way an http address does.
 */
export function localiseLegacyLinks(
  text: string | null | undefined,
  address: GalleryAddress
): string {
  if (!text) return '';
  const prefixes = prefixesOf(address);
  if (prefixes.length === 0) return text;

  const alternation = prefixes.map(escapeRegExp).join('|');
  const pattern = new RegExp(`\\s*https?://(?:${alternation})(?:/([^\\s"'<>)]*))?`, 'gi');

  return text.replace(pattern, (_match: string, rest: string | undefined) => {
    let path = rest ?? '';
    if (LANGUAGE_SEGMENT.test(path)) {
      path = path.replace(LANGUAGE_SEGMENT, '');
    }
    return `#/${path}`;
  });
}

interface AddressRows {
  query<T>(sql: string): Promise<T[]>;
}

/**
 * The address of every legacy gallery and exhibition: the canonical public
 * host from `thg_gallery_url` (galleries have one; exhibitions are served as
 * path segments under a shared host and have none of their own, so the
 * exhibitions host is the fallback) and the slug from `thg_gallery.link`.
 */
export async function loadGalleryAddresses(
  db: AddressRows,
  exhibitionsHost = 'https://exhibitions.museumwnf.org'
): Promise<Map<number, GalleryAddress>> {
  const galleries = await db.query<{ gallery_id: number; link: string | null }>(
    `SELECT gallery_id, link FROM mwnf3_thematic_gallery.thg_gallery`
  );
  const hosts = await db.query<{ gallery_id: number; link: string | null }>(
    `SELECT gallery_id, link FROM mwnf3_thematic_gallery.thg_gallery_url
     WHERE link IS NOT NULL AND link != ''`
  );
  const hostById = new Map(hosts.map(row => [row.gallery_id, row.link]));
  const addresses = new Map<number, GalleryAddress>();
  for (const gallery of galleries) {
    const own = hostById.get(gallery.gallery_id);
    addresses.set(gallery.gallery_id, {
      host: own ?? exhibitionsHost,
      slug: gallery.link,
      shared: !own,
    });
  }
  return addresses;
}

/**
 * Convert one legacy text to Markdown *and* localise its links, in that
 * order: the conversion sees the addresses as the curator wrote them, and the
 * rewrite sees the Markdown link destinations the conversion produced.
 */
export function localiseConverted(
  convert: (html: string | null | undefined) => string,
  text: string | null | undefined,
  address: GalleryAddress | undefined
): string {
  const markdown = convert(text);
  return address ? localiseLegacyLinks(markdown, address) : markdown;
}
