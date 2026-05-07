<?php

namespace App\Enums;

enum LegacyLinkConfidence: string
{
    case EXACT = 'exact';
    case INFERRED = 'inferred';
    case REQUIRES_LOOKUP = 'requires_lookup';
    case UNSUPPORTED = 'unsupported';

    public function label(): string
    {
        return match ($this) {
            self::EXACT => 'Exact',
            self::INFERRED => 'Inferred',
            self::REQUIRES_LOOKUP => 'Requires lookup',
            self::UNSUPPORTED => 'Unsupported',
        };
    }
}
