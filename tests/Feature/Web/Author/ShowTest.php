<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Author;

use App\Models\Author;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class ShowTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();
    }

    public function test_show_displays_core_fields(): void
    {
        $author = Author::factory()->create([
            'name' => 'Jane Doe',
            'internal_name' => 'J. Doe',
            'backward_compatibility' => 'LEG-AUTH',
        ]);

        $response = $this->get(route('authors.show', $author));
        $response->assertOk();
        $response->assertSee('Jane Doe');
        $response->assertSee('J. Doe');
        $response->assertSee('LEG-AUTH');
    }
}
