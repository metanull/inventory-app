<?php

namespace Tests\Feature\Api\Language;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_factory(): void
    {
        $language = \App\Models\Language::factory()->create();

        $this->assertDatabaseHas('languages', [
            'id' => $language->id,
            'internal_name' => $language->internal_name,
            'backward_compatibility' => $language->backward_compatibility,
            'is_default' => $language->is_default,
        ]);
    }

    public function test_factory_without_is_default(): void
    {
        $language = \App\Models\Language::factory()->create();

        $this->assertDatabaseHas('languages', [
            'id' => $language->id,
            'internal_name' => $language->internal_name,
            'backward_compatibility' => $language->backward_compatibility,
            'is_default' => false,
        ]);
    }

    public function test_factory_with_is_default(): void
    {
        $language = \App\Models\Language::factory()->withIsDefault()->create();

        $this->assertDatabaseHas('languages', [
            'id' => $language->id,
            'internal_name' => $language->internal_name,
            'backward_compatibility' => $language->backward_compatibility,
            'is_default' => true,
        ]);
    }

    public function test_store_forbids_anonymous_access(): void
    {
        $response = $this->postJson(route('language.store'), [
                'id' => 'TST',
                'internal_name' => 'Test Language',
                'backward_compatibility' => 'TT',
            ]);
        $response->assertUnauthorized();
    }

    public function test_store_allows_authenticated_users(): void
    {
        $response = $this->postJson(route('language.store'), [
            'id' => 'TST',
            'internal_name' => 'Test Language',
            'backward_compatibility' => 'TT',
        ]);
        $response->assertCreated();
    }

    public function test_store_validates_its_input(): void
    {
        $response = $this->postJson(route('language.store'), [
            'id' => 'TST',
            // 'internal_name' is required
            'backward_compatibility' => null,
            'is_default' => true, // is not allowed to be set during creation
        ]);

        $response->assertJsonValidationErrors(['internal_name', 'is_default']);
    }

    public function test_store_returns_unprocessable_when_input_is_invalid(): void
    {
        $response = $this->postJson(route('language.store'), [
            'id' => 'TST',
            // 'internal_name' is required
            'backward_compatibility' => null,
            'is_default' => true, // is not allowed to be set during creation
        ]);

        $response->assertUnprocessable();
    }

    public function test_store_inserts_a_row(): void
    {
        $response = $this->postJson(route('language.store'), [
            'id' => 'TST',
            'internal_name' => 'Test Language',
            'backward_compatibility' => 'TT',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('languages', [
            'id' => 'TST',
            'internal_name' => 'Test Language',
            'backward_compatibility' => 'TT',
        ]);
    }

    public function test_store_inserts_a_row_and_is_default_is_false(): void
    {
        $response = $this->postJson(route('language.store'), [
            'id' => 'TST',
            'internal_name' => 'Test Language',
            'backward_compatibility' => 'TT',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('languages', [
            'id' => 'TST',
            'is_default' => false,
        ]);
    }

    public function test_store_returns_created_on_success(): void
    {
        $response = $this->postJson(route('language.store'), [
            'id' => 'TST',
            'internal_name' => 'Test Language',
            'backward_compatibility' => 'TT',
        ]);

        $response->assertCreated();
    }

    public function test_store_returns_the_expected_structure(): void
    {
        $response = $this->postJson(route('language.store'), [
            'id' => 'TST',
            'internal_name' => 'Test Language',
            'backward_compatibility' => 'TT',
        ]);

        $response->assertJsonStructure([
            'data' => [
                'id',
                'internal_name',
                'backward_compatibility',
                'is_default',
            ],
        ]);
    }

    public function test_store_returns_the_expected_data(): void
    {
        $response = $this->postJson(route('language.store'), [
            'id' => 'TST',
            'internal_name' => 'Test Language',
            'backward_compatibility' => 'TT',
        ]);

        $response->assertJsonPath('data.id', 'TST')
            ->assertJsonPath('data.internal_name', 'Test Language')
            ->assertJsonPath('data.backward_compatibility', 'TT');
    }

    public function test_store_returns_the_expected_data_and_is_default_is_false(): void
    {
        $response = $this->postJson(route('language.store'), [
            'id' => 'TST',
            'internal_name' => 'Test Language',
            'backward_compatibility' => 'TT',
        ]);

        $response->assertJsonPath('data.is_default', false);
    }
}
