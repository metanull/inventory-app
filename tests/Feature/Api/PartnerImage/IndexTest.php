<?php

namespace Tests\Feature\Api\PartnerImage;

use App\Models\Partner;
use App\Models\PartnerImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class IndexTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createVisitorUser();
        $this->actingAs($this->user);
    }

    public function test_authenticated_user_can_list_partner_images(): void
    {
        $partner = Partner::factory()->create();
        PartnerImage::factory()->count(3)->create(['partner_id' => $partner->id]);

        $response = $this->getJson('/api/partner-image');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'partner_id', 'path', 'original_name', 'mime_type', 'size', 'alt_text', 'display_order'],
                ],
            ]);
    }

    public function test_can_paginate_partner_images(): void
    {
        $partner = Partner::factory()->create();
        PartnerImage::factory()->count(15)->create(['partner_id' => $partner->id]);

        $response = $this->getJson('/api/partner-image?per_page=5');

        $response->assertOk()
            ->assertJsonCount(5, 'data');
    }

    public function test_can_include_partner_relationship(): void
    {
        $partner = Partner::factory()->create();
        PartnerImage::factory()->create(['partner_id' => $partner->id]);

        $response = $this->getJson('/api/partner-image?include=partner');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['partner' => ['id', 'internal_name']],
                ],
            ]);
    }
}
