<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Parity;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemsParityTest extends TestCase
{
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_api_meta_total_matches_web_count_first_page(): void
    {
        Item::factory()->count(28)->create();

        $api = $this->getJson(route('item.index', ['per_page' => 25]));
        $api->assertOk();
        $this->assertSame(28, $api->json('meta.total'));

        $web = $this->get(route('items.index', ['perPage' => 25]));
        $web->assertOk();
        $rowCount = substr_count($web->getContent(), '<tr');
        $this->assertGreaterThanOrEqual(25, $rowCount - 1);
    }
}
