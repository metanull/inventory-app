---
applyTo: "**/*.php"
---
# Project coding standards and conventions for PHP

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