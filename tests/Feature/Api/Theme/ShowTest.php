<?php

namespace Tests\Feature\Api\Theme;

use App\Models\Theme;
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
        $theme = Theme::factory()->create();
        $response = $this->getJson(route('theme.show', $theme));
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'exhibition_id',
                'parent_id',
                'internal_name',
                'backward_compatibility',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    public function test_show_returns_the_expected_structure_with_all_relations_loaded(): void
    {
        $theme = Theme::factory()->create();
        $response = $this->getJson(route('theme.show', [$theme, 'include' => 'translations,subthemes,subthemes.translations']));
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'exhibition_id',
                'parent_id',
                'internal_name',
                'backward_compatibility',
                'translations',
                'subthemes',
                'created_at',
                'updated_at',
            ],
        ]);
    }
}
