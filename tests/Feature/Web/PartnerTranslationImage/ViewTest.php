<?php

namespace Tests\Feature\Web\PartnerTranslationImage;

use App\Models\PartnerTranslation;
use App\Models\PartnerTranslationImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class ViewTest extends TestCase
{
    use RefreshDatabase, RequiresDataPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();

        // Set up a fake storage disk for testing
        Storage::fake('local');
        config(['localstorage.pictures.disk' => 'local']);
    }

    public function test_view_returns_image_file(): void
    {
        $partnerTranslation = PartnerTranslation::factory()->create();

        // Create a test image file
        $imagePath = 'images/partner-translations/test-image.jpg';
        Storage::disk('local')->put($imagePath, file_get_contents(__DIR__.'/../../../fixtures/test-image.jpg'));

        // Create PartnerTranslationImage with the test file path
        $partnerTranslationImage = PartnerTranslationImage::factory()->create([
            'partner_translation_id' => $partnerTranslation->id,
            'path' => $imagePath,
            'mime_type' => 'image/jpeg',
            'display_order' => 1,
        ]);

        $response = $this->get(route('partner-translations.partner-translation-images.view', [$partnerTranslation, $partnerTranslationImage]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/jpeg');
    }

    public function test_view_returns_404_if_image_file_does_not_exist(): void
    {
        $partnerTranslation = PartnerTranslation::factory()->create();

        // Create PartnerTranslationImage with a non-existent file path
        $partnerTranslationImage = PartnerTranslationImage::factory()->create([
            'partner_translation_id' => $partnerTranslation->id,
            'path' => 'images/partner-translations/non-existent.jpg',
            'display_order' => 1,
        ]);

        $response = $this->get(route('partner-translations.partner-translation-images.view', [$partnerTranslation, $partnerTranslationImage]));

        $response->assertNotFound();
    }

    public function test_view_returns_404_if_image_belongs_to_different_partner_translation(): void
    {
        $partnerTranslation1 = PartnerTranslation::factory()->create();
        $partnerTranslation2 = PartnerTranslation::factory()->create();

        // Create PartnerTranslationImage for partnerTranslation1
        $partnerTranslationImage = PartnerTranslationImage::factory()->create([
            'partner_translation_id' => $partnerTranslation1->id,
            'path' => 'images/partner-translations/test.jpg',
            'display_order' => 1,
        ]);

        // Try to access via partnerTranslation2's route
        $response = $this->get(route('partner-translations.partner-translation-images.view', [$partnerTranslation2, $partnerTranslationImage]));

        $response->assertNotFound();
    }

    public function test_view_requires_authentication(): void
    {
        // Start as unauthenticated
        $partnerTranslation = PartnerTranslation::factory()->create();
        $partnerTranslationImage = PartnerTranslationImage::factory()->create([
            'partner_translation_id' => $partnerTranslation->id,
            'display_order' => 1,
        ]);

        // Make request without authentication
        $this->app['auth']->forgetGuards();
        $response = $this->get(route('partner-translations.partner-translation-images.view', [$partnerTranslation, $partnerTranslationImage]));

        $response->assertRedirect(route('login'));
    }

    public function test_view_requires_view_data_permission(): void
    {
        // Create a user without any permissions
        $user = User::factory()->create();
        $this->actingAs($user);

        $partnerTranslation = PartnerTranslation::factory()->create();
        $partnerTranslationImage = PartnerTranslationImage::factory()->create([
            'partner_translation_id' => $partnerTranslation->id,
            'display_order' => 1,
        ]);

        $response = $this->get(route('partner-translations.partner-translation-images.view', [$partnerTranslation, $partnerTranslationImage]));

        $response->assertForbidden();
    }

    public function test_download_returns_image_file_as_attachment(): void
    {
        $partnerTranslation = PartnerTranslation::factory()->create();

        // Create a test image file
        $imagePath = 'images/partner-translations/test-download.jpg';
        Storage::disk('local')->put($imagePath, file_get_contents(__DIR__.'/../../../fixtures/test-image.jpg'));

        // Create PartnerTranslationImage with the test file path
        $partnerTranslationImage = PartnerTranslationImage::factory()->create([
            'partner_translation_id' => $partnerTranslation->id,
            'path' => $imagePath,
            'original_name' => 'original-name.jpg',
            'mime_type' => 'image/jpeg',
            'display_order' => 1,
        ]);

        $response = $this->get(route('partner-translations.partner-translation-images.download', [$partnerTranslation, $partnerTranslationImage]));

        $response->assertOk();
        $response->assertDownload('original-name.jpg');
    }
}
