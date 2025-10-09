<?php

namespace Tests\Feature\Api\Language;

use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class UpdateTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;
    use WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createDataUser();
        $this->actingAs($this->user);
    }

    public function test_update_allows_authenticated_users(): void
    {
        $language = Language::factory()->create();
        $response = $this->putJson(route('language.update', $language->id), [
            'internal_name' => 'Updated Language',
            'backward_compatibility' => 'UU',
        ]);
        $response->assertOk();
    }

    public function test_update_validates_its_input(): void
    {
        $language = Language::factory()->create();

        $response = $this->putJson(route('language.update', $language->id), [
            // 'internal_name' => 'Updated Language',
            'backward_compatibility' => null,
            'is_default' => true, // is not allowed to be set during creation
        ]);

        $response->assertJsonValidationErrors(['internal_name', 'is_default']);
    }

    public function test_update_returns_unprocessable_when_input_is_invalid(): void
    {
        $language = Language::factory()->create();

        $response = $this->putJson(route('language.update', $language->id), [
            // 'internal_name' => 'Updated Language',
            'backward_compatibility' => null,
            'is_default' => true, // is not allowed to be set during creation
        ]);

        $response->assertUnprocessable();
    }

    public function test_update_returns_not_found_response_when_not_found(): void
    {
        $response = $this->putJson(route('language.update', 'NON_EXISTENT'), [
            'internal_name' => 'Updated Language',
            'backward_compatibility' => 'UU',
        ]);

        $response->assertNotFound();
    }

    public function test_update_updates_a_row(): void
    {
        $language = Language::factory()->create();

        $response = $this->putJson(route('language.update', $language->id), [
            'internal_name' => 'Updated Language',
            'backward_compatibility' => 'UU',
        ]);

        $this->assertDatabaseHas('languages', [
            'id' => $language->id,
            'internal_name' => 'Updated Language',
            'backward_compatibility' => 'UU',
            'is_default' => $language->is_default,
        ]);
    }

    public function test_update_updates_a_row_without_changing_its_is_default_value(): void
    {
        $language = Language::factory()->withIsDefault()->create();

        $response = $this->putJson(route('language.update', $language->id), [
            'internal_name' => 'Updated Language',
            'backward_compatibility' => 'UU',
        ]);

        $this->assertDatabaseHas('languages', [
            'id' => $language->id,
            'internal_name' => 'Updated Language',
            'backward_compatibility' => 'UU',
            'is_default' => $language->is_default,
        ]);
    }

    public function test_update_returns_ok_on_success(): void
    {
        $language = Language::factory()->create();

        $response = $this->putJson(route('language.update', $language->id), [
            'internal_name' => 'Updated Language',
            'backward_compatibility' => 'UU',
        ]);

        $response->assertOk();
    }

    public function test_update_returns_the_expected_structure(): void
    {
        $language = Language::factory()->create();

        $response = $this->putJson(route('language.update', $language->id), [
            'internal_name' => 'Updated Language',
            'backward_compatibility' => 'UU',
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

    public function test_update_returns_the_expected_data(): void
    {
        $language = Language::factory()->create();

        $response = $this->putJson(route('language.update', $language->id), [
            'internal_name' => 'Updated Language',
            'backward_compatibility' => 'UU',
        ]);

        $response->assertJsonPath('data.id', $language->id)
            ->assertJsonPath('data.internal_name', 'Updated Language')
            ->assertJsonPath('data.backward_compatibility', 'UU')
            ->assertJsonPath('data.is_default', $language->is_default);
    }
}
