import Database from 'better-sqlite3';
import * as path from 'path';
import * as fs from 'fs';

/**
 * Sample collector for building test fixtures from real import data
 * Collects diverse samples during import for use in automated tests
 */
export interface SampleCollectorConfig {
  enabled: boolean;
  dbPath: string;
  sampleSize: number; // Max samples per category (success cases)
  collectAllWarnings: boolean; // Collect all records that trigger warnings
  collectAllEdgeCases: boolean; // Collect all edge case records
  collectAllFoundation: boolean; // Collect ALL languages, countries (needed for dependencies)
}

export type SampleReason = 'success' | 'warning' | 'edge';

export class SampleCollector {
  private db: Database.Database;
  private collected: Map<string, Set<string>>; // category -> set of JSON stringified records
  private config: SampleCollectorConfig;

  constructor(config: SampleCollectorConfig) {
    this.config = config;
    this.collected = new Map();

    if (!config.enabled) {
      // Create no-op database (disabled mode)
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      this.db = null as any;
      return;
    }

    // Ensure directory exists
    const dir = path.dirname(config.dbPath);
    if (!fs.existsSync(dir)) {
      fs.mkdirSync(dir, { recursive: true });
    }

    // Initialize SQLite database
    this.db = new Database(config.dbPath);
    this.initializeSchema();
  }

  private initializeSchema(): void {
    // Create a SINGLE universal table for ALL entity types
    // Store complete raw JSON - NO constraints, ALL fields preserved
    // This handles multiple databases and avoids table name conflicts
    this.db.exec(`
      CREATE TABLE IF NOT EXISTS legacy_samples (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        entity_type TEXT NOT NULL,  -- 'language', 'country', 'partner', 'object', 'monument', etc.
        source_db TEXT,  -- 'mwnf3', 'discover-islamic-art', etc. (for multi-database support)
        raw_data TEXT NOT NULL,  -- Complete JSON stringified legacy record (ALL fields)
        sample_reason TEXT NOT NULL,  -- 'success', 'warning:missing_name', 'edge:long_field', etc.
        language TEXT,  -- ISO 639-3 language code if applicable
        collected_at TEXT NOT NULL,  -- ISO timestamp
        record_hash TEXT NOT NULL UNIQUE  -- Hash of raw_data to prevent duplicates
      );

      -- Indexes for efficient querying
      CREATE INDEX IF NOT EXISTS idx_samples_entity ON legacy_samples(entity_type);
      CREATE INDEX IF NOT EXISTS idx_samples_source ON legacy_samples(source_db);
      CREATE INDEX IF NOT EXISTS idx_samples_reason ON legacy_samples(sample_reason);
      CREATE INDEX IF NOT EXISTS idx_samples_language ON legacy_samples(language);
      CREATE INDEX IF NOT EXISTS idx_samples_entity_reason ON legacy_samples(entity_type, sample_reason);
    `);
  }

  /**
   * Collect a sample record
   * @param entityType - Type of entity (e.g., 'language', 'country', 'partner', 'object', 'monument')
   * @param data - The raw legacy data record (complete, all fields)
   * @param reason - Why this sample is interesting ('success', 'warning', 'edge')
   * @param details - Additional details about the reason (e.g., 'missing_name', 'long_field')
   * @param language - Language code if applicable
   * @param sourceDb - Source database name (e.g., 'mwnf3', 'discover-islamic-art') for multi-DB support
   */
  collectSample(
    entityType: string,
    data: Record<string, unknown>,
    reason: SampleReason,
    details?: string,
    language?: string,
    sourceDb?: string
  ): void {
    if (!this.config.enabled) return;

    const fullReason = details ? `${reason}:${details}` : reason;
    const category = `${entityType}:${fullReason}`;

    // Check if we should collect this sample
    if (!this.shouldCollect(category, reason)) {
      return;
    }

    // Get or create set for this category
    let collected = this.collected.get(category);
    if (!collected) {
      collected = new Set();
      this.collected.set(category, collected);
    }

    // Serialize complete raw data - preserve ALL fields
    const rawDataJson = JSON.stringify(data);

    // Create hash for deduplication (simple string hash)
    const recordHash = this.simpleHash(rawDataJson);

    // Skip if already collected
    if (collected.has(recordHash)) {
      return;
    }

    // Store in universal table with complete raw data
    try {
      const stmt = this.db.prepare(`
        INSERT OR IGNORE INTO legacy_samples 
        (entity_type, source_db, raw_data, sample_reason, language, collected_at, record_hash)
        VALUES (?, ?, ?, ?, ?, ?, ?)
      `);

      stmt.run(
        entityType,
        sourceDb || 'mwnf3', // Default to mwnf3 for now, will support multiple DBs later
        rawDataJson, // Complete raw data - ALL fields preserved
        fullReason,
        language || null,
        new Date().toISOString(),
        recordHash
      );

      collected.add(recordHash);
    } catch (error) {
      console.warn(`Failed to collect sample for ${entityType}: ${error}`);
    }
  }

  /**
   * Simple hash function for deduplication
   * Based on Java's String.hashCode() algorithm
   */
  private simpleHash(str: string): string {
    let hash = 0;
    for (let i = 0; i < str.length; i++) {
      const char = str.charCodeAt(i);
      hash = (hash << 5) - hash + char;
      hash = hash & hash; // Convert to 32bit integer
    }
    return hash.toString(36); // Base36 for shorter string
  }

  /**
   * Determine if we should collect this sample based on category and limits
   */
  private shouldCollect(category: string, reason: SampleReason): boolean {
    // Always collect warnings and edge cases if configured
    if (reason === 'warning' && this.config.collectAllWarnings) {
      return true;
    }
    if (reason === 'edge' && this.config.collectAllEdgeCases) {
      return true;
    }

    // Foundation data: collect ALL languages and countries (needed for dependencies)
    if (this.config.collectAllFoundation) {
      const entityType = category.split(':')[0]; // Extract entity type from "entity:reason"
      if (
        entityType === 'language' ||
        entityType === 'language_translation' ||
        entityType === 'country' ||
        entityType === 'country_translation'
      ) {
        return true; // Collect all foundation data
      }
    }

    // For success cases, check if we've hit the limit
    if (reason === 'success') {
      const collected = this.collected.get(category);
      if (collected && collected.size >= this.config.sampleSize) {
        return false;
      }

      // Use sampling for success cases to get diversity
      // 20% chance of collecting each success record until limit reached
      return Math.random() < 0.2;
    }

    return true;
  }

  /**
   * Get statistics about collected samples
   * Returns breakdown by entity type and reason
   */
  getStats(): Record<string, number> {
    if (!this.config.enabled) return {};

    const stats: Record<string, number> = {};

    try {
      // Count samples by entity_type and reason
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

      for (const row of rows) {
        stats[`${row.entity_type}:${row.sample_reason}`] = row.count;
      }
    } catch (error) {
      console.warn(`Failed to get stats: ${error}`);
    }

    return stats;
  }

  /**
   * Close the database connection
   */
  close(): void {
    if (this.db && this.config.enabled) {
      this.db.close();
    }
  }

  /**
   * Clear all collected samples (for testing or reset)
   */
  clear(): void {
    if (!this.config.enabled) return;

    try {
      this.db.exec(`DELETE FROM legacy_samples;`);
      this.collected.clear();
    } catch (error) {
      console.warn(`Failed to clear samples: ${error}`);
    }
  }
}
