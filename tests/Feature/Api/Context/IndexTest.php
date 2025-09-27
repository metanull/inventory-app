<?php

namespace Tests\Feature\Api\Context;

use App\Models\Context;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class IndexTest extends TestCase
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
     * Authentication: index allows authenticated users.
     */
    public function test_index_allows_authenticated_users()
    {
        $response = $this->getJson(route('context.index'));
        $response->assertOk();
    }

    /**
     * Process: index returns all rows.
     */
    public function test_index_returns_all_rows()
    {
        Context::factory()->count(3)->create();

        $response = $this->getJson(route('context.index'));
        $response->assertOk();
        $response->assertJsonCount(3, 'data');
    }

    /**
     * Response: index returns ok on success.
     */
    public function test_index_returns_ok_on_success()
    {
        $response = $this->getJson(route('context.index'));
        $response->assertOk();
    }

    /**
     * Response: index returns the expected structure.
     */
    public function test_index_returns_the_expected_structure()
    {
        Context::factory()->count(2)->create();

        $response = $this->getJson(route('context.index'));
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'internal_name',
                    'backward_compatibility',
                    'is_default',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    }

    /**
     * Response: index returns the expected data.
     */
    public function test_index_returns_the_expected_data()
    {
        $contexts = Context::factory()->count(2)->create();

        $response = $this->getJson(route('context.index'));
        foreach ($contexts as $context) {
            $response->assertJsonFragment([
                'id' => $context->id,
                'internal_name' => $context->internal_name,
                'backward_compatibility' => $context->backward_compatibility,
                'is_default' => $context->is_default,
            ]);
        }
    }

    public function test_index_returns_paginated_structure(): void
    {
        Context::factory(5)->create();

        $response = $this->getJson(route('context.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'links' => [
                    'first', 'last', 'prev', 'next',
                ],
                'meta' => [
                    'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
                ],
            ]);
    }

    public function test_index_accepts_pagination_parameters(): void
    {
        Context::factory(5)->create();

        $response = $this->getJson(route('context.index', ['per_page' => 2, 'page' => 1]));

        $response->assertOk()
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.current_page', 1);

        $this->assertCount(2, $response->json('data'));
    }

    public function test_index_validates_pagination_parameters(): void
    {
        $response = $this->getJson(route('context.index', ['per_page' => 0]));
        $response->assertUnprocessable();

        $response = $this->getJson(route('context.index', ['per_page' => 101]));
        $response->assertUnprocessable();

        $response = $this->getJson(route('context.index', ['page' => 0]));
        $response->assertUnprocessable();
    }
}
