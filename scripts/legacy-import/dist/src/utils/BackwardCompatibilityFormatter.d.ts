/**
 * Backward compatibility reference formatter
 *
 * Format: {schema}:{table}:{pk_column1_value}:{pk_column2_value}:...
 *
 * Rules:
 * - Use semicolon as separator
 * - For denormalized tables (language in PK), exclude language column
 * - For images, include image index/order number
 * - Values must be URL-safe (no spaces, special chars escaped)
 */
export interface BackwardCompatibilityRef {
    schema: string;
    table: string;
    pkValues: (string | number)[];
}
export declare class BackwardCompatibilityFormatter {
    /**
     * Format a backward compatibility reference
     */
    static format(ref: BackwardCompatibilityRef): string;
    /**
     * Parse a backward compatibility reference
     */
    static parse(formatted: string): BackwardCompatibilityRef;
    /**
     * Format for denormalized tables (exclude language)
     * Example: mwnf3:objects:vm:ma:louvre:001 (language excluded)
     */
    static formatDenormalized(schema: string, table: string, pkColumns: Record<string, string | number>, excludeColumns?: string[]): string;
    /**
     * Format for image references (include image index)
     * Example: mwnf3:objects_pictures:vm:ma:louvre:001:1
     */
    static formatImage(schema: string, table: string, itemPkValues: (string | number)[], imageIndex: number): string;
}
//# sourceMappingURL=BackwardCompatibilityFormatter.d.ts.map