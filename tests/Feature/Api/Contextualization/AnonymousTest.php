<?php

namespace Tests\Feature\Api\Contextualization;

use App\Models\Contextualization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnonymousTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_anonymous_user_cannot_access_index(): void
    {
        $response = $this->getJson(route('contextualization.index'));

        $response->assertUnauthorized();
    }

    public function test_anonymous_user_cannot_access_show(): void
    {
        $contextualization = Contextualization::factory()->create();

        $response = $this->getJson(route('contextualization.show', $contextualization));

        $response->assertUnauthorized();
    }

    public function test_anonymous_user_cannot_create(): void
    {
        $contextualizeData = Contextualization::factory()->make()->toArray();

        $response = $this->postJson(route('contextualization.store'), $contextualizeData);

        $response->assertUnauthorized();
    }

    public function test_anonymous_user_cannot_update(): void
    {
        $contextualization = Contextualization::factory()->create();
        $updateData = ['internal_name' => 'updated-name'];

        $response = $this->putJson(route('contextualization.update', $contextualization), $updateData);

        $response->assertUnauthorized();
    }

    public function test_anonymous_user_cannot_delete(): void
    {
        $contextualization = Contextualization::factory()->create();

        $response = $this->deleteJson(route('contextualization.destroy', $contextualization));

        $response->assertUnauthorized();
    }

    public function test_anonymous_user_cannot_access_default_context(): void
    {
        $response = $this->getJson(route('contextualization.defaultContext'));

        $response->assertUnauthorized();
    }

    public function test_anonymous_user_cannot_access_for_items(): void
    {
        $response = $this->getJson(route('contextualization.forItems'));

        $response->assertUnauthorized();
    }

    public function test_anonymous_user_cannot_access_for_details(): void
    {
        $response = $this->getJson(route('contextualization.forDetails'));

        $response->assertUnauthorized();
    }

    public function test_anonymous_user_cannot_store_with_default_context(): void
    {
        $contextualizeData = Contextualization::factory()->make()->toArray();
        unset($contextualizeData['context_id']); // Remove context_id since it's handled by the method

        $response = $this->postJson(route('contextualization.storeWithDefaultContext'), $contextualizeData);

        $response->assertUnauthorized();
    }
}
