<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Partner;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
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
