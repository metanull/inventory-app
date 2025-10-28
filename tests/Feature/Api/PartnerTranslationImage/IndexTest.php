namespace Tests\Feature\Api\PartnerTranslationImage;

use App\Enums\Permission;
use App\Models\Partner;
use App\Models\PartnerTranslation;
use App\Models\PartnerTranslationImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class IndexTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUserWith([Permission::VIEW_DATA->value]);
        $this->actingAs($this->user);
    }

    public function test_authenticated_user_can_list_partner_translation_images(): void
    {
        $translation = PartnerTranslation::factory()->create();
        PartnerTranslationImage::factory()->count(3)->create(['partner_translation_id' => $translation->id]);

        $response = $this->getJson('/api/partner-translation-image');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'partner_translation_id', 'path', 'original_name', 'mime_type', 'size', 'alt_text', 'display_order'],
                ],
            ]);
    }

    public function test_can_paginate_partner_translation_images(): void
    {
        $translation = PartnerTranslation::factory()->create();
        PartnerTranslationImage::factory()->count(15)->create(['partner_translation_id' => $translation->id]);

        $response = $this->getJson('/api/partner-translation-image?per_page=5');

        $response->assertOk()
            ->assertJsonCount(5, 'data');
    }

    public function test_can_include_partner_translation_relationship(): void
    {
        $translation = PartnerTranslation::factory()->create();
        PartnerTranslationImage::factory()->create(['partner_translation_id' => $translation->id]);

        $response = $this->getJson('/api/partner-translation-image?include=partnerTranslation');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['partner_translation' => ['id', 'name']],
                ],
            ]);
    }
}
