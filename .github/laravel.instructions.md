**CRITICAL: Always verify if my requests are compliant with Laravel 12 recommendations, guidelines and best practices. If not, propose compliant alternatives, and ask the user for confirmation before proceeding.**
**CRITICAL: When adding a model, always make sure it has Migration, Resource, Controller, Factory, Seeder and Tests.**
**CRITICAL: When adding a seeder, always make sure that the seeder is called from [database/seeders/DatabaseSeeder.php](database/seeders/DatabaseSeeder.php).**
**CRITICAL: Never modify existing migrations; instead create a new migration that modifes the database schema.**
**CRITICAL: Always use Laravel framework's built-in feature to access storage files, such as Flysystem and `Storage::disk('local')->put('file.txt', 'contents')` instead of using the filesystem directly.**
**CRITICAL: Always use Framework's built-in feature to access configuration files, such as `config('app.name')` instead of using the filesystem directly.**
**CRITICAL: Always use Framework's built-in feature to read and store images, such as Intervention Image Manager functions.**
**CRITICAL: Never use vendor specific code, use Laravel's built-in features instead.**
**CRITICAL: Never use low level php function when Laravel's framework offers higher level abstractions.**
**CRITICAL: Before creating pull request, ensure that `lint` and `test` pass successfully and without warning.**
This application is built on the Laravel framework:
    - It uses Eloquent ORM to define the database schema and interact with the database.
        - Every table is created via migrations
            - Migrations are defined in the `database/migrations` directory.
            - Migrations' `Schema::create()` instructions have `$table->timestamps();` to automatically add `created_at` and `updated_at` columns.
            - Soft deletes are not used by default; do not add `$table->softDeletes()` unless explicitly required.
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
                    - Language uses the `ISO 639-3` code as identifier (three letters).
                    - Country uses the `ISO 3166-1 alpha-3` code as identifier (three letters).
                    - User is a model provided by the Laravel framework.
                        - The User model uses the `id` column as primary key.
                        - The `id` column is an auto-incrementing integer.
                - The primary key is named `id`.
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
    - It provides a REST API
        - The routes are defined in the `routes/api.php` file.
        - It exposes methodes to interact with the database.
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
        - `composer ci-git:assert-no-change` - asserts that there are no changes in the local repository
    - `composer ci-before:pull-request` - runs `ci-openapi-doc`, `ci-lint:test`, `ci-audit`, `ci-test`, then `ci-git:assert-no-change` to verify code before a pull request is created
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
        - `composer ci-git:assert-no-change` - asserts that there are no changes in the local repository
        - `composer ci-before:pull-request` - runs `ci-openapi-doc`, `ci-lint:test`, `ci-audit`, `ci-test`, then `ci-git:assert-no-change` to verify code quality prior to the creation of a pull request.
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