<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Collections;

use App\Models\Collection;
use App\Models\Context;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_update_persists_changes_and_redirects(): void
    {
        $collection = Collection::factory()->create([
            'internal_name' => 'Old Coll',
        ]);
        $lang = Language::factory()->create(['id' => 'ENG']);
        $ctx = Context::factory()->create();

        $response = $this->put(route('collections.update', $collection), [
            'internal_name' => 'New Coll',
            'language_id' => $lang->id,
            'context_id' => $ctx->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('collections', [
            'id' => $collection->id,
            'internal_name' => 'New Coll',
            'language_id' => $lang->id,
            'context_id' => $ctx->id,
        ]);
    }
}
