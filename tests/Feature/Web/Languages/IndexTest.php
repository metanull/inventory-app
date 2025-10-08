<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Languages;

use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class IndexTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();
    }

    public function test_index_lists_languages_with_pagination(): void
    {
        // Create a language with a specific ID to ensure deterministic ordering
        $firstLanguage = Language::factory()->create(['id' => 'AAA', 'internal_name' => 'Test Language AAA']);
        Language::factory()->count(19)->create();

        $response = $this->get(route('languages.index'));
        $response->assertOk();
        $response->assertSee('Languages');
        $response->assertSee('Rows per page');
        // Since default sort is by 'id' ASC, 'AAA' should be the first
        $response->assertSee(e($firstLanguage->internal_name));
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
