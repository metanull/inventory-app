---
applyTo: "**/*.php"
---
# Project coding standards and conventions for PHP

> **Active milestone — Milestone 3 (Filament 3 migration)**
>
> `/admin` (Filament 3) is the **main UI** — not a restricted back-office. See `.github/copilot-instructions.md` for the three-tier authorization model and the self-service scope.
>
> **Do not** author new code against `IndexListRequest`, `{Entity}IndexQuery`, `SearchAndPaginate`, `SearchableSelect`, Livewire components, web-scoped Form Requests, or `/web/*` controllers. These describe the **legacy** stack being removed in EPIC 12. Prefer a Filament `Resource`, `RelationManager`, `Page`, `Action`, or `Widget`.
>
> New tests for Filament code go under `tests/Filament/{Resources,Pages,Panel,Authorization}/`. Never extend `tests/Web/`.

## General Guidelines

- **CRITICAL: Strictly follow Laravel 12 guidelines and recommendations.**
- **CRITICAL: Strictly follow Laravel 12 directory structure.**
- Organize code in a logical and consistent directory structure.
- Keep code (functions, classes, methods) simple and focused on a single behavior.
- Use well balanced if-else statements, ensuring all branches are covered.
- Always handle exceptions gracefully.
- Use try-catch blocks for asynchronous operations.
- Avoid using hard-coded values; use configuration files instead.
- Always log errors with meaningful messages.
- Use Laravel Form Request classes for request validation.
- Use PHPDoc and Scramble annotations for documentation.
    - Use dedoc/Scramble annotations for Controller methods.
    - Keep annotations, clear, concise and meaningful.
- Use comments in code to:
    - explain complex logic.
    - clarify the purpose of a function or method.
    - provide context for non-obvious decisions.
- Do not over-comment obvious code.
- Only add comments related to the code or the business logic.
- **CRITICAL: Never use vendor specific code when Laravel's framework offers built-in features.**
- **CRITICAL: Never use low level php function when Laravel's framework offers higher level abstractions.**
- **CRITICAL: Always use Laravel framework's built-in feature to access storage files, such as Flysystem and `Storage::disk('local')->put('file.txt', 'contents')` instead of using the filesystem directly.**
- **CRITICAL: Always use Framework's built-in feature to access configuration files, such as `config('app.name')` instead of using the filesystem directly.**

## Naming Conventions
- **CRITICAL: Use Laravel 12 naming conventions.**
- Use meaningful names that describe purpose and functionality.
- Use consistent naming conventions for variables, functions, classes, and methods.
    - Comply with the PSR-12 coding standard.
    - Comply with the PSR-4 autoloading standard.
- Case conventions:
    - **CRITICAL: When Laravel 12 has specific conventions, follow them strictly.**
    - If no specific Laravel 12 convention exists, use the following:
        - PHP class files follow PSR-4 and use PascalCase to match class names. Use `snake_case` for config and migration filenames only.
        - `snake_case` for database columns and table names.
        - `kebab-case` for URLs and routes.
        - `snake_case` for configuration files.
        - `camelCase` for variable and function names.
        - `PascalCase` for class names and methods.
        - `UPPER_CASE` for constants.

## Code Quality

- **CRITICAL: Strictly verify PHP code quality and formatting using Pint.**
- Never ignore lint errors and warnings.
- Never ignore failing tests.

## Web List Pages — Request-Driven Pattern (the only approved approach)

Every web index (`index()`) action must follow the request-driven list pattern. Do **not** use any other approach.

### Required pieces

| Piece | Role |
|---|---|
| `App\Http\Requests\Web\Index{Entity}Request` | Extends `IndexListRequest`; declares allowed sort columns, default sort, and allowed filters. |
| `App\Services\Web\{Entity}IndexQuery` | Encapsulates the Eloquent query; receives a `ListState` and returns a paginator. |
| `App\Support\Web\Lists\ListDefinition` | (base class) Wired via `IndexListRequest` — provides `listState()` to the controller. |
| Blade view | Receives the paginator and `$listState` — **no Eloquent calls inside the view**. |

### Canonical reference implementation

- Controller: `app/Http/Controllers/Web/ItemController::index()`
- Request: `app/Http/Requests/Web/IndexItemRequest`
- Query service: `app/Services/Web/ItemIndexQuery`
- Blade view: `resources/views/items/index.blade.php`

