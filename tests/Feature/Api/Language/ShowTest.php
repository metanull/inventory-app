<?php

namespace Tests\Feature\Api\Language;

use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class ShowTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;
    use WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createVisitorUser();
        $this->actingAs($this->user);
    }

    public function test_show_allows_authenticated_users(): void
    {
        $language = Language::factory()->create();
        $response = $this->get(route('language.show', $language->id));
        $response->assertOk();
    }

    public function test_show_returns_ok_when_found(): void
    {
        $language = Language::factory()->create();
        $response = $this->getJson(route('language.show', $language->id));
        $response->assertOk();
    }

    public function test_show_returns_not_found_when_not_found(): void
    {
        $response = $this->getJson(route('language.show', 'NON_EXISTENT'));
        $response->assertNotFound();
    }

    public function test_show_returns_the_default_structure_without_relations(): void
    {
        $language = Language::factory()->create();
        $response = $this->getJson(route('language.show', $language->id));

        $response->assertJsonStructure([
            'data' => [
                'id',
                'internal_name',
                'backward_compatibility',
                'is_default',
            ],
        ]);
    }

    public function test_show_returns_the_expected_data(): void
    {
        $language = Language::factory()->create();
        $response = $this->getJson(route('language.show', $language->id));

        $response->assertJsonPath('data.id', $language->id)
            ->assertJsonPath('data.internal_name', $language->internal_name)
            ->assertJsonPath('data.backward_compatibility', $language->backward_compatibility)
            ->assertJsonPath('data.is_default', $language->is_default);
    }

    public function test_show_returns_the_expected_data_with_is_default(): void
    {
        $language = Language::factory()->withIsDefault()->create();
        $response = $this->getJson(route('language.show', $language->id));

        $response->assertJsonPath('data.id', $language->id)
            ->assertJsonPath('data.internal_name', $language->internal_name)
            ->assertJsonPath('data.backward_compatibility', $language->backward_compatibility)
            ->assertJsonPath('data.is_default', $language->is_default);
    }
}
