<?php

namespace Tests\Feature\Api\PartnerTranslationImage;

use App\Enums\Permission;
use App\Models\PartnerTranslation;
use App\Models\PartnerTranslationImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class DestroyTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUserWith(Permission::dataOperations());
        $this->actingAs($this->user);
    }

    public function test_can_delete_partner_translation_image(): void
    {
        $translation = PartnerTranslation::factory()->create();
        $image = PartnerTranslationImage::factory()->create(['partner_translation_id' => $translation->id]);

        $response = $this->deleteJson("/api/partner-translation-image/{$image->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('partner_translation_images', [
            'id' => $image->id,
        ]);
    }

    public function test_cannot_delete_nonexistent_partner_translation_image(): void
    {
        $response = $this->deleteJson('/api/partner-translation-image/99999999-9999-9999-9999-999999999999');

        $response->assertNotFound();
    }
}
