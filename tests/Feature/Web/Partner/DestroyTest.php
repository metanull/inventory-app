<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Partner;

use App\Models\Partner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class DestroyTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsDataUser();
    }

    public function test_destroy_deletes_partner_and_redirects(): void
    {
        $partner = Partner::factory()->create();
        $response = $this->delete(route('partners.destroy', $partner));
        $response->assertRedirect();
        $this->assertDatabaseMissing('partners', [
            'id' => $partner->id,
        ]);
    }
}
