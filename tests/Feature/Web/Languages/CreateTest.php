<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Languages;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_create_form_renders(): void
    {
        $response = $this->get(route('languages.create'));
        $response->assertOk();
        $response->assertSee('Create Language');
        $response->assertSee('Code (3 letters)');
        $response->assertSee('Internal Name');
    }
}
