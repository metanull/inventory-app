<?php

namespace Tests\Feature\Api\ExhibitionTranslation;

use App\Models\Context;
use App\Models\Exhibition;
use App\Models\ExhibitionTranslation;
use App\Models\Language;
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

    public function test_can_create_exhibition_translation(): void
    {
        $exhibition = Exhibition::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $data = ExhibitionTranslation::factory()->make([
            'exhibition_id' => $exhibition->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
        ])->toArray();

        $response = $this->postJson(route('exhibition-translation.store'), $data);

        $response->assertCreated();
        $this->assertDatabaseHas('exhibition_translations', [
            'exhibition_id' => $data['exhibition_id'],
            'language_id' => $data['language_id'],
            'context_id' => $data['context_id'],
            'title' => $data['title'],
            'description' => $data['description'],
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->postJson(route('exhibition-translation.store'), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'exhibition_id',
                'language_id',
                'context_id',
                'title',
                'description',
            ]);
    }

    public function test_store_validates_exhibition_exists(): void
    {
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $data = ExhibitionTranslation::factory()->make([
            'exhibition_id' => 'non-existent-id',
            'language_id' => $language->id,
            'context_id' => $context->id,
        ])->toArray();

        $response = $this->postJson(route('exhibition-translation.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['exhibition_id']);
    }

    public function test_store_validates_language_exists(): void
    {
        $exhibition = Exhibition::factory()->create();
        $context = Context::factory()->create();

        $data = ExhibitionTranslation::factory()->make([
            'exhibition_id' => $exhibition->id,
            'language_id' => 'non-existent',
            'context_id' => $context->id,
        ])->toArray();

        $response = $this->postJson(route('exhibition-translation.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['language_id']);
    }

    public function test_store_validates_context_exists(): void
    {
        $exhibition = Exhibition::factory()->create();
        $language = Language::factory()->create();

        $data = ExhibitionTranslation::factory()->make([
            'exhibition_id' => $exhibition->id,
            'language_id' => $language->id,
            'context_id' => 'non-existent-id',
        ])->toArray();

        $response = $this->postJson(route('exhibition-translation.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['context_id']);
    }

    public function test_store_validates_title_is_required(): void
    {
        $exhibition = Exhibition::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $data = ExhibitionTranslation::factory()->make([
            'exhibition_id' => $exhibition->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'title' => '',
        ])->toArray();

        $response = $this->postJson(route('exhibition-translation.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['title']);
    }

    public function test_store_validates_description_is_required(): void
    {
        $exhibition = Exhibition::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $data = ExhibitionTranslation::factory()->make([
            'exhibition_id' => $exhibition->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'description' => '',
        ])->toArray();

        $response = $this->postJson(route('exhibition-translation.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['description']);
    }

    public function test_store_validates_url_format(): void
    {
        $exhibition = Exhibition::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $data = ExhibitionTranslation::factory()->make([
            'exhibition_id' => $exhibition->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'url' => 'not-a-valid-url',
        ])->toArray();

        $response = $this->postJson(route('exhibition-translation.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['url']);
    }

    public function test_store_allows_null_url(): void
    {
        $exhibition = Exhibition::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $data = ExhibitionTranslation::factory()->make([
            'exhibition_id' => $exhibition->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'url' => null,
        ])->toArray();

        $response = $this->postJson(route('exhibition-translation.store'), $data);

        $response->assertCreated();
        $this->assertDatabaseHas('exhibition_translations', [
            'exhibition_id' => $data['exhibition_id'],
            'language_id' => $data['language_id'],
            'context_id' => $data['context_id'],
            'url' => null,
        ]);
    }

    public function test_store_can_create_with_extra_data(): void
    {
        $exhibition = Exhibition::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $extraData = ['notes' => 'Test note', 'metadata' => ['key' => 'value']];

        $data = ExhibitionTranslation::factory()->make([
            'exhibition_id' => $exhibition->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'extra' => $extraData,
        ])->toArray();

        $response = $this->postJson(route('exhibition-translation.store'), $data);

        $response->assertCreated();

        $translation = ExhibitionTranslation::where('exhibition_id', $exhibition->id)
            ->where('language_id', $language->id)
            ->where('context_id', $context->id)
            ->first();

        $this->assertEquals($extraData, $translation->extra);
    }

    public function test_store_prevents_duplicate_translations(): void
    {
        $exhibition = Exhibition::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        // Create first translation
        ExhibitionTranslation::factory()->create([
            'exhibition_id' => $exhibition->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
        ]);

        // Try to create duplicate
        $data = ExhibitionTranslation::factory()->make([
            'exhibition_id' => $exhibition->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
        ])->toArray();

        $response = $this->postJson(route('exhibition-translation.store'), $data);

        $response->assertUnprocessable();
    }
}
