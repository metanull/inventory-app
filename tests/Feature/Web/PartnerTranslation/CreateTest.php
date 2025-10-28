<?php

declare(strict_types=1);

namespace Tests\Feature\Web\PartnerTranslation;

use App\Models\Context;
use App\Models\Language;
use App\Models\Partner;
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
        $partner = Partner::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $response = $this->get(route('partner-translations.create'));
        $response->assertOk();
        $response->assertSee('Create Partner Translation');
        $response->assertSee('Partner');
        $response->assertSee('Language');
        $response->assertSee('Context');
        $response->assertSee('Name');
        $response->assertSee('Description');
    }
}
