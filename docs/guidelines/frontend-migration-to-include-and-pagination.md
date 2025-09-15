# Frontend Migration Guide — Adapting to Includes + Pagination (Vue 3 + TypeScript)

This document is a precise “what/how” guide to adapt the Vue.js + TypeScript frontend and tests after regenerating and upgrading the TypeScript API client. It assumes the backend now:
- Uses `include=...` to control related data loading
- Paginates index routes using `page` and `per_page`
- Removes model-level `$with` so relations must be explicitly requested

Follow these steps in order. Changes are grouped by concern with concrete file targets and acceptance criteria.

---

## Step 0 — Upgrade the API Client

- Bump the dependency `@metanull/inventory-app-api-client` to the new version in `package.json`.
- Run `npm ci`.
- Perform a quick type check build to reveal breaking signature changes.

Acceptance criteria
- The project compiles; any new required params (include, pagination) are discoverable in API method signatures or via optional `params` argument.

---

## Step 1 — Centralize Common Query Params

Add a tiny helper to build request params for includes and pagination.

Suggested file
- `resources/js/utils/apiQueryParams.ts`

Contents (conceptual)
- `buildIncludes(relations: string[]): Record<string, string>` → `{ include: 'a,b,c' }` or `{}`
- `buildPagination(page?: number, perPage?: number): Record<string, number>` → `{ page, per_page }` with defaults from app config (e.g., page=1, per_page=20)
- `mergeParams(...objs: Array<Record<string, unknown>>)` → shallow merge into one object

Usage pattern
- `const params = mergeParams(buildIncludes(['partner','country']), buildPagination(page, perPage))`
- Pass as the last `params` argument for generated client methods that support it, e.g., `api.itemIndex(/* ... */ paramsConfig)` (shape depends on the regenerated client; see typed signatures after regen).

Acceptance criteria
- All calls to index/show endpoints use these helpers instead of hard-coded params or implicit defaults.

---

## Step 2 — Refactor Stores to Use Includes + Pagination

Targets
- `resources/js/stores/item.ts`
- `resources/js/stores/partner.ts`
- `resources/js/stores/country.ts`
- Repeat for other entity stores as needed

What to change
- For index/list methods (e.g., `fetchItems`, `fetchPartners`, `fetchCountries`):
  - Accept `options?: { include?: string[]; page?: number; perPage?: number }`
  - Build params via helpers from Step 1
  - Call the appropriate API index method with params
  - Store pagination metadata if returned by the API (e.g., total, current_page, per_page)
- For show methods (e.g., `fetchItem`, `fetchPartner`, `fetchCountry`):
  - Accept `options?: { include?: string[] }`
  - Build params and pass to the show method
- For create/update methods:
  - After save, reload with the same include params when setting current entity

Concrete examples
- `item.ts`:
  - `fetchItems({ include = [], page = 1, perPage = 20 } = {})`
  - Typical includes: `['partner', 'country']` for list screens; leave empty for minimal
- `partner.ts`:
  - `fetchPartners({ include = ['country'], page = 1, perPage = 20 } = {})` (common UI need)
- `country.ts`:
  - For admin UIs, defaults may be empty includes; when showing details, pass `include=['items','partners']` selectively

Acceptance criteria
- All store methods accept options for includes/pagination and pass params to the client
- Lists use pagination in state (page, perPage, total) and UIs can render page controls
- No store assumes relations are preloaded; data presence depends on requested includes

---

## Step 3 — Update Components and Views

- Replace any direct assumptions that related data are always present.
- For lists, add pagination controls bound to the store (page, perPage) and trigger re-fetch on change.
- For detail pages, pass includes that the view actually needs (e.g., item detail might include `partner,country,tags`).
- Guard rendering with optional chaining or v-if when relations may be absent.

Acceptance criteria
- No component crashes due to missing nested fields when includes are omitted
- Pagination controls exist where lists are large; page changes re-fetch data with correct params

---

## Step 4 — Synchronize Tests (Vitest)

Targets
- `resources/js/__tests__/feature/` and `resources/js/__tests__/integration/`

Changes
- Update API client mocks to respect include and pagination params
- For list tests:
  - Default: expect minimal payload (no relations) unless include is provided
  - With include: expect relations present in payload
  - With pagination: ensure the store updates `page`, `perPage`, and handles returned `meta`/`links` if present
- For detail tests:
  - Without include: relations absent
  - With include: relations present

Acceptance criteria
- Tests don’t assume eager-loaded relations; they explicitly request includes when needed
- Tests cover a list + include + pagination scenario per entity store

---

## Step 5 — API Client Usage Patterns

Generated clients may have different signatures; typical OpenAPI generator patterns:
- `index`/`list` signatures often accept an Axios config with `params`, e.g., `{ params: { include: 'a,b', page: 1, per_page: 20 } }`
- If the generator exposes query params as explicit arguments, use them accordingly (inspect the regenerated client types)

Ensure
- All index/show methods pass params for include/pagination
- Avoid hardcoding URL strings; use the client methods only

---

## Step 6 — Handle Breaking UI/UX Changes

- Lists are now paginated — add total count and page controls
- If screens previously relied on implicit relations, decide per view what to include by default to keep UX stable
- Consider centralizing per-view default includes (e.g., a map in a constants file) to keep the choice consistent

---

## Step 7 — Developer Ergonomics

- Add TypeScript types for pagination state (e.g., `{ page: number; perPage: number; total?: number }`)
- Expose typed store methods that default sensible includes per screen but still allow overrides
- Create a tiny `useIncludes()` composable to hold common include presets per entity/view

---

## Step 8 — Regression Checklist

- No store or component assumes relations are always present
- All list views fetch with pagination and render controls
- All detail views request the minimal includes they need
- Tests validate include and pagination behavior explicitly

---

## File-by-File Quick Plan

- Add: `resources/js/utils/apiQueryParams.ts`
- Update: `resources/js/stores/item.ts` (method signatures, params, pagination state)
- Update: `resources/js/stores/partner.ts` (accept includes/pagination; default include country for lists if needed)
- Update: `resources/js/stores/country.ts` (accept includes/pagination)
- Update: Views/components that render nested data — guard and request includes
- Update: Tests under `resources/js/__tests__/` to assert minimal-by-default + include/pagination cases

---

## Optional Enhancements

- Introduce an `includes.ts` constants file with per-view presets:
  - ItemsList: `['partner','country']`
  - ItemDetail: `['partner','country','project','tags']`
  - PartnersList: `['country']`
- Add an Axios request interceptor to inject default `per_page` if missing

---

## Quality Gates

- Lint: `npm run lint`
- Unit tests: `npm run test`
- Integration tests: `npm run test:integration`

Ensure no TypeScript `any` or unused variables, and that all tests pass with the new include/pagination behavior.

---

## Rollout Order (Frontend)

1) Upgrade client and add `apiQueryParams.ts`
2) Update stores to accept include/pagination options
3) Update components to guard relations and add pagination controls
4) Update tests to reflect new behavior
5) Run lint + tests; fix issues

This completes the migration to the new API contract while keeping the UI predictable and performant.
