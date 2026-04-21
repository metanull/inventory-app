<?php

namespace App\Support\Web\Lists;

final class UserManagementListDefinition extends ListDefinition
{
    public function filterParameters(): array
    {
        return ['role'];
    }

    public function filterRules(): array
    {
        return [
            'role' => ['sometimes', 'nullable', 'string', 'exists:roles,name'],
        ];
    }

    public function sorts(): array
    {
        return [
            'name' => new ListSortDefinition('name', ListQueryParameters::ASC),
            'email' => new ListSortDefinition('email', ListQueryParameters::ASC),
            'created_at' => new ListSortDefinition('created_at', ListQueryParameters::DESC),
            'email_verified_at' => new ListSortDefinition('email_verified_at', ListQueryParameters::DESC),
        ];
    }

    public function searchColumns(): array
    {
        return ['users.name', 'users.email'];
    }

    public function normalizeFilters(array $input): array
    {
        $role = $this->normalizeNullableString($input['role'] ?? null);

        return $role === null ? [] : ['role' => $role];
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $normalized = trim($value);

        return $normalized === '' ? null : $normalized;
    }
}
