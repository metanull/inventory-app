<?php

namespace Tests\Feature\Api\ContactTranslation;

use App\Models\ContactTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
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
