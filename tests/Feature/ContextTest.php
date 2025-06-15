<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ContextTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_the_application_returns_a_successful_response_as_a_user(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->get(route('context.index'));

        $response->assertStatus(200);
    }

    public function test_context_creation(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->postJson(route('context.store'), [
                'internal_name' => 'Test Context',
                'backward_compatibility' => 'TTT',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'internal_name' => 'Test Context',
                    'backward_compatibility' => 'TTT',
                    'is_default' => false,
                ],
            ]);
    }

    public function test_context_update(): void
    {
        $user = User::factory()->create();
        $context = \App\Models\Context::factory()->create([
            'internal_name' => 'Test Context',
            'backward_compatibility' => 'TTT',
        ]);

        $response = $this->actingAs($user)
            ->putJson(route('context.update', $context->id), [
                'internal_name' => 'Updated Context',
                'backward_compatibility' => 'UUU',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $context->id,
                    'internal_name' => 'Updated Context',
                    'backward_compatibility' => 'UUU',
                    'is_default' => false,
                ],
            ]);
    }

    public function test_context_update__is_default__is_prohibited(): void
    {
        $user = User::factory()->create();
        $context = \App\Models\Context::factory()->create([
            'internal_name' => 'Test Context',
            'backward_compatibility' => 'TTT',
        ]);

        $response = $this->actingAs($user)
            ->putJson(route('context.update', $context->id), [
                'internal_name' => 'Updated Context',
                'backward_compatibility' => 'UUU',
                'is_default' => true,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['is_default']);
    }

    public function test_context_deletion(): void
    {
        $user = User::factory()->create();
        $context = \App\Models\Context::factory()->create([
            'internal_name' => 'Test Context',
            'backward_compatibility' => 'TTT',
        ]);

        $response = $this->actingAs($user)
            ->deleteJson(route('context.destroy', $context->id));

        $response->assertStatus(204);
        $this->assertDatabaseMissing('contexts', ['id' => $context->id]);
    }

    public function test_context_retrieval(): void
    {
        $user = User::factory()->create();
        $context = \App\Models\Context::factory()->create([
            'internal_name' => 'Test Context',
            'backward_compatibility' => 'TTT',
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('context.show', $context->id));

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $context->id,
                    'internal_name' => 'Test Context',
                    'backward_compatibility' => 'TTT',
                    'is_default' => false,
                ],
            ]);
    }

    public function test_context_retrieval__default_context(): void
    {
        $user = User::factory()->create();
        $context = \App\Models\Context::factory()->withIsDefault()->create([
            'internal_name' => 'Test Context',
            'backward_compatibility' => 'TTT',
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('context.show', $context->id));

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $context->id,
                    'internal_name' => 'Test Context',
                    'backward_compatibility' => 'TTT',
                    'is_default' => true,
                ],
            ]);
    }

    public function test_context_retrieval__not_found(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->getJson(route('context.show', 'NON_EXISTENT'));

        $response->assertStatus(404);
    }

    public function test_context_list_retrieval(): void
    {
        $user = User::factory()->create();
        $context1 = \App\Models\Context::factory()->create([
            'internal_name' => 'Context1',
            'backward_compatibility' => 'TT1',
        ]);
        $context2 = \App\Models\Context::factory()->create([
            'internal_name' => 'Context2',
            'backward_compatibility' => 'TT2',
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('context.index'));

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    ['id' => $context1->id, 'internal_name' => 'Context1', 'backward_compatibility' => 'TT1', 'is_default' => false],
                    ['id' => $context2->id, 'internal_name' => 'Context2', 'backward_compatibility' => 'TT2', 'is_default' => false],
                ],
            ]);
    }

    public function test_context_list_retrieval__empty(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->getJson(route('context.index'));

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function test_context_list_retrieval__default_context(): void
    {
        $user = User::factory()->create();
        $context = \App\Models\Context::factory()->withIsDefault()->create([
            'internal_name' => 'Default Context',
            'backward_compatibility' => 'DDD',
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('context.index'));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $context->id,
                'internal_name' => 'Default Context',
                'backward_compatibility' => 'DDD',
                'is_default' => true,
            ]);
    }
}
