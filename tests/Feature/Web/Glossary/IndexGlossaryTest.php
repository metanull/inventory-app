<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Glossary;

use App\Models\Glossary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class IndexGlossaryTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    public function test_index_displays_glossary_entries(): void
    {
        $this->actingAsDataUser();

        $glossaries = Glossary::factory()->count(3)->create();

        $response = $this->get(route('glossaries.index'));

        $response->assertOk();
        $response->assertViewIs('glossary.index');
        $response->assertViewHas('glossaries');

        foreach ($glossaries as $glossary) {
            $response->assertSee($glossary->internal_name);
        }
    }

    public function test_index_search_filters_glossaries(): void
    {
        $this->actingAsDataUser();

        $matching = Glossary::factory()->create(['internal_name' => 'specific-term']);
        $other = Glossary::factory()->create(['internal_name' => 'other-term']);

        $response = $this->get(route('glossaries.index', ['q' => 'specific']));

        $response->assertOk();
        $response->assertSee($matching->internal_name);
        $response->assertDontSee($other->internal_name);
    }

    public function test_index_pagination_works(): void
    {
        $this->actingAsDataUser();

        Glossary::factory()->count(30)->create();

        $response = $this->get(route('glossaries.index'));

        $response->assertOk();
        $response->assertViewHas('glossaries');

        $glossaries = $response->viewData('glossaries');
        $this->assertLessThanOrEqual(25, $glossaries->count());
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->get(route('glossaries.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_index_requires_view_data_permission(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('glossaries.index'));

        $response->assertForbidden();
    }
}