### How to add a new web list page

1. Create `app/Http/Requests/Web/Index{Entity}Request` extending `IndexListRequest`.
2. Declare `$allowedSorts`, `$defaultSort`, and `$allowedFilters` on the request.
3. Create `app/Services/Web/{Entity}IndexQuery` with a `paginate(ListState $state)` method.
4. In the controller `index()`, inject both the request and the query service, call `$request->listState()`, and pass the result to the query service.
5. Pass `$listState` to the Blade view — never run Eloquent queries inside a view.

### Forbidden patterns — never reintroduce

- ❌ `App\Support\Web\SearchAndPaginate` trait — **deleted**; use the request-driven pattern above.
- ❌ Mounting a Livewire component to handle list filtering, sorting, searching, or pagination on a web list page.
- ❌ Issuing Eloquent queries directly from any Blade list view, detail view, or form view.
- ❌ Creating an `Index*Request` class for a web list page that does not extend `IndexListRequest`.

## Image Uploads — Indirect Pipeline Is Mandatory (API, `/web`, `/admin` Filament)

User-supplied image binaries are **never** written directly to public storage. Every image upload, from every UI surface (REST API, legacy `/web` controllers, Filament `/admin`, future surfaces), MUST go through the existing two-stage indirect pipeline. This is a security boundary.

### Canonical flow (do not bypass, do not reimplement)

1. Upload creates an `App\Models\ImageUpload` record. The binary lands on the **private** `local` disk in the `image_uploads` directory (see `config/localstorage.php`). It is NOT web-reachable.
2. `ImageUpload` creation dispatches `App\Events\ImageUploadEvent`.
3. `App\Listeners\ImageUploadListener` validates (Intervention Image), resizes to the configured max dimensions, creates an `App\Models\AvailableImage` record (same UUID), deletes the `ImageUpload`, and dispatches `App\Events\AvailableImageEvent`.
4. `App\Listeners\AvailableImageListener` moves the validated file to the **public** `public` disk in the `images` directory.
5. Only then can the `AvailableImage` be attached to a host entity via the existing pivot models (`ItemImage`, `CollectionImage`, `PartnerImage`).

### Rules

- ❌ **NEVER** write user-uploaded image binaries to the `public` disk, the `images` directory, or any web-reachable location directly. The `public` disk is the **output** of validation, never an input.
- ❌ **NEVER** create an `AvailableImage` record from a user upload. `AvailableImage` is produced exclusively by `ImageUploadListener`.
- ❌ **NEVER** use `Filament\Forms\Components\FileUpload->disk('public')` (or any equivalent) to accept a user image. Filament image upload fields MUST target the `local` disk + `image_uploads` directory and persist as an `ImageUpload` record so the event chain runs.
- ❌ **NEVER** reimplement validation, resizing, or thumbnailing in a new controller, action, page, or Filament component. Trigger the existing event chain.
- ❌ **NEVER** introduce a new disk, directory, or storage path for image uploads.
- ✅ A Filament "upload" Action / Page / Resource MUST create an `ImageUpload` (so `ImageUploadEvent` fires) and surface the resulting `AvailableImage` once the listeners have run.
- ✅ A Filament "attach image to entity" Action MUST pick from the existing `AvailableImage` pool via server-side search; it MUST NOT accept a raw file.
- ✅ Tests covering upload flows MUST use `Storage::fake('local')` and `Storage::fake('public')`, dispatch through the real event chain (or assert it was dispatched), and never write directly to the `public` disk.

### Reference implementation

- Model (upload staging): `app/Models/ImageUpload.php`
- Model (validated pool): `app/Models/AvailableImage.php`
- Events: `app/Events/ImageUploadEvent.php`, `app/Events/AvailableImageEvent.php`
- Listeners: `app/Listeners/ImageUploadListener.php`, `app/Listeners/AvailableImageListener.php`
- Config: `config/localstorage.php` (disks, directories, max dimensions)
- API controller: `app/Http/Controllers/ImageUploadController.php`
- Event tests: `tests/Event/ImageUpload/ImageUploadTest.php`, `tests/Event/AvailableImage/AvailableImageTest.php`