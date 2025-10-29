<?php

namespace Tests\Unit\Requests\Web;

use App\Http\Requests\Web\StoreItemTranslationRequest;
use App\Models\Context;
use App\Models\Item;
use App\Models\ItemTranslation;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Tests for StoreItemTranslationRequest uniqueness validation.
 *
 * Tests the custom business logic that enforces unique
 * combinations of item_id + language_id + context_id.
 */
class StoreItemTranslationRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_rejects_duplicate_translation(): void
    {
        $item = Item::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        // Create existing translation
        ItemTranslation::factory()->create([
            'item_id' => $item->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
        ]);

        // Try to create duplicate
        $request = new StoreItemTranslationRequest;
        $request->merge([
            'item_id' => $item->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => 'Test Name',
        ]);

        $validator = Validator::make($request->all(), $request->rules());
        $request->withValidator($validator);

        try {
            $validator->validate();
            $this->fail('Validation should have failed for duplicate translation');
        } catch (ValidationException $e) {
            $this->assertTrue($validator->errors()->has('item_id'));
            $this->assertStringContainsString('already exists', $validator->errors()->first('item_id'));
        }
    }

    public function test_accepts_unique_translation(): void
    {
        $item = Item::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $request = new StoreItemTranslationRequest;
        $request->merge([
            'item_id' => $item->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => 'Test Name',
        ]);

        $validator = Validator::make($request->all(), $request->rules());
        $request->withValidator($validator);

        $validator->validate();
        $this->assertFalse($validator->errors()->any());
    }

    public function test_accepts_same_item_different_language(): void
    {
        $item = Item::factory()->create();
        $language1 = Language::factory()->create();
        $language2 = Language::factory()->create();
        $context = Context::factory()->create();

        ItemTranslation::factory()->create([
            'item_id' => $item->id,
            'language_id' => $language1->id,
            'context_id' => $context->id,
        ]);

        $request = new StoreItemTranslationRequest;
        $request->merge([
            'item_id' => $item->id,
            'language_id' => $language2->id,
            'context_id' => $context->id,
            'name' => 'Test Name',
        ]);

        $validator = Validator::make($request->all(), $request->rules());
        $request->withValidator($validator);

        $validator->validate();
        $this->assertFalse($validator->errors()->any());
    }

    public function test_accepts_same_item_different_context(): void
    {
        $item = Item::factory()->create();
        $language = Language::factory()->create();
        $context1 = Context::factory()->create();
        $context2 = Context::factory()->create();

        ItemTranslation::factory()->create([
            'item_id' => $item->id,
            'language_id' => $language->id,
            'context_id' => $context1->id,
        ]);

        $request = new StoreItemTranslationRequest;
        $request->merge([
            'item_id' => $item->id,
            'language_id' => $language->id,
            'context_id' => $context2->id,
            'name' => 'Test Name',
        ]);

        $validator = Validator::make($request->all(), $request->rules());
        $request->withValidator($validator);

        $validator->validate();
        $this->assertFalse($validator->errors()->any());
    }
}
