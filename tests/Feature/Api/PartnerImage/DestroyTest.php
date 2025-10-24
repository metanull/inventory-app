<?php

namespace Tests\Feature\Api\PartnerImage;

use App\Models\Partner;
use App\Models\PartnerImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class DestroyTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createDataUser();
        $this->actingAs($this->user);
    }

    public function test_can_delete_partner_image(): void
    {
        $partner = Partner::factory()->create();
        $image = PartnerImage::factory()->create(['partner_id' => $partner->id]);

        $response = $this->deleteJson("/api/partner-image/{$image->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('partner_images', [
            'id' => $image->id,
        ]);
    }

    public function test_cannot_delete_nonexistent_partner_image(): void
    {
        $response = $this->deleteJson('/api/partner-image/99999999-9999-9999-9999-999999999999');

        $response->assertNotFound();
    }
}
