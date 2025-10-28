<?php

namespace Tests\Feature\Api\Item;

use App\Models\Country;
use App\Models\Item;
use App\Models\Partner;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class RelationshipUpdateTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createDataUser();
        $this->actingAs($this->user);
    }

    public function test_update_can_set_partner_from_null(): void
    {
        // Create item without partner
        $item = Item::factory()->create(['partner_id' => null]);
        $partner = Partner::factory()->create();

        // Set the partner
        $response = $this->putJson(route('item.update', [$item->id, 'include' => 'partner']), [
            'partner_id' => $partner->id,
            'internal_name' => $item->internal_name,
            'type' => $item->type,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.partner.id', $partner->id);

        // Verify in database
        $item->refresh();
        $this->assertEquals($partner->id, $item->partner_id);
    }

    public function test_update_can_unset_partner_to_null(): void
    {
        // Create item with partner
        $partner = Partner::factory()->create();
        $item = Item::factory()->create(['partner_id' => $partner->id]);

        // Unset the partner (set to null)
        $response = $this->putJson(route('item.update', [$item->id, 'include' => 'partner']), [
            'partner_id' => null,
            'internal_name' => $item->internal_name,
            'type' => $item->type,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.partner', null);

        // Verify in database
        $item->refresh();
        $this->assertNull($item->partner_id);
    }

    public function test_update_can_set_country_from_null(): void
    {
        // Create item without country
        $item = Item::factory()->create(['country_id' => null]);
        $country = Country::factory()->create();

        // Set the country
        $response = $this->putJson(route('item.update', [$item->id, 'include' => 'country']), [
            'country_id' => $country->id,
            'internal_name' => $item->internal_name,
            'type' => $item->type,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.country.id', $country->id);

        // Verify in database
        $item->refresh();
        $this->assertEquals($country->id, $item->country_id);
    }

    public function test_update_can_unset_country_to_null(): void
    {
        // Create item with country
        $country = Country::factory()->create();
        $item = Item::factory()->create(['country_id' => $country->id]);

        // Unset the country (set to null)
        $response = $this->putJson(route('item.update', [$item->id, 'include' => 'country']), [
            'country_id' => null,
            'internal_name' => $item->internal_name,
            'type' => $item->type,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.country', null);

        // Verify in database
        $item->refresh();
        $this->assertNull($item->country_id);
    }

    public function test_update_can_set_project_from_null(): void
    {
        // Create item without project
        $item = Item::factory()->create(['project_id' => null]);
        $project = Project::factory()->create();

        // Set the project
        $response = $this->putJson(route('item.update', [$item->id, 'include' => 'project']), [
            'project_id' => $project->id,
            'internal_name' => $item->internal_name,
            'type' => $item->type,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.project.id', $project->id);

        // Verify in database
        $item->refresh();
        $this->assertEquals($project->id, $item->project_id);
    }

    public function test_update_can_unset_project_to_null(): void
    {
        // Create item with project
        $project = Project::factory()->create();
        $item = Item::factory()->create(['project_id' => $project->id]);

        // Unset the project (set to null)
        $response = $this->putJson(route('item.update', [$item->id, 'include' => 'project']), [
            'project_id' => null,
            'internal_name' => $item->internal_name,
            'type' => $item->type,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.project', null);

        // Verify in database
        $item->refresh();
        $this->assertNull($item->project_id);
    }

    public function test_update_can_change_partner_from_one_to_another(): void
    {
        // Create item with first partner
        $firstPartner = Partner::factory()->create();
        $secondPartner = Partner::factory()->create();
        $item = Item::factory()->create(['partner_id' => $firstPartner->id]);

        // Change to second partner
        $response = $this->putJson(route('item.update', [$item->id, 'include' => 'partner']), [
            'partner_id' => $secondPartner->id,
            'internal_name' => $item->internal_name,
            'type' => $item->type,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.partner.id', $secondPartner->id);

        // Verify in database
        $item->refresh();
        $this->assertEquals($secondPartner->id, $item->partner_id);
    }
}
