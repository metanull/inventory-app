<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Languages;

use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_index_lists_languages_with_pagination(): void
    {
        Language::factory()->count(20)->create();
        $response = $this->get(route('languages.index'));
        $response->assertOk();
        $response->assertSee('Languages');
        $response->assertSee('Rows per page');
        $first = Language::query()->orderByDesc('created_at')->first();
        $response->assertSee(e($first->internal_name));
    }

    public function test_index_search_filters_results(): void
    {
        Language::factory()->count(5)->create();
        $target = Language::factory()->create(['internal_name' => 'SPECIAL_LANGUAGE_TOKEN']);

        $response = $this->get(route('languages.index', ['q' => 'SPECIAL_LANGUAGE_TOKEN']));
        $response->assertOk();
        $response->assertSee($target->internal_name);
    }
}
