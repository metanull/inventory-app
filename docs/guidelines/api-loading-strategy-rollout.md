# API Loading Strategy Rollout (Includes + Pagination + Chaperone)

This guide describes exactly what to change, where, and how to implement a consistent data-loading strategy across the API using:
- Request-scoped includes for relationships
- Consistent pagination parameters
- Laravel 12 chaperone() on HasOne/HasMany/MorphMany to avoid inverse N+1s
- Resources that only serialize relations when loaded

It prioritizes concrete “what/how” steps and adds minimal “why” context to avoid regressions.

## Decisions and Conventions

- Pagination parameters: use HTTP-friendly names
  - page: 1-based page number (default 1)
  - per_page: items per page (default 20, max 100)
  - Rationale: `page` is Laravel’s default page param; `per_page` is language-agnostic and widely used in REST APIs. Avoid `page[size]` (non-standard outside PHP ecosystems).
- Includes parameter: include=rel1,rel2,rel3
  - Values validated against an allow-list per entity
  - Unknown includes → 422 Unprocessable Entity with a clear error message
- Model-level eager loading ($with): remove across models
  - Eager loading decisions move to controllers based on the include parameter
- Resources: use whenLoaded() for every relation
  - Never access a relation directly in resources (prevents implicit lazy loads)
- chaperone(): use on HasOne/HasMany/MorphMany to permit traverse back to the parent from children fetched via the parent relation
  - Use explicit inverse names for clarity: chaperone('country'), chaperone('partner'), chaperone('pictureable')

---

## Step 1 — Normalize Resource Classes

Make all Resource classes serialize relations only when loaded.

- For ItemResource, change artists and workshops to use whenLoaded:
  - Before: `ArtistResource::collection($this->artists)`, `WorkshopResource::collection($this->workshops)`
  - After: `ArtistResource::collection($this->whenLoaded('artists'))`, `WorkshopResource::collection($this->whenLoaded('workshops'))`
- Review all Resource classes to ensure every relation uses whenLoaded() or equivalent conditional collection.

Acceptance criteria
- No Resource directly touches a relation without whenLoaded().
- Serializing a Resource without includes does not trigger extra queries.

Files to update
- app/Http/Resources/ItemResource.php (artists, workshops)
- Scan remaining resources under app/Http/Resources for any relations not using whenLoaded.

---

## Step 2 — Define Includes Allow-Lists Per Entity

Establish per-entity lists of valid include keys matching relation names defined on models.

Suggested allow-lists (adjust as needed based on actual model relations):

- Item: partner, country, project, collection, artists, workshops, tags, translations, pictures, galleries
- Partner: country, items, pictures
- Country: items, partners
- Detail: item, translations, pictures, galleries
- Picture: translations, pictureable
- Project: context, language

Implementation options
- Central registry (recommended): App\\Support\\Includes\\AllowList::for(string $resource): array
- Per-controller constant: a private array ALLOWED_INCLUDES

Acceptance criteria
- A single source of truth for valid includes per entity
- Invalid include values are rejected with 422 and a helpful error

---

## Step 3 — Create an Include Parsing + Validation Helper

Add a small helper to parse, validate, and normalize includes:

Contract
- Input: Request, allowed includes (array of strings)
- Output: array of includes (strings) safe to pass to with()
- Behavior: trims whitespace, de-duplicates, validates against allow-list; throws ValidationException (422) on invalid values

Suggested file
- app/Support/Includes/IncludeParser.php

Suggested public API
- IncludeParser::fromRequest(Request $request, array $allowed): array
  - Parses `?include=a,b,c`
  - Returns [] when include is empty or missing

---

## Step 4 — Standardize Pagination Parameters

Apply consistent pagination parsing with bounds.

Contract
- Input: Request
- Output: [page, per_page] (ints)
- Defaults: page=1, per_page=20; Bounds: per_page 1..100
- Behavior: invalid values → 422

Suggested file
- app/Support/Pagination/PaginationParams.php

Suggested public API
- PaginationParams::fromRequest(Request $request): array{page:int, per_page:int}

---

## Step 5 — Update Controllers to Use Includes + Pagination

For every REST controller’s index/show/store/update:

- index():
  - Parse includes via IncludeParser using the allow-list for the entity
  - Parse pagination via PaginationParams
  - Build query: Model::query()->with($includes)
  - Return Resource::collection($query->paginate($perPage, ['*'], 'page', $page))

- show(Model $model):
  - Parse includes
  - $model->load($includes)
  - Return Resource($model)

- store()/update():
  - After save, parse includes
  - $model->refresh()->load($includes)
  - Return Resource($model)

Examples to apply (non-exhaustive)
- app/Http/Controllers/ItemController.php
- app/Http/Controllers/PartnerController.php
- app/Http/Controllers/CountryController.php
- Repeat for other entity controllers following the same pattern

Acceptance criteria
- All index routes paginate and accept include
- All show/store/update routes accept include and only return requested relations

---

## Step 6 — Remove Model-Level $with Across Models

Remove protected $with from models to avoid heavy defaults and redundant eager loads.

Targets (from current codebase)
- app/Models/Item.php (partner, country, project, artists, workshops, tags)
- app/Models/Detail.php (item)
- app/Models/Partner.php (country)
- app/Models/Project.php (context, language)

Order of operations
- Only remove $with after Step 1 (whenLoaded in resources) and Step 5 (controllers use includes) are complete for the affected entity

