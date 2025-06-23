<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Language;
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

    public function test_api_process_store_validates_its_input(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->postJson(route('language.store'), [
                'id' => 'TST',
                'internal_name' => 'Test Language',
                // 'backward_compatibility' is missing
            ]);

        $response->assertJsonValidationErrors(['backward_compatibility']);
    }

    public function test_api_response_store_returns_unprocessable_when_input_is_invalid(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->postJson(route('language.store'), [
                'id' => 'TST',
                'internal_name' => 'Test Language',
                // 'backward_compatibility' is missing
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




    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->getJson('/');
        $response->assertOk();
    }

    public function test_the_application_returns_a_successful_response_as_a_user(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->get(route('language.index'));

        $response->assertOk();
    }

    public function test_language_creation(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->postJson(route('language.store'), [
                'id' => 'TST',
                'internal_name' => 'Test Language',
                'backward_compatibility' => 'TT',
            ]);

        $response->assertCreated()
            ->assertJson([
                'data' => [
                    'id' => 'TST',
                    'internal_name' => 'Test Language',
                    'backward_compatibility' => 'TT',
                    'is_default' => false,
                ],
            ]);
    }

    public function test_language_update(): void
    {
        $user = User::factory()->create();
        $language = \App\Models\Language::factory()->create([
            'id' => 'TST',
            'internal_name' => 'Test Language',
            'backward_compatibility' => 'TT',
        ]);

        $response = $this->actingAs($user)
            ->putJson(route('language.update', $language->id), [
                'internal_name' => 'Updated Language',
                'backward_compatibility' => 'UU',
            ]);

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => 'TST',
                    'internal_name' => 'Updated Language',
                    'backward_compatibility' => 'UU',
                    'is_default' => false,
                ],
            ]);
    }

    public function test_language_update__is_default__is_prohibited(): void
    {
        $user = User::factory()->create();
        $language = \App\Models\Language::factory()->create([
            'id' => 'TST',
            'internal_name' => 'Test Language',
            'backward_compatibility' => 'TT',
        ]);

        $response = $this->actingAs($user)
            ->putJson(route('language.update', $language->id), [
                'internal_name' => 'Updated Language',
                'backward_compatibility' => 'UU',
                'is_default' => true,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['is_default']);
    }

    public function test_language_deletion(): void
    {
        $user = User::factory()->create();
        $language = \App\Models\Language::factory()->create([
            'id' => 'TST',
            'internal_name' => 'Test Language',
            'backward_compatibility' => 'TT',
        ]);

        $response = $this->actingAs($user)
            ->deleteJson(route('language.destroy', $language->id));

        $response->assertNoContent();
        $this->assertDatabaseMissing('languages', ['id' => 'TST']);
    }

    public function test_language_retrieval(): void
    {
        $user = User::factory()->create();
        $language = \App\Models\Language::factory()->create([
            'id' => 'TST',
            'internal_name' => 'Test Language',
            'backward_compatibility' => 'TT',
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('language.show', $language->id));

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => 'TST',
                    'internal_name' => 'Test Language',
                    'backward_compatibility' => 'TT',
                    'is_default' => false,
                ],
            ]);
    }

    public function test_language_retrieval__default_language(): void
    {
        $user = User::factory()->create();
        $language = \App\Models\Language::factory()->withIsDefault()->create([
            'id' => 'TST',
            'internal_name' => 'Test Language',
            'backward_compatibility' => 'TT',
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('language.show', $language->id));

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => 'TST',
                    'internal_name' => 'Test Language',
                    'backward_compatibility' => 'TT',
                    'is_default' => true,
                ],
            ]);
    }

    public function test_language_retrieval__not_found(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->getJson(route('language.show', 'NON_EXISTENT'));

        $response->assertNotFound();
    }

    public function test_language_list_retrieval(): void
    {
        $user = User::factory()->create();
        $language1 = \App\Models\Language::factory()->create([
            'id' => 'ENG',
            'internal_name' => 'English',
            'backward_compatibility' => 'EN',
        ]);
        $language2 = \App\Models\Language::factory()->create([
            'id' => 'ESP',
            'internal_name' => 'Spanish',
            'backward_compatibility' => 'ES',
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('language.index'));

        $response->assertOk()
            ->assertJson([
                'data' => [
                    ['id' => 'ENG', 'internal_name' => 'English', 'backward_compatibility' => 'EN', 'is_default' => false],
                    ['id' => 'ESP', 'internal_name' => 'Spanish', 'backward_compatibility' => 'ES', 'is_default' => false],
                ],
            ]);
    }

    public function test_language_list_retrieval__empty(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->getJson(route('language.index'));

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_language_list_retrieval__default_language(): void
    {
        $user = User::factory()->create();
        $language = \App\Models\Language::factory()->withIsDefault()->create([
            'id' => 'TST',
            'internal_name' => 'Test Language',
            'backward_compatibility' => 'TT',
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('language.index'));

        $response->assertOk()
            ->assertJsonFragment([
                'id' => 'TST',
                'internal_name' => 'Test Language',
                'backward_compatibility' => 'TT',
                'is_default' => true,
            ]);
    }
}
