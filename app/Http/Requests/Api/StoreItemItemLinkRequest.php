<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreItemItemLinkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => ['prohibited'],
            'source_id' => [
                'required',
                'uuid',
                'exists:items,id',
                Rule::notIn([$this->input('target_id')]), // Prevent self-links
            ],
            'target_id' => [
                'required',
                'uuid',
                'exists:items,id',
                Rule::notIn([$this->input('source_id')]), // Prevent self-links
            ],
            'context_id' => [
                'required',
                'uuid',
                'exists:contexts,id',
            ],
            'created_at' => ['prohibited'],
            'updated_at' => ['prohibited'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'source_id.not_in' => 'The source item and target item cannot be the same.',
            'target_id.not_in' => 'The target item and source item cannot be the same.',
        ];
    }
}
