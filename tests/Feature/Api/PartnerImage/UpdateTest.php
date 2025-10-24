<?php

namespace Tests\Feature\Api\PartnerImage;

use App\Models\Partner;
use App\Models\PartnerImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class UpdateTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createDataUser();
        $this->actingAs($this->user);
    }

    public function test_can_update_partner_image(): void
    {
        $partner = Partner::factory()->create();
        $image = PartnerImage::factory()->create(['partner_id' => $partner->id]);

        $data = [
            'partner_id' => $partner->id,
            'path' => 'images/partners/updated.jpg',
            'original_name' => 'updated.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 60000,
            'alt_text' => 'Updated image',
            'display_order' => 2,
        ];

        $response = $this->patchJson("/api/partner-image/{$image->id}", $data);

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $image->id,
                    'path' => 'images/partners/updated.jpg',
                    'alt_text' => 'Updated image',
                ],
            ]);

        $this->assertDatabaseHas('partner_images', [
            'id' => $image->id,
            'path' => 'images/partners/updated.jpg',
        ]);
    }

    public function test_partner_id_is_required(): void
    {
        $partner = Partner::factory()->create();
        $image = PartnerImage::factory()->create(['partner_id' => $partner->id]);

        $data = [
            'path' => 'images/partners/updated.jpg',
            'original_name' => 'updated.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 60000,
            'display_order' => 2,
        ];

        $response = $this->patchJson("/api/partner-image/{$image->id}", $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('partner_id');
    }
}
