---
applyTo: "*.php"
title: PHP Coding Standards
description: |
  Coding standards for PHP files in this repository.
---
# Copilot instructions for PHP files

## Context
- I'm using a Windows workstation.
  - The shell environment is PowerShell.
  - The code editor is Visual Studio Code.
  - The code editor has the GitHub Copilot extension installed.
- This is a PHP application
  - It requires PHP 8.2 or higher.
  - It uses the Laravel Framework
    - It requires Laravel 12 or higher.
    - It uses composer for dependency management.
    - It uses artisan to generate files and run commands.
    - It uses phpunit for testing.
    - It uses Pint for code formatting and style checking.
    - It uses GitHub for version control.
    - It uses Blade as the templating engine.
    - It uses Tailwind CSS for styling.
  - It provides a Database
    - For development and testing purposes, it uses SQLite in memory.
    - For production, it uses MariaDb.
    - For development and testing purposes, it uses SQLite in memory.
    - For production, it uses MariaDb.
  - It uses Eloquent ORM to define the database schema and interact with the database.
    - Every table is created via migrations
      - Migrations are defined in the `database/migrations` directory.
      - Migrations' `Schema::create()` instructions have `$table->timestamps();` to automatically add `created_at` and `updated_at` columns.
      - Migrations have a `backward_compatibility` column of type `string`.
        - This column is `nullable` and has a default value of `null`.
        - This column is used to store the ID of the record in the previous version of the application.
      - Most migrations have a `internal_name` column of type `string`.
        - This column is used to store the internal name of the record.
        - In general it is associated with a `unique` constraint.
        - This column is not `nullable`.
        - This column doesn't have a default value.
    - Every table has a Model
    - Every Model has factories for testing
    - Every Model has seeders for database seeding
    - It uses GUID identifiers
      - Every table has a single column primary key.
        - The name of the column is `id`.
        - The type of the column is `uuid`, save for the Language, Country and User models:
          - Language uses the `ISO 639-1` code as identifier.
            - The code is a three-letter code.
          - Country uses the `ISO 3166-1 alpha-3` code as identifier.
            - The code is a three-letter code.
          - User is a model provided by the Laravel framework.
            - The User model uses the `id` column as primary key.
            - The `id` column is an auto-incrementing integer.
        - The primary key is named `id`.
      - Every `uuid` primary key is generated automatically through `HasUuids` trait, and the method `public function uniqueIds(): array{return ['id'];}`.
        - The `HasUuids` trait is provided by the Laravel framework.
        - The `HasUuids` trait is used in every Model that has a `uuid` primary key.
        - The corresponding model doesn't allow the `id` field to be set manually.
          - The `id` field is set automatically by the framework when the record is created.
          - The `id` field is not included in the fillable fields of the Model.
    - Every Model has a Factory
      - The Factory is used to generate test data.
      - The Factory is used to seed the database.
      - The Factory is used to create test data for the tests.
      - The Factory is defined in the `database/factories` directory.
      - The Factory uses the `Faker` library to generate random data.
      - The Factory uses the `HasFactory` trait, which is provided by the Laravel framework.
    - Every Model has a Seeder
      - The Seeder is used to seed the database with initial data.
      - The Seeder is defined in the `database/seeders` directory.
      - The Seeder uses the Factory to generate test data.
      - The Seeder uses the `HasSeeder` trait, which is provided by the Laravel framework.
      - The Language and Country models are seeded with the ISO 639-1 and ISO 3166-1 alpha-3 codes, respectively.
        - These Seeders are already defined in the `database/seeders/LanguageSeeder.php` and `database/seeders/CountrySeeder.php` files.
  - It provides a REST API
    - The routes are defined in the `routes/api.php` file.
    - It exposes methodes to interact with the database.
    - It uses Resource controllers
      - Controllers are defined in the `app/Http/Controllers` directory.
      - Every Controller method that accepts input data uses the `Request` class to validate the input.
        - The Validation in the controller is aligned with the constraints defined in the Model, Factory and Migration.
      - Every Controller method that returns data uses the `Resource` class to format the output.
    - Every Model has a Resource 
    - Every Model has a Controller
      - Every Controller uses the Resource
    - Most Controllers have methods for the following actions:
      - `index` - to list all records
      - `show` - to show a single record
      - `store` - to create a new record
      - `update` - to update an existing record
      - `destroy` - to delete a record
    - Model may have Scopes
      - Scopes are defined in the Model class.
      - Scopes are used to filter the results of a query.
      - Scopes are used to apply common query logic to the Model.
      - When a Model has Scopes, the Controller exposes extra methods to apply the Scopes.
        - For example, if the `User` model has a `scopeActive` method, the `UserController` will have an `active` method that applies the scope.
          The route for this method will be `GET /users/active`. 
    - Some Models are created through Event Listeners.
      - Such Controllers do not allow `store`, `update` methods.
    - All Models, Controllers, Resources, Factories, Seeders, and Tests are kept consistent
      - Every Controller exposes similar routes and follow similar naming conventions
      - Every Resource formats the output in a consistent way
      - Every Factory generates similar data for the Model
      - Every Test validates the data in a consistent way
  - It has Unit and Feature tests
    - The Feature tests are defined in the `tests/Feature` directory.
    - The Unit tests are defined in the `tests/Unit` directory.
    - Every Factory has Unit tests
      - The tests are defined in the `tests/Unit` directory.
      - The tests use the Factory to generate test data.
      - The tests use the `assertDatabaseHas` method to validate the data in the database.
      - The tests asserts that generated data complies with the constraints defined in the Model, Factory and Migration.
      - When the Model has Scopes, the Factory test has extra tests for each Scope
    - Every Model has Feature tests
      - The tests are defined in the `tests/Feature` directory.
      - The tests for a single Model are organized in a directory named after the Model.
        - Every Model has the following test files:
          - `AnonymousTest.php` - Tests for unauthorized access scenarios
          - `IndexTest.php` - Tests for listing/index operations
          - `ShowTest.php` - Tests for showing single records
          - `StoreTest.php` - Tests for creating new records
            - When the model forbids creation, this file contains one single test that asserts that the `store` method returns a `405 Method Not Allowed` response.
          - `UpdateTest.php` - Tests for updating existing records
            - when the model forbids updating, this file contains one single test that asserts that the `update` method returns a `405 Method Not Allowed` response.
          - `DestroyTest.php` - Tests for deleting records
            - when the model forbids deletion, this file contains one single test that asserts that the `destroy` method returns a `405 Method Not Allowed` response.
      - The test class has `setUp()`.
        - In AnonymousTest.php, the `setUp()` is empty.
        - In the other test files the class has a `protected ?User $user = null;` property and the `setUp()` method creates and authenticates a user with 
          ```
          $this->user = User::factory()->create();
          $this->actingAs($this->user);
          ```
      - The tests use the `RefreshDatabase` trait to reset the database state before each test.
      - The tests use the `WithFaker` trait to generate random data for tests.
      - The tests use the Factory to generate test data.
        - When the test requires data in the database, the test uses the Factory to create and store the data with `->create()`.
          - When creating a record it is preferred to use the Factory without customizing the data
        - When the test requires a second set of data to pass to a controller then it uses the Factory to create the data in memory with `->make()->toArray()`.
        - When the test requires a second set of data with missing fields then it uses the Factory to create the data in memory with `->make()->except()`.
      - The tests use `assertJsonStructure` method to validate the response structure.
      - The tests use `assertJsonPath` method to validate the response content.
      - The tests use `assertOk`, `assertCreated`, `assertNoContent`, `assertNotFound`, and `assertUnprocessable` methods to validate the status code of the response.
      - To generate url, if the route has a name, always use the `route()` helper with the route's name. Otherwise use the `url()` helper with the route's path.
        - For example, if the route is named `user.index`, use `route('user.index')` to generate the URL for the index method of the UserController.
        - If the route is not named, use `url('/users')` to generate the URL for the index method of the UserController.
  - It is deployed on a windows server.
    - It uses powershell to run commands.
    - It uses apache httpd 2.4 or higher as the web server.
      - It is exposed though a reverse proxy.
        - Reverse proxy is httpd 2.4 with mod_security and mod_proxy.
        - The reverse proxy is configured to handle HTTPS requests.
    - It uses MariaDB 10.5 or higher as the database server.
    - It uses php 8.2 or higher as the PHP version.
  - It has custom composer scripts for some tasks:
    - `composer ci-install` - installs the php and node.js dependencies for the CI environment
    - `composer ci-build` - builds the application for the CI environment
    - `composer ci-audit` - runs the composer audit command to check for vulnerabilities
    - `composer ci-lint` - runs Pint for code formatting and style checking in the CI environment
    - `composer ci-test` - runs the tests in the CI environment
    - `composer ci-reset` - resets the database in the CI environment
    - `composer ci-seed` - seeds the database in the CI environment
    - `composer ci-openapi-doc` - create an api.json file in `./docs/_openapi` directory
    - `composer ci-assert-no-changes` - asserts that there are no changes in the local repository
    - `composer ci-before-pull-request` - runs the `ci-openapi-doc`,`ci-assert-no-changes`, `ci-audit`, `ci-lint`, `ci-test` to verify code before a pull request is created
  - The repository uses GitHub Actions for CI/CD.
  - It is deployed on a windows server.
    - It uses powershell to run commands.
    - It uses apache httpd 2.4 or higher as the web server.
      - It is exposed though a reverse proxy.
        - Reverse proxy is httpd 2.4 with mod_security and mod_proxy.
        - The reverse proxy is configured to handle HTTPS requests.
    - It uses MariaDB 10.5 or higher as the database server.
    - It uses php 8.2 or higher as the PHP version.
  - It has custom composer scripts for some tasks:
    - `composer ci-install` - installs the php and node.js dependencies for the CI environment
    - `composer ci-build` - builds the application for the CI environment
    - `composer ci-audit` - runs the composer audit command to check for vulnerabilities
    - `composer ci-lint` - runs Pint for code formatting and style checking in the CI environment
    - `composer ci-test` - runs the tests in the CI environment
    - `composer ci-reset` - resets the database in the CI environment
    - `composer ci-seed` - seeds the database in the CI environment
    - `composer ci-assert-no-changes` - asserts that there are no changes in the local repository
    - `composer ci-before-pull-request` - runs the `ci-assert-no-changes`, `ci-audit`, `ci-lint`, `ci-test` to verify code before a pull request is created
  - The repository uses GitHub Actions for CI/CD.
  - The structure of the repository is as follows:
    ```
    .
    ├── app
    │   ├── Http
    │   │   ├── Controllers
    │   │   └── Resources
    │   └── Models
    ├── database
    │   ├── factories
    │   ├── migrations
    │   └── seeders
    ├── routes
    │   └── api.php
    ├── tests
    │   ├── Feature
    │   │   └── Api
    │   │       ├── ModelName
    │   │       │   ├── AnonymousTest.php
    │   │       │   ├── IndexTest.php
    │   │       │   ├── ShowTest.php
    │   │       │   ├── StoreTest.php
    │   │       │   ├── UpdateTest.php
    │   │       │   └── DestroyTest.php
    │   │       └── OtherModelName
    │   │           ├── AnonymousTest.php
    │   │           ├── IndexTest.php
    │   │           ├── ShowTest.php
    │   │           ├── StoreTest.php
    │   │           ├── UpdateTest.php
    │   │           └── DestroyTest.php
    │   └── Unit
    │       ├── ModelName
    │       |   └── FactoryTest.php
    │       └── OtherModelName
    │           └── FactoryTest.php
    └── composer.json
    ```
