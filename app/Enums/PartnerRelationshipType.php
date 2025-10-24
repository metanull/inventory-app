<?php

namespace App\Enums;

/**
 * Partner Relationship Type Enum
 *
 * Defines the different types of relationships between partners and collections.
 * Used in the pivot table relationship between collections and partners.
 */
enum PartnerRelationshipType: string
{
    case PARTNER = 'partner';
    case ASSOCIATE_PARTNER = 'associate_partner';
    case FURTHER_ASSOCIATE = 'further_associate';

    /**
     * Get a human-readable label for the relationship type.
     */
    public function label(): string
    {
        return match ($this) {
            self::PARTNER => 'Partner',
            self::ASSOCIATE_PARTNER => 'Associate Partner',
            self::FURTHER_ASSOCIATE => 'Further Associated Partner',
        };
    }

    /**
     * Get a description for the relationship type.
     */
    public function description(): string
    {
        return match ($this) {
            self::PARTNER => 'Primary partner contributing directly to the collection',
            self::ASSOCIATE_PARTNER => 'Associate partner with significant involvement',
            self::FURTHER_ASSOCIATE => 'Additional partner with supporting role',
        };
    }

    /**
     * Get all possible values as an array.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }
}
