<?php

declare(strict_types=1);

namespace Tests\Feature\Api\PartnerTranslation;

use App\Models\PartnerTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnonymousTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_forbids_anonymous_access(): void
    {
        $response = $this->getJson(route('partner-translation.index'));

        $response->assertUnauthorized();
    }

    public function test_show_forbids_anonymous_access(): void
    {
        $translation = PartnerTranslation::factory()->create();

        $response = $this->getJson(route('partner-translation.show', $translation));

        $response->assertUnauthorized();
    }

    public function test_store_forbids_anonymous_access(): void
    {
        $partner = \App\Models\Partner::factory()->create();
        $language = \App\Models\Language::factory()->create();
        $context = \App\Models\Context::factory()->create();

        $response = $this->postJson(route('partner-translation.store'), [
            'partner_id' => $partner->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => 'Test',
        ]);

        $response->assertUnauthorized();
    }

    public function test_update_forbids_anonymous_access(): void
    {
        $translation = PartnerTranslation::factory()->create();

        $response = $this->patchJson(route('partner-translation.update', $translation), [
            'partner_id' => $translation->partner_id,
            'language_id' => $translation->language_id,
            'context_id' => $translation->context_id,
            'name' => 'Test',
        ]);

        $response->assertUnauthorized();
    }

    public function test_destroy_forbids_anonymous_access(): void
    {
        $translation = PartnerTranslation::factory()->create();

        $response = $this->deleteJson(route('partner-translation.destroy', $translation));

        $response->assertUnauthorized();
    }
}
