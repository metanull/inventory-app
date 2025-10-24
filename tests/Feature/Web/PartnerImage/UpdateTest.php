<?php

namespace Tests\Feature\Web\PartnerImage;

use App\Models\Partner;
use App\Models\PartnerImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class UpdateTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    protected Partner $partner;

    protected PartnerImage $partnerImage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->partner = Partner::factory()->create();
        $this->partnerImage = PartnerImage::factory()->for($this->partner)->create();
    }

    public function test_authenticated_user_can_update_alt_text(): void
    {
        $this->actAsRegularUser();
        $user = User::find(1);

        $response = $this->actingAs($user)
            ->put(route('partners.partner-images.update', [$this->partner, $this->partnerImage]), [
                'alt_text' => 'Updated Alt Text',
            ]);

        $response->assertRedirect(route('partners.show', $this->partner));
        $response->assertSessionHas('success', 'Image updated successfully');

        $this->assertDatabaseHas('partner_images', [
            'id' => $this->partnerImage->id,
            'alt_text' => 'Updated Alt Text',
        ]);
    }

    public function test_alt_text_can_be_null(): void
    {
        $this->actAsRegularUser();
        $user = User::find(1);
        $this->partnerImage->update(['alt_text' => 'Some text']);

        $response = $this->actingAs($user)
            ->put(route('partners.partner-images.update', [$this->partner, $this->partnerImage]), [
                'alt_text' => null,
            ]);

        $response->assertRedirect(route('partners.show', $this->partner));

        $this->assertDatabaseHas('partner_images', [
            'id' => $this->partnerImage->id,
            'alt_text' => null,
        ]);
    }

    public function test_alt_text_cannot_exceed_255_characters(): void
    {
        $this->actAsRegularUser();
        $user = User::find(1);

        $response = $this->actingAs($user)
            ->put(route('partners.partner-images.update', [$this->partner, $this->partnerImage]), [
                'alt_text' => str_repeat('a', 256),
            ]);

        $response->assertSessionHasErrors('alt_text');
    }

    public function test_cannot_update_image_from_different_partner(): void
    {
        $this->actAsRegularUser();
        $user = User::find(1);
        $otherPartner = Partner::factory()->create();
        $otherPartnerImage = PartnerImage::factory()->for($otherPartner)->create();

        $response = $this->actingAs($user)
            ->put(route('partners.partner-images.update', [$this->partner, $otherPartnerImage]), [
                'alt_text' => 'Should not work',
            ]);

        $response->assertNotFound();
    }

    public function test_guest_cannot_update_image(): void
    {
        $response = $this->put(route('partners.partner-images.update', [$this->partner, $this->partnerImage]), [
            'alt_text' => 'Should not work',
        ]);

        $response->assertRedirect(route('login'));
    }
}
