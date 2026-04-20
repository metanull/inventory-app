<?php

namespace App\Http\Requests\Web;

use App\Support\Web\Lists\ListDefinition;
use App\Support\Web\Lists\ListInputNormalizer;
use App\Support\Web\Lists\ListQueryParameters;
use App\Support\Web\Lists\ListState;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class IndexListRequest extends FormRequest
{
    private ?ListDefinition $resolvedDefinition = null;

    abstract protected function createDefinition(): ListDefinition;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $definition = $this->definition();

        return array_merge([
            ListQueryParameters::SEARCH => ['sometimes', 'nullable', 'string'],
            ListQueryParameters::SORT => ['sometimes', 'string', Rule::in(array_keys($definition->sorts()))],
            ListQueryParameters::DIRECTION => ['sometimes', 'string', Rule::in(ListQueryParameters::directions())],
            ListQueryParameters::PAGE => ['sometimes', 'integer', 'min:1'],
            ListQueryParameters::PER_PAGE => ['sometimes', 'integer', Rule::in(array_map('intval', (array) config('interface.pagination.per_page_options')))],
        ], $definition->filterRules());
    }

    public function definition(): ListDefinition
    {
        return $this->resolvedDefinition ??= $this->createDefinition();
    }

    public function listState(): ListState
    {
        $definition = $this->definition();
        $validated = $this->validated();
        $filters = [];

        foreach ($definition->filterParameters() as $parameter) {
            if (array_key_exists($parameter, $validated)) {
                $filters[$parameter] = $validated[$parameter];
            }
        }

        return new ListState(
            search: $validated[ListQueryParameters::SEARCH] ?? null,
            sort: $validated[ListQueryParameters::SORT] ?? $definition->defaultSort(),
            direction: $validated[ListQueryParameters::DIRECTION] ?? $definition->defaultDirection(),
            page: $validated[ListQueryParameters::PAGE] ?? 1,
            perPage: $validated[ListQueryParameters::PER_PAGE] ?? (int) config('interface.pagination.default_per_page'),
            filters: $filters,
        );
    }

    protected function prepareForValidation(): void
    {
        $normalized = app(ListInputNormalizer::class)->normalize($this->query(), $this->definition());

        $this->merge(array_filter($normalized, static fn (mixed $value): bool => $value !== null && $value !== []));
    }
}
