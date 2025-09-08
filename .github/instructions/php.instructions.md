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