- When generating code, always:
  - Run `composer ci-lint` to format the code and check for style issues.
  - Run `composer ci-test` to run the tests and ensure that they pass.
  - Generate an issue description with the issues addressed.
    - Store the issue description in `ISSUE-DESCRIPTION.md` file.
      - If the file exists, clear its content first.
      - The description describes the problem that was solved or the feature that was implemented.
    - Make sure the file is in `.gitignore`.
  - Generate a pull request description with the changes made.
    - Store the pull request description in `PR-DESCRIPTION.md` file.
      - If the file exists, clear its content first.
      - The description describes the changes made in the pull request.
    - Make sure the file is in `.gitignore`.
  - Generate a commit message with the changes made.
    - Store the commit message in `COMMIT-MESSAGE.md` file.
      - If the file exists, clear its content first.
      - The commit message describes the changes made in the commit.
    - Make sure the file is in `.gitignore`.
  - Generate a markdown report with the changes made.
    - Make sure the report is in .gitignore file.
    - If the report was already pushed to the repository, then delete it from the repository.
  - Update the `CHANGELOG.md` file with the changes made.
    - Check for other changes pushed to the repository
    - If other change doccured singce `CHANGELOG.md` was last updated, also describe these changes.
## Git Repository
- The repository uses Git for version control.
- The repository uses GitHub for hosting the code.
- The repository uses GitHub issues to track bugs and feature requests.
- The repository uses GitHub pull requests to review and merge code changes.
- The repository uses GitHub Actions for continuous integration and deployment.
- The repository uses GitHub Actions to run tests and code quality checks.
- The default branch is `main`.
- Pushing to the `main` branch requires a pull request.
- The repository has GitHub rulesets configured for code quality and security:
  - **no-force-push no-delete**: Prevents force pushes and branch deletion
  - **requires-codeQL-scanning**: Mandates CodeQL security analysis
  - **requires-linear-history**: Enforces linear git history (no merge commits)
  - **requires-pull-request**: Requires pull requests for all changes with the following bypass permissions:
    - Repository administrators can bypass review requirements
    - Dependabot can bypass review requirements for dependency updates
    - All other contributors must have their pull requests reviewed before merging
