import { appendFileSync, mkdirSync, writeFileSync } from 'fs';

/**
 * Centralized logging utility
 * - Console: Emojis + colors for user-friendly output
 * - File: Plain text without formatting
 * - Error: stderr with minimal formatting
 */
export class Logger {
  private logPath: string | null = null;

  /**
   * Initialize file logging
   */
  initFile(options: { dryRun: boolean; startAt?: string; stopAt?: string; only?: string }): string {
    const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
    this.logPath = `./logs/import-${timestamp}.log`;

    try {
      mkdirSync('./logs', { recursive: true });
      const header = [
        '='.repeat(60),
        'IMPORT LOG',
        '='.repeat(60),
        `Timestamp: ${new Date().toISOString()}`,
        `Dry-run: ${options.dryRun}`,
        `Start-at: ${options.startAt || 'N/A'}`,
        `Stop-at: ${options.stopAt || 'N/A'}`,
        `Only: ${options.only || 'N/A'}`,
        '',
      ].join('\n');
      writeFileSync(this.logPath, header + '\n', 'utf-8');
    } catch {
      // Continue without file logging
    }

    return this.logPath;
  }

  /**
   * Write to file only (no console)
   */
  private toFile(text: string): void {
    if (!this.logPath) return;
    try {
      appendFileSync(this.logPath, text + '\n', 'utf-8');
    } catch {
      // Ignore
    }
  }

  /**
   * Strip emojis and ANSI colors from text
   */
  private stripFormatting(text: string): string {
    return text
      .replace(/[\u{1F300}-\u{1F9FF}]/gu, '') // Remove emojis
      .replace(/[\u2600-\u27BF]/gu, '') // Remove misc symbols (✓, ✗, etc.)
      .replace(/\x1b\[[0-9;]*m/g, '') // Remove ANSI codes
      .trim();
  }

  /**
   * Console + File
   */
  info(message: string, emoji?: string): void {
    const consoleMsg = emoji ? `${emoji} ${message}` : message;
    console.log(consoleMsg);
    this.toFile(this.stripFormatting(consoleMsg));
  }

  /**
   * Console only (no file)
   */
  console(message: string): void {
    console.log(message);
  }

  /**
   * Importer skipped
   */
  skipped(name: string): void {
    console.log(`${name}: ⏭️  Skipped`);
    this.toFile(`\n${name}: SKIPPED`);
  }

  /**
   * Importer started
   */
  started(name: string): void {
    console.log(`${name}: `);
    this.toFile(`\n${name}:`);
  }

  /**
   * Importer completed
   */
  completed(imported: number, skipped: number, errors: number, errorList?: string[]): void {
    this.toFile(`  Imported: ${imported}`);
    this.toFile(`  Skipped: ${skipped}`);
    if (errors > 0) {
      this.toFile(`  Errors: ${errors}`);
      errorList?.slice(0, 10).forEach((err) => this.toFile(`    - ${err}`));
    }
  }

  /**
   * Error (stderr + file)
   */
  error(message: string, includeEmoji = true): void {
    const msg = includeEmoji ? `❌ ${message}` : message;
    console.error(`\n${msg}\n`);
    this.toFile(`\n${this.stripFormatting(msg)}`);
  }

  /**
   * Summary
   */
  summary(imported: number, skipped: number, errors: number): void {
    const sep = '='.repeat(60);
    const success = errors === 0;

    // Console with colors
    console.log(sep);
    console.log('IMPORT SUMMARY');
    console.log(sep);
    console.log(`  Total Imported: ${imported}`);
    console.log(`  Total Skipped: ${skipped}`);
    console.log(`  Total Errors: ${errors}`);
    console.log(`  Status: ${success ? '\x1b[32m✓ SUCCESS\x1b[0m' : '\x1b[31m✗ FAILED\x1b[0m'}`);
    console.log(sep + '\n');

    // File without colors
    this.toFile('');
    this.toFile(sep);
    this.toFile('FINAL SUMMARY');
    this.toFile(sep);
    this.toFile(`Total Imported: ${imported}`);
    this.toFile(`Total Skipped: ${skipped}`);
    this.toFile(`Total Errors: ${errors}`);
    this.toFile(`Status: ${success ? 'SUCCESS' : 'FAILED'}`);
    this.toFile(sep);
  }

  /**
   * Get log path
   */
  getPath(): string | null {
    return this.logPath;
  }
}
