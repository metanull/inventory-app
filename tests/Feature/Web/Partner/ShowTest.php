<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Partner;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ShowTest extends TestCase
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

    public function test_show_displays_partner_detail(): void
    {
        $partner = Partner::factory()->create();
        $response = $this->get(route('partners.show', $partner));
        $response->assertOk();
        $response->assertSee(e($partner->internal_name));
    }
}
