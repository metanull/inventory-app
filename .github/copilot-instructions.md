# Copilot Coding Agent Instructions (concise)

## General Guidelines

**CRITICAL**: When the context window is full, reload the copilot-instructions.md file not to lose context.
**CRITICAL**: Use VS Code testing to run tests.
**CRITICAL**: Use VS Code tools instead of terminal when possible.
  - **CRITICAL**: **Never modify content of files through terminal scripts**. It is hazardous! Only do explicit code edits using VS Code tools such as `grep_search` or `repolace_string_in_file`. **No exceptions!**
  - To delete files and directories, first save the list of files in a temporary text file then run a terminal command that reads the list from that file, and delete all the files in a loop.
**CRITICAL**: Maintain consistency with existing code patterns and conventions.
  - Verify existing implementation before changeing to ensure alignment with project's patterns and standards.
  - If new issues are found, even if not directly related to the user request, fix them as part of the task.
**CRITICAL**: The project is in active development; breaking changes are accepted, there is no need to preserve backward compatibility.
**CRITICAL**: Always implement the entire user request (do not prioritize, do not take shortcuts, do not postpone tasks)
**CRITICAL**: All warnings and all errors must be addressed, none are acceptable.
**CRITICAL**: The terminal tool uses Windows PowerShell; never use *nix commands; use windows powershell compatbile escaping.
**CRITICAL**: Never delete then re-create a file! Instead, edit or replace its content using VS Code tools.
**CRITICAL**: If, in a test for a blade template you find the error `unexpected token "else", expecting end of file`, it can typically be resolved by verifying the source (not the cached version) carfully from the start of the file making sure that:
  - It does not not use shorthand notation (e.g. for components use <x-slot name="action"> instead of x-slot:action)
  - It uses variables like "$routePrefix" instead of string interpolation like "{$entity}.images.create" in blade components
  - All variables are defined in a single @php/@endphp block BEFORE the start of the section using them, and there are no duplicated variable definitions.
  - The wrapping of @if/@else and @php/@endphp is correct (as @php interrupts control flow, it must not appear inside control flow blocks (e.g. @if/@else) nor inside other @php/@endphp).
  - Nested @foreach and @if are correctly closed in the right order.
  - Examples:
      - Wrong:
        ```
        @if($items->count() > 0)
            @php ... @endphp       ← PHP block is INSIDE the @if
            <element class="{$entity}-xyz">  ← Opening div + string interpolation
                @foreach...
                @endforeach
            </element>                 ← Closing div is AFTER @endforeach but BEFORE @else
        @else
        ```
      - Correct:
        ```
        @php
            $variableEntity = "{$entity}.images.create";  ← All variable definitions are in a single PHP block BEFORE the control flow
        @endphp
        @if(...)
          <element class="{{ $variableEntity }}">  ← Opening div with variable usage (no string interpolation)
            @foreach...
            @endforeach
          </element>
        @else
        ```

## Project Overview

This is a Laravel 12 project powered by PHP 8.2. **CRITICAL**: Always use the right version of their documentation.
**CRITICAL**: Always follow Laravel conventions and best practices.
**CRITICAL**: Avoid adding new dependencies unless absolutely necessary.

### Project Structure & Key Components

This project contains several distinct elements:
- "/" directory: A standard Laravel application using Sanctum, Fortify and Jetstream
- "/api-client" directory - An automatically generated TypeScript API client for the Laravel backend API.
  - Never directly modify files in this directory; they are auto-generated.
  - Use `composer ci-openapi-doc` to generate the OpenAPI specification (docs/_openapi/api.json) and the TypeScript client in `/api-client/`
  - Use `.\scripts\publish-api-client.ps1 -Credential (Get-Secret github-package)` to publish the TypeScript client to our private github package registry.
