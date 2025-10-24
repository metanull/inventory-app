<?php

namespace Tests\Feature\Api\PartnerImage;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class StoreTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createDataUser();
        $this->actingAs($this->user);
    }

    public function test_can_create_partner_image(): void
    {
        $partner = Partner::factory()->create();

        $data = [
            'partner_id' => $partner->id,
            'path' => 'images/partners/test.jpg',
            'original_name' => 'test.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 50000,
            'alt_text' => 'Test image',
            'display_order' => 1,
        ];

        $response = $this->postJson('/api/partner-image', $data);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'partner_id', 'path', 'original_name'],
            ])
            ->assertJson([
                'data' => [
                    'partner_id' => $partner->id,
                    'path' => 'images/partners/test.jpg',
                ],
            ]);

        $this->assertDatabaseHas('partner_images', [
            'partner_id' => $partner->id,
            'path' => 'images/partners/test.jpg',
        ]);
    }

    public function test_partner_id_is_required(): void
    {
        $data = [
            'path' => 'images/partners/test.jpg',
            'original_name' => 'test.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 50000,
            'display_order' => 1,
        ];

        $response = $this->postJson('/api/partner-image', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('partner_id');
    }

    public function test_path_is_required(): void
    {
        $partner = Partner::factory()->create();

        $data = [
            'partner_id' => $partner->id,
            'original_name' => 'test.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 50000,
            'display_order' => 1,
        ];

        $response = $this->postJson('/api/partner-image', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('path');
    }

    public function test_display_order_is_required(): void
    {
        $partner = Partner::factory()->create();

        $data = [
            'partner_id' => $partner->id,
            'path' => 'images/partners/test.jpg',
            'original_name' => 'test.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 50000,
        ];

        $response = $this->postJson('/api/partner-image', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('display_order');
    }
}
