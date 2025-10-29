<?php

namespace Tests\Unit\Requests\Web;

use App\Http\Requests\Web\UpdatePartnerTranslationRequest;
use App\Models\Context;
use App\Models\Language;
use App\Models\Partner;
use App\Models\PartnerTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Tests for UpdatePartnerTranslationRequest uniqueness validation.
 */
class UpdatePartnerTranslationRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_rejects_duplicate_translation(): void
    {
        $partner = Partner::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $translation1 = PartnerTranslation::factory()->create([
            'partner_id' => $partner->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
        ]);

        $translation2 = PartnerTranslation::factory()->create();

        $request = new UpdatePartnerTranslationRequest;
        $request->setRouteResolver(function () use ($translation2) {
            return new class($translation2)
            {
                public function __construct(private PartnerTranslation $translation) {}

                public function parameter($key)
                {
                    return $key === 'partner_translation' ? $this->translation : null;
                }
            };
        });
        $request->merge([
            'partner_id' => $partner->id,
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
            $this->assertTrue($validator->errors()->has('partner_id'));
        }
    }

    public function test_accepts_unique_translation(): void
    {
        $translation = PartnerTranslation::factory()->create();
        $language = Language::factory()->create();

        $request = new UpdatePartnerTranslationRequest;
        $request->setRouteResolver(function () use ($translation) {
            return new class($translation)
            {
                public function __construct(private PartnerTranslation $translation) {}

                public function parameter($key)
                {
                    return $key === 'partner_translation' ? $this->translation : null;
                }
            };
        });
        $request->merge([
            'partner_id' => $translation->partner_id,
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
