<?php

namespace Tests\Feature\Api\Language;

use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AnonymousTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_index_forbids_anonymous_access(): void
    {
        $response = $this->getJson(route('language.index'));
        $response->assertUnauthorized();
    }

    public function test_show_forbids_anonymous_access(): void
    {
        $response = $this->getJson(route('language.show', 'TST'));
        $response->assertUnauthorized();
    }

    public function test_store_forbids_anonymous_access(): void
    {
        $response = $this->postJson(route('language.store'), [
            'id' => 'TST',
            'internal_name' => 'Test Language',
            'backward_compatibility' => 'TT',
        ]);
        $response->assertUnauthorized();
    }

    public function test_update_forbids_anonymous_access(): void
    {
        $response = $this->putJson(route('language.update', 'TST'), [
            'internal_name' => 'Updated Language',
            'backward_compatibility' => 'UU',
        ]);
        $response->assertUnauthorized();
    }

    public function test_destroy_forbids_anonymous_access(): void
    {
        $response = $this->deleteJson(route('language.destroy', 'TST'));
        $response->assertUnauthorized();
    }

    public function test_getdefault_forbids_anonymous_access(): void
    {
        $language = Language::factory()->withIsDefault()->create();
        $response = $this->getJson(route('language.getDefault'));
        $response->assertUnauthorized();
    }

    public function test_setdefault_forbids_anonymous_access(): void
    {
        $language = Language::factory()->create();
        $response = $this->patchJson(route('language.setDefault', $language->id), [
            'is_default' => true,
        ]);
        $response->assertUnauthorized();
    }
}
