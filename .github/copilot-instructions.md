# Guidelines and instructions for the project

## Introduction

- This is a PHP application with Laravel 12 and Vue.js.
- This is a PHP 8.2+ application.
- It uses a github Git repository for version control.
  - **CRITICAL: The repository forbids pushing changes to `main`. It requires pull requests.**
  - It uses GitHub issues to track bugs and feature requests.
  - It uses GitHub Actions for continuous integration and deployment.
  - It uses GitHub Actions to test and check quality of the pull-requests.

## General instructions

- **CRITICAL: Never create scripts to modify files in this repository. Always perform changes one by one, carefully.**
- **CRITICAL: Never use search/replace console commands to bulk modify files. Always perform changes one by one, carefully.**
- Never ignore lint errors and warnings.
- Never ignore failing tests.
- Run tests using VS Code's testing features.
- **CRITICAL: Always maintain consistency of the code. Similar features must be implemented in similar ways.**

## Tooling

The project uses the following tools and technologies:

### Backend
- `PHP` as the programming language.
- `Laravel 12` as the web framework.
- `composer` for dependency management.
- `artisan` to generate files and run commands.
- `phpunit` for testing.
- `Pint` for code formatting and style checking.
- `GitHub` for version control.
- `Blade` as the templating engine, with `Tailwind CSS` for styling.

### Frontend
- `TypeScript` as the programming language.
- `Vue.js 3` as the frontend framework.
- `Vite` as the build tool.
- `npm` for dependency management.
- `Vitest` for testing.
- `ESLint` for linting TypeScript and Vue.js code.
- `Prettier` for code formatting of TypeScript and Vue.js code.
- `Tailwind CSS` for styling.

## Database
- For development the project uses a SQLite database.
- For testing the project uses an in-memory SQLite database.
- For production the project uses a MariaDB database.

## Environment

