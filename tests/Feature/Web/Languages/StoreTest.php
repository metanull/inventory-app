<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Languages;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class StoreTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();
    }

    public function test_store_persists_language_and_redirects(): void
    {
        $payload = [
            'id' => 'TST',
            'internal_name' => 'Test Language',
            'backward_compatibility' => 'TL',
        ];

        $response = $this->post(route('languages.store'), $payload);
        $response->assertRedirect();
        $this->assertDatabaseHas('languages', [
            'id' => 'TST',
            'internal_name' => 'Test Language',
        ]);
    }

    public function test_store_validation_errors(): void
    {
        $response = $this->post(route('languages.store'), []);
        $response->assertSessionHasErrors(['id', 'internal_name']);
    }
}
