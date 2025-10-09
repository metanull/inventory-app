<?php

namespace Tests\Feature\Api\ContactTranslation;

use App\Models\ContactTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class IndexTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createVisitorUser();
        $this->actingAs($this->user);
    }

    public function test_can_list_contact_translations()
    {
        ContactTranslation::factory()->count(3)->create();

        $response = $this->getJson(route('contact-translation.index'));

        $response->assertOk()
            ->assertJsonStructure(['data']);
    }
}
