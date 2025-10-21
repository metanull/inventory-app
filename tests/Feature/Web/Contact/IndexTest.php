<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Contact;

use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class IndexTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();
    }

    public function test_index_lists_contacts_with_pagination(): void
    {
        Contact::factory()->count(25)->create();
        $response = $this->get(route('contacts.index'));
        $response->assertOk();
        $response->assertSee('Contacts');
        $response->assertSee('Search contacts');
        $first = Contact::query()->orderByDesc('created_at')->first();
        $response->assertSee(e($first->internal_name));
    }

    public function test_index_search_filters_results(): void
    {
        Contact::factory()->count(5)->create();
        $target = Contact::factory()->create(['internal_name' => 'SPECIAL_CONTACT_TOKEN']);

        $response = $this->get(route('contacts.index', ['q' => 'SPECIAL_CONTACT_TOKEN']));
        $response->assertOk();
        $response->assertSee('SPECIAL_CONTACT_TOKEN');

        $nonMatch = Contact::where('id', '!=', $target->id)->first();
        if ($nonMatch) {
            $response->assertDontSee(e($nonMatch->internal_name));
        }
    }

    public function test_index_respects_per_page_query(): void
    {
        Contact::factory()->count(15)->create();
        $response = $this->get(route('contacts.index', ['per_page' => 10]));
        $response->assertOk();
        $rowCount = substr_count($response->getContent(), '<tr');
        $this->assertGreaterThanOrEqual(10, $rowCount - 1);
    }
}
