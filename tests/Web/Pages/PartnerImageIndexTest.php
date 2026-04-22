<?php

namespace Tests\Web\Pages;

use App\Models\Partner;
use App\Models\PartnerImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;

class PartnerImageIndexTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;

    public function test_index_renders_request_driven_page_without_livewire_markup(): void
    {
        $partner = Partner::factory()->create(['internal_name' => 'Test Partner']);
        PartnerImage::factory()->forPartner($partner)->create(['original_name' => 'logo.jpg']);

        $response = $this->get(route('partners.partner-images.index', $partner));

        $response
            ->assertOk()
            ->assertViewIs('partner-images.index')
            ->assertSee('logo.jpg');

        $this->assertStringNotContainsString('livewire:dynamic-component', $response->getContent());
        $this->assertStringNotContainsString('wire:click', $response->getContent());
    }

    public function test_index_scopes_images_to_parent_partner(): void
    {
        $partner = Partner::factory()->create(['internal_name' => 'My Partner']);
        $otherPartner = Partner::factory()->create(['internal_name' => 'Other Partner']);

        PartnerImage::factory()->forPartner($partner)->create(['original_name' => 'matching-image.jpg']);
        PartnerImage::factory()->forPartner($otherPartner)->create(['original_name' => 'other-image.jpg']);

        $response = $this->get(route('partners.partner-images.index', $partner));

        $response
            ->assertOk()
            ->assertSee('matching-image.jpg')
            ->assertDontSee('other-image.jpg');
    }

    public function test_index_returns_not_found_for_non_existent_partner(): void
    {
        $response = $this->get(route('partners.partner-images.index', ['partner' => 'non-existent-uuid']));

        $response->assertNotFound();
    }

    public function test_index_can_search_by_original_name(): void
    {
        $partner = Partner::factory()->create();

        PartnerImage::factory()->forPartner($partner)->create(['original_name' => 'partner-logo.jpg']);
        PartnerImage::factory()->forPartner($partner)->create(['original_name' => 'other-logo.jpg']);

        $response = $this->get(route('partners.partner-images.index', ['partner' => $partner, 'q' => 'partner-logo']));

        $response
            ->assertOk()
            ->assertSee('partner-logo.jpg')
            ->assertDontSee('other-logo.jpg');
    }

    public function test_index_can_sort_by_display_order(): void
    {
        $partner = Partner::factory()->create();

        PartnerImage::factory()->forPartner($partner)->withOrder(2)->create(['original_name' => 'second.jpg']);
        PartnerImage::factory()->forPartner($partner)->withOrder(1)->create(['original_name' => 'first.jpg']);

        $response = $this->get(route('partners.partner-images.index', [
            'partner' => $partner,
            'sort' => 'display_order',
            'direction' => 'asc',
        ]));

        $response
            ->assertOk()
            ->assertSeeInOrder(['first.jpg', 'second.jpg']);
    }

    public function test_index_rejects_invalid_sort_field_gracefully(): void
    {
        $partner = Partner::factory()->create();

        $response = $this->get(route('partners.partner-images.index', [
            'partner' => $partner,
            'sort' => 'invalid_field',
        ]));

        $response->assertOk();
    }

    public function test_index_requires_view_data_permission(): void
    {
        $partner = Partner::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('partners.partner-images.index', $partner));

        $response->assertForbidden();
    }

    public function test_index_passes_parent_partner_to_view(): void
    {
        $partner = Partner::factory()->create(['internal_name' => 'My Special Partner']);

        $response = $this->get(route('partners.partner-images.index', $partner));

        $response
            ->assertOk()
            ->assertSee('My Special Partner');

        $this->assertSame($partner->id, $response->viewData('partner')->id);
    }

    public function test_index_contains_upload_zone_component(): void
    {
        $partner = Partner::factory()->create();

        $response = $this->get(route('partners.partner-images.index', $partner));

        $response
            ->assertOk()
            ->assertSee('imageUploadZone');
    }
}
