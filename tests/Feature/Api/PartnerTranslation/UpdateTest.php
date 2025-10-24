<?php

declare(strict_types=1);

namespace Tests\Feature\Api\PartnerTranslation;

use App\Models\Context;
use App\Models\Language;
use App\Models\Partner;
use App\Models\PartnerTranslation;
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

    public function test_update_modifies_partner_translation(): void
    {
        $translation = PartnerTranslation::factory()->create(['name' => 'Original Name']);

        $response = $this->patchJson(route('partner-translation.update', $translation), [
            'partner_id' => $translation->partner_id,
            'language_id' => $translation->language_id,
            'context_id' => $translation->context_id,
            'name' => 'Updated Name',
            'description' => 'Updated description',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('partner_translations', [
            'id' => $translation->id,
            'name' => 'Updated Name',
            'description' => 'Updated description',
        ]);
    }

    public function test_update_validates_required_fields(): void
    {
        $translation = PartnerTranslation::factory()->create();

        $response = $this->patchJson(route('partner-translation.update', $translation), [
            'name' => '',
            'partner_id' => '',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'partner_id']);
    }

    public function test_update_validates_email_format(): void
    {
        $translation = PartnerTranslation::factory()->create();

        $response = $this->patchJson(route('partner-translation.update', $translation), [
            'partner_id' => $translation->partner_id,
            'language_id' => $translation->language_id,
            'context_id' => $translation->context_id,
            'name' => 'Test',
            'contact_email_general' => 'not-an-email',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['contact_email_general']);
    }

    public function test_update_validates_unique_constraint(): void
    {
        $partner = Partner::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $existing = PartnerTranslation::factory()->create([
            'partner_id' => $partner->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
        ]);

        $other = PartnerTranslation::factory()->create();

        $response = $this->patchJson(route('partner-translation.update', $other), [
            'partner_id' => $partner->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => 'Test',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['partner_id']);
    }
}
