<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Collections;

use App\Models\Context;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_store_persists_collection_and_redirects(): void
    {
        $lang = Language::factory()->create(['id' => 'ENG']);
        $ctx = Context::factory()->create();

        $payload = [
            'internal_name' => 'Test Collection',
            'type' => 'collection',
            'language_id' => $lang->id,
            'context_id' => $ctx->id,
            'backward_compatibility' => 'COL-LEG',
        ];

        $response = $this->post(route('collections.store'), $payload);
        $response->assertRedirect();
        $this->assertDatabaseHas('collections', [
            'internal_name' => 'Test Collection',
            'type' => 'collection',
            'language_id' => $lang->id,
            'context_id' => $ctx->id,
        ]);
    }

    public function test_store_validation_errors(): void
    {
        $response = $this->post(route('collections.store'), [
            'internal_name' => '',
            'language_id' => 'zz',
            'context_id' => 'not-a-uuid',
        ]);
        $response->assertSessionHasErrors(['internal_name', 'language_id', 'context_id']);
    }
}
