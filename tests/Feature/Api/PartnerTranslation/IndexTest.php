<?php

declare(strict_types=1);

namespace Tests\Feature\Api\PartnerTranslation;

use App\Enums\Permission;
use App\Models\PartnerTranslation;
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

    public function test_index_returns_partner_translations_list(): void
    {
        PartnerTranslation::factory()
            ->count(3)
            ->create();

        $response = $this->getJson(route('partner-translation.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'partner_id',
                        'language_id',
                        'context_id',
                        'name',
                        'description',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_index_supports_pagination(): void
    {
        PartnerTranslation::factory()->count(15)->create();

        $response = $this->getJson(route('partner-translation.index', ['per_page' => 5]));

        $response->assertOk()
            ->assertJsonPath('meta.per_page', 5)
            ->assertJsonCount(5, 'data');
    }

    public function test_index_supports_includes(): void
    {
        $translation = PartnerTranslation::factory()->create();

        $response = $this->getJson(route('partner-translation.index', ['include' => 'partner,language,context']));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'partner',
                        'language',
                        'context',
                    ],
                ],
            ]);
    }
}
