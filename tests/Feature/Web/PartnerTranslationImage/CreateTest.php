<?php

namespace Tests\Feature\Web\PartnerTranslationImage;

use App\Models\AvailableImage;
use App\Models\PartnerTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class CreateTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    protected PartnerTranslation $partnerTranslation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->partnerTranslation = PartnerTranslation::factory()->create();
    }

    public function test_authenticated_user_can_view_attach_image_form(): void
    {
        $this->actAsRegularUser();
        $user = User::find(1);

        $response = $this->actingAs($user)
            ->get(route('partner-translations.partner-translation-images.create', $this->partnerTranslation));

        $response->assertOk();
        $response->assertViewIs('partner-translation-images.create');
        $response->assertViewHas('partnerTranslation', $this->partnerTranslation);
        $response->assertViewHas('availableImages');
        $response->assertSee('Attach Image to');
    }

    public function test_create_form_displays_available_images(): void
    {
        $this->actAsRegularUser();
        $user = User::find(1);
        $availableImage1 = AvailableImage::factory()->create(['comment' => 'Test Image 1']);
        $availableImage2 = AvailableImage::factory()->create(['comment' => 'Test Image 2']);

        $response = $this->actingAs($user)
            ->get(route('partner-translations.partner-translation-images.create', $this->partnerTranslation));

        $response->assertOk();
        $response->assertSee('Test Image 1');
        $response->assertSee('Test Image 2');
        $response->assertSee($availableImage1->id);
        $response->assertSee($availableImage2->id);
    }

    public function test_create_form_shows_empty_state_when_no_available_images(): void
    {
        $this->actAsRegularUser();
        $user = User::find(1);

        $response = $this->actingAs($user)
            ->get(route('partner-translations.partner-translation-images.create', $this->partnerTranslation));

        $response->assertOk();
        $response->assertSee('No available images');
        $response->assertSee('Upload images to the available images pool first');
    }

    public function test_guest_cannot_view_attach_image_form(): void
    {
        $response = $this->get(route('partner-translations.partner-translation-images.create', $this->partnerTranslation));

        $response->assertRedirect(route('login'));
    }
}
