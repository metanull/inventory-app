<?php

namespace Tests\Feature\Api\PartnerTranslationImage;

use App\Models\PartnerTranslation;
use App\Models\PartnerTranslationImage;
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

    public function test_can_update_partner_translation_image(): void
    {
        $translation = PartnerTranslation::factory()->create();
        $image = PartnerTranslationImage::factory()->create(['partner_translation_id' => $translation->id]);

        $data = [
            'partner_translation_id' => $translation->id,
            'path' => 'images/partner_translations/updated.jpg',
            'original_name' => 'updated.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 60000,
            'alt_text' => 'Updated image',
            'display_order' => 2,
        ];

        $response = $this->patchJson("/api/partner-translation-image/{$image->id}", $data);

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $image->id,
                    'path' => 'images/partner_translations/updated.jpg',
                    'alt_text' => 'Updated image',
                ],
            ]);

        $this->assertDatabaseHas('partner_translation_images', [
            'id' => $image->id,
            'path' => 'images/partner_translations/updated.jpg',
        ]);
    }

    public function test_partner_translation_id_is_required(): void
    {
        $translation = PartnerTranslation::factory()->create();
        $image = PartnerTranslationImage::factory()->create(['partner_translation_id' => $translation->id]);

        $data = [
            'path' => 'images/partner_translations/updated.jpg',
            'original_name' => 'updated.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 60000,
            'display_order' => 2,
        ];

        $response = $this->patchJson("/api/partner-translation-image/{$image->id}", $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('partner_translation_id');
    }
}
