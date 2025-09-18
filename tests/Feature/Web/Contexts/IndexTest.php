<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Contexts;

use App\Models\Context;
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

    public function test_index_lists_contexts_with_pagination(): void
    {
        Context::factory()->count(20)->create();
        $response = $this->get(route('contexts.index'));
        $response->assertOk();
        $response->assertSee('Contexts');
        $response->assertSee('Rows per page');
        $first = Context::query()->orderByDesc('created_at')->first();
        $response->assertSee(e($first->internal_name));
    }

    public function test_index_search_filters_results(): void
    {
        Context::factory()->count(5)->create();
        $target = Context::factory()->create(['internal_name' => 'SPECIAL_CONTEXT_TOKEN']);

        $response = $this->get(route('contexts.index', ['q' => 'SPECIAL_CONTEXT_TOKEN']));
        $response->assertOk();
        $response->assertSee('SPECIAL_CONTEXT_TOKEN');

        $nonMatch = Context::where('id', '!=', $target->id)->first();
        if ($nonMatch) {
            $response->assertDontSee(e($nonMatch->internal_name));
        }
    }
}
