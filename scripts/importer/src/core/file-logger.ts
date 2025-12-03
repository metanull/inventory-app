/**
 * File Logger - Writes logs to both console and file
 *
 * This logger implementation provides dual output (console + file) for import operations.
 * It follows the same pattern as the legacy importer's LogWriter.
 */

import { writeFile, appendFile, mkdir } from 'fs/promises';
import { resolve } from 'path';
import chalk from 'chalk';
import type { ILogger } from './base-importer.js';

/**
 * Phase result summary for logging
 */
export interface PhaseSummary {
  phase: string;
  duration: number;
  imported: number;
  skipped: number;
  errors: number;
}

/**
 * FileLogger - Writes to both console and file
 */
export class FileLogger implements ILogger {
  private name: string;
  private logFilePath: string;
  private startTime: number;

  constructor(name: string, logDir: string = 'logs') {
    this.name = name;
    this.startTime = Date.now();
    const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
    this.logFilePath = resolve(process.cwd(), logDir, `import-${timestamp}.log`);

    // Initialize log file asynchronously (fire and forget with error handling)
    this.initLogFile(logDir).catch((err) => {
      console.error(`Failed to initialize log file: ${err}`);
    });
  }

  private async initLogFile(logDir: string): Promise<void> {
    try {
      // Ensure log directory exists
      await mkdir(resolve(process.cwd(), logDir), { recursive: true });

      // Write header
      const header = [
        '='.repeat(80),
        'UNIFIED LEGACY IMPORT LOG',
        '='.repeat(80),
        `Start time: ${new Date().toISOString()}`,
        `Log file: ${this.logFilePath}`,
        '',
      ].join('\n');

      await writeFile(this.logFilePath, header + '\n');
    } catch (error) {
      console.error(`Error initializing log file: ${error}`);
    }
  }

  /**
   * Write a line to the log file (async, fire-and-forget with error handling)
   */
  private writeToFile(message: string): void {
    const timestamp = new Date().toISOString();
    const logLine = `[${timestamp}] ${message}`;
    appendFile(this.logFilePath, logLine + '\n').catch((err) => {
      console.error(`Failed to write to log file: ${err}`);
    });
  }

  /**
   * Get the log file path
   */
  getLogFilePath(): string {
    return this.logFilePath;
  }

  /**
   * Get start time for duration calculations
   */
  getStartTime(): number {
    return this.startTime;
  }

  info(message: string): void {
    console.log(`[${this.name}] ${message}`);
    this.writeToFile(`[INFO] [${this.name}] ${message}`);
  }

  warning(message: string, details?: unknown): void {
    const detailsStr = details ? ` (${JSON.stringify(details)})` : '';
    console.log(`[${this.name}] ⚠️  ${message}${detailsStr}`);
    this.writeToFile(`[WARN] [${this.name}] ${message}${detailsStr}`);
  }

  error(context: string, error: unknown, additionalContext?: Record<string, unknown>): void {
    const message = error instanceof Error ? error.message : String(error);
    const contextStr = additionalContext ? ` (${JSON.stringify(additionalContext)})` : '';
    console.error(`[${this.name}] ❌ ${context}: ${message}${contextStr}`);
    this.writeToFile(`[ERROR] [${this.name}] ${context}: ${message}${contextStr}`);
    if (error instanceof Error && error.stack) {
      this.writeToFile(`[STACK] ${error.stack}`);
    }
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
    const summary = `Summary: ${imported} imported, ${skipped} skipped, ${errors} errors`;
    console.log(`\n[${this.name}] ${summary}`);
    this.writeToFile(`[SUMMARY] [${this.name}] ${summary}`);
  }

  // =========================================================================
  // Extended methods for CLI orchestration
  // =========================================================================

  /**
   * Log start of a phase
   */
  logPhaseStart(phaseName: string): void {
    const line = `\n${'='.repeat(80)}\nPHASE: ${phaseName}\n${'='.repeat(80)}`;
    appendFile(this.logFilePath, line + '\n').catch((err) => {
      console.error(`Failed to write phase start to log: ${err}`);
    });
    console.log(chalk.bold.yellow(`\n📦 ${phaseName}\n`));
  }

  /**
   * Log start of an importer
   */
  logImporterStart(importerName: string): void {
    const message = `Starting ${importerName}...`;
    this.writeToFile(message);
  }

  /**
   * Log completion of an importer
   */
  logImporterComplete(
    importerName: string,
    imported: number,
    skipped: number,
    errors: number,
    duration: number
  ): void {
    const message = `Completed ${importerName}: ${imported} imported, ${skipped} skipped, ${errors} errors (${(duration / 1000).toFixed(2)}s)`;
    this.writeToFile(message);

    const status = errors > 0 ? chalk.red('❌') : chalk.green('✅');
    console.log(
      `  ${status} ${importerName}: ${imported} imported, ${skipped} skipped, ${errors} errors (${(duration / 1000).toFixed(2)}s)`
    );
  }

  /**
   * Log an error with importer context
   */
  logImporterError(importerName: string, errorMessage: string): void {
    const message = `ERROR in ${importerName}: ${errorMessage}`;
    this.writeToFile(message);
  }

  /**
   * Log final summary of all phases
   */
  logFinalSummary(phases: PhaseSummary[]): void {
    const totalDuration = Date.now() - this.startTime;
    const totalImported = phases.reduce((sum, p) => sum + p.imported, 0);
    const totalSkipped = phases.reduce((sum, p) => sum + p.skipped, 0);
    const totalErrors = phases.reduce((sum, p) => sum + p.errors, 0);

    const summary = [
      '',
      '='.repeat(80),
      'IMPORT SUMMARY',
      '='.repeat(80),
      '',
      ...phases.map(
        (p) =>
          `${p.phase}:\n  Imported: ${p.imported}, Skipped: ${p.skipped}, Errors: ${p.errors}, Duration: ${(p.duration / 1000).toFixed(2)}s`
      ),
      '',
      `TOTALS: ${totalImported} imported, ${totalSkipped} skipped, ${totalErrors} errors`,
      `Total duration: ${(totalDuration / 1000).toFixed(2)}s`,
      `End time: ${new Date().toISOString()}`,
      '='.repeat(80),
    ].join('\n');

    appendFile(this.logFilePath, summary + '\n').catch((err) => {
      console.error(`Failed to write final summary to log: ${err}`);
    });

    // Console summary
    console.log(chalk.bold.yellow('\n📊 IMPORT SUMMARY\n'));
    console.log(
      chalk.bold.green(
        `✅ Total: ${totalImported} imported, ${totalSkipped} skipped, ${totalErrors} errors`
      )
    );
    console.log(chalk.gray(`Total duration: ${(totalDuration / 1000).toFixed(2)}s`));
    console.log(chalk.gray(`End time: ${new Date().toISOString()}`));
    console.log(chalk.gray(`Log file: ${this.logFilePath}`));
  }
}
