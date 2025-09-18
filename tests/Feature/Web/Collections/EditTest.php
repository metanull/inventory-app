<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Collections;

use App\Models\Collection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditTest extends TestCase
{
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_edit_form_renders(): void
    {
        $collection = Collection::factory()->create();
        $response = $this->get(route('collections.edit', $collection));
        $response->assertOk();
        $response->assertSee('Edit Collection');
    }
}
