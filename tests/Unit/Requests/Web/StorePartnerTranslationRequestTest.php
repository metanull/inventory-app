<?php

namespace Tests\Unit\Requests\Web;

use App\Http\Requests\Web\StorePartnerTranslationRequest;
use App\Models\Context;
use App\Models\Language;
use App\Models\Partner;
use App\Models\PartnerTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Tests for StorePartnerTranslationRequest uniqueness validation.
 */
class StorePartnerTranslationRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_rejects_duplicate_translation(): void
    {
        $partner = Partner::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        PartnerTranslation::factory()->create([
            'partner_id' => $partner->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
        ]);

        $request = new StorePartnerTranslationRequest;
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
            $this->assertStringContainsString('already exists', $validator->errors()->first('partner_id'));
        }
    }

    public function test_accepts_unique_translation(): void
    {
        $partner = Partner::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $request = new StorePartnerTranslationRequest;
        $request->merge([
            'partner_id' => $partner->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => 'Test Name',
        ]);

        $validator = Validator::make($request->all(), $request->rules());
        $request->withValidator($validator);

        $validator->validate();
        $this->assertFalse($validator->errors()->any());
    }

    public function test_accepts_same_partner_different_language(): void
    {
        $partner = Partner::factory()->create();
        $language1 = Language::factory()->create();
        $language2 = Language::factory()->create();
        $context = Context::factory()->create();

        PartnerTranslation::factory()->create([
            'partner_id' => $partner->id,
            'language_id' => $language1->id,
            'context_id' => $context->id,
        ]);

        $request = new StorePartnerTranslationRequest;
        $request->merge([
            'partner_id' => $partner->id,
            'language_id' => $language2->id,
            'context_id' => $context->id,
            'name' => 'Test Name',
        ]);

        $validator = Validator::make($request->all(), $request->rules());
        $request->withValidator($validator);

        $validator->validate();
        $this->assertFalse($validator->errors()->any());
    }
}
