<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LanguageTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_the_application_returns_a_successful_response_as_a_user(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->get(route('language.index'));

        $response->assertStatus(200);
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

        $response->assertStatus(201)
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

        $response->assertStatus(200)
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

        $response->assertStatus(422)
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

        $response->assertStatus(204);
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

        $response->assertStatus(200)
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

        $response->assertStatus(200)
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

        $response->assertStatus(404);
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

        $response->assertStatus(200)
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

        $response->assertStatus(200)
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

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => 'TST',
                'internal_name' => 'Test Language',
                'backward_compatibility' => 'TT',
                'is_default' => true,
            ]);
    }
}
