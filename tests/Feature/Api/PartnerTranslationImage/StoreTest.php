<?php

namespace Tests\Feature\Api\PartnerTranslationImage;

use App\Enums\Permission;
use App\Models\PartnerTranslation;
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

    public function test_can_create_partner_translation_image(): void
    {
        $translation = PartnerTranslation::factory()->create();

        $data = [
            'partner_translation_id' => $translation->id,
            'path' => 'images/partner_translations/test.jpg',
            'original_name' => 'test.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 50000,
            'alt_text' => 'Test image',
            'display_order' => 1,
        ];

        $response = $this->postJson('/api/partner-translation-image', $data);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'partner_translation_id', 'path', 'original_name'],
            ])
            ->assertJson([
                'data' => [
                    'partner_translation_id' => $translation->id,
                    'path' => 'images/partner_translations/test.jpg',
                ],
            ]);

        $this->assertDatabaseHas('partner_translation_images', [
            'partner_translation_id' => $translation->id,
            'path' => 'images/partner_translations/test.jpg',
        ]);
    }

    public function test_partner_translation_id_is_required(): void
    {
        $data = [
            'path' => 'images/partner_translations/test.jpg',
            'original_name' => 'test.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 50000,
            'display_order' => 1,
        ];

        $response = $this->postJson('/api/partner-translation-image', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('partner_translation_id');
    }

    public function test_path_is_required(): void
    {
        $translation = PartnerTranslation::factory()->create();

        $data = [
            'partner_translation_id' => $translation->id,
            'original_name' => 'test.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 50000,
            'display_order' => 1,
        ];

        $response = $this->postJson('/api/partner-translation-image', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('path');
    }

    public function test_display_order_is_required(): void
    {
        $translation = PartnerTranslation::factory()->create();

        $data = [
            'partner_translation_id' => $translation->id,
            'path' => 'images/partner_translations/test.jpg',
            'original_name' => 'test.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 50000,
        ];

        $response = $this->postJson('/api/partner-translation-image', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('display_order');
    }
}
