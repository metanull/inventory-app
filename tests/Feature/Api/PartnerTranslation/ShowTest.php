<?php

declare(strict_types=1);

namespace Tests\Feature\Api\PartnerTranslation;

use App\Models\PartnerTranslation;
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
        $this->user = $this->createVisitorUser();
        $this->actingAs($this->user);
    }

    public function test_show_returns_partner_translation_details(): void
    {
        $translation = PartnerTranslation::factory()->create();

        $response = $this->getJson(route('partner-translation.show', $translation));

        $response->assertOk()
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
                    'contact_name',
                    'contact_email_general',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('data.id', $translation->id);
    }

    public function test_show_supports_includes(): void
    {
        $translation = PartnerTranslation::factory()->create();

        $response = $this->getJson(route('partner-translation.show', $translation).'?include=partner,language,context');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'partner',
                    'language',
                    'context',
                ],
            ]);
    }
}