## Naming Conventions
- The repository complies with Laravel's naming conventions.
  - It adheres to the PSR-12 coding standard.
  - Is uses the PSR-4 autoloading standard.
  - It uses Pint for code formatting and style checking.
- It uses `snake_case` for database columns and table names.
- It uses `kebab-case` for URLs and routes.
- It uses `snake_case` for configuration files.
- It uses `camelCase` for variable and function names.
- It uses `PascalCase` for class names and methods.
- It uses `UPPER_CASE` for constants.
- It uses `snake_case` for file names.

## Error Handling
- Use try-catch blocks for asynchronous operations.
- Always log errors with meaningful messages.
- Always handle exceptions gracefully.

## Annotation
- Use PHPDoc for class and method annotations.
- Use Laravel's built-in validation annotations for request validation.
- Use dedoc/Scramble annotations for Controller methods.
- When adding annotations, ensure they are clear and concise.
- When adding annotations, explain the purpose of that annotation.

## Code Comments
- Use comments to explain complex logic.
- Use comments to clarify the purpose of a function or method.

---
applyTo: pull_request
title: Copilot Instructions
description: |
  Instructions for using GitHub Copilot in this repository.
---
# GitHub Copilot Instructions

## Context
- This repository is a Laravel application.
- This repository uses PHP 8.2 or higher.
- This repository follows the Laravel standard.
- This repository adheres to the PSR-12 coding standard.
- This repository uses GitHub for version control.
- Our team uses GitHub issues to track bugs and feature requests.
- When providing code samples or instructions, please ensure that they are clear and concise, and that they address the specific issue or feature request being discussed.
- When reviewing pull requests, ensure that the git commits and PR include a clear description of the changes.
- When reviewing pull requests, ensure that the git commits and PR include references to relevant issues or discussions.