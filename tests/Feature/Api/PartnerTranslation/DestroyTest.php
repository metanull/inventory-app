<?php

declare(strict_types=1);

namespace Tests\Feature\Api\PartnerTranslation;

use App\Models\PartnerTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class DestroyTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createDataUser();
        $this->actingAs($this->user);
    }

    public function test_destroy_deletes_partner_translation(): void
    {
        $translation = PartnerTranslation::factory()->create();

        $response = $this->deleteJson(route('partner-translation.destroy', $translation));

        $response->assertNoContent();
        $this->assertDatabaseMissing('partner_translations', [
            'id' => $translation->id,
        ]);
    }
}
