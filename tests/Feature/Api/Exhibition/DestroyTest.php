<?php

namespace Tests\Feature\Api\Exhibition;

use App\Models\Exhibition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function it_deletes_an_exhibition(): void
    {
        $exhibition = Exhibition::factory()->create();
        $response = $this->deleteJson(route('exhibition.destroy', $exhibition));
        $response->assertNoContent();
        $this->assertDatabaseMissing('exhibitions', ['id' => $exhibition->id]);
    }
}