- **CRITICAL: This is a window system.**
  - The shell environment is PowerShell.
  - Always use `powershell` commands when interacting with the terminal.
  - Always use `powershell` compatible syntax when interacting with the terminal.
    - By example the escape character is the backtick character (\`), not the backslash character (\\).
  - Never use Unix/Linux commands or syntax in the terminal.
    - By example do not use `ls`, use `Get-ChildItem`.
    - By example do not use `cat`, use `Get-Content`.
    - By example do not use `rm`, use `Remove-Item`.
    - By example do not use `cp`, use `Copy-Item`.
    - By example do not use `mv`, use `Move-Item`.
    - By example do not use `grep`, use `Select-String`.
    - By example do not use `sed`, use `(Get-Content file) -replace 'search', 'replace' | Set-Content file`.

## Github page

- The `/docs/` directory contains a distinct `Ruby` application based on `Jekyll`.
  - It is used to generate a static `github page` website.
  - Ruby is invoked as part of the CI/CD pipeline.
    - The build process is based on the Jekyll framework. It takes the markdown files in the docs/ directory and generates a static website.
- Do not modify the build scripts directly.
- Do not build the docs/ directory manually.
- Always use `wsl bash -c 'COMMANDS'` instead of PowerShell to interact with Ruby.
  - Example: `wsl bash -lc 'cd docs && PATH="$HOME/.local/share/gem/ruby/3.2.0/bin:$PATH" && bundle exec jekyll build'`

## Source Control

- The repository uses Git for version control.
- The repository uses GitHub for hosting the code.
  - The repository uses GitHub issues to track bugs and feature requests.
  - The repository uses GitHub pull requests to review and merge code changes.
  - The repository uses GitHub Actions for continuous integration and deployment.
  - The repository uses GitHub Actions to run tests and code quality checks.
- github cli is available as `gh` command in the terminal.
- The default branch is `main`.
- The repository has GitHub rulesets configured for code quality and security:
    - **no-force-push no-delete**: Prevents force pushes and branch deletion
    - **requires-codeQL-scanning**: Mandates CodeQL security analysis
    - **requires-linear-history**: Enforces linear git history (no merge commits)
    - **requires-pull-request**: Requires pull requests for all changes with the following bypass permissions:
        - Repository administrators can bypass review requirements
        - Dependabot can bypass review requirements for dependency updates
        - All other contributors must have their pull requests reviewed before merging
- **CRITICAL: Never stage or commit changes to the `main` branch.**
- **CRITICAL: Never push the `main` branch.**
- **CRITICAL: Never stage temporary files to git repository.**
- **CRITICAL: Always create a new branch for pull-requests (pr).**
- **CRITICAL: Always use the `feature/` or `fix/` prefix for the branch name, depending on the type of changes.**
- **CRITICAL: Always use `gh pr` to manage pull requests.**
  - **CRITICAL: With `gh pr create` always escape the `--assignee @me` like this: `--assignee "@me"`.**
  - **CRITICAL: With `gh pr create` never use `--label`, `--merge`, `--auto`. Instead create the pr first, then make it 'auto-merge' in a second instruction with `gh pr merge`.**
  - **CRITICAL: With `gh pr merge` always make the pr auto-merge in squash mode with `--squash --auto`.**
- **CRITICAL: On commit always store the commit message in a temporary markdown file (temp\_\*.md); and let git use that file as an input to avoid escaping issues.**
- **CRITICAL: On pr creation always store the pr description in a temporary markdown file (temp\_\*.md); and let git use that file as an input to avoid escaping issues.**
- Before pull requests, update the `CHANGELOG.md` file to reflect the changes made.

## Laravel Specific Guidelines

- This application is built on the Laravel framework:
- **CRITICAL: Use Laravel 12 features.**
  - **CRITICAL: Strictly follow Laravel 12 guidelines and recommendations.**
  - **CRITICAL: Strictly follow Laravel 12 directory structure.**
  - **CRITICAL: Check if my requests are compliant with Laravel 12 recommendations, guidelines and best practices. If not, propose compliant alternatives, and ask the user for confirmation before proceeding.**
- **CRITICAL: When adding a model, always make sure it has Migration, Resource, Controller, Factory, Seeder and Tests.**
- **CRITICAL: When adding a seeder, always make sure that the seeder is called from [database/seeders/DatabaseSeeder.php](database/seeders/DatabaseSeeder.php).**
- **CRITICAL: Never modify existing migrations; instead create a new migration that modifes the database schema.**

- The application uses Eloquent ORM to define the database schema and interact with the database.
    - Every table is created via migrations
        - Migrations are defined in the `database/migrations` directory.
        - Migrations' `Schema::create()` instructions have `$table->timestamps();` to automatically add `created_at` and `updated_at` columns.
    - Soft deletes are not used by default; do not add `$table->softDeletes()` unless explicitly required.
    - Most tables have a `backward_compatibility` column of type `string`.
        - This column is `nullable` and has a default value of `null`.
        - This column is used to store the ID of the record in the previous version of the application.
    - Most tables have a `internal_name` column of type `string`.
        - This column is used to store the internal name of the record.
        - In general it is associated with a `unique` constraint.
        - This column is not `nullable`.
        - This column doesn't have a default value.
    - Every table has a Model
    - Every Model has factories for testing
    - Every Model has seeders for database seeding
    - Every table has a single column primary key.
        - The name of the column is `id`.
        - The type of the column is `uuid`, save for the Language, Country and User models:
            - Language uses the `ISO 639-3` code as identifier (three letters).
            - Country uses the `ISO 3166-1 alpha-3` code as identifier (three letters).
            - User is a model provided by the Laravel framework.
                - The User model uses the `id` column as primary key.
                - The `id` column is an auto-incrementing integer.
    - Every `uuid` primary key is generated automatically through `HasUuids` trait, and the method `public function uniqueIds(): array{return ['id'];}`.
        - The `HasUuids` trait is provided by the Laravel framework.
        - The `HasUuids` trait is used in every Model that has a `uuid` primary key.
        - Models with UUID primary keys set `$incrementing = false;` and `$keyType = 'string';`.
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
        - Seeders extend `Illuminate\\Database\\Seeder` and are registered in `DatabaseSeeder`.
        - The Language and Country models are seeded with the ISO 639-3 and ISO 3166-1 alpha-3 codes, respectively.
            - These Seeders are already defined in the `database/seeders/LanguageSeeder.php` and `database/seeders/CountrySeeder.php` files.
- The application provides a REST API
    - The routes are defined in the `routes/api.php` file.
    - It exposes methods to interact with the database.
    - It uses Resource controllers
        - Controllers are defined in the `app/Http/Controllers` directory.
        - Every Controller method that accepts input data uses the `Request` object to validate the input.
            - The Validation in the controller is aligned with the constraints defined in the Model, Factory and Migration.
        - Every Controller method that returns data uses the `Resource` object to format the output.
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
- The application has Unit and Feature tests
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
            - In the other test files the class has a `protected ?User $user = null;` property and the `setUp()` method creates and authenticates a user with Sanctum and the appropriate API guard
                ```
                $this->user = User::factory()->create();
                $this->$actingAs($this->user);
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
- In production, the application is deployed on a windows server through github action: [deploy-mwnf-svr.md](.github/workflows/deploy-mwnf-srv.yml).
    - It uses a github environment, `MWNF-SVR`, to store the secrets and variables for the deployment.
    - It uses apache httpd 2.4 or higher as the web server.
    - It is exposed though a reverse proxy.
        - Reverse proxy is httpd 2.4 with mod_security and mod_proxy.
        - The reverse proxy is configured to handle HTTPS requests.
    - It uses MariaDB 10.5 or higher as the database server.
    - It uses php 8.2 or higher as the PHP version.