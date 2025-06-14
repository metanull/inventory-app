<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        // Run migrations before each test
        // Artisan::call('migrate', ['--force' => true, '--env' => 'testing']);

        // // Optionally, you can seed the database
        // // Artisan::call('db:seed', ['--force' => true, '--env' => 'testing']);
    }
    protected function tearDown(): void
    {
        // Rollback migrations after each test
        // Artisan::call('migrate:rollback', ['--force' => true, '--env' => 'testing']);

        parent::tearDown();
    }
    //
}
