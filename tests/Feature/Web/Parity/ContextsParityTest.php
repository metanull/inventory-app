<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Parity;

use App\Models\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class ContextsParityTest extends TestCase
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
        Context::factory()->count(13)->create();

        $api = $this->getJson(route('context.index', ['per_page' => 10]));
        $api->assertOk();
        $this->assertSame(13, $api->json('meta.total'));

        $web = $this->get(route('contexts.index', ['perPage' => 10]));
        $web->assertOk();
        $rowCount = substr_count($web->getContent(), '<tr');
        $this->assertGreaterThanOrEqual(10, $rowCount - 1);
    }
}
