// Centralized helpers to build API query params for includes and pagination

export type IncludeParam = string[] | undefined

export interface PaginationOptions {
  page?: number
  perPage?: number
}

export interface IndexQueryOptions extends PaginationOptions {
  include?: IncludeParam
}

export interface ShowQueryOptions {
  include?: IncludeParam
}

// Default pagination values; keep aligned with backend defaults
export const DEFAULT_PAGE = 1
export const DEFAULT_PER_PAGE = 20

export const buildIncludes = (relations: IncludeParam): Record<string, string> => {
  if (!relations || relations.length === 0) return {}
  // Deduplicate and join with comma as API expects include=a,b,c
  const unique = Array.from(new Set(relations)).filter(r => r && r.trim().length > 0)
  if (unique.length === 0) return {}
  return { include: unique.join(',') }
}

export const buildPagination = (page?: number, perPage?: number): Record<string, number> => {
  const p = typeof page === 'number' && page > 0 ? page : DEFAULT_PAGE
  const pp = typeof perPage === 'number' && perPage > 0 ? perPage : DEFAULT_PER_PAGE
  return { page: p, per_page: pp }
}

export const mergeParams = (
  ...objs: Array<Record<string, unknown> | undefined>
): Record<string, unknown> => {
  return Object.assign({}, ...objs.filter(Boolean))
}

export interface PaginationMeta {
  total?: number
  current_page?: number
  per_page?: number
}

export interface PaginationState {
  page: number
  perPage: number
  total: number | null
}

// Type guard utility to detect and extract pagination meta from unknown response payloads
export const extractPaginationMeta = (payload: unknown): PaginationMeta | undefined => {
  if (
    typeof payload === 'object' &&
    payload !== null &&
    'meta' in (payload as Record<string, unknown>) &&
    typeof (payload as Record<string, unknown>).meta === 'object' &&
    (payload as Record<string, { [k: string]: unknown }>).meta !== null
  ) {
    const meta = (payload as { meta?: unknown }).meta as Record<string, unknown> | undefined
    if (!meta) return undefined
    const total = typeof meta.total === 'number' ? meta.total : undefined
    const current_page = typeof meta.current_page === 'number' ? meta.current_page : undefined
    const per_page = typeof meta.per_page === 'number' ? meta.per_page : undefined
    return { total, current_page, per_page }
  }
  return undefined
}
