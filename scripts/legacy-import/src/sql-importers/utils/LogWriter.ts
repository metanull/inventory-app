import { writeFileSync, appendFileSync, mkdirSync } from 'fs';
import { resolve } from 'path';
import chalk from 'chalk';

export class LogWriter {
  private logFilePath: string;
  private startTime: number;

  constructor(logDir: string = 'logs') {
    this.startTime = Date.now();
    const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
    this.logFilePath = resolve(process.cwd(), logDir, `sql-import-${timestamp}.log`);

    // Ensure log directory exists
    mkdirSync(resolve(process.cwd(), logDir), { recursive: true });

    // Initialize log file
    this.writeHeader();
  }

  private writeHeader(): void {
    const header = [
      '='.repeat(80),
      'SQL-BASED LEGACY IMPORT LOG',
      '='.repeat(80),
      `Start time: ${new Date().toISOString()}`,
      `Log file: ${this.logFilePath}`,
      '',
    ].join('\n');

    writeFileSync(this.logFilePath, header + '\n');
  }

  log(message: string): void {
    const timestamp = new Date().toISOString();
    const logLine = `[${timestamp}] ${message}`;
    appendFileSync(this.logFilePath, logLine + '\n');
  }

  logPhaseStart(phaseName: string): void {
    const line = `\n${'='.repeat(80)}\nPHASE: ${phaseName}\n${'='.repeat(80)}`;
    appendFileSync(this.logFilePath, line + '\n');
    console.log(chalk.bold.yellow(`\n📦 ${phaseName}\n`));
  }

  logImporterStart(importerName: string): void {
    const message = `Starting ${importerName}...`;
    this.log(message);
  }

  logImporterComplete(
    importerName: string,
    imported: number,
    skipped: number,
    errors: number,
    duration: number
  ): void {
    const message = `Completed ${importerName}: ${imported} imported, ${skipped} skipped, ${errors} errors (${(duration / 1000).toFixed(2)}s)`;
    this.log(message);

    const status = errors > 0 ? chalk.red('❌') : chalk.green('✅');
    console.log(
      `  ${status} ${importerName}: ${imported} imported, ${skipped} skipped, ${errors} errors (${(duration / 1000).toFixed(2)}s)`
    );
  }

  logError(importerName: string, error: string): void {
    const message = `ERROR in ${importerName}: ${error}`;
    this.log(message);
  }

  logSummary(
    phases: Array<{
      phase: string;
      duration: number;
      imported: number;
      skipped: number;
      errors: number;
    }>
  ): void {
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

    appendFileSync(this.logFilePath, summary + '\n');

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

  getLogFilePath(): string {
    return this.logFilePath;
  }
}
