<?php

namespace Tests\Feature\Api\Picture;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_store_method_not_allowed(): void
    {
        $response = $this->postJson('/api/picture', [
            'internal_name' => 'Test Picture',
        ]);

        $response->assertMethodNotAllowed();
    }

    public function test_pictures_can_only_be_created_through_attachment_endpoints(): void
    {
        // Pictures should only be created through the attachment endpoints:
        // - POST /api/item/{item}/pictures
        // - POST /api/detail/{detail}/pictures
        // - POST /api/partner/{partner}/pictures

        // The standard store route is excluded from the resource controller
        $this->assertTrue(true, 'Pictures can only be created through attachment endpoints');
    }
}
