<?php

namespace Tests\Feature\Api\Item;

use App\Enums\Permission;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class IndexTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUserWith([Permission::VIEW_DATA->value]);
        $this->actingAs($this->user);
    }

    public function test_index_returns_ok_when_no_data(): void
    {
        $response = $this->getJson(route('item.index'));
        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }

    public function test_index_returns_the_expected_structure(): void
    {
        $item = Item::factory()->create();
        $response = $this->getJson(route('item.index'));

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'internal_name',
                    'backward_compatibility',
                    'type',
                    'owner_reference',
                    'mwnf_reference',
                    'created_at',
                    'updated_at',
                ],
            ],
            'links' => [
                'first', 'last', 'prev', 'next',
            ],
            'meta' => [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
            ],
        ]);
    }

    public function test_index_returns_the_expected_structure_including_partner_data(): void
    {
        $item = Item::factory()->WithPartner()->create();
        $response = $this->getJson(route('item.index', ['include' => 'partner']));

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'partner' => [
                        'id',
                        'internal_name',
                        'backward_compatibility',
                        'country',
                        'type',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ],
        ]);
    }

    public function test_index_returns_the_expected_structure_including_country_data(): void
    {
        $item = Item::factory()->WithCountry()->create();
        $response = $this->getJson(route('item.index', ['include' => 'country']));

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'country' => [
                        'id',
                        'internal_name',
                        'backward_compatibility',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ],
        ]);
    }

    public function test_index_returns_the_expected_structure_including_project_data(): void
    {
        $item = Item::factory()->WithProject()->create();
        $response = $this->getJson(route('item.index', ['include' => 'project']));

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'project' => [
                        'id',
                        'internal_name',
                        'backward_compatibility',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ],
        ]);
    }

    public function test_index_returns_the_expected_data(): void
    {
        $item1 = Item::factory()->create();
        $item2 = Item::factory()->create();
        $response = $this->getJson(route('item.index'));
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'internal_name',
                    'backward_compatibility',
                    'type',
                    'owner_reference',
                    'mwnf_reference',
                    'created_at',
                    'updated_at',
                ],
            ],
            'links' => [
                'first', 'last', 'prev', 'next',
            ],
            'meta' => [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
            ],
        ]);

        $response->assertJsonPath('data.0.id', $item1->id)
            ->assertJsonPath('data.1.id', $item2->id);
    }
}
