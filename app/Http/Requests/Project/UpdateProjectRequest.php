<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $projectId = $this->route('project') ? $this->route('project')->id : null;

        return [
            'id' => 'prohibited',
            'internal_name' => 'required|string|unique:projects,internal_name,'.$projectId,
            'backward_compatibility' => 'nullable|string',
            'launch_date' => 'nullable|date',
            'is_launched' => 'boolean',
            'is_enabled' => 'boolean',
            'context_id' => 'nullable|uuid',
            'language_id' => 'nullable|string|size:3',
            'include' => 'sometimes|string',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $allowedParameters = [
                'id', 'internal_name', 'backward_compatibility', 'launch_date',
                'is_launched', 'is_enabled', 'context_id', 'language_id', 'include',
            ];
            $receivedParameters = array_keys($this->all());
            $unexpectedParameters = array_diff($receivedParameters, $allowedParameters);

            foreach ($unexpectedParameters as $parameter) {
                $validator->errors()->add($parameter, "The {$parameter} parameter is not allowed.");
            }
        });
    }
}
