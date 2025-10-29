<?php

namespace Tests\Unit\Requests\Web;

use App\Http\Requests\Web\UpdateCollectionTranslationRequest;
use App\Models\Collection;
use App\Models\CollectionTranslation;
use App\Models\Context;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Tests for UpdateCollectionTranslationRequest uniqueness validation.
 */
class UpdateCollectionTranslationRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_rejects_duplicate_translation(): void
    {
        $collection = Collection::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        CollectionTranslation::factory()->create([
            'collection_id' => $collection->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
        ]);

        $translation2 = CollectionTranslation::factory()->create();

        $request = new UpdateCollectionTranslationRequest;
        $request->setRouteResolver(function () use ($translation2) {
            return new class($translation2)
            {
                public function __construct(private CollectionTranslation $translation) {}

                public function parameter($key)
                {
                    return $key === 'collection_translation' ? $this->translation : null;
                }
            };
        });
        $request->merge([
            'collection_id' => $collection->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'title' => 'Test Title',
        ]);

        $validator = Validator::make($request->all(), $request->rules());
        $request->withValidator($validator);

        try {
            $validator->validate();
            $this->fail('Validation should have failed for duplicate translation');
        } catch (ValidationException $e) {
            $this->assertTrue($validator->errors()->has('collection_id'));
        }
    }
}