Acceptance criteria
- No protected $with remains in the above models
- API responses continue to include relations only when explicitly requested via include

---

## Step 7 — Expand chaperone() Usage Where It Adds Value

Add chaperone with explicit inverse names on relations where you often traverse back to the parent after fetching children via that parent.

Additions
- app/Models/Partner.php
  - pictures(): MorphMany → `->chaperone('pictureable')`
- app/Models/Item.php
  - pictures(): MorphMany → `->chaperone('pictureable')`
- app/Models/Detail.php
  - pictures(): MorphMany → `->chaperone('pictureable')`
- app/Models/Country.php
  - items(): HasMany → ensure `->chaperone('country')` (explicit)
  - partners(): HasMany → ensure `->chaperone('country')` (explicit)
- Optional (only if you traverse back to parent in workflows/serialization):
  - app/Models/Item.php → translations(): HasMany → `->chaperone('item')`
  - app/Models/Detail.php → translations(): HasMany → `->chaperone('detail')`
  - app/Models/Picture.php → translations(): HasMany → `->chaperone('picture')`

Notes
- Do not use chaperone on many-to-many relations
- chaperone complements includes; it prevents inverse N+1 and marks the inverse as loaded

Acceptance criteria
- chaperone is applied to HasOne/HasMany/MorphMany where inverse traversal is common
- Explicit inverse names are used for readability

---

## Step 8 — Feature Tests to Lock in Behavior

Add or update tests per entity according to existing project conventions (Feature tests under tests/Feature/{Entity}/):

General
- AnonymousTest.php — keep as-is
- IndexTest.php — assert pagination and minimal payload by default
- ShowTest.php — assert includes respected
- StoreTest.php, UpdateTest.php — assert includes respected in response

Specific test behaviors
- Index default minimal
  - GET /items without include → response data have relations absent or null; assert pagination metadata present
- Includes behavior
  - GET /items?include=partner,country → assert those relations are present; ensure bounded query count (no N+1)
- chaperone inverse linkage
  - For Country with partners: load partners via relation, serialize PartnerResource including country, assert no extra queries beyond the relation fetch
- Many-to-many includes
  - GET /items?include=artists,workshops,tags → assert included; index without include does not return them

Implementation tips
- Use RefreshDatabase + WithFaker
- Use factories to create data
- To assert queries: enable DB::enableQueryLog() and compare counts before/after serialization, or use a simple upper bound

Acceptance criteria
- Tests pass with model-level $with removed
- Tests fail if a resource accesses a not-loaded relation

---

## Step 9 — Documentation Updates

- Add a short section to docs/guidelines or CONTRIBUTING.md:
  - When to use chaperone (HasOne/HasMany/MorphMany with a clear inverse)
  - Request-scoped includes policy and allow-lists
  - Pagination parameter names and limits
  - No model-level $with by default; resources must use whenLoaded

---

## Step 10 — Rollout Plan and Order of Changes

Recommended sequence (commit-by-commit):

1) Resource normalization
- Update ItemResource (artists, workshops) and scan others for whenLoaded
2) Infrastructure helpers
- Add IncludeParser and PaginationParams helpers
- Add allow-lists per controller or a central registry
3) Controller updates (entity by entity)
- ItemController: add include + pagination
- PartnerController, CountryController; repeat for others
4) chaperone expansion
- Add chaperone to pictures (and optionally translations) with explicit inverse names
5) Remove $with (entity by entity)
- Remove only for entities whose controller/resources have been updated and covered by tests
6) Tests
- Add/adjust feature tests per entity to lock behavior
7) Docs
- Update CONTRIBUTING.md and guidelines

---

## Quick Reference — Code Skeletons

Include parser (conceptual)
- Input: Request with ?include=partner,country
- Output: ['partner','country'] or []

Controller index pattern
- includes = IncludeParser::fromRequest($request, ALLOWED_INCLUDES)
- {page, per_page} = PaginationParams::fromRequest($request)
- return Resource::collection(Model::query()->with(includes).paginate(per_page, ['*'], 'page', page))

Controller show/store/update pattern
- includes = IncludeParser::fromRequest(...)
- model->load(includes)
- return new Resource(model)

Resource relation pattern
- 'relation' => new RelationResource($this->whenLoaded('relation'))
- 'relation_many' => RelationResource::collection($this->whenLoaded('relation_many'))

Model relation with chaperone
- return $this->hasMany(Child::class)->chaperone('parentRelationName')
- return $this->morphMany(Picture::class, 'pictureable')->chaperone('pictureable')

---

## Quality Gates

Before opening a PR for each batch of changes:
- Backend lint: composer ci-lint
- Frontend lint (if applicable): npm run lint
- Tests: composer ci-test (or run via VS Code test runner)
- Ensure no remaining protected $with in updated models

---

## Notes and Caveats

- This change is API-affecting: clients that relied on implicit includes will need to request them explicitly via include
- Consider announcing the change and offering a deprecation window if necessary
- chaperone is complementary to includes; it does not replace eager loading for unrelated relations

---

## Acceptance Checklist (per entity)

- Resource uses whenLoaded for all relations
- Controller index/show/store/update support include and pagination
- chaperone applied where beneficial (HasOne/HasMany/MorphMany with clear inverse)
- Model has no protected $with
- Feature tests added/updated and passing
- Docs updated
