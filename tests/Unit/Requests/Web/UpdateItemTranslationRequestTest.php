<?php

namespace Tests\Unit\Requests\Web;

use App\Http\Requests\Web\UpdateItemTranslationRequest;
use App\Models\Context;
use App\Models\Item;
use App\Models\ItemTranslation;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Tests for UpdateItemTranslationRequest uniqueness validation.
 */
class UpdateItemTranslationRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_rejects_duplicate_translation(): void
    {
        $item = Item::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        ItemTranslation::factory()->create([
            'item_id' => $item->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
        ]);

        $translation2 = ItemTranslation::factory()->create();

        $request = new UpdateItemTranslationRequest;
        $request->setRouteResolver(function () use ($translation2) {
            return new class($translation2)
            {
                public function __construct(private ItemTranslation $translation) {}

                public function parameter($key)
                {
                    return $key === 'item_translation' ? $this->translation : null;
                }
            };
        });
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
        }
    }

    public function test_accepts_unique_translation(): void
    {
        $translation = ItemTranslation::factory()->create();
        $language = Language::factory()->create();

        $request = new UpdateItemTranslationRequest;
        $request->setRouteResolver(function () use ($translation) {
            return new class($translation)
            {
                public function __construct(private ItemTranslation $translation) {}

                public function parameter($key)
                {
                    return $key === 'item_translation' ? $this->translation : null;
                }
            };
        });
        $request->merge([
            'item_id' => $translation->item_id,
            'language_id' => $language->id,
            'context_id' => $translation->context_id,
            'name' => 'Test Name',
        ]);

        $validator = Validator::make($request->all(), $request->rules());
        $request->withValidator($validator);

        $validator->validate();
        $this->assertFalse($validator->errors()->any());
    }
}
