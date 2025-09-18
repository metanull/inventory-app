<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Parity;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnersParityTest extends TestCase
{
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_api_meta_total_matches_web_count_first_page(): void
    {
        Partner::factory()->count(28)->create();

        $api = $this->getJson(route('partner.index', ['per_page' => 25]));
        $api->assertOk();
        $this->assertSame(28, $api->json('meta.total'));

        $web = $this->get(route('partners.index', ['perPage' => 25]));
        $web->assertOk();
        $rowCount = substr_count($web->getContent(), '<tr');
        $this->assertGreaterThanOrEqual(25, $rowCount - 1);
    }
}
