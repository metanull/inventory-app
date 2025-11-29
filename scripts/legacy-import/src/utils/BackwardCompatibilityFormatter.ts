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

export class BackwardCompatibilityFormatter {
  /**
   * Format a backward compatibility reference
   */
  static format(ref: BackwardCompatibilityRef): string {
    const parts = [ref.schema, ref.table, ...ref.pkValues.map(String)];
    return parts.join(':');
  }

  /**
   * Parse a backward compatibility reference
   */
  static parse(formatted: string): BackwardCompatibilityRef {
    const parts = formatted.split(':');
    if (parts.length < 3) {
      throw new Error(`Invalid backward_compatibility format: ${formatted}`);
    }

    return {
      schema: parts[0]!,
      table: parts[1]!,
      pkValues: parts.slice(2),
    };
  }

  /**
   * Format for denormalized tables (exclude language)
   * Example: mwnf3:objects:vm:ma:louvre:001 (language excluded)
   */
  static formatDenormalized(
    schema: string,
    table: string,
    pkColumns: Record<string, string | number>,
    excludeColumns: string[] = ['lang', 'language', 'language_id']
  ): string {
    const values = Object.entries(pkColumns)
      .filter(([key]) => !excludeColumns.includes(key.toLowerCase()))
      .map(([, value]) => value);

    return this.format({ schema, table, pkValues: values });
  }

  /**
   * Format for image references (include image index)
   * Example: mwnf3:objects_pictures:vm:ma:louvre:001:1
   */
  static formatImage(
    schema: string,
    table: string,
    itemPkValues: (string | number)[],
    imageIndex: number
  ): string {
    return this.format({ schema, table, pkValues: [...itemPkValues, imageIndex] });
  }
}
