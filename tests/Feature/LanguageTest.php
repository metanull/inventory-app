<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LanguageTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_language_factory(): void
    {
        $language = \App\Models\Language::factory()->create();

        $this->assertDatabaseHas('languages', [
            'id' => $language->id,
            'internal_name' => $language->internal_name,
            'backward_compatibility' => $language->backward_compatibility,
            'is_default' => $language->is_default,
        ]);
    }

    public function test_language_factory_with_is_default(): void
    {
        $language = \App\Models\Language::factory()->withIsDefault()->create();

        $this->assertDatabaseHas('languages', [
            'id' => $language->id,
            'internal_name' => $language->internal_name,
            'backward_compatibility' => $language->backward_compatibility,
            'is_default' => true,
        ]);
    }

    public function test_api_authentication_index_forbids_anonymous_access(): void
    {
        $response = $this->getJson(route('language.index'));

        $response->assertUnauthorized();
    }

    public function test_api_authentication_index_allows_authenticated_users(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->get(route('language.index'));

        $response->assertOk();
    }

    public function test_api_authentication_show_forbids_anonymous_access(): void
    {
        $response = $this->getJson(route('language.show', 'TST'));

        $response->assertUnauthorized();
    }

    public function test_api_authentication_show_allows_authenticated_users(): void
    {
        $user = User::factory()->create();
        $language = Language::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('language.show', $language->id));

        $response->assertOk();
    }

    public function test_api_authentication_store_forbids_anonymous_access(): void
    {
        $response = $this->postJson(route('language.store'), [
            'id' => 'TST',
            'internal_name' => 'Test Language',
            'backward_compatibility' => 'TT',
        ]);

        $response->assertUnauthorized();
    }

    public function test_api_authentication_store_allows_authenticated_users(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->postJson(route('language.store'), [
                'id' => 'TST',
                'internal_name' => 'Test Language',
                'backward_compatibility' => 'TT',
            ]);

        $response->assertCreated();
    }

    public function test_api_authentication_update_forbids_anonymous_access(): void
    {
        $response = $this->putJson(route('language.update', 'TST'), [
            'internal_name' => 'Updated Language',
            'backward_compatibility' => 'UU',
        ]);

        $response->assertUnauthorized();
    }

    public function test_api_authentication_update_allows_authenticated_users(): void
    {
        $user = User::factory()->create();
        $language = Language::factory()->create();

        $response = $this->actingAs($user)
            ->putJson(route('language.update', $language->id), [
                'internal_name' => 'Updated Language',
                'backward_compatibility' => 'UU',
            ]);

        $response->assertOk();
    }

    public function test_api_authentication_destroy_forbids_anonymous_access(): void
    {
        $response = $this->deleteJson(route('language.destroy', 'TST'));

        $response->assertUnauthorized();
    }

    public function test_api_authentication_destroy_allows_authenticated_users(): void
    {
        $user = User::factory()->create();
        $language = Language::factory()->create();

        $response = $this->actingAs($user)
            ->deleteJson(route('language.destroy', $language->id));

        $response->assertNoContent();
    }

    public function test_api_response_show_returns_not_found_when_not_found(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->getJson(route('language.show', 'NON_EXISTENT'));

        $response->assertNotFound();
    }

    public function test_api_response_show_returns_the_expected_structure(): void
    {
        $user = User::factory()->create();
        $language = Language::factory()->create([
            'id' => 'TST',
            'internal_name' => 'Test Language',
            'backward_compatibility' => 'TT',
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('language.show', $language->id));

        $response->assertJsonStructure([
            'data' => [
                'id',
                'internal_name',
                'backward_compatibility',
                'is_default',
            ],
        ]);
    }

    public function test_api_response_show_returns_the_expected_data(): void
    {
        $user = User::factory()->create();
        $language = Language::factory()->create([
            'id' => 'TST',
            'internal_name' => 'Test Language',
            'backward_compatibility' => 'TT',
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('language.show', $language->id));

        $response->assertJsonPath('data.id', 'TST')
            ->assertJsonPath('data.internal_name', 'Test Language')
            ->assertJsonPath('data.backward_compatibility', 'TT')
            ->assertJsonPath('data.is_default', false);
    }

    public function test_api_response_show_returns_the_expected_data_with_is_default(): void
    {
        $user = User::factory()->create();
        $language = Language::factory()->withIsDefault()->create([
            'id' => 'TST',
            'internal_name' => 'Test Language',
            'backward_compatibility' => 'TT',
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('language.show', $language->id));

        $response->assertJsonPath('data.id', 'TST')
            ->assertJsonPath('data.internal_name', 'Test Language')
            ->assertJsonPath('data.backward_compatibility', 'TT')
            ->assertJsonPath('data.is_default', true);
    }

    public function test_api_response_index_returns_ok_when_no_data(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->getJson(route('language.index'));

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_api_response_index_returns_an_empty_array_when_no_data(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->getJson(route('language.index'));

        $response->assertJson([
            'data' => [],
        ]);
    }

    public function test_api_response_index_returns_the_expected_structure(): void
    {
        $user = User::factory()->create();
        $language = Language::factory()->create([
            'id' => 'TST',
            'internal_name' => 'Test Language',
            'backward_compatibility' => 'TT',
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('language.index'));

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

    public function test_api_response_index_returns_the_expected_data(): void
    {
        $user = User::factory()->create();
        $language1 = Language::factory()->create();
        $language2 = Language::factory()->withIsDefault()->create();

        $response = $this->actingAs($user)
            ->getJson(route('language.index'));

        $response->assertJsonPath('data.0.id', $language1->id)
            ->assertJsonPath('data.0.internal_name', $language1->internal_name)
            ->assertJsonPath('data.0.backward_compatibility', $language1->backward_compatibility)
            ->assertJsonPath('data.0.is_default', $language1->is_default);

        $response->assertJsonPath('data.1.id', $language2->id)
            ->assertJsonPath('data.1.internal_name', $language2->internal_name)
            ->assertJsonPath('data.1.backward_compatibility', $language2->backward_compatibility)
            ->assertJsonPath('data.1.is_default', $language2->is_default);
    }

    public function test_api_validation_store_validates_its_input(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->postJson(route('language.store'), [
                'id' => 'TST',
                //'internal_name' => 'Test Language',
                'backward_compatibility' => null,
                'is_default' => true // is not allowed to be set during creation
            ]);

        $response->assertJsonValidationErrors(['internal_name','is_default']);
    }

    public function test_api_response_store_returns_unprocessable_when_input_is_invalid(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->postJson(route('language.store'), [
                'id' => 'TST',
                //'internal_name' => 'Test Language',
                'backward_compatibility' => null,
                'is_default' => true // is not allowed to be set during creation
            ]);

        $response->assertUnprocessable();
    }

    public function test_api_process_store_inserts_a_row(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->postJson(route('language.store'), [
                'id' => 'TST',
                'internal_name' => 'Test Language',
                'backward_compatibility' => 'TT',
            ]);

        $response->assertCreated();
        $this->assertDatabaseHas('languages', [
            'id' => 'TST',
            'internal_name' => 'Test Language',
            'backward_compatibility' => 'TT',
            'is_default' => false,
        ]);
    }

    public function test_api_response_store_returns_created_on_success(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->postJson(route('language.store'), [
                'id' => 'TST',
                'internal_name' => 'Test Language',
                'backward_compatibility' => 'TT',
            ]);

        $response->assertCreated();
    }

    public function test_api_response_store_returns_the_expected_structure(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->postJson(route('language.store'), [
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

    public function test_api_response_store_returns_the_expected_data(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->postJson(route('language.store'), [
                'id' => 'TST',
                'internal_name' => 'Test Language',
                'backward_compatibility' => 'TT',
            ]);

        $response->assertJson([
            'data' => [
                'id' => 'TST',
                'internal_name' => 'Test Language',
                'backward_compatibility' => 'TT',
                'is_default' => false,
            ],
        ]);
    }

    public function test_api_validation_update_validates_its_input(): void
    {
        $user = User::factory()->create();
        $language = Language::factory()->create();

        $response = $this->actingAs($user)
            ->putJson(route('language.update', $language->id), [
                //'internal_name' => 'Updated Language',
                'backward_compatibility' => null,
                'is_default' => true // is not allowed to be set during creation
            ]);
            

        $response->assertJsonValidationErrors(['internal_name','is_default']);
    }

    public function test_api_validation_update_returns_unprocessable_when_input_is_invalid(): void
    {
        $user = User::factory()->create();
        $language = Language::factory()->create([
            'id' => 'TST',
            'internal_name' => 'Test Language',
            'backward_compatibility' => 'TT',
        ]);

        $response = $this->actingAs($user)
            ->putJson(route('language.update', $language->id), [
                //'internal_name' => 'Updated Language',
                'backward_compatibility' => null,
                'is_default' => true // is not allowed to be set during creation
            ]);

        $response->assertUnprocessable();
    }

    public function test_api_response_update_returns_not_found_response_when_not_found(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->putJson(route('language.update', 'NON_EXISTENT'), [
                'internal_name' => 'Updated Language',
                'backward_compatibility' => 'UU',
            ]);

        $response->assertNotFound();
    }

    public function test_api_process_update_updates_a_row(): void
    {
        $user = User::factory()->create();
        $language = Language::factory()->create([
            'id' => 'TST',
            'internal_name' => 'Test Language',
            'backward_compatibility' => 'TT',
        ]);

        $response = $this->actingAs($user)
            ->putJson(route('language.update', $language->id), [
                'internal_name' => 'Updated Language',
                'backward_compatibility' => 'UU',
            ]);

        $this->assertDatabaseHas('languages', [
            'id' => 'TST',
            'internal_name' => 'Updated Language',
            'backward_compatibility' => 'UU',
            'is_default' => false,
        ]);
    }

    public function test_api_response_update_returns_ok_on_success(): void
    {
        $user = User::factory()->create();
        $language = Language::factory()->create([
            'id' => 'TST',
            'internal_name' => 'Test Language',
            'backward_compatibility' => 'TT',
        ]);

        $response = $this->actingAs($user)
            ->putJson(route('language.update', $language->id), [
                'internal_name' => 'Updated Language',
                'backward_compatibility' => 'UU',
            ]);

        $response->assertOk();
    }

    public function test_api_response_update_returns_the_expected_structure(): void
    {
        $user = User::factory()->create();
        $language = Language::factory()->create([
            'id' => 'TST',
            'internal_name' => 'Test Language',
            'backward_compatibility' => 'TT',
        ]);

        $response = $this->actingAs($user)
            ->putJson(route('language.update', $language->id), [
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

    public function test_api_response_update_returns_the_expected_data(): void
    {
        $user = User::factory()->create();
        $language = Language::factory()->create([
            'id' => 'TST',
            'internal_name' => 'Test Language',
            'backward_compatibility' => 'TT',
        ]);

        $response = $this->actingAs($user)
            ->putJson(route('language.update', $language->id), [
                'internal_name' => 'Updated Language',
                'backward_compatibility' => 'UU',
            ]);

        $response->assertJson([
            'data' => [
                'id' => 'TST',
                'internal_name' => 'Updated Language',
                'backward_compatibility' => 'UU',
                'is_default' => false,
            ],
        ]);
    }

    public function test_api_response_destroy_returns_not_found_response_when_not_found(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->deleteJson(route('language.destroy', 'NON_EXISTENT'));

        $response->assertNotFound();
    }

    public function test_api_process_destroy_deletes_a_row(): void
    {
        $user = User::factory()->create();
        $language = Language::factory()->create([
            'id' => 'TST',
            'internal_name' => 'Test Language',
            'backward_compatibility' => 'TT',
        ]);

        $response = $this->actingAs($user)
            ->deleteJson(route('language.destroy', $language->id));

        $this->assertDatabaseMissing('languages', ['id' => 'TST']);
    }

    public function test_api_response_destroy_returns_no_content_on_success(): void
    {
        $user = User::factory()->create();
        $language = Language::factory()->create([
            'id' => 'TST',
            'internal_name' => 'Test Language',
            'backward_compatibility' => 'TT',
        ]);

        $response = $this->actingAs($user)
            ->deleteJson(route('language.destroy', $language->id));

        $response->assertNoContent();
    }

}
