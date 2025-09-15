<?php

namespace App\Support\Includes;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class IncludeParser
{
    /**
     * Parse and validate include parameters from request.
     *
     * @param  array<int, string>  $allowed
     * @return array<int, string>
     *
     * @throws ValidationException
     */
    public static function fromRequest(Request $request, array $allowed): array
    {
        $raw = (string) $request->query('include', '');
        if ($raw === '') {
            return [];
        }

        $parts = collect(explode(',', $raw))
            ->map(fn ($v) => trim((string) $v))
            ->filter(fn ($v) => $v !== '')
            ->unique()
            ->values();

        // Validate
        $invalid = $parts->reject(fn ($v) => in_array($v, $allowed, true))->values();
        if ($invalid->isNotEmpty()) {
            throw ValidationException::withMessages([
                'include' => [
                    'Invalid include value(s): '.$invalid->join(', ').'. Allowed: '.implode(', ', $allowed),
                ],
            ]);
        }

        return $parts->all();
    }
}
