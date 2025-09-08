<?php

namespace Tests\Feature\Api\Language;

use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DefaultTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_set_default_language_successfully(): void
    {
        $language = Language::factory()->create();

        $response = $this->actingAs($this->user)
            ->patchJson(route('language.setDefault', $language->id), [
                'is_default' => true,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.is_default', true);

        $this->assertDatabaseHas('languages', [
            'id' => $language->id,
            'is_default' => true,
        ]);
    }

    public function test_unset_default_language_successfully(): void
    {
        $language = Language::factory()->withIsDefault()->create();

        $response = $this->actingAs($this->user)
            ->patchJson(route('language.setDefault', $language->id), [
                'is_default' => false,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.is_default', false);

        $this->assertDatabaseHas('languages', [
            'id' => $language->id,
            'is_default' => false,
        ]);
    }

    public function test_set_default_language_clears_previous_default(): void
    {
        $existingDefault = Language::factory()->withIsDefault()->create();
        $newDefault = Language::factory()->create();

        $response = $this->actingAs($this->user)
            ->patchJson(route('language.setDefault', $newDefault->id), [
                'is_default' => true,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.is_default', true);

        $this->assertDatabaseHas('languages', [
            'id' => $newDefault->id,
            'is_default' => true,
        ]);

        $this->assertDatabaseHas('languages', [
            'id' => $existingDefault->id,
            'is_default' => false,
        ]);
    }

    public function test_clear_default_language_successfully(): void
    {
        Language::factory()->withIsDefault()->create();

        $response = $this->actingAs($this->user)
            ->deleteJson(route('language.clearDefault'));

        $response->assertOk()
            ->assertJsonPath('message', 'Default language cleared');

        $this->assertDatabaseMissing('languages', [
            'is_default' => true,
        ]);
    }

    public function test_clear_default_language_when_no_default_exists(): void
    {
        Language::factory()->create();

        $response = $this->actingAs($this->user)
            ->deleteJson(route('language.clearDefault'));

        $response->assertOk()
            ->assertJsonPath('message', 'Default language cleared');
    }

    public function test_get_default_language_successfully(): void
    {
        $language = Language::factory()->withIsDefault()->create();

        $response = $this->actingAs($this->user)
            ->getJson(route('language.getDefault'));

        $response->assertOk()
            ->assertJsonPath('data.id', $language->id)
            ->assertJsonPath('data.is_default', true);
    }

    public function test_get_default_language_when_no_default_exists(): void
    {
        Language::factory()->create();

        $response = $this->actingAs($this->user)
            ->getJson(route('language.getDefault'));

        $response->assertNotFound()
            ->assertJsonPath('message', 'No default language found');
    }

    public function test_set_default_language_requires_authentication(): void
    {
        $language = Language::factory()->create();

        $response = $this->patchJson(route('language.setDefault', $language->id), [
            'is_default' => true,
        ]);

        $response->assertUnauthorized();
    }

    public function test_clear_default_language_requires_authentication(): void
    {
        $response = $this->deleteJson(route('language.clearDefault'));

        $response->assertUnauthorized();
    }

    public function test_get_default_language_requires_authentication(): void
    {
        $response = $this->getJson(route('language.getDefault'));

        $response->assertUnauthorized();
    }

    public function test_set_default_language_validates_is_default_required(): void
    {
        $language = Language::factory()->create();

        $response = $this->actingAs($this->user)
            ->patchJson(route('language.setDefault', $language->id), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['is_default']);
    }

    public function test_set_default_language_validates_is_default_boolean(): void
    {
        $language = Language::factory()->create();

        $response = $this->actingAs($this->user)
            ->patchJson(route('language.setDefault', $language->id), [
                'is_default' => 'not-boolean',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['is_default']);
    }

    public function test_set_default_language_requires_valid_language(): void
    {
        $response = $this->actingAs($this->user)
            ->patchJson(route('language.setDefault', 'invalid'), [
                'is_default' => true,
            ]);

        $response->assertNotFound();
    }
}
