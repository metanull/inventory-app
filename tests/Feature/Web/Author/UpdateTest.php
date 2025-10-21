<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Author;

use App\Models\Author;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class UpdateTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();
    }

    public function test_update_modifies_author_and_redirects(): void
    {
        $author = Author::factory()->create([
            'name' => 'Original Name',
        ]);

        $payload = [
            'name' => 'Updated Name',
            'internal_name' => 'Updated Internal',
        ];

        $response = $this->put(route('authors.update', $author), $payload);
        $response->assertRedirect();
        $this->assertDatabaseHas('authors', [
            'id' => $author->id,
            'name' => 'Updated Name',
            'internal_name' => 'Updated Internal',
        ]);
    }

    public function test_update_validation_errors(): void
    {
        $author = Author::factory()->create();

        $response = $this->put(route('authors.update', $author), [
            'name' => '',
        ]);
        $response->assertSessionHasErrors(['name']);
    }
}
