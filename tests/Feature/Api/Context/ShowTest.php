<?php

namespace Tests\Feature\Api\Context;

use App\Models\Context;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /**
     * Authentication: show allows authenticated users.
     */
    public function test_show_allows_authenticated_users()
    {
        $context = Context::factory()->create();
        $response = $this->getJson(route('context.show', $context));
        $response->assertOk();
    }

    /**
     * Process: show returns one row.
     */
    public function test_show_returns_one_row()
    {
        $context = Context::factory()->create();

        $response = $this->getJson(route('context.show', $context));
        $response->assertOk();
        $response->assertJsonPath('data.id', $context->id);
    }

    /**
     * Response: show returns ok on success.
     */
    public function test_show_returns_ok_on_success()
    {
        $context = Context::factory()->create();

        $response = $this->getJson(route('context.show', $context));
        $response->assertOk();
    }

    /**
     * Response: show returns not found when record does not exist.
     */
    public function test_show_returns_not_found_when_record_does_not_exist()
    {
        $response = $this->getJson(route('context.show', 'non-existent-id'));
        $response->assertNotFound();
    }

    /**
     * Response: show returns the expected structure.
     */
    public function test_show_returns_the_default_structure_without_relations()
    {
        $context = Context::factory()->create();

        $response = $this->getJson(route('context.show', $context));
        $response->assertJsonStructure([
            'data' => [
                'id',
                'internal_name',
                'backward_compatibility',
                'is_default',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    /**
     * Response: show returns the expected data.
     */
    public function test_show_returns_the_expected_data()
    {
        $context = Context::factory()->create();

        $response = $this->getJson(route('context.show', $context));
        $response->assertJsonPath('data.id', $context->id);
        $response->assertJsonPath('data.internal_name', $context->internal_name);
        $response->assertJsonPath('data.backward_compatibility', $context->backward_compatibility);
    }
}
