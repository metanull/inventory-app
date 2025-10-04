<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Parity;

use App\Models\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class CollectionsParityTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();
    }

    public function test_api_meta_total_matches_web_count_first_page(): void
    {
        Collection::factory()->count(28)->create();

        $api = $this->getJson(route('collection.index', ['per_page' => 25]));
        $api->assertOk();
        $this->assertSame(28, $api->json('meta.total'));

        $web = $this->get(route('collections.index', ['perPage' => 25]));
        $web->assertOk();
        $rowCount = substr_count($web->getContent(), '<tr');
        $this->assertGreaterThanOrEqual(25, $rowCount - 1);
    }
}
