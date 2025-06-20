<?php

namespace Tests\Feature;

use App\Models\AvailableImage;
use App\Models\User;
use Tests\TestCase;

class AvailableImageTest extends TestCase
{
    public function test_index_requires_authentication(): void
    {
        $response_anonymous = $this->getJson(route('available-image.index'));
        $response_anonymous->assertUnauthorized();

        $user = User::factory()->create();
        $response_authenticated = $this->actingAs($user)
            ->getJson(route('available-image.index'));
        $response_authenticated->assertOk();
    }

    public function test_show_requires_authentication(): void
    {
        $availableImage = AvailableImage::factory()->create();

        $response_anonymous = $this->getJson(route('available-image.show', $availableImage->id));
        $response_anonymous->assertUnauthorized();

        $user = User::factory()->create();
        $response_authenticated = $this->actingAs($user)
            ->getJson(route('available-image.show', $availableImage->id));
        $response_authenticated->assertOk();
    }

    public function test_update_requires_authentication(): void
    {
        $availableImage = AvailableImage::factory()->create();

        $response_anonymous = $this->putJson(route('available-image.update', $availableImage->id), [
            'comment' => fake()->sentence(),
        ]);
        $response_anonymous->assertUnauthorized();

        $user = User::factory()->create();
        $response_authenticated = $this->actingAs($user)
            ->putJson(route('available-image.update', $availableImage->id), [
                'comment' => fake()->sentence(),
            ]);
        $response_authenticated->assertOk();
    }

    public function test_destroy_requires_authentication(): void
    {
        $availableImage = AvailableImage::factory()->create();

        $response_anonymous = $this->deleteJson(route('available-image.destroy', $availableImage->id));
        $response_anonymous->assertUnauthorized();

        $user = User::factory()->create();
        $response_authenticated = $this->actingAs($user)
            ->deleteJson(route('available-image.destroy', $availableImage->id));
        $response_authenticated->assertNoContent();
    }
}
