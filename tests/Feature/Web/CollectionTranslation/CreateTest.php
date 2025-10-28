<?php

declare(strict_types=1);

namespace Tests\Feature\Web\CollectionTranslation;

use App\Models\Collection;
use App\Models\Context;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class CreateTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsDataUser();
    }

    public function test_create_displays_form(): void
    {
        $response = $this->get(route('collection-translations.create'));
        $response->assertOk();
        $response->assertSee('Create Collection Translation');
    }

    public function test_create_pre_populates_collection_from_query_param(): void
    {
        $collection = Collection::factory()->create(['internal_name' => 'Test Collection ABC']);

        $response = $this->get(route('collection-translations.create', ['collection_id' => $collection->id]));
        $response->assertOk();
        $response->assertSee('Test Collection ABC');
    }

    public function test_create_loads_necessary_reference_data(): void
    {
        $collection = Collection::factory()->create(['internal_name' => 'Sample Collection']);
        $language = Language::factory()->create(['internal_name' => 'Sample Language']);
        $context = Context::factory()->create(['internal_name' => 'Sample Context']);

        $response = $this->get(route('collection-translations.create'));
        $response->assertOk();
        $response->assertSee('Sample Collection');
        $response->assertSee('Sample Language');
        $response->assertSee('Sample Context');
    }
}
