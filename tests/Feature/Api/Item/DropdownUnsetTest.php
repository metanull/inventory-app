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

class DropdownUnsetTest extends TestCase
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

    public function test_frontend_workflow_partner_unset(): void
    {
        // Create item with partner (simulating initial creation)
        $partner = Partner::factory()->create();
        $item = Item::factory()->create(['partner_id' => $partner->id]);

        // Verify item has partner
        $this->assertEquals($partner->id, $item->partner_id);

        // Simulate frontend "unselecting" partner (sending null)
        $response = $this->putJson(route('item.update', $item->id), [
            'internal_name' => $item->internal_name,
            'type' => $item->type,
            'partner_id' => null, // This is what our fixed frontend should send
        ]);

        $response->assertOk()
            ->assertJsonPath('data.partner', null);

        // Verify in database
        $item->refresh();
        $this->assertNull($item->partner_id);
    }

    public function test_frontend_workflow_country_unset(): void
    {
        // Create item with country
        $country = Country::factory()->create();
        $item = Item::factory()->create(['country_id' => $country->id]);

        // Verify item has country
        $this->assertEquals($country->id, $item->country_id);

        // Simulate frontend "unselecting" country (sending null)
        $response = $this->putJson(route('item.update', $item->id), [
            'internal_name' => $item->internal_name,
            'type' => $item->type,
            'country_id' => null, // This is what our fixed frontend should send
        ]);

        $response->assertOk()
            ->assertJsonPath('data.country', null);

        // Verify in database
        $item->refresh();
        $this->assertNull($item->country_id);
    }

    public function test_frontend_workflow_project_unset(): void
    {
        // Create item with project
        $project = Project::factory()->create();
        $item = Item::factory()->create(['project_id' => $project->id]);

        // Verify item has project
        $this->assertEquals($project->id, $item->project_id);

        // Simulate frontend "unselecting" project (sending null)
        $response = $this->putJson(route('item.update', $item->id), [
            'internal_name' => $item->internal_name,
            'type' => $item->type,
            'project_id' => null, // This is what our fixed frontend should send
        ]);

        $response->assertOk()
            ->assertJsonPath('data.project', null);

        // Verify in database
        $item->refresh();
        $this->assertNull($item->project_id);
    }
}
