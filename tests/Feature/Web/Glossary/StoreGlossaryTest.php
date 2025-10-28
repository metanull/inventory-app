<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Glossary;

use App\Models\Glossary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class StoreGlossaryTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    public function test_store_creates_glossary_with_valid_data(): void
    {
        $this->actingAsDataUser();

        $data = [
            'internal_name' => 'test-term',
            'backward_compatibility' => 'legacy-id',
        ];

        $response = $this->post(route('glossaries.store'), $data);

        $this->assertDatabaseHas('glossaries', [
            'internal_name' => 'test-term',
            'backward_compatibility' => 'legacy-id',
        ]);

        $glossary = Glossary::where('internal_name', 'test-term')->first();
        $response->assertRedirect(route('glossaries.show', $glossary));
        $response->assertSessionHas('success');
    }

    public function test_store_validates_required_internal_name(): void
    {
        $this->actingAsDataUser();

        $data = [
            'backward_compatibility' => 'legacy-id',
        ];

        $response = $this->post(route('glossaries.store'), $data);

        $response->assertSessionHasErrors('internal_name');
        $this->assertDatabaseCount('glossaries', 0);
    }

    public function test_store_allows_optional_backward_compatibility(): void
    {
        $this->actingAsDataUser();

        $data = [
            'internal_name' => 'test-term',
        ];

        $response = $this->post(route('glossaries.store'), $data);

        $this->assertDatabaseHas('glossaries', [
            'internal_name' => 'test-term',
        ]);
    }

    public function test_store_validates_unique_internal_name(): void
    {
        $this->actingAsDataUser();

        Glossary::factory()->create(['internal_name' => 'duplicate-term']);

        $data = [
            'internal_name' => 'duplicate-term',
        ];

        $response = $this->post(route('glossaries.store'), $data);

        $response->assertSessionHasErrors('internal_name');
        $this->assertEquals(1, Glossary::where('internal_name', 'duplicate-term')->count());
    }

    public function test_store_requires_authentication(): void
    {
        $data = [
            'internal_name' => 'test-term',
        ];

        $response = $this->post(route('glossaries.store'), $data);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('glossaries', 0);
    }

    public function test_store_requires_create_data_permission(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $data = [
            'internal_name' => 'test-term',
        ];

        $response = $this->post(route('glossaries.store'), $data);

        $response->assertForbidden();
        $this->assertDatabaseCount('glossaries', 0);
    }
}
