<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Glossary;

use App\Models\Glossary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class IndexTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();
    }

    public function test_index_displays_glossaries(): void
    {
        $glossary1 = Glossary::factory()->create(['internal_name' => 'First Entry']);
        $glossary2 = Glossary::factory()->create(['internal_name' => 'Second Entry']);
        $glossary3 = Glossary::factory()->create(['internal_name' => 'Third Entry']);

        $response = $this->get(route('glossaries.index'));
        $response->assertOk();
        $response->assertSee('Glossary');
        $response->assertSee('First Entry');
        $response->assertSee('Second Entry');
        $response->assertSee('Third Entry');
    }

    public function test_index_displays_empty_state_when_no_glossaries(): void
    {
        $response = $this->get(route('glossaries.index'));
        $response->assertOk();
        $response->assertSee('Glossary');
    }
}
