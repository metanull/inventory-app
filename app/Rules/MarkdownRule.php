<?php

namespace App\Rules;

use App\Services\MarkdownService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validation rule for Markdown content
 *
 * This rule validates that the content is properly formatted Markdown
 * and doesn't contain any unsafe elements or malicious content.
 */
class MarkdownRule implements ValidationRule
{
    private MarkdownService $markdownService;

    public function __construct()
    {
        $this->markdownService = app(MarkdownService::class);
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('The :attribute must be a string.');

            return;
        }

        try {
            $this->markdownService->validateMarkdown($value);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            foreach ($errors as $error) {
                $fail($error);
            }
        } catch (\Exception $e) {
            $fail('The :attribute contains invalid Markdown formatting.');
        }
    }
}
