<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Contact;

use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class ShowTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();
    }

    public function test_show_displays_core_fields(): void
    {
        $contact = Contact::factory()->create([
            'internal_name' => 'John Smith',
            'phone_number' => '+12025551234',
            'email' => 'john@example.com',
        ]);

        $response = $this->get(route('contacts.show', $contact));
        $response->assertOk();
        $response->assertSee('John Smith');
        $response->assertSee('john@example.com');
    }
}
