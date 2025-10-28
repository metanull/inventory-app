<?php

namespace Tests\Feature\Web\PartnerImage;

use App\Models\Partner;
use App\Models\PartnerImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class ViewTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsDataUser();

        // Set up a fake storage disk for testing
        Storage::fake('local');
        config(['localstorage.pictures.disk' => 'local']);
    }

    public function test_view_returns_image_file(): void
    {
        $partner = Partner::factory()->create();

        // Create a test image file
        $imagePath = 'images/partners/test-image.jpg';
        Storage::disk('local')->put($imagePath, file_get_contents(__DIR__.'/../../../fixtures/test-image.jpg'));

        // Create PartnerImage with the test file path
        $partnerImage = PartnerImage::factory()->create([
            'partner_id' => $partner->id,
            'path' => $imagePath,
            'mime_type' => 'image/jpeg',
            'display_order' => 1,
        ]);

        $response = $this->get(route('partners.partner-images.view', [$partner, $partnerImage]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/jpeg');
    }

    public function test_view_returns_404_if_image_file_does_not_exist(): void
    {
        $partner = Partner::factory()->create();

        // Create PartnerImage with a non-existent file path
        $partnerImage = PartnerImage::factory()->create([
            'partner_id' => $partner->id,
            'path' => 'images/partners/non-existent.jpg',
            'display_order' => 1,
        ]);

        $response = $this->get(route('partners.partner-images.view', [$partner, $partnerImage]));

        $response->assertNotFound();
    }

    public function test_view_returns_404_if_image_belongs_to_different_partner(): void
    {
        $partner1 = Partner::factory()->create();
        $partner2 = Partner::factory()->create();

        // Create PartnerImage for partner1
        $partnerImage = PartnerImage::factory()->create([
            'partner_id' => $partner1->id,
            'path' => 'images/partners/test.jpg',
            'display_order' => 1,
        ]);

        // Try to access via partner2's route
        $response = $this->get(route('partners.partner-images.view', [$partner2, $partnerImage]));

        $response->assertNotFound();
    }

    public function test_view_requires_authentication(): void
    {
        // Start as unauthenticated
        $partner = Partner::factory()->create();
        $partnerImage = PartnerImage::factory()->create([
            'partner_id' => $partner->id,
            'display_order' => 1,
        ]);

        // Make request without authentication
        $this->app['auth']->forgetGuards();
        $response = $this->get(route('partners.partner-images.view', [$partner, $partnerImage]));

        $response->assertRedirect(route('login'));
    }

    public function test_view_requires_view_data_permission(): void
    {
        // Create a user without any permissions
        $user = User::factory()->create();
        $this->actingAs($user);

        $partner = Partner::factory()->create();
        $partnerImage = PartnerImage::factory()->create([
            'partner_id' => $partner->id,
            'display_order' => 1,
        ]);

        $response = $this->get(route('partners.partner-images.view', [$partner, $partnerImage]));

        $response->assertForbidden();
    }

    public function test_download_returns_image_file_as_attachment(): void
    {
        $partner = Partner::factory()->create();

        // Create a test image file
        $imagePath = 'images/partners/test-download.jpg';
        Storage::disk('local')->put($imagePath, file_get_contents(__DIR__.'/../../../fixtures/test-image.jpg'));

        // Create PartnerImage with the test file path
        $partnerImage = PartnerImage::factory()->create([
            'partner_id' => $partner->id,
            'path' => $imagePath,
            'original_name' => 'original-name.jpg',
            'mime_type' => 'image/jpeg',
            'display_order' => 1,
        ]);

        $response = $this->get(route('partners.partner-images.download', [$partner, $partnerImage]));

        $response->assertOk();
        $response->assertDownload('original-name.jpg');
    }
}
