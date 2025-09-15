<?php

namespace Tests\Feature\Api\Exhibition;

use App\Models\Exhibition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_show_returns_the_default_structure_without_relations(): void
    {
        $exhibition = Exhibition::factory()->create();
        $response = $this->getJson(route('exhibition.show', $exhibition));
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'internal_name',
                'backward_compatibility',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    public function test_show_returns_the_expected_structure_with_all_relations_loaded(): void
    {
        $exhibition = Exhibition::factory()->create();
        $response = $this->getJson(route('exhibition.show', [$exhibition, 'include' => 'translations,partners']));
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'internal_name',
                'backward_compatibility',
                'translations',
                'partners',
                'created_at',
                'updated_at',
            ],
        ]);
    }
}
