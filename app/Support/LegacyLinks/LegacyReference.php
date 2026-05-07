<?php

namespace App\Support\LegacyLinks;

final readonly class LegacyReference
{
    /**
     * @param  array<int, string>  $parts
     */
    private function __construct(
        public string $raw,
        public string $schema,
        public string $table,
        public array $parts,
    ) {}

    public static function parse(?string $value): ?self
    {
        $raw = trim((string) $value);

        if ($raw === '') {
            return null;
        }

        $segments = explode(':', $raw);

        if (count($segments) < 2 || in_array('', [$segments[0], $segments[1]], true)) {
            return null;
        }

        return new self(
            raw: $raw,
            schema: $segments[0],
            table: $segments[1],
            parts: array_slice($segments, 2),
        );
    }

    public function is(string $schema, ?string $table = null): bool
    {
        if ($this->schema !== $schema) {
            return false;
        }

        return $table === null || $this->table === $table;
    }

    public function part(int $index): ?string
    {
        return $this->parts[$index] ?? null;
    }

    public function hasParts(int $count): bool
    {
        return count($this->parts) >= $count;
    }
}
