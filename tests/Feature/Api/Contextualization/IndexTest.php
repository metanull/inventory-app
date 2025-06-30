<?php

namespace Tests\Feature\Api\Contextualization;

use App\Models\Contextualization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class IndexTest extends TestCase
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

    public function test_authenticated_user_can_access_index(): void
    {
        Contextualization::factory(3)->create();

        $response = $this->getJson(route('contextualization.index'));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
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
            ],
            'links',
            'meta',
        ]);
    }

    public function test_index_returns_paginated_results(): void
    {
        Contextualization::factory(25)->create();

        $response = $this->getJson(route('contextualization.index'));

        $response->assertOk();
        $response->assertJsonStructure([
            'data',
            'links' => [
                'first',
                'last',
                'prev',
                'next',
            ],
            'meta' => [
                'current_page',
                'from',
                'last_page',
                'per_page',
                'to',
                'total',
            ],
        ]);

        $this->assertCount(15, $response->json('data')); // Default pagination limit
    }

    public function test_index_includes_relationships(): void
    {
        $contextualization = Contextualization::factory()->create();

        $response = $this->getJson(route('contextualization.index'));

        $response->assertOk();
        $response->assertJsonPath('data.0.context.id', $contextualization->context_id);

        if ($contextualization->item_id) {
            $response->assertJsonPath('data.0.item.id', $contextualization->item_id);
        } else {
            $response->assertJsonPath('data.0.detail.id', $contextualization->detail_id);
        }
    }

    public function test_index_returns_empty_when_no_contextualizations(): void
    {
        $response = $this->getJson(route('contextualization.index'));

        $response->assertOk();
        $response->assertJsonPath('data', []);
        $response->assertJsonPath('meta.total', 0);
    }
}
