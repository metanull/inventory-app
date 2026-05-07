<?php

namespace App\Support\LegacyLinks\Rules;

use App\Support\LegacyLinks\LegacyLink;
use App\Support\LegacyLinks\LegacyReference;
use Illuminate\Database\Eloquent\Model;

interface LegacyUrlRule
{
    public function supports(Model $record, LegacyReference $reference): bool;

    /**
     * @return array<int, LegacyLink>
     */
    public function resolve(Model $record, LegacyReference $reference, string $legacyLanguage): array;
}
