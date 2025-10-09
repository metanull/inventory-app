<?php

namespace Tests\Feature\Api\Item;

use App\Models\Item;
use App\Models\ItemImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class ShowTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createVisitorUser();
        $this->actingAs($this->user);
    }

    public function test_show_allows_authenticated_users(): void
    {
        $item = Item::factory()->create();
        $response = $this->getJson(route('item.show', $item->id));
        $response->assertOk();
    }

    public function test_show_returns_not_found_when_not_found(): void
    {
        $response = $this->getJson(route('item.show', 'nonexistent'));
        $response->assertNotFound();
    }

    public function test_show_returns_the_default_structure_without_relations(): void
    {
        $item = Item::factory()->create();
        $response = $this->getJson(route('item.show', $item->id));

        $response->assertJsonStructure([
            'data' => [
                'id',
                'internal_name',
                'backward_compatibility',
                'type',
                'owner_reference',
                'mwnf_reference',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    public function test_show_returns_the_expected_structure_including_partner_data(): void
    {
        $item = Item::factory()->withPartner()->create();
        $response = $this->getJson(route('item.show', [$item->id, 'include' => 'partner']));

        $response->assertJsonStructure([
            'data' => [
                'partner' => [
                    'id',
                    'internal_name',
                    'backward_compatibility',
                    'type',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    }

    public function test_show_returns_the_expected_structure_including_country_data(): void
    {
        $item = Item::factory()->withCountry()->create();
        $response = $this->getJson(route('item.show', [$item->id, 'include' => 'country']));

        $response->assertJsonStructure([
            'data' => [
                'country' => [
                    'id',
                    'internal_name',
                    'backward_compatibility',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    }

    public function test_show_returns_the_expected_structure_including_project_data(): void
    {
        $item = Item::factory()->WithProject()->create();
        $response = $this->getJson(route('item.show', [$item->id, 'include' => 'project']));

        $response->assertJsonStructure([
            'data' => [
                'project' => [
                    'id',
                    'internal_name',
                    'backward_compatibility',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    }

    public function test_show_returns_the_expected_data(): void
    {
        $item = Item::factory()->create();
        $response = $this->getJson(route('item.show', $item->id));

        $response->assertJsonPath('data.id', $item->id)
            ->assertJsonPath('data.internal_name', $item->internal_name)
            ->assertJsonPath('data.backward_compatibility', $item->backward_compatibility)
            ->assertJsonPath('data.type', $item->type);
    }

    public function test_show_returns_the_expected_data_including_partner_data(): void
    {
        $item = Item::factory()->withPartner()->create();
        $response = $this->getJson(route('item.show', [$item->id, 'include' => 'partner']));

        $response->assertJsonPath('data.id', $item->id)
            ->assertJsonPath('data.partner.id', $item->partner->id)
            ->assertJsonPath('data.partner.internal_name', $item->partner->internal_name)
            ->assertJsonPath('data.partner.backward_compatibility', $item->partner->backward_compatibility);
    }

    public function test_show_returns_the_expected_data_including_country_data(): void
    {
        $item = Item::factory()->withCountry()->create();
        $response = $this->getJson(route('item.show', [$item->id, 'include' => 'country']));

        $response->assertJsonPath('data.id', $item->id)
            ->assertJsonPath('data.country.id', $item->country->id)
            ->assertJsonPath('data.country.internal_name', $item->country->internal_name)
            ->assertJsonPath('data.country.backward_compatibility', $item->country->backward_compatibility);
    }

    public function test_show_returns_the_expected_data_including_project_data(): void
    {
        $item = Item::factory()->withProject()->create();
        $response = $this->getJson(route('item.show', [$item->id, 'include' => 'project']));

        $response->assertJsonPath('data.id', $item->id)
            ->assertJsonPath('data.project.id', $item->project->id)
            ->assertJsonPath('data.project.internal_name', $item->project->internal_name)
            ->assertJsonPath('data.project.backward_compatibility', $item->project->backward_compatibility);
    }

    public function test_show_returns_the_expected_structure_with_all_relations_loaded(): void
    {
        $item = Item::factory()
            ->has(ItemImage::factory()->count(2))
            ->create();

        $response = $this->getJson(route('item.show', [
            $item->id,
            'include' => 'partner,country,project,collection,artists,workshops,tags,translations,itemImages',
        ]));

        $response->assertJsonStructure([
            'data' => [
                'id',
                'internal_name',
                'backward_compatibility',
                'type',
                'parent_id',
                'owner_reference',
                'mwnf_reference',
                'partner',
                'country',
                'project',
                'collection',
                'artists',
                'workshops',
                'tags',
                'translations',
                'itemImages' => [
                    '*' => [
                        'id',
                        'path',
                        'original_name',
                        'mime_type',
                        'size',
                        'alt_text',
                        'display_order',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'created_at',
                'updated_at',
            ],
        ]);
    }
}
