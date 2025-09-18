<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Collections;

use App\Models\Context;
use App\Models\Language;
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
        // Ensure dropdowns have data
        Context::factory()->create();
        Language::factory()->create();

        $response = $this->get(route('collections.create'));
        $response->assertOk();
        $response->assertSee('Create Collection');
        $response->assertSee('Internal Name');
    }
}
