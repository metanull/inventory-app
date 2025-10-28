<?php

namespace Tests\Feature\Api\PartnerImage;

use App\Enums\Permission;
use App\Models\Partner;
use App\Models\PartnerImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class ShowTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUserWith([Permission::VIEW_DATA->value]);
        $this->actingAs($this->user);
    }

    public function test_can_view_partner_image(): void
    {
        $partner = Partner::factory()->create();
        $image = PartnerImage::factory()->create(['partner_id' => $partner->id]);

        $response = $this->getJson("/api/partner-image/{$image->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'partner_id', 'path', 'original_name', 'mime_type', 'size', 'alt_text', 'display_order'],
            ])
            ->assertJson([
                'data' => [
                    'id' => $image->id,
                    'partner_id' => $partner->id,
                ],
            ]);
    }

    public function test_can_include_partner_relationship(): void
    {
        $partner = Partner::factory()->create();
        $image = PartnerImage::factory()->create(['partner_id' => $partner->id]);

        $response = $this->getJson("/api/partner-image/{$image->id}?include=partner");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['partner' => ['id', 'internal_name']],
            ]);
    }

    public function test_cannot_view_nonexistent_partner_image(): void
    {
        $response = $this->getJson('/api/partner-image/99999999-9999-9999-9999-999999999999');

        $response->assertNotFound();
    }
}
