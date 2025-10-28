<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Item;

use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class EditTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsDataUser();
    }

    public function test_edit_form_renders(): void
    {
        $item = Item::factory()->create();
        $response = $this->get(route('items.edit', $item));
        $response->assertOk();
        $response->assertSee('Edit Item');
        $response->assertSee(e($item->internal_name));
    }
}
