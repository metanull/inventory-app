<?php

namespace Tests\Feature\Api\MobileAppAuthentication;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WipeTokensTest extends TestCase
{
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_wipe_tokens(): void
    {
        // Create some tokens
        $this->user->createToken('Token 1');
        $this->user->createToken('Token 2');
        $this->user->createToken('Token 3');

        $this->assertCount(3, $this->user->fresh()->tokens);

        $response = $this->getJson(route('token.wipe'));

        $response->assertNoContent();
        $this->assertCount(0, $this->user->fresh()->tokens);
    }

    public function test_can_wipe_tokens_when_no_tokens_exist(): void
    {
        $this->assertCount(0, $this->user->fresh()->tokens);

        $response = $this->getJson(route('token.wipe'));

        $response->assertNoContent();
        $this->assertCount(0, $this->user->fresh()->tokens);
    }
}
