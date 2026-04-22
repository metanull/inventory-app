---
applyTo: "tests/**/*Test.php"
---

> **CRITICAL — Never pipe test command output through any filter.**
> Always run `php artisan test` and `composer ci-*` commands **unpiped**. Piping through `Select-Object`, `head`, `tail`, or any other trimming command hides failure details and forces the full test run to be repeated just to see the error.
> - ✅ `php artisan test --testsuite=Web --no-ansi --stop-on-failure`
> - ❌ `php artisan test --testsuite=Web --no-ansi --stop-on-failure 2>&1 | Select-Object -Last 10`

- Organize tests in a logical and consistent directory structure.
- Keep tests simple and focused on a single behavior.
- Keep tests isolated and independent from each other.
- Use meaningful test names that describe the behavior being tested.
- In test, never assume existence of a record, create records using factories and use the `refreshDatabase` trait.
- Use existing tests as a reference for creating new tests maintaining consistency: 
    - [tests/Api/Resources/ContextTest.php](tests/Api/Resources/ContextTest.php),
    - [tests/Api/Resources/ItemTranslationTest.php](tests/Api/Resources/ItemTranslationTest.php),
    - [tests/Web/Pages/ItemIndexTest.php](tests/Web/Pages/ItemIndexTest.php) (canonical web list page test — request-driven pattern),
    - [tests/Web/Pages/ItemTest.php](tests/Web/Pages/ItemTest.php) (canonical web CRUD page test).

## Web List Page Tests

Web index pages use the request-driven list pattern (`IndexListRequest` + `{Entity}IndexQuery`). Tests for these pages live in `tests/Web/Pages/{Entity}IndexTest.php` and must verify:

- The page renders without Livewire markup (`assertDontSee('wire:')`).
- Filtering and searching work correctly via query parameters.
- Sorting respects the whitelist (invalid sort columns are normalized to the default).
- Pagination preserves query strings in links.
- Authorization gates (permission checks) are enforced.

**Do not** write Livewire component tests for web list/filter/sort/pagination behaviour — use HTTP page tests instead.

Canonical example: [tests/Web/Pages/ItemIndexTest.php](tests/Web/Pages/ItemIndexTest.php).