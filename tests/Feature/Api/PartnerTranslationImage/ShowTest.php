<?php

namespace Tests\Feature\Api\PartnerTranslationImage;

use App\Enums\Permission;
use App\Models\PartnerTranslation;
use App\Models\PartnerTranslationImage;
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

    public function test_can_view_partner_translation_image(): void
    {
        $translation = PartnerTranslation::factory()->create();
        $image = PartnerTranslationImage::factory()->create(['partner_translation_id' => $translation->id]);

        $response = $this->getJson("/api/partner-translation-image/{$image->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'partner_translation_id', 'path', 'original_name', 'mime_type', 'size', 'alt_text', 'display_order'],
            ])
            ->assertJson([
                'data' => [
                    'id' => $image->id,
                    'partner_translation_id' => $translation->id,
                ],
            ]);
    }

    public function test_can_include_partner_translation_relationship(): void
    {
        $translation = PartnerTranslation::factory()->create();
        $image = PartnerTranslationImage::factory()->create(['partner_translation_id' => $translation->id]);

        $response = $this->getJson("/api/partner-translation-image/{$image->id}?include=partnerTranslation");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['partner_translation' => ['id', 'name']],
            ]);
    }

    public function test_cannot_view_nonexistent_partner_translation_image(): void
    {
        $response = $this->getJson('/api/partner-translation-image/99999999-9999-9999-9999-999999999999');

        $response->assertNotFound();
    }
}
