<?php

namespace Tests\Feature\Api\Context;

use App\Models\Context;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class DefaultTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createDataUser();
    }

    public function test_set_default_context_successfully(): void
    {
        $context = Context::factory()->create();

        $response = $this->actingAs($this->user)
            ->patchJson(route('context.setDefault', $context->id), [
                'is_default' => true,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.is_default', true);

        $this->assertDatabaseHas('contexts', [
            'id' => $context->id,
            'is_default' => true,
        ]);
    }

    public function test_unset_default_context_successfully(): void
    {
        $context = Context::factory()->withIsDefault()->create();

        $response = $this->actingAs($this->user)
            ->patchJson(route('context.setDefault', $context->id), [
                'is_default' => false,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.is_default', false);

        $this->assertDatabaseHas('contexts', [
            'id' => $context->id,
            'is_default' => false,
        ]);
    }

    public function test_set_default_context_clears_previous_default(): void
    {
        $existingDefault = Context::factory()->withIsDefault()->create();
        $newDefault = Context::factory()->create();

        $response = $this->actingAs($this->user)
            ->patchJson(route('context.setDefault', $newDefault->id), [
                'is_default' => true,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.is_default', true);

        $this->assertDatabaseHas('contexts', [
            'id' => $newDefault->id,
            'is_default' => true,
        ]);

        $this->assertDatabaseHas('contexts', [
            'id' => $existingDefault->id,
            'is_default' => false,
        ]);
    }

    public function test_clear_default_context_successfully(): void
    {
        Context::factory()->withIsDefault()->create();

        $response = $this->actingAs($this->user)
            ->deleteJson(route('context.clearDefault'));

        $response->assertOk()
            ->assertJsonPath('message', 'Default context cleared');

        $this->assertDatabaseMissing('contexts', [
            'is_default' => true,
        ]);
    }

    public function test_clear_default_context_when_no_default_exists(): void
    {
        Context::factory()->create();

        $response = $this->actingAs($this->user)
            ->deleteJson(route('context.clearDefault'));

        $response->assertOk()
            ->assertJsonPath('message', 'Default context cleared');
    }

    public function test_get_default_context_successfully(): void
    {
        $context = Context::factory()->withIsDefault()->create();

        $response = $this->actingAs($this->user)
            ->getJson(route('context.getDefault'));

        $response->assertOk()
            ->assertJsonPath('data.id', $context->id)
            ->assertJsonPath('data.is_default', true);
    }

    public function test_get_default_context_when_no_default_exists(): void
    {
        Context::factory()->create();

        $response = $this->actingAs($this->user)
            ->getJson(route('context.getDefault'));

        $response->assertNotFound()
            ->assertJsonPath('message', 'No default context found');
    }

    public function test_set_default_context_requires_authentication(): void
    {
        $context = Context::factory()->create();

        $response = $this->patchJson(route('context.setDefault', $context->id), [
            'is_default' => true,
        ]);

        $response->assertUnauthorized();
    }

    public function test_clear_default_context_requires_authentication(): void
    {
        $response = $this->deleteJson(route('context.clearDefault'));

        $response->assertUnauthorized();
    }

    public function test_get_default_context_requires_authentication(): void
    {
        $response = $this->getJson(route('context.getDefault'));

        $response->assertUnauthorized();
    }

    public function test_set_default_context_validates_is_default_required(): void
    {
        $context = Context::factory()->create();

        $response = $this->actingAs($this->user)
            ->patchJson(route('context.setDefault', $context->id), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['is_default']);
    }

    public function test_set_default_context_validates_is_default_boolean(): void
    {
        $context = Context::factory()->create();

        $response = $this->actingAs($this->user)
            ->patchJson(route('context.setDefault', $context->id), [
                'is_default' => 'not-boolean',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['is_default']);
    }

    public function test_set_default_context_requires_valid_context(): void
    {
        $response = $this->actingAs($this->user)
            ->patchJson(route('context.setDefault', 999), [
                'is_default' => true,
            ]);

        $response->assertNotFound();
    }
}
