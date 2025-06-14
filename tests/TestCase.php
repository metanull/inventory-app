<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The RefreshDatabase trait will handle migrations and database state.
    }

    protected function tearDown(): void
    {
        // The RefreshDatabase trait will handle database rollbacks.

        parent::tearDown();
    }
    //
}
