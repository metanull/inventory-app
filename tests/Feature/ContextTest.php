<?php

namespace Tests\Feature;

use App\Models\Context;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ContextTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_index_requires_authentication(): void
    {
        $response_anonymous = $this->getJson(route('context.index'));
        $response_anonymous->assertUnauthorized();

        $user = User::factory()->create();
        $response_authenticated = $this->actingAs($user)
            ->getJson(route('context.index'));
        $response_authenticated->assertOk();
    }

    public function test_show_requires_authentication(): void
    {
        $context = Context::factory()->create();

        $response_anonymous = $this->getJson(route('context.show', $context->id));
        $response_anonymous->assertUnauthorized();

        $user = User::factory()->create();
        $response_authenticated = $this->actingAs($user)
            ->getJson(route('context.show', $context->id));
        $response_authenticated->assertOk();
    }

    public function test_store_requires_authentication(): void
    {
        $response_anonymous = $this->postJson(route('context.store'), [
            'internal_name' => 'Test Context',
            'backward_compatibility' => 'TC',
        ]);
        $response_anonymous->assertUnauthorized();

        $user = User::factory()->create();
        $response_authenticated = $this->actingAs($user)
            ->postJson(route('context.store'), [
                'internal_name' => 'Test Context',
                'backward_compatibility' => 'TC',
            ]);
        $response_authenticated->assertCreated();
    }

    public function test_update_requires_authentication(): void
    {
        $context = Context::factory()->create();

        $response_anonymous = $this->putJson(route('context.update', $context->id), [
            'internal_name' => 'Updated Context',
            'backward_compatibility' => 'UP',
        ]);
        $response_anonymous->assertUnauthorized();

        $user = User::factory()->create();
        $response_authenticated = $this->actingAs($user)
            ->putJson(route('context.update', $context->id), [
                'internal_name' => 'Updated Context',
                'backward_compatibility' => 'UP',
            ]);
        $response_authenticated->assertOk();
    }

    public function test_destroy_requires_authentication(): void
    {
        $context = Context::factory()->create();

        $response_anonymous = $this->deleteJson(route('context.destroy', $context->id));
        $response_anonymous->assertUnauthorized();

        $user = User::factory()->create();
        $response_authenticated = $this->actingAs($user)
            ->deleteJson(route('context.destroy', $context->id));
        $response_authenticated->assertNoContent();
    }

    public function test_show_returns_a_well_structured_response(): void
    {
        $user = User::factory()->create();
        $context = Context::factory()->create();
        $response = $this->actingAs($user)
            ->getJson(route('context.show', $context->id));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'internal_name',
                    'backward_compatibility',
                    'is_default',
                ],
            ])
            ->assertJsonFragment([
                'id' => $context->id,
                'internal_name' => $context->internal_name,
                'backward_compatibility' => $context->backward_compatibility,
                'is_default' => $context->is_default,
            ]);
    }

    public function test_index_returns_a_well_structured_response(): void
    {
        $user = User::factory()->create();
        $context = Context::factory()->create();
        $response = $this->actingAs($user)
            ->getJson(route('context.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'internal_name',
                        'backward_compatibility',
                        'is_default',
                    ],
                ],
            ])
            ->assertJsonFragment([
                'id' => $context->id,
                'internal_name' => $context->internal_name,
                'backward_compatibility' => $context->backward_compatibility,
                'is_default' => $context->is_default,
            ]);
    }

    public function test_store_returns_a_well_structured_response(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->postJson(route('context.store'), [
                'internal_name' => 'Test Context',
                'backward_compatibility' => 'TC',
            ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'internal_name',
                    'backward_compatibility',
                    'is_default',
                ],
            ])
            ->assertJsonFragment([
                'internal_name' => 'Test Context',
                'backward_compatibility' => 'TC',
                'is_default' => false,
            ]);
    }

    public function test_update_returns_a_well_structured_response(): void
    {
        $user = User::factory()->create();
        $context = Context::factory()->create();
        $response = $this->actingAs($user)
            ->putJson(route('context.update', $context->id), [
                'internal_name' => 'Updated Context',
                'backward_compatibility' => 'UP',
            ]);
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'internal_name',
                    'backward_compatibility',
                    'is_default',
                ],
            ])
            ->assertJsonFragment([
                'id' => $context->id,
                'internal_name' => 'Updated Context',
                'backward_compatibility' => 'UP',
                'is_default' => false,
            ]);
    }

    public function test_destroy_returns_no_content(): void
    {
        $user = User::factory()->create();
        $context = Context::factory()->create();

        $response = $this->actingAs($user)
            ->deleteJson(route('context.destroy', $context->id));

        $response->assertNoContent();
        $this->assertDatabaseMissing('contexts', ['id' => $context->id]);
    }

    public function test_index_returns_empty_response_when_no_data(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->getJson(route('context.index'));

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_show_returns_not_found_response_when_not_found(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->getJson(route('context.show', 'nonexistent'));

        $response->assertNotFound();
    }

    public function test_update_returns_not_found_response_when_not_found(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->putJson(route('context.update', 'nonexistent'), [
                'internal_name' => 'Updated Context',
                'backward_compatibility' => 'UP',
            ]);

        $response->assertNotFound();
    }

    public function test_destroy_returns_not_found_response_when_not_found(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->deleteJson(route('context.destroy', 'nonexistent'));

        $response->assertNotFound();
    }

    public function test_store_returns_unprocessable_and_adequate_validation_errors(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->postJson(route('context.store'), [
                'id' => 'invalid-id', // Invalid: prohibited field
                'internal_name' => '', // Invalid: required field
                'backward_compatibility' => 'TC',
                'is_default' => true, // Invalid: prohibited field
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['id', 'internal_name', 'is_default']);
    }

    public function test_update_returns_unprocessable_and_adequate_validation_errors(): void
    {
        $user = User::factory()->create();
        $context = Context::factory()->create();

        $response = $this->actingAs($user)
            ->putJson(route('context.update', $context->id), [
                'id' => 'invalid-id', // Invalid: prohibited field
                'internal_name' => '', // Invalid: required field
                'backward_compatibility' => 'UP',
                'is_default' => true, // Invalid: prohibited field
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['id', 'internal_name', 'is_default']);
    }

    public function test_get_default_context_returns_the_default_one(): void
    {
        $user = User::factory()->create();
        $a_context = Context::factory()->create();
        $b_context = Context::factory()->withIsDefault()->create();
        $c_context = Context::factory()->create();

        $response = $this->actingAs($user)
            ->getJson(route('context.getDefault'), []);

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'internal_name' => $b_context->internal_name,
                    'backward_compatibility' => $b_context->backward_compatibility,
                    'is_default' => true,
                ],
            ]);
    }

    public function test_set_context_as_default_makes_one_context_the_default_one(): void
    {
        $user = User::factory()->create();
        $a_context = Context::factory()->create();
        $b_context = Context::factory()->withIsDefault()->create();
        $c_context = Context::factory()->create();

        $response = $this->actingAs($user)
            ->patchJson(route('context.setDefault', $c_context->id), [
                'is_default' => true,
            ]);
        $response->assertOk()
            ->assertJson([
                'data' => [
                    'internal_name' => $c_context->internal_name,
                    'backward_compatibility' => $c_context->backward_compatibility,
                    'is_default' => true,
                ],
            ]);

        $response2 = $this->actingAs($user)
            ->getJson(route('context.getDefault'), []);
        $response2->assertOk()
            ->assertJson([
                'data' => [
                    'internal_name' => $c_context->internal_name,
                    'backward_compatibility' => $c_context->backward_compatibility,
                    'is_default' => true,
                ],
            ]);
    }
}
