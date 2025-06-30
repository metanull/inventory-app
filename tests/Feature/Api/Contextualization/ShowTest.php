<?php

namespace Tests\Feature\Api\Contextualization;

use App\Models\Contextualization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_authenticated_user_can_view_contextualization(): void
    {
        $contextualization = Contextualization::factory()->create();

        $response = $this->getJson(route('contextualization.show', $contextualization));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'context_id',
                'item_id',
                'detail_id',
                'extra',
                'internal_name',
                'backward_compatibility',
                'created_at',
                'updated_at',
                'context',
                'item',
                'detail',
            ],
        ]);
        $response->assertJsonPath('data.id', $contextualization->id);
    }

    public function test_show_includes_context_relationship(): void
    {
        $contextualization = Contextualization::factory()->create();

        $response = $this->getJson(route('contextualization.show', $contextualization));

        $response->assertOk();
        $response->assertJsonPath('data.context.id', $contextualization->context_id);
    }

    public function test_show_includes_item_relationship_when_present(): void
    {
        $contextualization = Contextualization::factory()->forItem()->create();

        $response = $this->getJson(route('contextualization.show', $contextualization));

        $response->assertOk();
        $response->assertJsonPath('data.item.id', $contextualization->item_id);
        $response->assertJsonPath('data.detail', null);
    }

    public function test_show_includes_detail_relationship_when_present(): void
    {
        $contextualization = Contextualization::factory()->forDetail()->create();

        $response = $this->getJson(route('contextualization.show', $contextualization));

        $response->assertOk();
        $response->assertJsonPath('data.detail.id', $contextualization->detail_id);
        $response->assertJsonPath('data.item', null);
    }

    public function test_show_returns_not_found_for_nonexistent_contextualization(): void
    {
        $response = $this->getJson(route('contextualization.show', '99999999-9999-9999-9999-999999999999'));

        $response->assertNotFound();
    }

    public function test_show_displays_extra_data_when_present(): void
    {
        $extraData = ['key1' => 'value1', 'key2' => 'value2'];
        $contextualization = Contextualization::factory()->create([
            'extra' => $extraData,
        ]);

        $response = $this->getJson(route('contextualization.show', $contextualization));

        $response->assertOk();
        $response->assertJsonPath('data.extra', $extraData);
    }

    public function test_show_displays_null_extra_when_not_set(): void
    {
        $contextualization = Contextualization::factory()->create([
            'extra' => null,
        ]);

        $response = $this->getJson(route('contextualization.show', $contextualization));

        $response->assertOk();
        $response->assertJsonPath('data.extra', null);
    }
}
