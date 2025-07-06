<?php

namespace Tests\Feature\Api\DetailTranslation;

use App\Models\Author;
use App\Models\Context;
use App\Models\Detail;
use App\Models\DetailTranslation;
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

    public function test_can_store_detail_translation(): void
    {
        $detail = Detail::factory()->withoutTranslations()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $data = DetailTranslation::factory()->make([
            'detail_id' => $detail->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
        ])->toArray();

        $response = $this->postJson(route('detail-translation.store'), $data);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'detail_id',
                    'language_id',
                    'context_id',
                    'name',
                    'description',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('detail_translations', [
            'detail_id' => $data['detail_id'],
            'language_id' => $data['language_id'],
            'context_id' => $data['context_id'],
            'name' => $data['name'],
            'description' => $data['description'],
        ]);
    }

    public function test_store_requires_detail_id(): void
    {
        $language = Language::factory()->create();
        $context = Context::factory()->create();
        $data = DetailTranslation::factory()->make([
            'language_id' => $language->id,
            'context_id' => $context->id,
        ])->except(['detail_id']);

        $response = $this->postJson(route('detail-translation.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['detail_id']);
    }

    public function test_store_requires_language_id(): void
    {
        $detail = Detail::factory()->withoutTranslations()->create();
        $context = Context::factory()->create();
        $data = DetailTranslation::factory()->make([
            'detail_id' => $detail->id,
            'context_id' => $context->id,
        ])->except(['language_id']);

        $response = $this->postJson(route('detail-translation.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['language_id']);
    }

    public function test_store_requires_context_id(): void
    {
        $detail = Detail::factory()->withoutTranslations()->create();
        $language = Language::factory()->create();
        $data = DetailTranslation::factory()->make([
            'detail_id' => $detail->id,
            'language_id' => $language->id,
        ])->except(['context_id']);

        $response = $this->postJson(route('detail-translation.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['context_id']);
    }

    public function test_store_requires_name(): void
    {
        $detail = Detail::factory()->withoutTranslations()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();
        $data = DetailTranslation::factory()->make([
            'detail_id' => $detail->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
        ])->except(['name']);

        $response = $this->postJson(route('detail-translation.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_requires_description(): void
    {
        $detail = Detail::factory()->withoutTranslations()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();
        $data = DetailTranslation::factory()->make([
            'detail_id' => $detail->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
        ])->except(['description']);

        $response = $this->postJson(route('detail-translation.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['description']);
    }

    public function test_store_validates_unique_constraint(): void
    {
        $detail = Detail::factory()->withoutTranslations()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        // Create first translation
        DetailTranslation::factory()->create([
            'detail_id' => $detail->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
        ]);

        // Try to create duplicate
        $data = DetailTranslation::factory()->make([
            'detail_id' => $detail->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
        ])->toArray();

        $response = $this->postJson(route('detail-translation.store'), $data);

        $response->assertUnprocessable();
    }

    public function test_store_allows_nullable_fields(): void
    {
        $detail = Detail::factory()->withoutTranslations()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $data = [
            'detail_id' => $detail->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph,
            'alternate_name' => null,
            'author_id' => null,
            'text_copy_editor_id' => null,
            'translator_id' => null,
            'translation_copy_editor_id' => null,
            'backward_compatibility' => null,
            'extra' => null,
        ];

        $response = $this->postJson(route('detail-translation.store'), $data);

        $response->assertCreated();
        $this->assertDatabaseHas('detail_translations', [
            'detail_id' => $data['detail_id'],
            'language_id' => $data['language_id'],
            'context_id' => $data['context_id'],
            'name' => $data['name'],
            'description' => $data['description'],
            'alternate_name' => null,
        ]);
    }

    public function test_store_with_author_relationships(): void
    {
        $detail = Detail::factory()->withoutTranslations()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();
        $author = Author::factory()->create();
        $textCopyEditor = Author::factory()->create();
        $translator = Author::factory()->create();
        $translationCopyEditor = Author::factory()->create();

        $data = DetailTranslation::factory()->make([
            'detail_id' => $detail->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'author_id' => $author->id,
            'text_copy_editor_id' => $textCopyEditor->id,
            'translator_id' => $translator->id,
            'translation_copy_editor_id' => $translationCopyEditor->id,
        ])->toArray();

        $response = $this->postJson(route('detail-translation.store'), $data);

        $response->assertCreated();
        $this->assertDatabaseHas('detail_translations', [
            'detail_id' => $data['detail_id'],
            'language_id' => $data['language_id'],
            'context_id' => $data['context_id'],
            'author_id' => $author->id,
            'text_copy_editor_id' => $textCopyEditor->id,
            'translator_id' => $translator->id,
            'translation_copy_editor_id' => $translationCopyEditor->id,
        ]);
    }

    public function test_store_with_extra_json_data(): void
    {
        $detail = Detail::factory()->withoutTranslations()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();
        $extraData = ['notes' => 'Test note', 'metadata' => ['key' => 'value']];

        $data = DetailTranslation::factory()->make([
            'detail_id' => $detail->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'extra' => $extraData,
        ])->toArray();

        $response = $this->postJson(route('detail-translation.store'), $data);

        $response->assertCreated();

        $translation = DetailTranslation::where('detail_id', $detail->id)
            ->where('language_id', $language->id)
            ->where('context_id', $context->id)
            ->first();

        $this->assertEquals($extraData, $translation->extra);
    }
}
