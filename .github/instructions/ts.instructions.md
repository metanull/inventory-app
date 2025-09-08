---
applyTo: "**/*.ts"
---
# Project development guidelines for TypeScript

- Strictly verify typescript code quality and formatting using ESLint and Prettier. Do not ignore linting errors and warnings.
- Do not ignore lint errors and warnings.
- **CRITICAL:** Never explicitly use the `any` type. This is forbidden by linting rules.
- **CRITICAL:** Never leave unused variables. This is forbidden by linting rules.
- Use explicit types for function parameters and return values.

## Code Quality

- **CRITICAL: Strictly verify TypeScript code quality and formatting using ESLint and Prettier.**
- Never ignore lint errors and warnings.
- Never ignore failing tests.
