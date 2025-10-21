<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Author;

use App\Models\Author;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class DestroyTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();
    }

    public function test_destroy_deletes_author_and_redirects(): void
    {
        $author = Author::factory()->create();

        $response = $this->delete(route('authors.destroy', $author));
        $response->assertRedirect(route('authors.index'));
        $this->assertDatabaseMissing('authors', [
            'id' => $author->id,
        ]);
    }
}
