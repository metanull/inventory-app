<?php

namespace Tests\Feature\Api\Picture;

use App\Models\Picture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_update_returns_method_not_allowed(): void
    {
        $picture = Picture::factory()->create();
        $data = ['internal_name' => 'Updated Name'];
        $response = $this->putJson(route('picture.update', $picture), $data);
        $response->assertMethodNotAllowed();
    }
}
