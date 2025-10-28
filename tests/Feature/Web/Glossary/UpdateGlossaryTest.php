<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Glossary;

use App\Models\Glossary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class UpdateGlossaryTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    public function test_update_modifies_glossary_with_valid_data(): void
    {
        $this->actingAsDataUser();

        $glossary = Glossary::factory()->create([
            'internal_name' => 'original-term',
            'backward_compatibility' => 'old-id',
        ]);

        $data = [
            'internal_name' => 'updated-term',
            'backward_compatibility' => 'new-id',
        ];

        $response = $this->put(route('glossaries.update', $glossary), $data);

        $this->assertDatabaseHas('glossaries', [
            'id' => $glossary->id,
            'internal_name' => 'updated-term',
            'backward_compatibility' => 'new-id',
        ]);

        $response->assertRedirect(route('glossaries.show', $glossary));
        $response->assertSessionHas('success');
    }

    public function test_update_validates_required_internal_name(): void
    {
        $this->actingAsDataUser();

        $glossary = Glossary::factory()->create(['internal_name' => 'original-term']);

        $data = [
            'internal_name' => '',
        ];

        $response = $this->put(route('glossaries.update', $glossary), $data);

        $response->assertSessionHasErrors('internal_name');
        $this->assertDatabaseHas('glossaries', [
            'id' => $glossary->id,
            'internal_name' => 'original-term',
        ]);
    }

    public function test_update_validates_unique_internal_name_except_itself(): void
    {
        $this->actingAsDataUser();

        $other = Glossary::factory()->create(['internal_name' => 'other-term']);
        $glossary = Glossary::factory()->create(['internal_name' => 'original-term']);

        $data = [
            'internal_name' => 'other-term',
        ];

        $response = $this->put(route('glossaries.update', $glossary), $data);

        $response->assertSessionHasErrors('internal_name');
        $this->assertDatabaseHas('glossaries', [
            'id' => $glossary->id,
            'internal_name' => 'original-term',
        ]);
    }

    public function test_update_allows_same_internal_name(): void
    {
        $this->actingAsDataUser();

        $glossary = Glossary::factory()->create([
            'internal_name' => 'same-term',
            'backward_compatibility' => 'old-id',
        ]);

        $data = [
            'internal_name' => 'same-term',
            'backward_compatibility' => 'new-id',
        ];

        $response = $this->put(route('glossaries.update', $glossary), $data);

        $response->assertRedirect(route('glossaries.show', $glossary));
        $this->assertDatabaseHas('glossaries', [
            'id' => $glossary->id,
            'internal_name' => 'same-term',
            'backward_compatibility' => 'new-id',
        ]);
    }

    public function test_update_requires_authentication(): void
    {
        $glossary = Glossary::factory()->create(['internal_name' => 'original-term']);

        $data = [
            'internal_name' => 'updated-term',
        ];

        $response = $this->put(route('glossaries.update', $glossary), $data);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('glossaries', [
            'id' => $glossary->id,
            'internal_name' => 'original-term',
        ]);
    }

    public function test_update_requires_update_data_permission(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $glossary = Glossary::factory()->create(['internal_name' => 'original-term']);

        $data = [
            'internal_name' => 'updated-term',
        ];

        $response = $this->put(route('glossaries.update', $glossary), $data);

        $response->assertForbidden();
        $this->assertDatabaseHas('glossaries', [
            'id' => $glossary->id,
            'internal_name' => 'original-term',
        ]);
    }
}
