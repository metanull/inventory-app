<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Contexts;

use App\Models\Context;
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

    public function test_destroy_deletes_non_default_and_redirects(): void
    {
        $context = Context::factory()->create(['is_default' => false]);

        $response = $this->delete(route('contexts.destroy', $context));
        $response->assertRedirect(route('contexts.index'));
        $this->assertDatabaseMissing('contexts', ['id' => $context->id]);
    }
}
