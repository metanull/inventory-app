<?php

namespace Tests\Feature\Api\Theme;

use App\Enums\Permission;
use App\Models\Collection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class StoreTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUserWith(Permission::dataOperations());
        $this->actingAs($this->user);
    }

    public function it_creates_a_theme(): void
    {
        $collection = Collection::factory()->create();
        $data = [
            'collection_id' => $collection->id,
            'internal_name' => 'unique-theme',
        ];
        $response = $this->postJson(route('theme.store'), $data);
        $response->assertCreated()->assertJsonPath('data.internal_name', 'unique-theme');
        $this->assertDatabaseHas('themes', ['internal_name' => 'unique-theme']);
    }
}
