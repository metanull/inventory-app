<?php

namespace App\Enums;

enum LegacyLinkType: string
{
    case PUBLIC = 'public';
    case BACKOFFICE = 'backoffice';
    case SOURCE = 'source';
    case DIAGNOSTIC = 'diagnostic';

    public function label(): string
    {
        return match ($this) {
            self::PUBLIC => 'Public',
            self::BACKOFFICE => 'Back-office',
            self::SOURCE => 'Source',
            self::DIAGNOSTIC => 'Diagnostic',
        };
    }
}