- "/docs" directory - A distinct documentation website powered by Jekyll (Ruby)
  - It is deployed separately on GitHub Pages by CI/CD workflows
  - It contains auto generated elements maintained by CI/CD workflows (e.g. git commit history)
  - It contains manually maintained documentation pages (e.g. /docs/index.md, /docs/guidelines/*.md, etc.) that are transformed by Jekyll into static HTML pages by CI/CD workflows.
  - To test the documentation locally, use WSL to run Ruby/Jekyll commands.
- "/resources/js" directory - A sample SPA frontend application demonstrating usage of the API backend via the generated TypeScript API client.
  - It is totally independent from the main Laravel application.

### Key web routes and components

The laravel application exposes several components via distinct classes of routes:
  - "/api" route - The REST API backend
    - It aims to be a pure API backend, with no frontend code.
  - "/web" route - A server-rendered Blade frontend using Livewire, Alpine.js, and Tailwind CSS
    - **CRITICAL**: It NEVER uses the API backend for data interactions; all data interactions are done via Laravel models and controllers directly.
    - It is the main frontend for end users.
  - "/cli" route - A sample "api client" SPA frontend implementation. It aims at demonstrating usage of the API backend from a SPA application.
    - It is totally independent from the main frontend.
    - Its source code is in /resources/js
    - It uses Vue 3 + TypeScript, with Pinia.
    - **CRITICAL**: It NEVER uses direct HTTP calls (fetch, axios, etc.) to interact with the API backend.
    - **CRITICAL**: It uses exclusively the api-client from our github package repository to interact with the API.
     generated API client in /api-client for all API interactions.
  - "/api/docs" route - An auto-generated OpenAPI documentation page powered by swagger-ui.
    - It is generated by Laravel automatically, no manual maintenance is needed.

### Quality Standards

- Code must be linted and formatted according to project standards before push/PR.
  - Use Laravel Pint for PHP code and ESLint for other code (npm run lint is configured to handle js, ts, md, etc.).
- Code must be covered by tests before push/PR.
  - Use PHPUnit for backend code and Vitest for frontend code.
  - Tests must be run via VS Code testing features.
  - All tests must pass locally and without warnings before push/PR.
  - Tests must be simple, single-purpose, deterministic, independent, and explicit about their dependencies.
  - Tests must consistently use mocking/faking for all external dependencies.
  - Tests must consistently follow existing project patterns and directory structures; use existing tests as examples.
  - Tests must be exempt from assumptions and flaky behavior; they must test the actual implementation and the intended behavior reliably.
- All TypeScript code must be free of warnings and errors before push/PR.
- Source control rules:
  - Never push/commit directly to `main`. Changes must be transmitted via Pull Requests.
  - Create a dedicated branch when starting to work on a new request. Always ensure that you're working on the latest code before handling a new request.

### Coding Conventions & Patterns

- Conventions for the main Laravel application:
  - Every new model must include Migration, Resource, Controller, Factory, Seeder and Tests.
  - Controllers must use dedicated Request and Resource classes for input validation and output formatting.
  - Never alter existing migrations; create new migrations for schema changes.
  - UUID primary keys are used for all models (with the only exception of Language and Country that are using 3 character long iso codes).
  - **CRITICAL**: Do not use shorthand notation (e.g. for components use <x-slot name="action"> instead of <x-slot:action>)
  - **CRITICAL**: in blade components use variables like "$routePrefix" instead of string interpolation like "{$entity}.images.create". To avoid issue when nesting.
  - Code must respect the DRY principle.
- Conventions for the Sample SPA application:
  - Use Pinia for state management
  - Use Vue 3 Composition API with TypeScript for all components.
  - Use Tailwind CSS for styling; do not use custom CSS unless absolutely necessary.
  - Use components to avoid code duplication; do not repeat code across multiple components.
  - Use the generated API client in `api-client/` for all API interactions; do not use `fetch` or `axios` directly.
  - Follow existing store patterns for actions, getters, and state organization.
- Conventions for the documentation site (Jekyll):
  - The main configuration for Jekyll is in `_config.yml`.
  - The site uses just-the-docs theme as base.
  - All md files must include proper front matter with layout, title, nav_order, and other relevant fields (e.g. parent, permalink and has_children must be added when applicable).
  - Any directory that is part of the documentation must contain an `index.md`.
  - Keep the navigation structure in sync with the actual files and their front matter.
  - Hyperlinks are transformed by Jekyll during site generation; follow these rules when creating links in markdown files:
      - Links to a directory must include the trailing slash. e.g. `[API Models](api-models/)` is a valid link to the directory `/docs/api-models/` and will point to `/docs/api-models/index.md`
      - Links to a file must omit the trailing extension. e.g. `[guidelines](guidelines)` is a valid link to the file `/docs/guidelines.md`
      - For cross-references between sections, use front matter permalinks.
      - Links must always omit the `/docs/` prefix.
      - Never extrapolate how the link target will be transformed; always use the exact path as per the rules above.
      - Always use "absolute links", starting with `/` (where / is the root of the documentation and maps to our /docs/ directory). E.g. `[Guidelines](/guidelines)` is an absolute link mapping to our `/docs/guidelines` directory (and more precisely to its index.md file).
      - Create links to non-markdown files (e.g. json, images, etc.) by creating a markdown placeholder file, with `permalink` and `layout: null` frontmatter fields. 
        -By example, to create publish our /docs/_openapi/api.json file as '/api.json' on the generated website: create a file `docs/api.json.md` with the following content:
            ```
            ---
            layout: null
            permalink: /api.json
            ---
            {% include_relative _openapi/api.json %}
            ```
        - To create an hyperlink to that file, use its front matter permalink's value. e.g. `[OpenAPI Spec](/api.json)`.