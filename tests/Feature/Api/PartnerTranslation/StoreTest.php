<?php

namespace Tests\Feature\Api\PartnerTranslation;

use App\Models\Context;
use App\Models\Language;
use App\Models\Partner;
use App\Models\PartnerTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class StoreTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createDataUser();
        $this->actingAs($this->user);
    }

    public function test_can_create_partner_translation(): void
    {
        $partner = Partner::factory()->create();
        $context = Context::factory()->create();
        $language = Language::factory()->create();

        $data = PartnerTranslation::factory()->make([
            'partner_id' => $partner->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
        ])->toArray();

        $response = $this->postJson('/api/partner-translation', $data);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'partner_id',
                    'language_id',
                    'context_id',
                    'name',
                    'description',
                    'city_display',
                    'address_line_1',
                    'address_line_2',
                    'postal_code',
                    'address_notes',
                    'contact_name',
                    'contact_email_general',
                    'contact_email_press',
                    'contact_phone',
                    'contact_website',
                    'contact_notes',
                    'contact_emails',
                    'contact_phones',
                    'backward_compatibility',
                    'extra',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('data.partner_id', $data['partner_id'])
            ->assertJsonPath('data.language_id', $data['language_id'])
            ->assertJsonPath('data.context_id', $data['context_id'])
            ->assertJsonPath('data.name', $data['name']);

        $this->assertDatabaseHas('partner_translations', [
            'partner_id' => $data['partner_id'],
            'language_id' => $data['language_id'],
            'context_id' => $data['context_id'],
            'name' => $data['name'],
        ]);
    }

    public function test_cannot_create_partner_translation_without_required_fields(): void
    {
        $data = [];

        $response = $this->postJson('/api/partner-translation', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['partner_id', 'language_id', 'context_id', 'name']);
    }

    public function test_cannot_create_duplicate_partner_translation(): void
    {
        $existingTranslation = PartnerTranslation::factory()->create();

        $data = [
            'partner_id' => $existingTranslation->partner_id,
            'language_id' => $existingTranslation->language_id,
            'context_id' => $existingTranslation->context_id,
            'name' => 'Test Name',
        ];

        $response = $this->postJson('/api/partner-translation', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['partner_id']);
    }

    public function test_validates_contact_email_format(): void
    {
        $partner = Partner::factory()->create();
        $context = Context::factory()->create();
        $language = Language::factory()->create();

        $data = PartnerTranslation::factory()->make([
            'partner_id' => $partner->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'contact_email_general' => 'invalid-email',
        ])->toArray();

        $response = $this->postJson('/api/partner-translation', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['contact_email_general']);
    }

    public function test_validates_contact_website_format(): void
    {
        $partner = Partner::factory()->create();
        $context = Context::factory()->create();
        $language = Language::factory()->create();

        $data = PartnerTranslation::factory()->make([
            'partner_id' => $partner->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'contact_website' => 'not-a-url',
        ])->toArray();

        $response = $this->postJson('/api/partner-translation', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['contact_website']);
    }
}
