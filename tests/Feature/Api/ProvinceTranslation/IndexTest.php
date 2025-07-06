<?php

namespace Tests\Feature\Api\ProvinceTranslation;

use App\Models\ProvinceTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_list_province_translations(): void
    {
        ProvinceTranslation::factory()->count(3)->create();

        $response = $this->getJson(route('province-translation.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'province_id',
                        'language_id',
                        'name',
                        'description',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);

        $this->assertGreaterThanOrEqual(3, count($response->json('data')));
    }

    public function test_index_returns_empty_when_no_province_translations(): void
    {
        $response = $this->getJson(route('province-translation.index'));

        $response->assertOk()
            ->assertJson([
                'data' => [],
            ]);
    }
}
