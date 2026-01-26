/**
 * Base Importer - Abstract base class for all importers
 *
 * This class implements the Template Method pattern, providing a skeleton
 * for the import algorithm while allowing subclasses to override specific steps.
 *
 * Key responsibilities:
 * - Provide common logging infrastructure
 * - Manage import context (tracker, strategy, database)
 * - Define the import result structure
 * - Collect samples for testing
 */

import type { ITracker } from './tracker.js';
import type { IWriteStrategy } from './strategy.js';
import type { ImportResult, EntityType } from './types.js';
import { createImportResult } from './types.js';

/**
 * Sample reason types for test fixture collection
 */
export type SampleReason = 'success' | 'warning' | 'edge' | 'foundation';

/**
 * Sample collector interface for test fixture generation
 */
export interface ISampleCollector {
  collectSample(
    entityType: string,
    data: Record<string, unknown>,
    reason: SampleReason,
    details?: string,
    language?: string,
    sourceDb?: string
  ): void;
}

/**
 * Legacy database interface for reading source data
 */
export interface ILegacyDatabase {
  query<T>(sql: string, params?: unknown[]): Promise<T[]>;
  execute(sql: string, params?: unknown[]): Promise<void>;
  connect(): Promise<void>;
  disconnect(): Promise<void>;
}

/**
 * Import context containing all dependencies needed by importers
 */
export interface ImportContext {
  legacyDb: ILegacyDatabase;
  strategy: IWriteStrategy;
  tracker: ITracker;
  dryRun: boolean;
  sampleCollector?: ISampleCollector;
  sampleOnlyMode?: boolean;
  logPath?: string;
}

/**
 * Logger interface for import operations
 */
export interface ILogger {
  info(message: string): void;
  warning(message: string, details?: unknown): void;
  error(context: string, error: unknown, additionalContext?: Record<string, unknown>): void;
  showProgress(): void;
  showSkipped(): void;
  showError(): void;
  showSummary(imported: number, skipped: number, errors: number): void;
}

/**
 * Simple console logger implementation
 */
export class ConsoleLogger implements ILogger {
  private name: string;

  constructor(name: string) {
    this.name = name;
  }

  info(message: string): void {
    console.log(`[${this.name}] ${message}`);
  }

  warning(message: string, _details?: unknown): void {
    console.log(`[${this.name}] ⚠️  ${message}`);
  }

  error(context: string, error: unknown, _additionalContext?: Record<string, unknown>): void {
    const message = error instanceof Error ? error.message : String(error);
    console.error(`[${this.name}] ❌ ${context}: ${message}`);
  }

  showProgress(): void {
    process.stdout.write('.');
  }

  showSkipped(): void {
    process.stdout.write('s');
  }

  showError(): void {
    process.stdout.write('×');
  }

  showSummary(imported: number, skipped: number, errors: number): void {
    console.log(
      `\n[${this.name}] Summary: ${imported} imported, ${skipped} skipped, ${errors} errors`
    );
  }
}

/**
 * Abstract base class for all importers
 *
 * Subclasses must implement:
 * - getName(): Returns the importer name for logging
 * - import(): Performs the actual import operation
 */
export abstract class BaseImporter {
  protected context: ImportContext;
  protected logger: ILogger;

  constructor(context: ImportContext, logger?: ILogger) {
    this.context = context;
    this.logger = logger || new ConsoleLogger(this.getName());
  }

  /**
   * Get importer name for logging
   */
  abstract getName(): string;

  /**
   * Execute the import process
   */
  abstract import(): Promise<ImportResult>;

  /**
   * Create a new import result
   */
  protected createResult(): ImportResult {
    return createImportResult();
  }

  /**
   * Get the default language ID from tracker
   */
  protected getDefaultLanguageId(): string {
    const defaultLangId = this.context.tracker.getMetadata('default_language_id');
    if (!defaultLangId) {
      throw new Error('Default language ID not found in tracker. Language import must run first.');
    }
    return defaultLangId;
  }

  /**
   * Get the default context ID from tracker
   */
  protected getDefaultContextId(): string {
    const defaultContextId = this.context.tracker.getMetadata('default_context_id');
    if (!defaultContextId) {
      throw new Error(
        'Default context ID not found in tracker. Default context import must run first.'
      );
    }
    return defaultContextId;
  }

  /**
   * Check if we're in dry-run mode
   */
  protected get isDryRun(): boolean {
    return this.context.dryRun;
  }

  /**
   * Check if we're in sample-only mode
   */
  protected get isSampleOnlyMode(): boolean {
    return this.context.sampleOnlyMode === true;
  }

  /**
   * Log info message
   */
  protected logInfo(message: string): void {
    this.logger.info(message);
  }

