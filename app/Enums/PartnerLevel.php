<?php

namespace App\Enums;

/**
 * Partner Level Enum
 *
 * Defines the different levels of partner contribution to collections.
 * Used in the pivot table relationship between collections and partners.
 */
enum PartnerLevel: string
{
    case PARTNER = 'partner';
    case ASSOCIATED_PARTNER = 'associated_partner';
    case MINOR_CONTRIBUTOR = 'minor_contributor';

    /**
     * Get a human-readable label for the partner level.
     */
    public function label(): string
    {
        return match ($this) {
            self::PARTNER => 'Partner',
            self::ASSOCIATED_PARTNER => 'Associated Partner',
            self::MINOR_CONTRIBUTOR => 'Minor Contributor',
        };
    }

    /**
     * Get a description for the partner level.
     */
    public function description(): string
    {
        return match ($this) {
            self::PARTNER => 'Partners contributing directly to the collection by providing content',
            self::ASSOCIATED_PARTNER => 'Partners contributing indirectly by supporting the collection',
            self::MINOR_CONTRIBUTOR => 'Partners with minor and indirect contribution to the collection',
        };
    }

    /**
     * Get all available partner levels.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::PARTNER->value => self::PARTNER->label(),
            self::ASSOCIATED_PARTNER->value => self::ASSOCIATED_PARTNER->label(),
            self::MINOR_CONTRIBUTOR->value => self::MINOR_CONTRIBUTOR->label(),
        ];
    }
}
