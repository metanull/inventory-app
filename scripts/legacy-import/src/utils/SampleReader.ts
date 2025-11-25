import Database from 'better-sqlite3';
import * as fs from 'fs';

/**
 * Reader for sample data collected during import
 * Used in tests to validate importers against real legacy data samples
 */
export interface SampleRecord {
  id: number;
  entity_type: string;
  source_db: string;
  raw_data: string; // JSON stringified legacy record
  sample_reason: string; // 'success', 'warning:detail', 'edge:detail'
  language: string | null;
  collected_at: string;
  record_hash: string;
}

export interface SampleQuery {
  entityType?: string;
  reason?: string; // 'success', 'warning', 'edge', or specific like 'warning:missing_name'
  language?: string;
  sourceDb?: string;
  limit?: number;
}

/**
 * Utility class to read sample data from SQLite database
 */
export class SampleReader {
  private db: Database.Database;

  constructor(dbPath: string) {
    if (!fs.existsSync(dbPath)) {
      throw new Error(`Sample database not found: ${dbPath}`);
    }

    this.db = new Database(dbPath, { readonly: true });
  }

  /**
   * Query samples with filters
   * @param query Filter criteria
   * @returns Array of sample records
   */
  query(query: SampleQuery): SampleRecord[] {
    const conditions: string[] = [];
    const params: unknown[] = [];

    if (query.entityType) {
      conditions.push('entity_type = ?');
      params.push(query.entityType);
    }

    if (query.reason) {
      // Support both exact match and prefix match
      if (query.reason.includes(':')) {
        // Exact match: 'warning:missing_name'
        conditions.push('sample_reason = ?');
        params.push(query.reason);
      } else {
        // Prefix match: 'warning' matches 'warning', 'warning:missing_name', etc.
        conditions.push('(sample_reason = ? OR sample_reason LIKE ?)');
        params.push(query.reason, `${query.reason}:%`);
      }
    }

    if (query.language) {
      conditions.push('language = ?');
      params.push(query.language);
    }

    if (query.sourceDb) {
      conditions.push('source_db = ?');
      params.push(query.sourceDb);
    }

    const whereClause = conditions.length > 0 ? `WHERE ${conditions.join(' AND ')}` : '';
    const limitClause = query.limit ? `LIMIT ${query.limit}` : '';

    const sql = `
      SELECT * FROM legacy_samples
      ${whereClause}
      ORDER BY id
      ${limitClause}
    `;

    return this.db.prepare(sql).all(...params) as SampleRecord[];
  }

  /**
   * Get all samples for a specific entity type
   * @param entityType Entity type (e.g., 'language', 'country', 'object')
   * @param limit Maximum number of records (default: all)
   */
  getByEntityType(entityType: string, limit?: number): SampleRecord[] {
    return this.query({ entityType, limit });
  }

  /**
   * Get all success samples for an entity type
   * @param entityType Entity type
   * @param limit Maximum number of records (default: 20)
   */
  getSuccessSamples(entityType: string, limit: number = 20): SampleRecord[] {
    return this.query({ entityType, reason: 'success', limit });
  }

  /**
   * Get all warning samples for an entity type
   * @param entityType Entity type
   * @param warningType Optional specific warning type (e.g., 'missing_name')
   */
  getWarningSamples(entityType: string, warningType?: string): SampleRecord[] {
    const reason = warningType ? `warning:${warningType}` : 'warning';
    return this.query({ entityType, reason });
  }

  /**
   * Get all edge case samples for an entity type
   * @param entityType Entity type
   * @param edgeType Optional specific edge case type (e.g., 'long_field')
   */
  getEdgeCaseSamples(entityType: string, edgeType?: string): SampleRecord[] {
    const reason = edgeType ? `edge:${edgeType}` : 'edge';
    return this.query({ entityType, reason });
  }

  /**
   * Get samples for a specific language
   * @param entityType Entity type
   * @param language ISO 639-3 language code
   * @param limit Maximum number of records (default: 20)
   */
  getByLanguage(entityType: string, language: string, limit: number = 20): SampleRecord[] {
    return this.query({ entityType, language, limit });
  }

  /**
   * Parse raw_data JSON into typed object
   * @param sample Sample record
   * @returns Parsed legacy data object
   */
  parseRawData<T = Record<string, unknown>>(sample: SampleRecord): T {
    return JSON.parse(sample.raw_data) as T;
  }

  /**
   * Get statistics about collected samples
   */
  getStats(): Record<string, number> {
    const rows = this.db
      .prepare(
        `
      SELECT entity_type, sample_reason, COUNT(*) as count 
      FROM legacy_samples 
      GROUP BY entity_type, sample_reason
      ORDER BY entity_type, sample_reason
    `
      )
      .all() as Array<{ entity_type: string; sample_reason: string; count: number }>;

    const stats: Record<string, number> = {};
    for (const row of rows) {
      stats[`${row.entity_type}:${row.sample_reason}`] = row.count;
    }
    return stats;
  }

  /**
   * Get total count of samples
   */
  getTotalCount(): number {
    const row = this.db.prepare('SELECT COUNT(*) as count FROM legacy_samples').get() as {
      count: number;
    };
    return row.count;
  }

  /**
   * Get count for specific entity type
   */
  getCount(entityType: string): number {
    const row = this.db
      .prepare('SELECT COUNT(*) as count FROM legacy_samples WHERE entity_type = ?')
      .get(entityType) as { count: number };
    return row.count;
  }

  /**
   * Close the database connection
   */
  close(): void {
    this.db.close();
  }
}
