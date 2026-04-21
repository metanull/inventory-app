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
    - [tests/Feature/Api/Language/AnonymousTest.php](tests/Feature/Api/Language/AnonymousTest.php), 
    - [tests/Feature/Api/Language/DestroyTest.php](tests/Feature/Api/Language/DestroyTest.php), 
    - [tests/Feature/Api/Language/IndexTest.php](tests/Feature/Api/Language/IndexTest.php), 
    - [tests/Feature/Api/Language/ShowTest.php](tests/Feature/Api/Language/ShowTest.php), 
    - [tests/Feature/Api/Language/StoreTest.php](tests/Feature/Api/Language/StoreTest.php), 
    - [tests/Feature/Api/Language/UpdateTest.php](tests/Feature/Api/Language/UpdateTest.php), 
    - [tests/Feature/Event/AvailableImage/AvailableImageTest.php](tests/Feature/Event/AvailableImage/AvailableImageTest.php).