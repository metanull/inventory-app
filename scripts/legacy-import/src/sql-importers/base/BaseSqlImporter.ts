import type { Connection, RowDataPacket } from 'mysql2/promise';
import chalk from 'chalk';
import type { SampleCollector, SampleReason } from '../../utils/SampleCollector.js';
import type { LogWriter } from '../utils/LogWriter.js';

export interface ImportResult {
  success: boolean;
  imported: number;
  skipped: number;
  errors: string[];
}

export abstract class BaseSqlImporter {
  protected db: Connection;
  protected tracker: Map<string, string>;
  protected now: string;
  protected sampleCollector?: SampleCollector;
  protected logger?: LogWriter;

  constructor(
    db: Connection,
    tracker: Map<string, string>,
    sampleCollector?: SampleCollector,
    logger?: LogWriter
  ) {
    this.db = db;
    this.tracker = tracker;
    this.now = new Date().toISOString().slice(0, 19).replace('T', ' ');
    this.sampleCollector = sampleCollector;
    this.logger = logger;
  }

  abstract getName(): string;
  abstract import(): Promise<ImportResult>;

  protected log(message: string): void {
    const logMessage = `[${this.getName()}] ${message}`;
    console.log(logMessage);
    if (this.logger) {
      this.logger.log(logMessage);
    }
  }

  protected logSuccess(message: string): void {
    const logMessage = `[${this.getName()}] ✅ ${message}`;
    console.log(chalk.green(logMessage));
    if (this.logger) {
      this.logger.log(logMessage);
    }
  }

  protected logWarning(message: string): void {
    const logMessage = `[${this.getName()}] ⚠️  ${message}`;
    console.log(chalk.yellow(logMessage));
    if (this.logger) {
      this.logger.log(logMessage);
    }
  }

  protected logError(message: string, error?: unknown): void {
    console.error(chalk.red(`[${this.getName()}] ❌ ${message}`));
    if (error) {
      console.error(chalk.red(`  ${error instanceof Error ? error.message : String(error)}`));
    }
  }

  protected async exists(table: string, backwardCompat: string): Promise<boolean> {
    const [rows] = await this.db.execute<RowDataPacket[]>(
      `SELECT id FROM ${table} WHERE backward_compatibility = ?`,
      [backwardCompat]
    );
    return rows.length > 0;
  }

  protected async findByBackwardCompat(
    table: string,
    backwardCompat: string
  ): Promise<string | null> {
    if (this.tracker.has(backwardCompat)) {
      return this.tracker.get(backwardCompat)!;
    }

    const [rows] = await this.db.execute<RowDataPacket[]>(
      `SELECT id FROM ${table} WHERE backward_compatibility = ?`,
      [backwardCompat]
    );

    if (rows.length > 0 && rows[0]) {
      const id = rows[0].id as string;
      this.tracker.set(backwardCompat, id);
      return id;
    }

    return null;
  }

  protected formatBackwardCompat(schema: string, table: string, pkValues: string[]): string {
    return `${schema}:${table}:${pkValues.join(':')}`;
  }

  protected showProgress(current: number, total: number): void {
    process.stdout.write(
      `\r  Progress: ${current}/${total} (${Math.round((current / total) * 100)}%)`
    );
  }

  /**
   * Collect a sample record for test fixtures
   * @param entityType - Type of entity (e.g., 'language', 'object', 'monument')
   * @param data - The raw legacy data record
   * @param reason - Why this sample is interesting ('success', 'warning', 'edge')
   * @param details - Additional details about the reason
   * @param language - Language code if applicable
   * @param sourceDb - Source database name
   */
  protected collectSample(
    entityType: string,
    data: Record<string, unknown>,
    reason: SampleReason,
    details?: string,
    language?: string,
    sourceDb?: string
  ): void {
    if (this.sampleCollector) {
      this.sampleCollector.collectSample(entityType, data, reason, details, language, sourceDb);
    }
  }
}
