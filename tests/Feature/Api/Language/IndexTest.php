<?php

namespace Tests\Feature\Api\Language;

use App\Models\Language;
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

    public function test_index_forbids_anonymous_access(): void
    {
        $response = $this->getJson(route('language.index'));
        $response->assertUnauthorized();
    }

    public function test_index_allows_authenticated_users(): void
    {
        $response = $this->get(route('language.index'));
        $response->assertOk();
    }

    public function test_index_returns_ok_when_no_data(): void
    {
        $response = $this->getJson(route('language.index'));
        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_index_returns_an_empty_array_when_no_data(): void
    {
        $response = $this->getJson(route('language.index'));
        $response->assertJson([
            'data' => [],
        ]);
    }

    public function test_index_returns_the_expected_structure(): void
    {
        $language = Language::factory()->create();

        $response = $this->getJson(route('language.index'));

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'internal_name',
                    'backward_compatibility',
                    'is_default',
                ],
            ],
        ]);
    }

    public function test_index_returns_the_expected_data(): void
    {
        $language1 = Language::factory()->create();
        $language2 = Language::factory()->withIsDefault()->create();

        $response = $this->getJson(route('language.index'));

        $response->assertJsonPath('data.0.id', $language1->id)
            ->assertJsonPath('data.0.internal_name', $language1->internal_name)
            ->assertJsonPath('data.0.backward_compatibility', $language1->backward_compatibility)
            ->assertJsonPath('data.0.is_default', $language1->is_default);

        $response->assertJsonPath('data.1.id', $language2->id)
            ->assertJsonPath('data.1.internal_name', $language2->internal_name)
            ->assertJsonPath('data.1.backward_compatibility', $language2->backward_compatibility)
            ->assertJsonPath('data.1.is_default', $language2->is_default);
    }
}