  /**
   * Log warning message
   */
  protected logWarning(message: string, details?: unknown): void {
    this.logger.warning(message, details);
  }

  /**
   * Log error message
   */
  protected logError(
    context: string,
    error: unknown,
    additionalContext?: Record<string, unknown>
  ): void {
    this.logger.error(context, error, additionalContext);
  }

  /**
   * Show progress indicator
   */
  protected showProgress(): void {
    this.logger.showProgress();
  }

  /**
   * Show skipped indicator
   */
  protected showSkipped(): void {
    this.logger.showSkipped();
  }

  /**
   * Show error indicator
   */
  protected showError(): void {
    this.logger.showError();
  }

  /**
   * Show import summary
   */
  protected showSummary(imported: number, skipped: number, errors: number): void {
    this.logger.showSummary(imported, skipped, errors);
  }

  /**
   * Collect a sample for test fixtures
   */
  protected collectSample(
    entityType: string,
    data: Record<string, unknown>,
    reason: SampleReason,
    details?: string,
    language?: string,
    sourceDb?: string
  ): void {
    if (this.context.sampleCollector) {
      this.context.sampleCollector.collectSample(
        entityType,
        data,
        reason,
        details,
        language,
        sourceDb
      );
    }
  }

  /**
   * Register an imported entity in the tracker
   */
  protected registerEntity(
    uuid: string,
    backwardCompatibility: string,
    entityType: EntityType
  ): void {
    this.context.tracker.register({
      uuid,
      backwardCompatibility,
      entityType,
      createdAt: new Date(),
    });
  }

  /**
   * Map EntityType to database table name for lookup queries
   */
  private entityTypeToTable(entityType: EntityType): string {
    const mapping: Record<EntityType, string> = {
      language: 'languages',
      language_translation: 'language_translations',
      country: 'countries',
      country_translation: 'country_translations',
      context: 'contexts',
      collection: 'collections',
      project: 'projects',
      partner: 'partners',
      partner_translation: 'partner_translations',
      item: 'items',
      item_translation: 'item_translations',
      image: 'item_images',
      tag: 'tags',
      author: 'authors',
      artist: 'artists',
      glossary: 'glossaries',
      glossary_translation: 'glossary_translations',
      glossary_spelling: 'glossary_spellings',
      theme: 'themes',
      theme_translation: 'theme_translations',
      item_item_link: 'item_item_links',
      item_item_link_translation: 'item_item_link_translations',
    };
    return mapping[entityType];
  }

  /**
   * Check if entity already exists in tracker or database
   * First checks tracker (memory), then falls back to database for skipped phases.
   */
  protected entityExists(backwardCompatibility: string, entityType: EntityType): boolean {
    return this.context.tracker.exists(backwardCompatibility, entityType);
  }

  /**
   * Check if entity already exists (async version with database fallback)
   * First checks tracker (memory), then falls back to database for skipped phases.
   */
  protected async entityExistsAsync(
    backwardCompatibility: string,
    entityType: EntityType
  ): Promise<boolean> {
    // Check tracker first (fast path)
    if (this.context.tracker.exists(backwardCompatibility, entityType)) {
      return true;
    }

    // Fall back to database lookup for entities from skipped phases
    const table = this.entityTypeToTable(entityType);
    return this.context.strategy.exists(table, backwardCompatibility);
  }

  /**
   * Get UUID from tracker by backward_compatibility (entityType is required to avoid collisions)
   * @deprecated Use getEntityUuidAsync for proper database fallback when starting from later phases
   */
  protected getEntityUuid(backwardCompatibility: string, entityType: EntityType): string | null {
    return this.context.tracker.getUuid(backwardCompatibility, entityType);
  }

  /**
   * Get UUID from tracker or database by backward_compatibility
   * First checks tracker (memory), then falls back to database for entities from skipped phases.
   * This is essential when starting import from a later phase (e.g., --start-at phase-05).
   */
  protected async getEntityUuidAsync(
    backwardCompatibility: string,
    entityType: EntityType
  ): Promise<string | null> {
    // Check tracker first (fast path)
    const trackerResult = this.context.tracker.getUuid(backwardCompatibility, entityType);
    if (trackerResult) {
      return trackerResult;
    }

    // Fall back to database lookup for entities from skipped phases
    const table = this.entityTypeToTable(entityType);
    const dbResult = await this.context.strategy.findByBackwardCompatibility(
      table,
      backwardCompatibility
    );

    // If found in database, cache in tracker for future lookups
    if (dbResult) {
      this.context.tracker.set(backwardCompatibility, dbResult, entityType);
    }

    return dbResult;
  }
}
