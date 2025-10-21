<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Author;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class StoreTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();
    }

    public function test_store_persists_author_and_redirects(): void
    {
        $payload = [
            'name' => 'Test Author',
            'internal_name' => 'T. Author',
            'backward_compatibility' => 'AUTH-LEG',
        ];

        $response = $this->post(route('authors.store'), $payload);
        $response->assertRedirect();
        $this->assertDatabaseHas('authors', [
            'name' => 'Test Author',
            'internal_name' => 'T. Author',
        ]);
    }

    public function test_store_validation_errors(): void
    {
        $response = $this->post(route('authors.store'), [
            'name' => '',
        ]);
        $response->assertSessionHasErrors(['name']);
    }

    public function test_store_allows_nullable_optional_fields(): void
    {
        $payload = [
            'name' => 'Minimal Author',
        ];

        $response = $this->post(route('authors.store'), $payload);
        $response->assertRedirect();
        $this->assertDatabaseHas('authors', [
            'name' => 'Minimal Author',
        ]);
    }
}
